<?php

namespace Jiny\Store\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'store_inventory';

    protected $fillable = [
        'product_id',
        'location',
        'quantity_on_hand',
        'quantity_reserved',
        'reorder_point',
        'reorder_quantity',
        'last_cost',
        'last_updated_at',
        'last_updated_by',
        'notes'
    ];

    protected $casts = [
        'quantity_on_hand' => 'integer',
        'quantity_reserved' => 'integer',
        'reorder_point' => 'integer',
        'reorder_quantity' => 'integer',
        'last_cost' => 'decimal:2',
        'last_updated_at' => 'datetime'
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Accessor for virtual column
    public function getQuantityAvailableAttribute()
    {
        return $this->quantity_on_hand - $this->quantity_reserved;
    }

    // Scopes
    public function scopeByLocation($query, $location)
    {
        return $query->where('location', $location);
    }

    public function scopeLowStock($query)
    {
        return $query->whereRaw('quantity_on_hand <= reorder_point');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('quantity_on_hand', '<=', 0);
    }

    // Helper methods
    public function isLowStock()
    {
        return $this->quantity_on_hand <= $this->reorder_point;
    }

    public function isOutOfStock()
    {
        return $this->quantity_on_hand <= 0;
    }

    public function needsReorder()
    {
        return $this->isLowStock() && $this->reorder_quantity > 0;
    }

    public function addStock($quantity, $cost = null, $updatedBy = null)
    {
        $this->increment('quantity_on_hand', $quantity);

        if ($cost !== null) {
            $this->last_cost = $cost;
        }

        $this->last_updated_at = now();
        $this->last_updated_by = $updatedBy;
        $this->save();

        return $this;
    }

    public function removeStock($quantity, $updatedBy = null)
    {
        if ($quantity > $this->quantity_on_hand) {
            throw new \InvalidArgumentException('Cannot remove more stock than available');
        }

        $this->decrement('quantity_on_hand', $quantity);
        $this->last_updated_at = now();
        $this->last_updated_by = $updatedBy;
        $this->save();

        return $this;
    }

    public function reserveStock($quantity)
    {
        if ($quantity > $this->quantity_available) {
            throw new \InvalidArgumentException('Cannot reserve more stock than available');
        }

        $this->increment('quantity_reserved', $quantity);
        return $this;
    }

    public function releaseReservation($quantity)
    {
        $releaseAmount = min($quantity, $this->quantity_reserved);
        $this->decrement('quantity_reserved', $releaseAmount);
        return $this;
    }

    public function fulfillReservation($quantity)
    {
        if ($quantity > $this->quantity_reserved) {
            throw new \InvalidArgumentException('Cannot fulfill more than reserved');
        }

        $this->decrement('quantity_on_hand', $quantity);
        $this->decrement('quantity_reserved', $quantity);
        return $this;
    }
}
