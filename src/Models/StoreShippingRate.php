<?php

namespace Jiny\Store\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StoreShippingRate extends Model
{
    use HasFactory;

    protected $table = 'store_shipping_rates';

    protected $fillable = [
        'zone_id',
        'method_id',
        'min_value',
        'max_value',
        'cost',
        'additional_cost',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'min_value' => 'decimal:2',
        'max_value' => 'decimal:2',
        'cost' => 'decimal:2',
        'additional_cost' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * 배송 지역 관계
     */
    public function zone()
    {
        return $this->belongsTo(ShippingZone::class, 'zone_id');
    }

    /**
     * 배송 방법 관계
     */
    public function method()
    {
        return $this->belongsTo(ShippingMethod::class, 'method_id');
    }

    /**
     * 활성화된 요금만 조회
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 정렬 순서대로 조회
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('min_value');
    }

    /**
     * 지역별 조회
     */
    public function scopeForZone($query, $zoneId)
    {
        return $query->where('zone_id', $zoneId);
    }

    /**
     * 배송 방법별 조회
     */
    public function scopeForMethod($query, $methodId)
    {
        return $query->where('method_id', $methodId);
    }

    /**
     * 값 범위에 해당하는 요금 조회
     */
    public function scopeForValue($query, $value)
    {
        return $query->where(function($q) use ($value) {
            $q->where('min_value', '<=', $value)
              ->where(function($sq) use ($value) {
                  $sq->whereNull('max_value')
                    ->orWhere('max_value', '>=', $value);
              });
        });
    }

    /**
     * 특정 값에 대한 배송비 계산
     */
    public function calculateCost($value)
    {
        $cost = $this->cost;

        // 범위를 초과하는 경우 추가 비용 계산
        if ($this->max_value && $value > $this->max_value) {
            $extraValue = $value - $this->max_value;
            $cost += $extraValue * $this->additional_cost;
        }

        return $cost;
    }

    /**
     * 값이 이 요금 범위에 해당하는지 확인
     */
    public function appliesToValue($value)
    {
        if ($value < $this->min_value) {
            return false;
        }

        if ($this->max_value && $value > $this->max_value) {
            return false;
        }

        return true;
    }
}
