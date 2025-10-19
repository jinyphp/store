<?php

namespace Jiny\Store\Http\Controllers\Store;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Store\Helpers\CurrencyHelper;

/**
 * Store API Controller (AJAX 요청용)
 */
class ApiController extends Controller
{
    /**
     * 상품 정보 조회
     */
    public function product(Request $request, $id)
    {
        $product = DB::table('store_products')
            ->leftJoin('store_categories', 'store_products.category_id', '=', 'store_categories.id')
            ->select(
                'store_products.*',
                'store_categories.title as category_name',
                'store_categories.slug as category_slug'
            )
            ->where('store_products.id', $id)
            ->where('store_products.enable', true)
            ->whereNull('store_products.deleted_at')
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        // 가격 옵션 조회
        $pricingOptions = DB::table('site_product_pricing')
            ->where('product_id', $id)
            ->where('enable', true)
            ->whereNull('deleted_at')
            ->orderBy('pos')
            ->orderBy('price')
            ->get();

        // 이미지 갤러리 조회
        $images = DB::table('store_product_images')
            ->where('product_id', $id)
            ->where('enable', true)
            ->whereNull('deleted_at')
            ->orderBy('featured', 'desc')
            ->orderBy('pos')
            ->get();

        return response()->json([
            'success' => true,
            'product' => $product,
            'pricing_options' => $pricingOptions,
            'images' => $images
        ]);
    }

    /**
     * 장바구니 요약 정보
     */
    public function cartSummary(Request $request)
    {
        $userId = auth()->id();
        $sessionId = $userId ? null : session()->getId();

        // 장바구니 아이템 조회
        $cartItems = DB::table('store_cart')
            ->leftJoin('store_products', function($join) {
                $join->on('store_cart.item_id', '=', 'store_products.id')
                     ->where('store_cart.item_type', '=', 'product');
            })
            ->leftJoin('store_services', function($join) {
                $join->on('store_cart.item_id', '=', 'store_services.id')
                     ->where('store_cart.item_type', '=', 'service');
            })
            ->select(
                'store_cart.*',
                'store_products.price as product_price',
                'store_services.price as service_price'
            )
            ->where(function($query) use ($userId, $sessionId) {
                if ($userId) {
                    $query->where('store_cart.user_id', $userId);
                } else {
                    $query->where('store_cart.session_id', $sessionId);
                }
            })
            ->whereNull('store_cart.deleted_at')
            ->get();

        // 사용자 통화 및 국가 정보
        $userCurrency = CurrencyHelper::getUserCurrency();
        $userCountry = CurrencyHelper::getUserCountry();
        $baseCurrency = CurrencyHelper::getBaseCurrency();

        // 총 금액 계산
        $subtotal = 0;
        $itemCount = 0;

        foreach ($cartItems as $item) {
            $basePrice = $item->item_type === 'product' ? $item->product_price : $item->service_price;
            $convertedPrice = CurrencyHelper::convertAmount($basePrice, $baseCurrency, $userCurrency);
            $subtotal += $convertedPrice * $item->quantity;
            $itemCount += $item->quantity;
        }

        // 세금 계산
        $taxInfo = CurrencyHelper::applyTax($subtotal, $userCountry->code ?? null);

        return response()->json([
            'success' => true,
            'summary' => [
                'item_count' => $itemCount,
                'cart_count' => $cartItems->count(),
                'subtotal' => $taxInfo['subtotal'],
                'subtotal_formatted' => CurrencyHelper::formatCurrency($taxInfo['subtotal'], $userCurrency),
                'tax_amount' => $taxInfo['tax_amount'],
                'tax_amount_formatted' => CurrencyHelper::formatCurrency($taxInfo['tax_amount'], $userCurrency),
                'total' => $taxInfo['total'],
                'total_formatted' => CurrencyHelper::formatCurrency($taxInfo['total'], $userCurrency),
                'currency' => $userCurrency
            ]
        ]);
    }

    /**
     * 배송비 계산
     */
    public function calculateShipping(Request $request)
    {
        $request->validate([
            'country' => 'required|string',
            'state' => 'nullable|string',
            'city' => 'nullable|string',
            'postal_code' => 'nullable|string'
        ]);

        // 기본 배송비는 무료 (설정에 따라 변경 가능)
        $shippingCost = 0;
        $shippingMethod = 'standard';

        // 국가별 배송비 규칙 (예시)
        $country = strtoupper($request->country);
        switch ($country) {
            case 'KR':
                $shippingCost = 0; // 국내 무료배송
                break;
            case 'US':
            case 'CA':
                $shippingCost = 15000; // 북미 배송비
                break;
            case 'JP':
            case 'CN':
                $shippingCost = 8000; // 아시아 배송비
                break;
            default:
                $shippingCost = 25000; // 기타 국가
                break;
        }

        $userCurrency = CurrencyHelper::getUserCurrency();
        $baseCurrency = CurrencyHelper::getBaseCurrency();

        // 통화 변환
        $convertedShippingCost = CurrencyHelper::convertAmount($shippingCost, $baseCurrency, $userCurrency);

        return response()->json([
            'success' => true,
            'shipping' => [
                'cost' => $convertedShippingCost,
                'cost_formatted' => CurrencyHelper::formatCurrency($convertedShippingCost, $userCurrency),
                'method' => $shippingMethod,
                'country' => $country,
                'estimated_days' => $country === 'KR' ? '1-2' : '7-14'
            ]
        ]);
    }

    /**
     * 재고 정보 조회
     */
    public function inventory(Request $request, $id)
    {
        $itemType = $request->get('type', 'product');

        if ($itemType === 'product') {
            $item = DB::table('store_products')
                ->select('id', 'title', 'stock_quantity', 'stock_status', 'enable')
                ->where('id', $id)
                ->where('enable', true)
                ->whereNull('deleted_at')
                ->first();
        } else {
            $item = DB::table('store_services')
                ->select('id', 'title', 'enable')
                ->where('id', $id)
                ->where('enable', true)
                ->whereNull('deleted_at')
                ->first();
        }

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found'
            ], 404);
        }

        $inventory = [
            'available' => (bool) $item->enable,
            'in_stock' => true
        ];

        if ($itemType === 'product') {
            $inventory['stock_quantity'] = $item->stock_quantity ?? 0;
            $inventory['stock_status'] = $item->stock_status ?? 'in_stock';
            $inventory['in_stock'] = $item->stock_status !== 'out_of_stock';
        }

        return response()->json([
            'success' => true,
            'inventory' => $inventory
        ]);
    }
}