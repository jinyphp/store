<?php

namespace Jiny\Store\Http\Controllers\Store\Cart;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Store\Helpers\CurrencyHelper;

/**
 * 장바구니 목록 컨트롤러
 */
class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $userId = auth()->id();
        $sessionId = $userId ? null : session()->getId();

        // 장바구니 아이템 조회 (빈 상태로 시작)
        $cartItems = collect([]);

        // 요약 정보 (하드코딩)
        $summary = [
            'item_count' => 0,
            'subtotal' => 0,
            'subtotal_formatted' => '0원',
            'tax_rate' => 0,
            'tax_rate_percent' => 0,
            'tax_amount' => 0,
            'tax_amount_formatted' => '0원',
            'tax_name' => 'VAT',
            'total' => 0,
            'total_formatted' => '0원',
            'country_name' => 'Korea'
        ];

        // 통화 정보 (하드코딩)
        $currency = [
            'user_currency' => 'KRW',
            'currency_symbol' => '₩',
            'currency_name' => 'Korean Won'
        ];

        return view('jiny-store::store.cart.index', [
            'cartItems' => $cartItems,
            'summary' => $summary,
            'currency' => $currency
        ]);
    }
}
