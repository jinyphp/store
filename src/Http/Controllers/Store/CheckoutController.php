<?php

namespace Jiny\Store\Http\Controllers\Store;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Jiny\Store\Helpers\CurrencyHelper;

/**
 * 주문 프로세스 컨트롤러
 */
class CheckoutController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->config = [
            'view' => 'jiny-store::store.checkout.index',
            'title' => '주문하기',
        ];
    }

    /**
     * 주문 시작 페이지
     */
    public function __invoke(Request $request)
    {
        // 장바구니 확인
        $cartController = new CartController();
        $cartItems = $cartController->getCartItems();
        
        if ($cartItems->isEmpty()) {
            return redirect()->route('store.cart.index')
                ->with('error', '장바구니가 비어있습니다.');
        }

        $summary = $cartController->getCartSummary($cartItems);

        return view($this->config['view'], [
            'config' => $this->config,
            'cartItems' => $cartItems,
            'summary' => $summary,
        ]);
    }

    /**
     * 배송 정보 입력
     */
    public function shipping(Request $request)
    {
        $shippingInfo = Session::get('checkout.shipping', []);
        $countries = CurrencyHelper::getActiveCountries();

        return view('jiny-store::store.checkout.shipping', [
            'config' => $this->config,
            'shippingInfo' => $shippingInfo,
            'countries' => $countries,
        ]);
    }

    /**
     * 배송 정보 업데이트
     */
    public function updateShipping(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'nullable|string|max:255',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:2',
            'delivery_notes' => 'nullable|string|max:500',
        ]);

        Session::put('checkout.shipping', $validated);

        return redirect()->route('store.checkout.payment')
            ->with('success', '배송 정보가 저장되었습니다.');
    }

    /**
     * 결제 정보 입력
     */
    public function payment(Request $request)
    {
        $cartController = new CartController();
        $cartItems = $cartController->getCartItems();
        $summary = $cartController->getCartSummary($cartItems);
        
        $shippingInfo = Session::get('checkout.shipping');
        if (!$shippingInfo) {
            return redirect()->route('store.checkout.shipping')
                ->with('error', '배송 정보를 먼저 입력해주세요.');
        }

        $paymentMethods = $this->getPaymentMethods();

        return view('jiny-store::store.checkout.payment', [
            'config' => $this->config,
            'cartItems' => $cartItems,
            'summary' => $summary,
            'shippingInfo' => $shippingInfo,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    /**
     * 결제 처리
     */
    public function processPayment(Request $request)
    {
        $validated = $request->validate([
            'payment_method' => 'required|string|in:credit_card,bank_transfer,paypal',
            'card_number' => 'required_if:payment_method,credit_card|string',
            'card_expiry' => 'required_if:payment_method,credit_card|string',
            'card_cvc' => 'required_if:payment_method,credit_card|string',
            'card_name' => 'required_if:payment_method,credit_card|string',
        ]);

        Session::put('checkout.payment', $validated);

        return redirect()->route('store.checkout.review')
            ->with('success', '결제 정보가 저장되었습니다.');
    }

    /**
     * 주문 확인
     */
    public function review(Request $request)
    {
        $cartController = new CartController();
        $cartItems = $cartController->getCartItems();
        $summary = $cartController->getCartSummary($cartItems);
        
        $shippingInfo = Session::get('checkout.shipping');
        $paymentInfo = Session::get('checkout.payment');

        if (!$shippingInfo || !$paymentInfo) {
            return redirect()->route('store.checkout.index')
                ->with('error', '주문 정보가 완전하지 않습니다.');
        }

        return view('jiny-store::store.checkout.review', [
            'config' => $this->config,
            'cartItems' => $cartItems,
            'summary' => $summary,
            'shippingInfo' => $shippingInfo,
            'paymentInfo' => $paymentInfo,
        ]);
    }

    /**
     * 주문 완료
     */
    public function complete(Request $request)
    {
        $cartController = new CartController();
        $cartItems = $cartController->getCartItems();
        $summary = $cartController->getCartSummary($cartItems);
        
        $shippingInfo = Session::get('checkout.shipping');
        $paymentInfo = Session::get('checkout.payment');

        if (!$shippingInfo || !$paymentInfo || $cartItems->isEmpty()) {
            return redirect()->route('store.checkout.index')
                ->with('error', '주문 정보가 완전하지 않습니다.');
        }

        // 주문 생성
        $orderId = $this->createOrder($cartItems, $summary, $shippingInfo, $paymentInfo);

        // 주문 완료 후 장바구니 및 체크아웃 세션 클리어
        Session::forget('store_cart');
        Session::forget('checkout');

        return redirect()->route('store.orders.show', $orderId)
            ->with('success', '주문이 성공적으로 완료되었습니다.');
    }

    /**
     * 주문 생성
     */
    protected function createOrder($cartItems, $summary, $shippingInfo, $paymentInfo)
    {
        $orderNumber = $this->generateOrderNumber();
        $userId = auth()->id();

        // 주문 기본 정보
        $orderId = DB::table('store_orders')->insertGetId([
            'order_number' => $orderNumber,
            'user_id' => $userId,
            'status' => 'pending',
            'currency' => CurrencyHelper::getUserCurrency()->code,
            'subtotal' => $summary['subtotal'],
            'tax_amount' => $summary['tax_amount'],
            'total' => $summary['total'],
            'payment_method' => $paymentInfo['payment_method'],
            'payment_status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 주문 아이템
        foreach ($cartItems as $item) {
            DB::table('store_order_items')->insert([
                'order_id' => $orderId,
                'type' => $item->type,
                'product_id' => $item->type === 'product' ? $item->item_id : null,
                'service_id' => $item->type === 'service' ? $item->item_id : null,
                'title' => $item->title,
                'price' => $item->final_price,
                'quantity' => $item->quantity,
                'total' => $item->total_price,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 배송 정보
        DB::table('store_order_shipping')->insert([
            'order_id' => $orderId,
            'first_name' => $shippingInfo['first_name'],
            'last_name' => $shippingInfo['last_name'],
            'email' => $shippingInfo['email'],
            'phone' => $shippingInfo['phone'],
            'address_line_1' => $shippingInfo['address_line_1'],
            'address_line_2' => $shippingInfo['address_line_2'],
            'city' => $shippingInfo['city'],
            'state' => $shippingInfo['state'],
            'postal_code' => $shippingInfo['postal_code'],
            'country' => $shippingInfo['country'],
            'delivery_notes' => $shippingInfo['delivery_notes'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $orderId;
    }

    /**
     * 주문번호 생성
     */
    protected function generateOrderNumber()
    {
        $prefix = 'ORD-';
        $date = date('Ymd');
        $sequence = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        return $prefix . $date . '-' . $sequence;
    }

    /**
     * 결제 수단 목록
     */
    protected function getPaymentMethods()
    {
        return [
            'credit_card' => [
                'name' => '신용카드',
                'description' => 'Visa, MasterCard, JCB 등',
                'icon' => 'credit-card',
                'enabled' => true,
            ],
            'bank_transfer' => [
                'name' => '계좌이체',
                'description' => '실시간 계좌이체',
                'icon' => 'bank',
                'enabled' => true,
            ],
            'paypal' => [
                'name' => 'PayPal',
                'description' => 'PayPal로 안전하게 결제',
                'icon' => 'paypal',
                'enabled' => false,
            ],
        ];
    }
}
