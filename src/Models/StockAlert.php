<?php

namespace Jiny\Store\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockAlert extends Model
{
    use HasFactory;

    protected $table = 'store_stock_alerts';

    protected $fillable = [
        'product_id',
        'inventory_item_id',
        'type',
        'severity',
        'title',
        'message',
        'current_quantity',
        'threshold_quantity',
        'expiry_date',
        'status',
        'acknowledged_at',
        'acknowledged_by',
        'resolved_at',
        'resolved_by',
        'resolution_notes'
    ];

    protected $casts = [
        'current_quantity' => 'integer',
        'threshold_quantity' => 'integer',
        'expiry_date' => 'date',
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime'
    ];

    protected $attributes = [
        'status' => 'active'
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

    public function acknowledgedBy()
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeAcknowledged($query)
    {
        return $query->where('status', 'acknowledged');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    public function scopeHigh($query)
    {
        return $query->where('severity', 'high');
    }

    public function scopeMedium($query)
    {
        return $query->where('severity', 'medium');
    }

    public function scopeLow($query)
    {
        return $query->where('severity', 'low');
    }

    public function scopeLowStock($query)
    {
        return $query->where('type', 'low_stock');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('type', 'out_of_stock');
    }

    public function scopeOverstock($query)
    {
        return $query->where('type', 'overstock');
    }

    public function scopeExpiryWarning($query)
    {
        return $query->where('type', 'expiry_warning');
    }

    // Helper methods
    public function acknowledge($userId, $notes = null)
    {
        $this->update([
            'status' => 'acknowledged',
            'acknowledged_at' => now(),
            'acknowledged_by' => $userId,
            'resolution_notes' => $notes
        ]);
    }

    public function resolve($userId, $notes = null)
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolved_by' => $userId,
            'resolution_notes' => $notes
        ]);
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isAcknowledged()
    {
        return $this->status === 'acknowledged';
    }

    public function isResolved()
    {
        return $this->status === 'resolved';
    }

    public function isCritical()
    {
        return $this->severity === 'critical';
    }

    public function isHigh()
    {
        return $this->severity === 'high';
    }

    public function isMedium()
    {
        return $this->severity === 'medium';
    }

    public function isLow()
    {
        return $this->severity === 'low';
    }

    public function getTypeDisplayAttribute()
    {
        return match($this->type) {
            'low_stock' => '재고 부족',
            'out_of_stock' => '품절',
            'overstock' => '과재고',
            'expiry_warning' => '유효기간 경고',
            default => $this->type
        };
    }

    public function getSeverityDisplayAttribute()
    {
        return match($this->severity) {
            'critical' => '긴급',
            'high' => '높음',
            'medium' => '보통',
            'low' => '낮음',
            default => $this->severity
        };
    }

    public function getSeverityColorAttribute()
    {
        return match($this->severity) {
            'critical' => 'danger',
            'high' => 'warning',
            'medium' => 'info',
            'low' => 'secondary',
            default => 'secondary'
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'active' => 'danger',
            'acknowledged' => 'warning',
            'resolved' => 'success',
            default => 'secondary'
        };
    }

    public function getStatusDisplayAttribute()
    {
        return match($this->status) {
            'active' => '활성',
            'acknowledged' => '확인됨',
            'resolved' => '해결됨',
            default => $this->status
        };
    }

    // Static methods
    public static function getActiveAlertsCount()
    {
        return static::active()->count();
    }

    public static function getCriticalAlertsCount()
    {
        return static::active()->critical()->count();
    }

    public static function getAlertsSummary()
    {
        return [
            'total_active' => static::active()->count(),
            'critical' => static::active()->critical()->count(),
            'high' => static::active()->high()->count(),
            'medium' => static::active()->medium()->count(),
            'low' => static::active()->low()->count(),
            'low_stock' => static::active()->lowStock()->count(),
            'out_of_stock' => static::active()->outOfStock()->count(),
            'overstock' => static::active()->overstock()->count(),
            'expiry_warning' => static::active()->expiryWarning()->count(),
        ];
    }

    public static function getRecentAlerts($limit = 10)
    {
        return static::with(['product', 'inventoryItem'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public static function getUnresolvedCriticalAlerts()
    {
        return static::with(['product', 'inventoryItem'])
            ->active()
            ->critical()
            ->orderBy('created_at', 'asc')
            ->get();
    }

    // Auto-check and create alerts for all products
    public static function checkAllProductsForAlerts()
    {
        $inventoryItems = InventoryItem::with('product')->get();

        foreach ($inventoryItems as $item) {
            $item->checkAndCreateAlerts();
        }
    }
}