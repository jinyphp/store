<?php

namespace Jiny\Store\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StoreExchangeRateLog extends Model
{
    use HasFactory;

    protected $table = 'store_exchange_rate_logs';

    protected $fillable = [
        'exchange_rate_id',
        'from_currency',
        'to_currency',
        'old_rate',
        'new_rate',
        'source',
        'updated_at',
    ];

    protected $casts = [
        'old_rate' => 'decimal:6',
        'new_rate' => 'decimal:6',
        'updated_at' => 'datetime',
    ];

    /**
     * 환율 관계
     */
    public function exchangeRate()
    {
        return $this->belongsTo(StoreExchangeRate::class, 'exchange_rate_id');
    }

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
     * 최근 로그 조회
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('updated_at', '>=', now()->subDays($days));
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
     * 변화율 계산
     */
    public function getChangePercentageAttribute()
    {
        if ($this->old_rate == 0) {
            return 0;
        }

        return (($this->new_rate - $this->old_rate) / $this->old_rate) * 100;
    }

    /**
     * 변화량 계산
     */
    public function getChangeDifferenceAttribute()
    {
        return $this->new_rate - $this->old_rate;
    }
}
