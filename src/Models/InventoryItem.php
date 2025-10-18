<?php

namespace Jiny\Store\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryItem extends Model
{
    use HasFactory;

    protected $table = 'store_inventory_items';

    protected $fillable = [
        'product_id',
        'location',
        'quantity_on_hand',
        'quantity_allocated',
        'quantity_available',
        'reorder_point',
        'reorder_quantity',
        'unit_cost',
        'last_received_at',
        'last_sold_at',
        'notes'
    ];

    protected $casts = [
        'quantity_on_hand' => 'integer',
        'quantity_allocated' => 'integer',
        'quantity_available' => 'integer',
        'reorder_point' => 'integer',
        'reorder_quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'last_received_at' => 'date',
        'last_sold_at' => 'date'
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function stockAlerts()
    {
        return $this->hasMany(StockAlert::class);
    }

    // Helper methods
    public function getAvailableQuantity()
    {
        return $this->quantity_on_hand - $this->quantity_allocated;
    }

    public function isLowStock()
    {
        return $this->quantity_on_hand <= $this->reorder_point;
    }

    public function isOutOfStock()
    {
        return $this->quantity_on_hand <= 0;
    }

    public function allocateStock($quantity)
    {
        if ($this->getAvailableQuantity() < $quantity) {
            throw new \Exception('Insufficient available stock for allocation');
        }

        $this->increment('quantity_allocated', $quantity);
        $this->updateAvailableQuantity();
    }

    public function deallocateStock($quantity)
    {
        $deallocationAmount = min($quantity, $this->quantity_allocated);
        $this->decrement('quantity_allocated', $deallocationAmount);
        $this->updateAvailableQuantity();
    }

    public function recordTransaction($type, $reason, $quantity, $unitCost = null, $attributes = [])
    {
        if ($type === 'out' && $this->quantity_on_hand < $quantity) {
            throw new \Exception('Insufficient stock');
        }

        $previousQuantity = $this->quantity_on_hand;
        $newQuantity = $type === 'in' ? $previousQuantity + $quantity : $previousQuantity - $quantity;

        // Create transaction record
        $transaction = $this->transactions()->create(array_merge([
            'product_id' => $this->product_id,
            'type' => $type,
            'reason' => $reason,
            'quantity' => $quantity,
            'previous_quantity' => $previousQuantity,
            'new_quantity' => $newQuantity,
            'unit_cost' => $unitCost,
            'total_cost' => $unitCost ? $unitCost * $quantity : null,
            'created_by' => auth()->id()
        ], $attributes));

        // Update inventory quantity
        $this->update([
            'quantity_on_hand' => $newQuantity,
            'last_received_at' => $type === 'in' ? now()->toDateString() : $this->last_received_at,
            'last_sold_at' => $type === 'out' ? now()->toDateString() : $this->last_sold_at
        ]);

        $this->updateAvailableQuantity();
        $this->checkAndCreateAlerts();

        return $transaction;
    }

    public function updateAvailableQuantity()
    {
        $this->update([
            'quantity_available' => $this->quantity_on_hand - $this->quantity_allocated
        ]);
    }

    public function checkAndCreateAlerts()
    {
        // Check for out of stock
        if ($this->isOutOfStock()) {
            $this->createStockAlert('out_of_stock', 'critical', 'Out of Stock', 'Product is out of stock');
        }
        // Check for low stock
        elseif ($this->isLowStock()) {
            $this->createStockAlert('low_stock', 'medium', 'Low Stock Warning', 'Product is running low on stock');
        }
    }

    protected function createStockAlert($type, $severity, $title, $message)
    {
        // Don't create duplicate active alerts
        $existingAlert = $this->stockAlerts()
            ->where('type', $type)
            ->where('status', 'active')
            ->first();

        if ($existingAlert) {
            return $existingAlert;
        }

        return $this->stockAlerts()->create([
            'product_id' => $this->product_id,
            'type' => $type,
            'severity' => $severity,
            'title' => $title,
            'message' => $message,
            'current_quantity' => $this->quantity_on_hand,
            'threshold_quantity' => $this->reorder_point
        ]);
    }

    // Static methods
    public static function getInventoryStatistics()
    {
        $totalProducts = static::count();
        $totalValue = static::sum(\DB::raw('quantity_on_hand * unit_cost'));
        $lowStockCount = static::whereRaw('quantity_on_hand <= reorder_point')->count();
        $outOfStockCount = static::where('quantity_on_hand', '<=', 0)->count();

        return [
            'total_products' => $totalProducts,
            'total_value' => $totalValue,
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount
        ];
    }

    // Scopes
    public function scopeLowStock($query)
    {
        return $query->whereRaw('quantity_on_hand <= reorder_point');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('quantity_on_hand', '<=', 0);
    }

    public function scopeInStock($query)
    {
        return $query->where('quantity_on_hand', '>', 0);
    }
}