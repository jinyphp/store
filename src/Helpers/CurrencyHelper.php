<?php

namespace Jiny\Store\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * 통화 관련 헬퍼 클래스
 */
class CurrencyHelper
{
    /**
     * 활성화된 통화 목록 조회
     */
    public static function getActiveCurrencies()
    {
        return Cache::remember('active_currencies', 3600, function () {
            return DB::table('store_currencies')
                ->where('enable', true)
                ->orderBy('is_base', 'desc')
                ->orderBy('order', 'asc')
                ->get();
        });
    }

    /**
     * 활성화된 국가 목록 조회
     */
    public static function getActiveCountries()
    {
        return Cache::remember('active_countries', 3600, function () {
            return DB::table('site_countries')
                ->where('enable', true)
                ->orderBy('name', 'asc')
                ->get();
        });
    }

    /**
     * 기본 통화 조회
     */
    public static function getBaseCurrency()
    {
        return Cache::remember('base_currency', 3600, function () {
            return DB::table('store_currencies')
                ->where('is_base', true)
                ->first();
        });
    }

    /**
     * 사용자 통화 조회 (세션 또는 기본값)
     */
    public static function getUserCurrency()
    {
        // 세션에서 사용자가 선택한 통화를 가져오거나 기본 통화 반환
        $currencyCode = session('user_currency', null);

        if ($currencyCode) {
            $currency = DB::table('store_currencies')
                ->where('code', $currencyCode)
                ->where('enable', true)
                ->first();

            if ($currency) {
                return $currency;
            }
        }

        return self::getBaseCurrency();
    }

    /**
     * 사용자 국가 조회 (IP 기반 또는 기본값)
     */
    public static function getUserCountry()
    {
        // 세션에서 사용자 국가를 가져오거나 기본 국가 반환
        $countryCode = session('user_country', null);

        if ($countryCode) {
            $country = DB::table('site_countries')
                ->where('code', $countryCode)
                ->where('enable', true)
                ->first();

            if ($country) {
                return $country;
            }
        }

        // 기본 국가 (한국) 반환
        return DB::table('site_countries')
            ->where('code', 'KR')
            ->first();
    }

    /**
     * 환율 조회
     */
    public static function getExchangeRate($fromCurrency, $toCurrency)
    {
        if ($fromCurrency->code === $toCurrency->code) {
            return 1.0;
        }

        // 환율 데이터가 있다면 조회 (현재는 기본값 반환)
        return 1.0;
    }

    /**
     * 금액 변환
     */
    public static function convertAmount($amount, $fromCurrency, $toCurrency)
    {
        if ($fromCurrency->code === $toCurrency->code) {
            return $amount;
        }

        $rate = self::getExchangeRate($fromCurrency, $toCurrency);
        return round($amount * $rate, 2);
    }

    /**
     * 통화 형식으로 포맷
     */
    public static function formatCurrency($amount, $currency)
    {
        if (is_object($currency)) {
            $symbol = $currency->symbol ?? $currency->code;
            $code = $currency->code;
        } else {
            $symbol = $currency;
            $code = $currency;
        }

        // 통화별 소수점 자리수 설정
        $decimals = in_array($code, ['JPY', 'KRW']) ? 0 : 2;

        return $symbol . number_format($amount, $decimals);
    }

    /**
     * 세금 적용
     */
    public static function applyTax($amount, $countryCode = null)
    {
        $taxRate = 0;

        if ($countryCode) {
            $country = DB::table('site_countries')
                ->where('code', $countryCode)
                ->first();

            if ($country && $country->tax_rate) {
                $taxRate = $country->tax_rate;
            }
        }

        $taxAmount = $amount * $taxRate;
        $total = $amount + $taxAmount;

        return [
            'subtotal' => $amount,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'total' => $total
        ];
    }

    /**
     * 다중 통화 가격 표시
     */
    public static function getPriceDisplay($amount, $currency)
    {
        $baseCurrency = self::getBaseCurrency();
        $prices = [];

        // 현재 통화로 표시
        $prices[$currency->code] = self::formatCurrency($amount, $currency);

        // 기본 통화가 다르면 추가 표시
        if ($currency->code !== $baseCurrency->code) {
            $convertedAmount = self::convertAmount($amount, $currency, $baseCurrency);
            $prices[$baseCurrency->code] = self::formatCurrency($convertedAmount, $baseCurrency);
        }

        return $prices;
    }

    /**
     * 통화별 설정 조회
     */
    public static function getCurrencySettings($currencyCode)
    {
        return DB::table('store_currencies')
            ->where('code', $currencyCode)
            ->first();
    }

    /**
     * 통화 캐시 클리어
     */
    public static function clearCache()
    {
        Cache::forget('active_currencies');
        Cache::forget('active_countries');
        Cache::forget('base_currency');
    }
}