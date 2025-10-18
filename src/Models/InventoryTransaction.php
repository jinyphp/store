<?php

namespace Jiny\Store\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $table = 'store_inventory_transactions';

    protected $fillable = [
        'product_id',
        'inventory_item_id',
        'type',
        'reason',
        'quantity',
        'previous_quantity',
        'new_quantity',
        'unit_cost',
        'total_cost',
        'reference_type',
        'reference_id',
        'batch_number',
        'expiry_date',
        'supplier',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'previous_quantity' => 'integer',
        'new_quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'expiry_date' => 'date'
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Polymorphic relationship for reference (Order, Purchase, etc.)
    public function reference()
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByReason($query, $reason)
    {
        return $query->where('reason', $reason);
    }

    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeInbound($query)
    {
        return $query->where('type', 'in');
    }

    public function scopeOutbound($query)
    {
        return $query->where('type', 'out');
    }

    public function scopeAdjustments($query)
    {
        return $query->where('type', 'adjustment');
    }

    public function scopeTransfers($query)
    {
        return $query->where('type', 'transfer');
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Helper methods
    public function getTypeDisplayAttribute()
    {
        return match($this->type) {
            'in' => '입고',
            'out' => '출고',
            'adjustment' => '조정',
            'transfer' => '이동',
            default => $this->type
        };
    }

    public function getReasonDisplayAttribute()
    {
        return match($this->reason) {
            'purchase' => '구매',
            'sale' => '판매',
            'return' => '반품',
            'damage' => '손상',
            'theft' => '도난',
            'adjustment' => '조정',
            'transfer' => '이동',
            'promotion' => '프로모션',
            'sample' => '샘플',
            'expired' => '유효기간 만료',
            'order_fulfillment' => '주문 처리',
            default => $this->reason
        };
    }

    public function isInbound()
    {
        return $this->type === 'in';
    }

    public function isOutbound()
    {
        return $this->type === 'out';
    }

    public function isAdjustment()
    {
        return $this->type === 'adjustment';
    }

    public function isTransfer()
    {
        return $this->type === 'transfer';
    }

    // Static methods for analytics
    public static function getTransactionSummary($startDate = null, $endDate = null)
    {
        $query = static::query();

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return [
            'total_transactions' => $query->count(),
            'inbound_transactions' => $query->clone()->where('type', 'in')->count(),
            'outbound_transactions' => $query->clone()->where('type', 'out')->count(),
            'adjustment_transactions' => $query->clone()->where('type', 'adjustment')->count(),
            'transfer_transactions' => $query->clone()->where('type', 'transfer')->count(),
            'total_value_in' => $query->clone()->where('type', 'in')->sum('total_cost'),
            'total_value_out' => $query->clone()->where('type', 'out')->sum('total_cost'),
        ];
    }

    public static function getRecentActivity($limit = 10)
    {
        return static::with(['product', 'inventoryItem', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public static function getTopMovingProducts($days = 30, $limit = 10)
    {
        return static::with('product')
            ->where('created_at', '>=', now()->subDays($days))
            ->where('type', 'out')
            ->selectRaw('product_id, SUM(quantity) as total_quantity')
            ->groupBy('product_id')
            ->orderBy('total_quantity', 'desc')
            ->limit($limit)
            ->get();
    }
}