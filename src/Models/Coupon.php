<?php

namespace Jiny\Store\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Jiny\Store\Traits\HasAssignments;
use Carbon\Carbon;

class Coupon extends Model
{
    use HasAssignments;

    protected $table = 'store_coupons';

    protected $fillable = [
        'name',
        'code',
        'description',
        'type',
        'value',
        'minimum_amount',
        'maximum_discount',
        'usage_limit',
        'usage_limit_per_user',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
        'conditions',
        'metadata',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'minimum_amount' => 'decimal:2',
        'maximum_discount' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'conditions' => 'array',
        'metadata' => 'array',
    ];

    /**
     * 쿠폰 사용 내역 관계
     */
    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    /**
     * 쿠폰 배포 관계
     */
    public function distributions(): HasMany
    {
        return $this->hasMany(CouponDistribution::class);
    }

    /**
     * 개인화 쿠폰 관계
     */
    public function personalCoupons(): HasMany
    {
        return $this->hasMany(PersonalCoupon::class);
    }

    /**
     * 쿠폰 카테고리 관계
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(CouponCategory::class, 'coupon_category_relations');
    }

    /**
     * 활성 상태 스코프
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 유효한 쿠폰 스코프
     */
    public function scopeValid($query)
    {
        $now = now();
        return $query->where('is_active', true)
                     ->where(function($q) use ($now) {
                         $q->whereNull('starts_at')
                           ->orWhere('starts_at', '<=', $now);
                     })
                     ->where(function($q) use ($now) {
                         $q->whereNull('expires_at')
                           ->orWhere('expires_at', '>=', $now);
                     });
    }

    /**
     * 코드로 쿠폰 찾기
     */
    public function scopeByCode($query, $code)
    {
        return $query->where('code', $code);
    }

    /**
     * 타입별 스코프
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * 만료 예정 스코프
     */
    public function scopeExpiringSoon($query, $days = 7)
    {
        return $query->where('expires_at', '>=', now())
                     ->where('expires_at', '<=', now()->addDays($days));
    }

    /**
     * 쿠폰 유효성 검사
     */
    public function isValid(): bool
    {
        // 활성 상태 확인
        if (!$this->is_active) {
            return false;
        }

        // 시작일 확인
        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        // 만료일 확인
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        // 사용 제한 확인
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * 특정 사용자가 사용 가능한지 확인
     */
    public function isAvailableForUser($userId): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        // 사용자별 사용 제한 확인
        if ($this->usage_limit_per_user) {
            $userUsageCount = $this->usages()
                                   ->where('user_id', $userId)
                                   ->count();

            if ($userUsageCount >= $this->usage_limit_per_user) {
                return false;
            }
        }

        return true;
    }

    /**
     * 할인 금액 계산
     */
    public function calculateDiscount($orderData): float
    {
        switch ($this->type) {
            case 'fixed':
                return min($this->value, $orderData['total']);

            case 'percentage':
                $discount = ($orderData['total'] * $this->value) / 100;
                return $this->maximum_discount
                    ? min($discount, $this->maximum_discount)
                    : $discount;

            case 'free_shipping':
                return $orderData['shipping_cost'] ?? 0;

            case 'buy_x_get_y':
                return $this->calculateBuyXGetYDiscount($orderData);

            default:
                return 0;
        }
    }

    /**
     * 쿠폰 사용 처리
     */
    public function use($userId, $orderData)
    {
        $discountAmount = $this->calculateDiscount($orderData);

        // 사용 내역 생성
        $usage = $this->usages()->create([
            'user_id' => $userId,
            'order_id' => $orderData['order_id'] ?? null,
            'discount_amount' => $discountAmount,
            'order_total' => $orderData['total'],
            'applied_items' => $orderData['items'] ?? [],
            'used_at' => now(),
        ]);

        // 사용 횟수 증가
        $this->increment('used_count');

        return $usage;
    }

    /**
     * 남은 사용 횟수
     */
    public function getRemainingUsage(): ?int
    {
        if (!$this->usage_limit) {
            return null;
        }

        return max(0, $this->usage_limit - $this->used_count);
    }

    /**
     * 사용률 계산
     */
    public function getUsageRate(): float
    {
        if (!$this->usage_limit) {
            return 0;
        }

        return ($this->used_count / $this->usage_limit) * 100;
    }
}