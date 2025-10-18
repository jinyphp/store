<?php

namespace Jiny\Store\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StoreCurrency extends Model
{
    use HasFactory;

    protected $table = 'store_currencies';

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'symbol_position',
        'decimal_separator',
        'thousand_separator',
        'decimal_places',
        'is_active',
        'is_default',
        'sort_order',
    ];

    protected $casts = [
        'decimal_places' => 'integer',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * 환율 관계
     */
    public function exchangeRates()
    {
        return $this->hasMany(StoreExchangeRate::class, 'from_currency', 'code');
    }

    /**
     * 역환율 관계
     */
    public function inverseExchangeRates()
    {
        return $this->hasMany(StoreExchangeRate::class, 'to_currency', 'code');
    }

    /**
     * 활성화된 통화만 조회
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 기본 통화 조회
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * 정렬 순서대로 조회
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * 통화 코드로 조회
     */
    public function scopeByCode($query, $code)
    {
        return $query->where('code', $code);
    }

    /**
     * 금액 포맷팅
     */
    public function format($amount)
    {
        $formatted = number_format(
            $amount,
            $this->decimal_places,
            $this->decimal_separator,
            $this->thousand_separator
        );

        if ($this->symbol_position === 'before') {
            return $this->symbol . $formatted;
        }

        return $formatted . $this->symbol;
    }
}
