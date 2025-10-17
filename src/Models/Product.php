<?php

namespace Jiny\Store\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'description',
        'price',
        'cost',
        'category',
        'images',
        'specifications',
        'weight',
        'dimensions',
        'status',
        'track_inventory',
        'minimum_stock'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'weight' => 'decimal:2',
        'images' => 'array',
        'specifications' => 'array',
        'track_inventory' => 'boolean',
        'minimum_stock' => 'integer'
    ];

    // Relationships
    public function inventoryItem()
    {
        return $this->hasOne(InventoryItem::class);
    }

    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function stockAlerts()
    {
        return $this->hasMany(StockAlert::class);
    }

    // Legacy relationships for backward compatibility
    public function inventory()
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function mainInventory()
    {
        return $this->hasOne(InventoryItem::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeLowStock($query)
    {
        return $query->whereHas('inventory', function ($q) {
            $q->whereRaw('quantity_on_hand <= minimum_stock');
        });
    }

    // Helper methods
    public function getTotalInventoryAttribute()
    {
        return $this->inventory->sum('quantity_on_hand');
    }

    public function getAvailableInventoryAttribute()
    {
        return $this->inventory->sum('quantity_available');
    }

    public function isInStock($quantity = 1, $location = 'main_warehouse')
    {
        $inventory = $this->inventory()
            ->where('location', $location)
            ->first();

        if (!$inventory) {
            return false;
        }

        return $inventory->quantity_available >= $quantity;
    }

    public function reserveStock($quantity, $location = 'main_warehouse')
    {
        $inventory = $this->inventory()
            ->where('location', $location)
            ->first();

        if (!$inventory || $inventory->quantity_available < $quantity) {
            return false;
        }

        $inventory->increment('quantity_reserved', $quantity);
        return true;
    }

    public function releaseStock($quantity, $location = 'main_warehouse')
    {
        $inventory = $this->inventory()
            ->where('location', $location)
            ->first();

        if (!$inventory) {
            return false;
        }

        $inventory->decrement('quantity_reserved', min($quantity, $inventory->quantity_reserved));
        return true;
    }

    public function consumeStock($quantity, $location = 'main_warehouse')
    {
        $inventory = $this->inventory()
            ->where('location', $location)
            ->first();

        if (!$inventory || $inventory->quantity_on_hand < $quantity) {
            return false;
        }

        $inventory->decrement('quantity_on_hand', $quantity);
        $inventory->decrement('quantity_reserved', min($quantity, $inventory->quantity_reserved));

        return true;
    }
}
