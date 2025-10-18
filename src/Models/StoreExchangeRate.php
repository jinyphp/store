<?php

namespace Jiny\Store\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StoreExchangeRate extends Model
{
    use HasFactory;

    protected $table = 'store_exchange_rates';

    protected $fillable = [
        'from_currency',
        'to_currency',
        'rate',
        'inverse_rate',
        'source',
        'is_active',
        'last_updated_at',
    ];

    protected $casts = [
        'rate' => 'decimal:6',
        'inverse_rate' => 'decimal:6',
        'is_active' => 'boolean',
        'last_updated_at' => 'datetime',
    ];

    /**
     * 출발 통화 관계
     */
    public function fromCurrency()
    {
        return $this->belongsTo(StoreCurrency::class, 'from_currency', 'code');
    }

    /**
     * 도착 통화 관계
     */
    public function toCurrency()
    {
        return $this->belongsTo(StoreCurrency::class, 'to_currency', 'code');
    }

    /**
     * 환율 로그 관계
     */
    public function logs()
    {
        return $this->hasMany(StoreExchangeRateLog::class, 'exchange_rate_id');
    }

    /**
     * 활성화된 환율만 조회
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 통화 쌍으로 조회
     */
    public function scopeForPair($query, $fromCurrency, $toCurrency)
    {
        return $query->where('from_currency', $fromCurrency)
                    ->where('to_currency', $toCurrency);
    }

    /**
     * 금액 변환
     */
    public function convert($amount)
    {
        return $amount * $this->rate;
    }

    /**
     * 역변환
     */
    public function convertInverse($amount)
    {
        return $amount * $this->inverse_rate;
    }

    /**
     * 환율 업데이트 및 로그 기록
     */
    public function updateRate($newRate, $source = null)
    {
        $oldRate = $this->rate;

        $this->update([
            'rate' => $newRate,
            'inverse_rate' => 1 / $newRate,
            'source' => $source ?: $this->source,
            'last_updated_at' => now(),
        ]);

        // 로그 기록
        $this->logs()->create([
            'from_currency' => $this->from_currency,
            'to_currency' => $this->to_currency,
            'old_rate' => $oldRate,
            'new_rate' => $newRate,
            'source' => $source ?: $this->source,
            'updated_at' => now(),
        ]);

        return $this;
    }
}
