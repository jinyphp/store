<?php

namespace Jiny\Store\Http\Controllers\Store;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Jiny\Store\Helpers\CurrencyHelper;

/**
 * 장바구니 컨트롤러
 */
class CartController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->config = [
            'view' => 'jiny-store::store.cart.index',
            'title' => '장바구니',
        ];
    }

    /**
     * 장바구니 페이지
     */
    public function __invoke(Request $request)
    {
        $cartItems = $this->getCartItems();
        $summary = $this->getCartSummary($cartItems);
        $currency = $this->getCurrencyInfo();

        return view($this->config['view'], [
            'config' => $this->config,
            'cartItems' => $cartItems,
            'summary' => $summary,
            'currency' => $currency,
        ]);
    }

    /**
     * 장바구니에 상품 추가
     */
    public function add(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:product,service',
            'item_id' => 'required|integer',
            'quantity' => 'integer|min:1',
            'pricing_id' => 'nullable|integer',
        ]);

        $quantity = $validated['quantity'] ?? 1;
        $type = $validated['type'];
        $itemId = $validated['item_id'];
        $pricingId = $validated['pricing_id'] ?? null;

        // 상품/서비스 정보 확인
        $item = $this->getItemInfo($type, $itemId);
        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => '상품/서비스를 찾을 수 없습니다.'
            ], 404);
        }

        // 가격 정보 가져오기
        $pricing = $this->getItemPricing($type, $itemId, $pricingId);
        if (!$pricing) {
            return response()->json([
                'success' => false,
                'message' => '가격 정보를 찾을 수 없습니다.'
            ], 404);
        }

        // 장바구니에 추가
        $cartId = $this->addToCart([
            'type' => $type,
            'item_id' => $itemId,
            'title' => $item->title,
            'image' => $item->image,
            'pricing_id' => $pricing->id,
            'pricing_name' => $pricing->name ?? 'Standard',
            'price' => $pricing->price,
            'sale_price' => $pricing->sale_price,
            'currency' => $pricing->currency,
            'quantity' => $quantity,
        ]);

        $cartCount = $this->getCartCount();

        return response()->json([
            'success' => true,
            'message' => '장바구니에 추가되었습니다.',
            'cart_count' => $cartCount,
            'cart_id' => $cartId,
        ]);
    }

    /**
     * 장바구니 수량 업데이트
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        $quantity = $validated['quantity'];
        $cart = $this->getCart();

        if (!isset($cart[$id])) {
            return response()->json([
                'success' => false,
                'message' => '장바구니 항목을 찾을 수 없습니다.'
            ], 404);
        }

        // 수량 업데이트
        $cart[$id]['quantity'] = $quantity;
        $cart[$id]['updated_at'] = now();

        Session::put('store_cart', $cart);

        $cartItems = $this->getCartItems();
        $summary = $this->getCartSummary($cartItems);

        return response()->json([
            'success' => true,
            'message' => '수량이 업데이트되었습니다.',
            'summary' => $summary,
        ]);
    }

    /**
     * 장바구니에서 상품 제거
     */
    public function remove(Request $request, $id)
    {
        $cart = $this->getCart();

        if (!isset($cart[$id])) {
            return response()->json([
                'success' => false,
                'message' => '장바구니 항목을 찾을 수 없습니다.'
            ], 404);
        }

        unset($cart[$id]);
        Session::put('store_cart', $cart);

        $cartCount = $this->getCartCount();

        return response()->json([
            'success' => true,
            'message' => '상품이 장바구니에서 제거되었습니다.',
            'cart_count' => $cartCount,
        ]);
    }

    /**
     * 장바구니 비우기
     */
    public function clear(Request $request)
    {
        Session::forget('store_cart');

        return response()->json([
            'success' => true,
            'message' => '장바구니가 비워졌습니다.',
            'cart_count' => 0,
        ]);
    }

    /**
     * 장바구니 상품 개수
     */
    public function count(Request $request)
    {
        $count = $this->getCartCount();

        return response()->json([
            'count' => $count
        ]);
    }

    /**
     * 장바구니 데이터 가져오기
     */
    protected function getCart()
    {
        return Session::get('store_cart', []);
    }

    /**
     * 장바구니에 아이템 추가
     */
    protected function addToCart($item)
    {
        $cart = $this->getCart();
        
        // 중복 체크 (같은 상품, 같은 가격 옵션)
        $existingKey = null;
        foreach ($cart as $key => $cartItem) {
            if ($cartItem['type'] === $item['type'] && 
                $cartItem['item_id'] === $item['item_id'] && 
                $cartItem['pricing_id'] === $item['pricing_id']) {
                $existingKey = $key;
                break;
            }
        }

        if ($existingKey) {
            // 기존 상품 수량 증가
            $cart[$existingKey]['quantity'] += $item['quantity'];
            $cart[$existingKey]['updated_at'] = now();
            $cartId = $existingKey;
        } else {
            // 새 상품 추가
            $cartId = uniqid('cart_');
            $item['id'] = $cartId;
            $item['created_at'] = now();
            $item['updated_at'] = now();
            $cart[$cartId] = $item;
        }

        Session::put('store_cart', $cart);
        return $cartId;
    }

    /**
     * 장바구니 아이템 목록 가져오기
     */
    protected function getCartItems()
    {
        $cart = $this->getCart();
        $items = collect($cart)->map(function ($item) {
            // 최종 가격 계산
            $finalPrice = $item['sale_price'] ?? $item['price'];
            $totalPrice = $finalPrice * $item['quantity'];

            $item['final_price'] = $finalPrice;
            $item['total_price'] = $totalPrice;
            $item['final_price_formatted'] = CurrencyHelper::formatCurrency($finalPrice, $item['currency']);
            $item['total_price_formatted'] = CurrencyHelper::formatCurrency($totalPrice, $item['currency']);

            return (object) $item;
        });

        return $items;
    }

    /**
     * 장바구니 요약 정보
     */
    protected function getCartSummary($cartItems)
    {
        $itemCount = $cartItems->sum('quantity');
        $subtotal = $cartItems->sum('total_price');

        // 세금 계산
        $userCountry = CurrencyHelper::getUserCountry();
        $taxInfo = CurrencyHelper::applyTax($subtotal, $userCountry->code ?? 'KR');

        $currency = CurrencyHelper::getUserCurrency();

        return [
            'item_count' => $itemCount,
            'subtotal' => $subtotal,
            'subtotal_formatted' => CurrencyHelper::formatCurrency($subtotal, $currency),
            'tax_rate' => $taxInfo['tax_rate'],
            'tax_rate_percent' => $taxInfo['tax_rate'] * 100,
            'tax_amount' => $taxInfo['tax_amount'],
            'tax_amount_formatted' => CurrencyHelper::formatCurrency($taxInfo['tax_amount'], $currency),
            'total' => $taxInfo['total'],
            'total_formatted' => CurrencyHelper::formatCurrency($taxInfo['total'], $currency),
            'country_name' => $userCountry->name ?? 'Korea',
            'tax_name' => $userCountry->tax_name ?? 'VAT',
        ];
    }

    /**
     * 장바구니 상품 개수
     */
    protected function getCartCount()
    {
        $cart = $this->getCart();
        return array_sum(array_column($cart, 'quantity'));
    }

    /**
     * 상품/서비스 정보 가져오기
     */
    protected function getItemInfo($type, $itemId)
    {
        $table = $type === 'product' ? 'store_products' : 'store_services';

        return DB::table($table)
            ->where('id', $itemId)
            ->where('enable', true)
            ->whereNull('deleted_at')
            ->first();
    }

    /**
     * 가격 정보 가져오기
     */
    protected function getItemPricing($type, $itemId, $pricingId = null)
    {
        $table = $type === 'product' ? 'store_product_pricing' : 'store_service_pricing';

        $query = DB::table($table)
            ->where($type . '_id', $itemId)
            ->where('enable', true);

        if ($pricingId) {
            $query->where('id', $pricingId);
        } else {
            $query->where('is_default', true);
        }

        return $query->first();
    }

    /**
     * 통화 정보
     */
    protected function getCurrencyInfo()
    {
        $currency = CurrencyHelper::getUserCurrency();
        
        return [
            'user_currency' => $currency->code ?? 'KRW',
            'currency_symbol' => $currency->symbol ?? '₩',
            'currency_name' => $currency->name ?? 'Korean Won',
        ];
    }
}
