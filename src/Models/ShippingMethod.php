<?php

namespace Jiny\Store\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Jiny\Store\Traits\HasAssignments;

class ShippingMethod extends Model
{
    use HasAssignments;

    protected $table = 'store_shipping_methods';

    protected $fillable = [
        'name',
        'code',
        'description',
        'type',
        'base_cost',
        'min_order_amount',
        'max_order_amount',
        'max_weight',
        'estimated_days_min',
        'estimated_days_max',
        'requires_signature',
        'is_active',
        'options',
        'sort_order',
    ];

    protected $casts = [
        'base_cost' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_order_amount' => 'decimal:2',
        'max_weight' => 'decimal:2',
        'requires_signature' => 'boolean',
        'is_active' => 'boolean',
        'options' => 'array',
    ];

    /**
     * 배송 요금 관계
     */
    public function rates(): HasMany
    {
        return $this->hasMany(ShippingRate::class, 'method_id');
    }

    /**
     * 배송 추적 관계
     */
    public function trackings(): HasMany
    {
        return $this->hasMany(ShippingTracking::class, 'method_id');
    }

    /**
     * 활성 상태 스코프
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 정렬 순서별 스코프
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * 타입별 스코프
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * 주문에 적용 가능한지 확인
     */
    public function isAvailableFor($orderData): bool
    {
        // 주문 금액 확인
        if ($this->min_order_amount && $orderData['total'] < $this->min_order_amount) {
            return false;
        }

        if ($this->max_order_amount && $orderData['total'] > $this->max_order_amount) {
            return false;
        }

        // 무게 확인
        if ($this->max_weight && isset($orderData['weight']) && $orderData['weight'] > $this->max_weight) {
            return false;
        }

        return $this->is_active;
    }

    /**
     * 특정 지역과 주문에 대한 배송비 계산
     */
    public function calculateCost($zoneId, $orderData): float
    {
        $rate = $this->rates()
                     ->where('zone_id', $zoneId)
                     ->where('is_active', true)
                     ->first();

        if (!$rate) {
            return $this->base_cost;
        }

        $cost = $rate->cost;

        // 타입별 계산
        switch ($this->type) {
            case 'weight_based':
                if (isset($orderData['weight']) && $orderData['weight'] > $rate->max_value) {
                    $extraWeight = $orderData['weight'] - $rate->max_value;
                    $cost += $extraWeight * $rate->additional_cost;
                }
                break;

            case 'price_based':
                if ($orderData['total'] > $rate->max_value) {
                    $extraAmount = $orderData['total'] - $rate->max_value;
                    $cost += ($extraAmount / 100) * $rate->additional_cost;
                }
                break;

            case 'free':
                if ($orderData['total'] >= $this->min_order_amount) {
                    $cost = 0;
                }
                break;
        }

        return $cost;
    }

    /**
     * 예상 배송일 가져오기
     */
    public function getEstimatedDelivery(): string
    {
        if ($this->estimated_days_min && $this->estimated_days_max) {
            if ($this->estimated_days_min == $this->estimated_days_max) {
                return $this->estimated_days_min . '일';
            }
            return $this->estimated_days_min . '-' . $this->estimated_days_max . '일';
        }

        if ($this->estimated_days_min) {
            return $this->estimated_days_min . '일 이상';
        }

        return '배송일 미정';
    }

    /**
     * 추적 번호 생성
     */
    public function generateTrackingNumber(): string
    {
        $prefix = strtoupper(substr($this->code, 0, 3));
        $timestamp = time();
        $random = mt_rand(1000, 9999);

        return $prefix . $timestamp . $random;
    }
}