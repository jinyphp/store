<?php

namespace Jiny\Store\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StoreShippingZoneCountry extends Model
{
    use HasFactory;

    protected $table = 'store_shipping_zone_countries';

    protected $fillable = [
        'zone_id',
        'country_code',
        'country_name',
        'regions',
        'postcodes',
        'is_active',
    ];

    protected $casts = [
        'regions' => 'array',
        'postcodes' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * 배송 지역 관계
     */
    public function zone()
    {
        return $this->belongsTo(ShippingZone::class, 'zone_id');
    }

    /**
     * 활성화된 국가만 조회
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 지역별 조회
     */
    public function scopeForZone($query, $zoneId)
    {
        return $query->where('zone_id', $zoneId);
    }

    /**
     * 국가 코드로 조회
     */
    public function scopeByCountry($query, $countryCode)
    {
        return $query->where('country_code', $countryCode);
    }

    /**
     * 특정 지역이 이 국가 설정에 포함되는지 확인
     */
    public function includesRegion($region)
    {
        if (empty($this->regions)) {
            return true; // 모든 지역 포함
        }

        return in_array($region, $this->regions);
    }

    /**
     * 특정 우편번호가 이 국가 설정에 포함되는지 확인
     */
    public function includesPostcode($postcode)
    {
        if (empty($this->postcodes)) {
            return true; // 모든 우편번호 포함
        }

        foreach ($this->postcodes as $pattern) {
            if (fnmatch($pattern, $postcode)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 특정 주소가 이 국가 설정에 포함되는지 확인
     */
    public function includesAddress($address)
    {
        // 지역 확인
        if (isset($address['region']) && !$this->includesRegion($address['region'])) {
            return false;
        }

        // 우편번호 확인
        if (isset($address['postcode']) && !$this->includesPostcode($address['postcode'])) {
            return false;
        }

        return true;
    }
}
