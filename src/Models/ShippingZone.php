<?php

namespace Jiny\Store\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Jiny\Store\Traits\HasAssignments;

class ShippingZone extends Model
{
    use HasAssignments;

    protected $table = 'store_shipping_zones';

    protected $fillable = [
        'name',
        'description',
        'countries',
        'regions',
        'postcodes',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'countries' => 'array',
        'regions' => 'array',
        'postcodes' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * 배송 요금 관계
     */
    public function rates(): HasMany
    {
        return $this->hasMany(ShippingRate::class, 'zone_id');
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
     * 국가로 지역 검색
     */
    public function scopeForCountry($query, $country)
    {
        return $query->where('countries', 'LIKE', '%"' . $country . '"%');
    }

    /**
     * 우편번호로 지역 검색
     */
    public function scopeForPostcode($query, $postcode)
    {
        return $query->where(function($q) use ($postcode) {
            $q->whereNull('postcodes')
              ->orWhere('postcodes', 'LIKE', '%' . $postcode . '%');
        });
    }

    /**
     * 특정 주소가 이 지역에 포함되는지 확인
     */
    public function includes($address): bool
    {
        // 국가 확인
        if (!empty($this->countries) && isset($address['country'])) {
            if (!in_array($address['country'], $this->countries)) {
                return false;
            }
        }

        // 지역 확인
        if (!empty($this->regions) && isset($address['region'])) {
            if (!in_array($address['region'], $this->regions)) {
                return false;
            }
        }

        // 우편번호 확인
        if (!empty($this->postcodes) && isset($address['postcode'])) {
            $included = false;
            foreach ($this->postcodes as $pattern) {
                if (fnmatch($pattern, $address['postcode'])) {
                    $included = true;
                    break;
                }
            }
            if (!$included) {
                return false;
            }
        }

        return true;
    }

    /**
     * 활성 배송 방법 가져오기
     */
    public function getAvailableMethods()
    {
        return $this->rates()
                    ->with('method')
                    ->whereHas('method', function($query) {
                        $query->where('is_active', true);
                    })
                    ->where('is_active', true)
                    ->get()
                    ->pluck('method')
                    ->unique('id');
    }
}