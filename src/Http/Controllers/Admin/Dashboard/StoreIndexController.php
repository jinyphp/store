<?php

namespace Jiny\Store\Http\Controllers\Admin\Dashboard;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Store 메인 대시보드 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/store/') → StoreIndexController::__invoke()
 */
class StoreIndexController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->loadConfig();
    }

    protected function loadConfig()
    {
        $this->config = [
            'title' => 'Store 대시보드',
            'subtitle' => '스토어 운영 현황을 한눈에 확인하세요.',
            'view' => 'jiny-store::admin.dashboard.index',
        ];
    }

    public function __invoke(Request $request)
    {
        // 스토어 통계 데이터 수집
        $stats = $this->getStoreStats();
        $recentOrders = $this->getRecentOrders();
        $topProducts = $this->getTopProducts();
        $salesData = $this->getSalesData();

        return view($this->config['view'], [
            'config' => $this->config,
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'topProducts' => $topProducts,
            'salesData' => $salesData,
        ]);
    }

    /**
     * 스토어 기본 통계
     */
    protected function getStoreStats()
    {
        $today = now()->format('Y-m-d');
        $thisMonth = now()->format('Y-m');

        return [
            'total_products' => $this->safeCount('store_products'),
            'active_products' => $this->safeCount('store_products', ['enable' => true]),
            'total_orders' => $this->safeCount('site_orders'),
            'pending_orders' => $this->safeCount('site_orders', ['status' => 'pending']),
            'total_customers' => $this->safeCount('users'),
            'cart_items' => $this->safeCount('store_cart'),
            'today_orders' => $this->safeCountWithDate('site_orders', 'created_at', $today),
            'month_orders' => $this->safeCountWithDate('site_orders', 'created_at', $thisMonth, 'month'),
            'total_revenue' => $this->safeSumRevenue(),
            'month_revenue' => $this->safeMonthRevenue(),
        ];
    }

    /**
     * 최근 주문
     */
    protected function getRecentOrders()
    {
        try {
            return DB::table('site_orders')
                ->leftJoin('users', 'site_orders.user_id', '=', 'users.id')
                ->select(
                    'site_orders.id',
                    'site_orders.order_number',
                    'site_orders.total_amount',
                    'site_orders.status',
                    'site_orders.created_at',
                    'users.name as customer_name',
                    'users.email as customer_email'
                )
                ->orderBy('site_orders.created_at', 'desc')
                ->limit(5)
                ->get();
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    /**
     * 인기 상품
     */
    protected function getTopProducts()
    {
        try {
            return DB::table('store_products')
                ->select(
                    'id',
                    'title',
                    'price',
                    'sale_price',
                    'view_count',
                    'image'
                )
                ->where('enable', true)
                ->whereNull('deleted_at')
                ->orderBy('view_count', 'desc')
                ->limit(5)
                ->get();
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    /**
     * 매출 데이터 (최근 7일)
     */
    protected function getSalesData()
    {
        try {
            $data = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $amount = DB::table('site_orders')
                    ->whereDate('created_at', $date)
                    ->where('status', '!=', 'cancelled')
                    ->sum('total_amount') ?? 0;

                $data[] = [
                    'date' => $date,
                    'amount' => $amount,
                    'label' => now()->subDays($i)->format('m/d'),
                ];
            }
            return $data;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 안전한 카운트 함수
     */
    protected function safeCount($table, $conditions = [])
    {
        try {
            $query = DB::table($table);

            // deleted_at이 있는 테이블인 경우 체크
            if (in_array($table, ['store_products', 'site_orders'])) {
                $query->whereNull('deleted_at');
            }

            foreach ($conditions as $key => $value) {
                $query->where($key, $value);
            }

            return $query->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * 날짜별 안전한 카운트 함수
     */
    protected function safeCountWithDate($table, $dateColumn, $date, $type = 'day')
    {
        try {
            $query = DB::table($table);

            if ($type === 'month') {
                $query->where($dateColumn, 'LIKE', $date . '%');
            } else {
                $query->whereDate($dateColumn, $date);
            }

            return $query->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * 안전한 매출 합계
     */
    protected function safeSumRevenue()
    {
        try {
            return DB::table('site_orders')
                ->where('status', '!=', 'cancelled')
                ->whereNull('deleted_at')
                ->sum('total_amount') ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * 이번 달 매출
     */
    protected function safeMonthRevenue()
    {
        try {
            $thisMonth = now()->format('Y-m');
            return DB::table('site_orders')
                ->where('status', '!=', 'cancelled')
                ->where('created_at', 'LIKE', $thisMonth . '%')
                ->whereNull('deleted_at')
                ->sum('total_amount') ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }
}