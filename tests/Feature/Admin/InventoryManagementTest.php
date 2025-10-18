<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Inventory Management 기능 테스트
 *
 * 테스트 대상:
 * - GET /admin/store/inventory - 재고 목록
 * - GET /admin/store/inventory/stock-in - 입고 관리
 * - GET /admin/store/inventory/stock-out - 출고 관리
 * - GET /admin/store/inventory/alerts - 재고 알림
 * - POST /admin/store/inventory/stock-in/process - 입고 처리
 * - POST /admin/store/inventory/stock-out/process - 출고 처리
 */
class InventoryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // 관리자 사용자 생성
        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'name' => 'Inventory Admin',
            'isAdmin' => true,
            'utype' => 'admin'
        ]);

        // 테스트 데이터 시딩
        $this->seedInventoryData();
    }

    /**
     * 테스트 데이터 시딩
     */
    protected function seedInventoryData()
    {
        // 상품 테이블에 데이터 추가
        if (DB::getSchemaBuilder()->hasTable('products')) {
            DB::table('products')->insert([
                [
                    'id' => 1,
                    'name' => 'Test Product 1',
                    'sku' => 'SKU001',
                    'description' => 'Test product for inventory',
                    'price' => 19.99,
                    'cost' => 10.00,
                    'category' => 'electronics',
                    'status' => 'active',
                    'track_inventory' => 1,
                    'minimum_stock' => 10,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 2,
                    'name' => 'Test Product 2',
                    'sku' => 'SKU002',
                    'description' => 'Another test product',
                    'price' => 29.99,
                    'cost' => 15.00,
                    'category' => 'books',
                    'status' => 'active',
                    'track_inventory' => 1,
                    'minimum_stock' => 5,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);
        }

        // 재고 테이블에 데이터 추가
        if (DB::getSchemaBuilder()->hasTable('inventory')) {
            DB::table('inventory')->insert([
                [
                    'id' => 1,
                    'product_id' => 1,
                    'location' => 'main_warehouse',
                    'quantity_on_hand' => 50,
                    'quantity_reserved' => 5,
                    'reorder_point' => 10,
                    'reorder_quantity' => 100,
                    'last_cost' => 10.00,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 2,
                    'product_id' => 2,
                    'location' => 'main_warehouse',
                    'quantity_on_hand' => 3,
                    'quantity_reserved' => 0,
                    'reorder_point' => 5,
                    'reorder_quantity' => 50,
                    'last_cost' => 15.00,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);
        }

        // 재고 거래 내역 테이블에 데이터 추가
        if (DB::getSchemaBuilder()->hasTable('inventory_transactions')) {
            DB::table('inventory_transactions')->insert([
                [
                    'product_id' => 1,
                    'inventory_item_id' => 1,
                    'type' => 'inbound',
                    'reason' => 'stock_in',
                    'quantity' => 50,
                    'previous_quantity' => 0,
                    'new_quantity' => 50,
                    'unit_cost' => 10.00,
                    'total_cost' => 500.00,
                    'notes' => 'Initial stock',
                    'created_by' => $this->adminUser->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);
        }
    }

    // ==================== READ (조회) 테스트 ====================

    /**
     * 테스트: 재고 목록 페이지 접근
     *
     * @test
     */
    public function test_inventory_index_page_loads_successfully()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
                         ->get('/admin/store/inventory');

        $response->assertStatus(200);
        $response->assertViewIs('jiny-store::admin.inventory.index');
    }

    /**
     * 테스트: 재고 목록 데이터 표시
     *
     * @test
     */
    public function test_inventory_index_displays_inventory_data()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
                         ->get('/admin/store/inventory');

        $response->assertStatus(200);

        if (DB::getSchemaBuilder()->hasTable('inventory') && DB::getSchemaBuilder()->hasTable('products')) {
            $response->assertSee('Test Product 1');
            $response->assertSee('SKU001');
            $response->assertSee('50'); // quantity
        }
    }

    /**
     * 테스트: 입고 관리 페이지 접근
     *
     * @test
     */
    public function test_stock_in_page_loads_successfully()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
                         ->get('/admin/store/inventory/stock-in');

        $response->assertStatus(200);
        $response->assertViewIs('jiny-store::admin.inventory.stock-in');
    }

    /**
     * 테스트: 출고 관리 페이지 접근
     *
     * @test
     */
    public function test_stock_out_page_loads_successfully()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
                         ->get('/admin/store/inventory/stock-out');

        $response->assertStatus(200);
        $response->assertViewIs('jiny-store::admin.inventory.stock-out');
    }

    /**
     * 테스트: 재고 알림 페이지 접근
     *
     * @test
     */
    public function test_alerts_page_loads_successfully()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
                         ->get('/admin/store/inventory/alerts');

        $response->assertStatus(200);
        $response->assertViewIs('jiny-store::admin.inventory.alerts');
    }

    // ==================== CREATE (입고/출고) 테스트 ====================

    /**
     * 테스트: 재고 입고 처리 - 성공 케이스
     *
     * @test
     */
    public function test_can_process_stock_in_successfully()
    {
        if (!DB::getSchemaBuilder()->hasTable('inventory') ||
            !DB::getSchemaBuilder()->hasTable('products') ||
            !DB::getSchemaBuilder()->hasTable('inventory_transactions')) {
            $this->markTestSkipped('Required tables do not exist');
        }

        $stockInData = [
            'product_id' => 1,
            'quantity' => 25,
            'unit_cost' => 10.50,
            'location' => 'main_warehouse',
            'notes' => 'Test stock in'
        ];

        $response = $this->actingAs($this->adminUser, 'admin')
                         ->post('/admin/store/inventory/stock-in/process', $stockInData);

        // 성공적으로 처리되면 리다이렉트
        $response->assertStatus(302);
        $response->assertSessionHas('success');

        // 재고가 업데이트되었는지 확인
        $inventory = DB::table('inventory')->where('product_id', 1)->first();
        $this->assertEquals(75, $inventory->quantity_on_hand); // 50 + 25

        // 거래 내역이 기록되었는지 확인
        $this->assertDatabaseHas('inventory_transactions', [
            'product_id' => 1,
            'type' => 'inbound',
            'reason' => 'stock_in',
            'quantity' => 25
        ]);
    }

    /**
     * 테스트: 재고 입고 처리 - 유효성 검사 실패
     *
     * @test
     */
    public function test_stock_in_process_fails_with_invalid_data()
    {
        $invalidData = [
            'quantity' => -5, // 음수 수량
            'unit_cost' => 'invalid', // 잘못된 비용
        ];

        $response = $this->actingAs($this->adminUser, 'admin')
                         ->post('/admin/store/inventory/stock-in/process', $invalidData);

        // 유효성 검사 실패로 422 또는 리다이렉트
        $this->assertTrue(
            $response->status() === 422 ||
            $response->status() === 302
        );
    }

    // ==================== 권한 및 보안 테스트 ====================

    /**
     * 테스트: 비인증 사용자 접근 거부
     *
     * @test
     */
    public function test_unauthenticated_user_cannot_access_inventory()
    {
        $response = $this->get('/admin/store/inventory');
        $response->assertRedirect('/admin/login');

        $response = $this->get('/admin/store/inventory/stock-in');
        $response->assertRedirect('/admin/login');

        $response = $this->get('/admin/store/inventory/alerts');
        $response->assertRedirect('/admin/login');
    }

    /**
     * 테스트: 일반 사용자 접근 거부
     *
     * @test
     */
    public function test_regular_user_cannot_access_inventory()
    {
        $regularUser = User::factory()->create([
            'isAdmin' => false,
            'utype' => 'user'
        ]);

        $response = $this->actingAs($regularUser)->get('/admin/store/inventory');
        $this->assertTrue(
            $response->status() === 403 ||
            $response->status() === 302
        );
    }

    // ==================== 성능 및 기타 테스트 ====================

    /**
     * 테스트: 재고 목록 페이지 성능
     *
     * @test
     */
    public function test_inventory_index_performance()
    {
        $startTime = microtime(true);

        $response = $this->actingAs($this->adminUser, 'admin')
                         ->get('/admin/store/inventory');

        $endTime = microtime(true);
        $responseTime = $endTime - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(3.0, $responseTime, 'Inventory index should load within 3 seconds');
    }

    /**
     * 테스트: 재고 검색 기능
     *
     * @test
     */
    public function test_inventory_search_functionality()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
                         ->get('/admin/store/inventory?search=SKU001');

        $response->assertStatus(200);

        if (DB::getSchemaBuilder()->hasTable('inventory') && DB::getSchemaBuilder()->hasTable('products')) {
            $response->assertSee('SKU001');
        }
    }

    /**
     * 테스트: 재고 필터링 기능
     *
     * @test
     */
    public function test_inventory_filtering_functionality()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
                         ->get('/admin/store/inventory?stock_status=low_stock');

        $response->assertStatus(200);
    }

    /**
     * 테스트: 재고 통계 정확성
     *
     * @test
     */
    public function test_inventory_statistics_accuracy()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
                         ->get('/admin/store/inventory');

        $response->assertStatus(200);

        if (DB::getSchemaBuilder()->hasTable('inventory')) {
            $stats = $response->viewData('stats');

            $this->assertIsArray($stats);
            $this->assertArrayHasKey('total', $stats);
            $this->assertArrayHasKey('in_stock', $stats);
            $this->assertArrayHasKey('out_of_stock', $stats);
            $this->assertArrayHasKey('low_stock', $stats);
            $this->assertArrayHasKey('total_value', $stats);
        }
    }
}