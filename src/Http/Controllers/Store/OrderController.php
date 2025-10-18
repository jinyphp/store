<?php

namespace Jiny\Store\Http\Controllers\Store;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * 주문 내역 컨트롤러
 */
class OrderController extends Controller
{
    public function __invoke(Request $request, $id = null)
    {
        if ($id) {
            return $this->show($request, $id);
        }

        return $this->index($request);
    }

    /**
     * 주문 내역 목록
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('message', '로그인이 필요합니다.');
        }

        // 주문 목록 조회
        $query = DB::table('store_orders')
            ->where('user_id', $user->id)
            ->whereNull('deleted_at');

        // 상태 필터
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // 날짜 필터
        if ($dateFrom = $request->get('date_from')) {
            $query->where('created_at', '>=', $dateFrom . ' 00:00:00');
        }
        if ($dateTo = $request->get('date_to')) {
            $query->where('created_at', '<=', $dateTo . ' 23:59:59');
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(10);

        // 각 주문의 상품 정보 추가
        foreach ($orders as $order) {
            $order->items = DB::table('store_order_items')
                ->leftJoin('store_products', function($join) {
                    $join->on('store_order_items.item_id', '=', 'store_products.id')
                         ->where('store_order_items.item_type', '=', 'product');
                })
                ->leftJoin('store_services', function($join) {
                    $join->on('store_order_items.item_id', '=', 'store_services.id')
                         ->where('store_order_items.item_type', '=', 'service');
                })
                ->select(
                    'store_order_items.*',
                    'store_products.title as product_title',
                    'store_products.image as product_image',
                    'store_services.title as service_title',
                    'store_services.image as service_image'
                )
                ->where('store_order_items.order_id', $order->id)
                ->get();

            // 아이템별 제목과 이미지 설정
            foreach ($order->items as $item) {
                if ($item->item_type === 'product') {
                    $item->title = $item->product_title;
                    $item->image = $item->product_image;
                } else {
                    $item->title = $item->service_title;
                    $item->image = $item->service_image;
                }
            }
        }

        // 주문 상태 옵션
        $statusOptions = [
            '' => '전체',
            'pending' => '주문 대기',
            'confirmed' => '주문 확인',
            'processing' => '처리중',
            'shipped' => '배송중',
            'delivered' => '배송완료',
            'cancelled' => '주문 취소',
            'refunded' => '환불완료'
        ];

        return view('jiny-store::store.orders.index', [
            'orders' => $orders,
            'statusOptions' => $statusOptions,
            'currentFilters' => [
                'status' => $request->get('status'),
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to')
            ]
        ]);
    }

    /**
     * 주문 상세
     */
    public function show(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('message', '로그인이 필요합니다.');
        }

        // 주문 정보 조회
        $order = DB::table('store_orders')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$order) {
            abort(404, 'Order not found');
        }

        // 주문 상품 조회
        $orderItems = DB::table('store_order_items')
            ->leftJoin('store_products', function($join) {
                $join->on('store_order_items.item_id', '=', 'store_products.id')
                     ->where('store_order_items.item_type', '=', 'product');
            })
            ->leftJoin('store_services', function($join) {
                $join->on('store_order_items.item_id', '=', 'store_services.id')
                     ->where('store_order_items.item_type', '=', 'service');
            })
            ->leftJoin('site_product_pricing', function($join) {
                $join->on('store_order_items.pricing_option_id', '=', 'site_product_pricing.id')
                     ->where('store_order_items.item_type', '=', 'product');
            })
            ->leftJoin('site_service_pricing', function($join) {
                $join->on('store_order_items.pricing_option_id', '=', 'site_service_pricing.id')
                     ->where('store_order_items.item_type', '=', 'service');
            })
            ->select(
                'store_order_items.*',
                'store_products.title as product_title',
                'store_products.image as product_image',
                'store_products.slug as product_slug',
                'store_services.title as service_title',
                'store_services.image as service_image',
                'store_services.slug as service_slug',
                'site_product_pricing.name as product_pricing_name',
                'site_service_pricing.name as service_pricing_name'
            )
            ->where('store_order_items.order_id', $order->id)
            ->get();

        // 아이템별 정보 정리
        foreach ($orderItems as $item) {
            if ($item->item_type === 'product') {
                $item->title = $item->product_title;
                $item->image = $item->product_image;
                $item->slug = $item->product_slug;
                $item->pricing_name = $item->product_pricing_name;
            } else {
                $item->title = $item->service_title;
                $item->image = $item->service_image;
                $item->slug = $item->service_slug;
                $item->pricing_name = $item->service_pricing_name;
            }
        }

        // 배송 정보 조회
        $shipping = DB::table('store_order_shipping')
            ->where('order_id', $order->id)
            ->first();

        // 결제 정보 조회
        $payment = DB::table('store_order_payments')
            ->where('order_id', $order->id)
            ->first();

        // 주문 로그 조회
        $logs = DB::table('store_order_logs')
            ->where('order_id', $order->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('jiny-store::store.orders.show', [
            'order' => $order,
            'orderItems' => $orderItems,
            'shipping' => $shipping,
            'payment' => $payment,
            'logs' => $logs
        ]);
    }
}