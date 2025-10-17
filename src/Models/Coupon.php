<?php

namespace Jiny\Store\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'type',
        'value',
        'minimum_order_amount',
        'maximum_discount_amount',
        'usage_limit',
        'usage_limit_per_customer',
        'times_used',
        'applicable_products',
        'applicable_categories',
        'excluded_products',
        'stackable',
        'auto_apply',
        'starts_at',
        'expires_at',
        'status',
        'created_by'
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'minimum_order_amount' => 'decimal:2',
        'maximum_discount_amount' => 'decimal:2',
        'usage_limit' => 'integer',
        'usage_limit_per_customer' => 'integer',
        'times_used' => 'integer',
        'applicable_products' => 'array',
        'applicable_categories' => 'array',
        'excluded_products' => 'array',
        'stackable' => 'boolean',
        'auto_apply' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime'
    ];

    // Relationships
    public function usage()
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('starts_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }

    public function scopeByCode($query, $code)
    {
        return $query->where('code', $code);
    }

    public function scopeAutoApply($query)
    {
        return $query->where('auto_apply', true);
    }

    public function scopeStackable($query)
    {
        return $query->where('stackable', true);
    }

    // Helper methods
    public function isActive()
    {
        return $this->status === 'active' &&
               $this->starts_at <= now() &&
               ($this->expires_at === null || $this->expires_at > now());
    }

    public function isExpired()
    {
        return $this->expires_at !== null && $this->expires_at <= now();
    }

    public function hasUsageLeft()
    {
        if ($this->usage_limit === null) {
            return true;
        }

        return $this->times_used < $this->usage_limit;
    }

    public function canBeUsedBy($userId = null, $sessionId = null)
    {
        if (!$this->isActive() || !$this->hasUsageLeft()) {
            return false;
        }

        if ($this->usage_limit_per_customer !== null) {
            $query = CouponUsage::where('coupon_id', $this->id);

            if ($userId) {
                $query->where('user_id', $userId);
            } elseif ($sessionId) {
                $query->where('session_id', $sessionId);
            } else {
                return true; // No tracking for guest without session
            }

            $usageCount = $query->count();
            return $usageCount < $this->usage_limit_per_customer;
        }

        return true;
    }

    public function calculateDiscount($orderAmount, $items = [])
    {
        if (!$this->isActive() || $orderAmount < ($this->minimum_order_amount ?? 0)) {
            return 0;
        }

        $discount = 0;

        switch ($this->type) {
            case 'percentage':
                $discount = $orderAmount * ($this->value / 100);
                break;

            case 'fixed_amount':
                $discount = $this->value;
                break;

            case 'free_shipping':
                // This would need to be handled in the order calculation logic
                $discount = 0;
                break;

            case 'buy_x_get_y':
                // This would need more complex logic based on items
                $discount = $this->calculateBuyXGetYDiscount($items);
                break;
        }

        // Apply maximum discount limit
        if ($this->maximum_discount_amount !== null) {
            $discount = min($discount, $this->maximum_discount_amount);
        }

        return round($discount, 2);
    }

    protected function calculateBuyXGetYDiscount($items)
    {
        // Simplified buy X get Y logic
        // This would need to be customized based on specific coupon rules
        return 0;
    }

    public function recordUsage($userId, $orderAmount, $discountAmount, $customerEmail, $orderId = null, $sessionId = null, $orderItems = null)
    {
        // Create usage record
        CouponUsage::create([
            'coupon_id' => $this->id,
            'user_id' => $userId,
            'order_id' => $orderId,
            'session_id' => $sessionId,
            'discount_amount' => $discountAmount,
            'order_amount' => $orderAmount,
            'customer_email' => $customerEmail,
            'order_items' => $orderItems,
            'used_at' => now()
        ]);

        // Increment usage count
        $this->increment('times_used');

        // Auto-expire if usage limit reached
        if ($this->usage_limit !== null && $this->times_used >= $this->usage_limit) {
            $this->update(['status' => 'expired']);
        }

        return $this;
    }

    public static function getAutoApplicableCoupons($orderAmount, $userId = null, $sessionId = null)
    {
        return static::active()
            ->autoApply()
            ->get()
            ->filter(function ($coupon) use ($orderAmount, $userId, $sessionId) {
                return $coupon->canBeUsedBy($userId, $sessionId) &&
                       $coupon->calculateDiscount($orderAmount) > 0;
            });
    }

    public function getFormattedValueAttribute()
    {
        switch ($this->type) {
            case 'percentage':
                return $this->value . '%';
            case 'fixed_amount':
                return '₩' . number_format($this->value);
            case 'free_shipping':
                return '무료배송';
            default:
                return $this->value;
        }
    }

    public function getStatusBadgeAttribute()
    {
        if ($this->status === 'active') {
            if ($this->isExpired()) {
                return '<span class="badge bg-warning">만료됨</span>';
            }
            if (!$this->hasUsageLeft()) {
                return '<span class="badge bg-secondary">사용완료</span>';
            }
            return '<span class="badge bg-success">활성</span>';
        }

        return '<span class="badge bg-secondary">' . ucfirst($this->status) . '</span>';
    }
}
