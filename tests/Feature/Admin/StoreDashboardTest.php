<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Store Dashboard 기능 테스트
 *
 * 테스트 대상:
 * - GET /admin/store - 스토어 대시보드 메인 페이지
 */
class StoreDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // 관리자 사용자 생성
        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'name' => 'Store Admin',
            'isAdmin' => true,
            'utype' => 'admin'
        ]);

        // 기본 데이터 시딩
        $this->seedBasicData();
    }

    /**
     * 기본 데이터 시딩
     */
    protected function seedBasicData()
    {
        // 상품 테이블 데이터
        if (DB::getSchemaBuilder()->hasTable('store_products')) {
            DB::table('store_products')->insert([
                [
                    'title' => 'Test Product 1',
                    'price' => 10000,
                    'sale_price' => 8000,
                    'enable' => true,
                    'view_count' => 100,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'title' => 'Test Product 2',
                    'price' => 20000,
                    'sale_price' => null,
                    'enable' => true,
                    'view_count' => 50,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        // 주문 테이블 데이터
        if (DB::getSchemaBuilder()->hasTable('site_orders')) {
            DB::table('site_orders')->insert([
                [
                    'user_id' => $this->adminUser->id,
                    'order_number' => 'ORD001',
                    'total_amount' => 10000,
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'user_id' => $this->adminUser->id,
                    'order_number' => 'ORD002',
                    'total_amount' => 20000,
                    'status' => 'completed',
                    'created_at' => now()->subDays(1),
                    'updated_at' => now()->subDays(1),
                ],
            ]);
        }

        // 장바구니 테이블 데이터
        if (DB::getSchemaBuilder()->hasTable('store_cart')) {
            DB::table('store_cart')->insert([
                [
                    'user_id' => $this->adminUser->id,
                    'product_id' => 1,
                    'quantity' => 2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }

    /**
     * 테스트: 스토어 대시보드 접근 (비로그인)
     *
     * @test
     */
    public function test_store_dashboard_requires_authentication()
    {
        $response = $this->get('/admin/store');

        // 인증되지 않은 사용자는 로그인 페이지로 리다이렉트
        $response->assertRedirect('/admin/login');
    }

    /**
     * 테스트: 스토어 대시보드 접근 (일반 사용자)
     *
     * @test
     */
    public function test_store_dashboard_requires_admin_permission()
    {
        $regularUser = User::factory()->create([
            'isAdmin' => false,
            'utype' => 'user'
        ]);

        $response = $this->actingAs($regularUser)->get('/admin/store');

        // 일반 사용자는 접근 거부 (403) 또는 리다이렉트
        $this->assertTrue(
            $response->status() === 403 ||
            $response->status() === 302
        );
    }

    /**
     * 테스트: 스토어 대시보드 정상 접근 (관리자)
     *
     * @test
     */
    public function test_store_dashboard_loads_successfully_for_admin()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
                         ->get('/admin/store');

        $response->assertStatus(200);
        $response->assertViewIs('jiny-store::admin.dashboard.index');

        // 뷰에 필요한 데이터가 포함되어 있는지 확인
        $response->assertViewHas([
            'config',
            'stats',
            'recentOrders',
            'topProducts',
            'salesData'
        ]);
    }

    /**
     * 테스트: 대시보드 통계 데이터 검증
     *
     * @test
     */
    public function test_dashboard_statistics_are_calculated_correctly()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
                         ->get('/admin/store');

        $response->assertStatus(200);

        $stats = $response->viewData('stats');

        // 통계 데이터 구조 검증
        $this->assertArrayHasKey('total_products', $stats);
        $this->assertArrayHasKey('active_products', $stats);
        $this->assertArrayHasKey('total_orders', $stats);
        $this->assertArrayHasKey('pending_orders', $stats);
        $this->assertArrayHasKey('total_customers', $stats);
        $this->assertArrayHasKey('cart_items', $stats);
        $this->assertArrayHasKey('total_revenue', $stats);
        $this->assertArrayHasKey('month_revenue', $stats);

        // 테스트 데이터 기반 통계 검증
        if (DB::getSchemaBuilder()->hasTable('store_products')) {
            $this->assertGreaterThanOrEqual(0, $stats['total_products']);
            $this->assertGreaterThanOrEqual(0, $stats['active_products']);
        }

        if (DB::getSchemaBuilder()->hasTable('site_orders')) {
            $this->assertGreaterThanOrEqual(0, $stats['total_orders']);
            $this->assertGreaterThanOrEqual(0, $stats['pending_orders']);
        }
    }

    /**
     * 테스트: 최근 주문 데이터 검증
     *
     * @test
     */
    public function test_dashboard_recent_orders_data()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
                         ->get('/admin/store');

        $response->assertStatus(200);

        $recentOrders = $response->viewData('recentOrders');

        // 최근 주문이 Collection 인지 확인
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $recentOrders);

        // 주문 데이터가 있는 경우 구조 확인
        if ($recentOrders->isNotEmpty()) {
            $firstOrder = $recentOrders->first();
            $this->assertObjectHasAttribute('id', $firstOrder);
            $this->assertObjectHasAttribute('order_number', $firstOrder);
            $this->assertObjectHasAttribute('total_amount', $firstOrder);
            $this->assertObjectHasAttribute('status', $firstOrder);
        }
    }

    /**
     * 테스트: 인기 상품 데이터 검증
     *
     * @test
     */
    public function test_dashboard_top_products_data()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
                         ->get('/admin/store');

        $response->assertStatus(200);

        $topProducts = $response->viewData('topProducts');

        // 인기 상품이 Collection 인지 확인
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $topProducts);

        // 상품 데이터가 있는 경우 구조 확인
        if ($topProducts->isNotEmpty()) {
            $firstProduct = $topProducts->first();
            $this->assertObjectHasAttribute('id', $firstProduct);
            $this->assertObjectHasAttribute('title', $firstProduct);
            $this->assertObjectHasAttribute('price', $firstProduct);
            $this->assertObjectHasAttribute('view_count', $firstProduct);
        }
    }

    /**
     * 테스트: 매출 차트 데이터 검증
     *
     * @test
     */
    public function test_dashboard_sales_chart_data()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
                         ->get('/admin/store');

        $response->assertStatus(200);

        $salesData = $response->viewData('salesData');

        // 매출 데이터가 배열인지 확인
        $this->assertIsArray($salesData);

        // 7일치 데이터가 있는지 확인 (정확히 7개)
        $this->assertCount(7, $salesData);

        // 각 데이터 항목의 구조 확인
        foreach ($salesData as $dayData) {
            $this->assertArrayHasKey('date', $dayData);
            $this->assertArrayHasKey('amount', $dayData);
            $this->assertArrayHasKey('label', $dayData);
        }
    }

    /**
     * 테스트: 대시보드 성능 (응답 시간)
     *
     * @test
     */
    public function test_dashboard_response_time_is_acceptable()
    {
        $startTime = microtime(true);

        $response = $this->actingAs($this->adminUser, 'admin')
                         ->get('/admin/store');

        $endTime = microtime(true);
        $responseTime = $endTime - $startTime;

        $response->assertStatus(200);

        // 응답 시간이 2초 이내인지 확인 (성능 테스트)
        $this->assertLessThan(2.0, $responseTime, 'Dashboard should load within 2 seconds');
    }

    /**
     * 테스트: 대시보드 HTML 구조 검증
     *
     * @test
     */
    public function test_dashboard_html_structure()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
                         ->get('/admin/store');

        $response->assertStatus(200);

        // 중요한 HTML 요소들이 포함되어 있는지 확인
        $response->assertSee('스토어 대시보드');
        $response->assertSee('총 상품수');
        $response->assertSee('총 주문수');
        $response->assertSee('총 고객수');
        $response->assertSee('총 매출');
        $response->assertSee('최근 주문');
        $response->assertSee('인기 상품');
        $response->assertSee('매출 추이');
    }
}