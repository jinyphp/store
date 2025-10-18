<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Products 관리 기능 테스트
 *
 * 테스트 대상:
 * - GET /admin/store/products - 상품 목록
 * - GET /admin/store/products/create - 상품 생성 폼
 * - POST /admin/store/products - 상품 저장
 * - GET /admin/store/products/{id} - 상품 상세
 * - GET /admin/store/products/{id}/edit - 상품 수정 폼
 * - PUT /admin/store/products/{id} - 상품 업데이트
 * - DELETE /admin/store/products/{id} - 상품 삭제
 */
class ProductsManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $testProduct;

    protected function setUp(): void
    {
        parent::setUp();

        // 관리자 사용자 생성
        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'name' => 'Products Admin',
            'isAdmin' => true,
            'utype' => 'admin'
        ]);

        // 테스트용 상품 데이터 생성
        $this->createTestProduct();
    }

    /**
     * 테스트용 상품 생성
     */
    protected function createTestProduct()
    {
        if (DB::getSchemaBuilder()->hasTable('store_products')) {
            $productId = DB::table('store_products')->insertGetId([
                'title' => 'Test Product',
                'description' => 'Test Product Description',
                'price' => 10000,
                'sale_price' => 8000,
                'enable' => true,
                'view_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->testProduct = DB::table('store_products')->where('id', $productId)->first();
        }
    }

    // ==================== READ (조회) 테스트 ====================

    /**
     * 테스트: 상품 목록 페이지 접근
     *
     * @test
     */
    public function test_products_index_page_loads_successfully()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
                         ->get('/admin/store/products');

        $response->assertStatus(200);
        $response->assertViewIs('jiny-store::admin.products.index');
    }

    /**
     * 테스트: 상품 목록 데이터 표시
     *
     * @test
     */
    public function test_products_index_displays_products()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
                         ->get('/admin/store/products');

        $response->assertStatus(200);

        if ($this->testProduct) {
            $response->assertSee($this->testProduct->title);
            $response->assertSee(number_format($this->testProduct->price));
        }
    }

    /**
     * 테스트: 상품 생성 폼 페이지
     *
     * @test
     */
    public function test_products_create_form_loads_successfully()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
                         ->get('/admin/store/products/create');

        $response->assertStatus(200);
        $response->assertViewIs('jiny-store::admin.products.create');
    }

    /**
     * 테스트: 상품 상세 페이지
     *
     * @test
     */
    public function test_products_show_page_loads_successfully()
    {
        if (!$this->testProduct) {
            $this->markTestSkipped('No test product available');
        }

        $response = $this->actingAs($this->adminUser, 'admin')
                         ->get("/admin/store/products/{$this->testProduct->id}");

        $response->assertStatus(200);
        $response->assertViewIs('jiny-store::admin.products.show');
        $response->assertSee($this->testProduct->title);
    }

    /**
     * 테스트: 상품 수정 폼 페이지
     *
     * @test
     */
    public function test_products_edit_form_loads_successfully()
    {
        if (!$this->testProduct) {
            $this->markTestSkipped('No test product available');
        }

        $response = $this->actingAs($this->adminUser, 'admin')
                         ->get("/admin/store/products/{$this->testProduct->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('jiny-store::admin.products.edit');
        $response->assertSee($this->testProduct->title);
    }

    // ==================== CREATE (생성) 테스트 ====================

    /**
     * 테스트: 상품 생성 - 성공 케이스
     *
     * @test
     */
    public function test_can_create_new_product_successfully()
    {
        if (!DB::getSchemaBuilder()->hasTable('store_products')) {
            $this->markTestSkipped('store_products table does not exist');
        }

        $productData = [
            'title' => 'New Test Product',
            'description' => 'New product description',
            'price' => 15000,
            'sale_price' => 12000,
            'enable' => true,
            'category' => 'electronics',
        ];

        $response = $this->actingAs($this->adminUser, 'admin')
                         ->post('/admin/store/products', $productData);

        // 성공적으로 생성되면 리다이렉트
        $this->assertTrue(
            $response->status() === 201 ||
            $response->status() === 302
        );

        // 데이터베이스에서 생성 확인
        $this->assertDatabaseHas('store_products', [
            'title' => 'New Test Product',
            'price' => 15000,
        ]);
    }

    /**
     * 테스트: 상품 생성 - 필수 필드 누락
     *
     * @test
     */
    public function test_product_creation_fails_with_missing_required_fields()
    {
        $invalidData = [
            'description' => 'Product without title',
            // title 필드 누락
        ];

        $response = $this->actingAs($this->adminUser, 'admin')
                         ->post('/admin/store/products', $invalidData);

        // 유효성 검사 실패로 422 또는 리다이렉트
        $this->assertTrue(
            $response->status() === 422 ||
            $response->status() === 302
        );
    }

    /**
     * 테스트: 상품 생성 - 이미지 업로드
     *
     * @test
     */
    public function test_can_create_product_with_image_upload()
    {
        if (!DB::getSchemaBuilder()->hasTable('store_products')) {
            $this->markTestSkipped('store_products table does not exist');
        }

        Storage::fake('public');

        $image = UploadedFile::fake()->image('product.jpg', 800, 600);

        $productData = [
            'title' => 'Product with Image',
            'description' => 'Product with uploaded image',
            'price' => 20000,
            'image' => $image,
            'enable' => true,
        ];

        $response = $this->actingAs($this->adminUser, 'admin')
                         ->post('/admin/store/products', $productData);

        $this->assertTrue(
            $response->status() === 201 ||
            $response->status() === 302
        );

        // 이미지 파일이 저장되었는지 확인
        Storage::disk('public')->assertExists('products/' . $image->hashName());
    }

    // ==================== UPDATE (수정) 테스트 ====================

    /**
     * 테스트: 상품 수정 - 성공 케이스
     *
     * @test
     */
    public function test_can_update_existing_product_successfully()
    {
        if (!$this->testProduct) {
            $this->markTestSkipped('No test product available');
        }

        $updateData = [
            'title' => 'Updated Product Title',
            'description' => 'Updated description',
            'price' => 25000,
            'sale_price' => 20000,
            'enable' => false,
        ];

        $response = $this->actingAs($this->adminUser, 'admin')
                         ->put("/admin/store/products/{$this->testProduct->id}", $updateData);

        $this->assertTrue(
            $response->status() === 200 ||
            $response->status() === 302
        );

        // 데이터베이스에서 수정 확인
        $this->assertDatabaseHas('store_products', [
            'id' => $this->testProduct->id,
            'title' => 'Updated Product Title',
            'price' => 25000,
        ]);
    }

    /**
     * 테스트: 상품 부분 수정
     *
     * @test
     */
    public function test_can_partially_update_product()
    {
        if (!$this->testProduct) {
            $this->markTestSkipped('No test product available');
        }

        $partialUpdateData = [
            'price' => 30000,
            // 다른 필드는 수정하지 않음
        ];

        $response = $this->actingAs($this->adminUser, 'admin')
                         ->put("/admin/store/products/{$this->testProduct->id}", $partialUpdateData);

        $this->assertTrue(
            $response->status() === 200 ||
            $response->status() === 302
        );

        // 가격만 변경되고 다른 데이터는 유지되는지 확인
        $updatedProduct = DB::table('store_products')->where('id', $this->testProduct->id)->first();
        $this->assertEquals(30000, $updatedProduct->price);
        $this->assertEquals($this->testProduct->title, $updatedProduct->title);
    }

    // ==================== DELETE (삭제) 테스트 ====================

    /**
     * 테스트: 상품 삭제 - 성공 케이스
     *
     * @test
     */
    public function test_can_delete_product_successfully()
    {
        if (!$this->testProduct) {
            $this->markTestSkipped('No test product available');
        }

        $response = $this->actingAs($this->adminUser, 'admin')
                         ->delete("/admin/store/products/{$this->testProduct->id}");

        $this->assertTrue(
            $response->status() === 200 ||
            $response->status() === 302 ||
            $response->status() === 204
        );

        // 소프트 삭제인지 하드 삭제인지에 따라 다르게 검증
        if (DB::getSchemaBuilder()->hasColumn('store_products', 'deleted_at')) {
            // 소프트 삭제의 경우
            $this->assertDatabaseHas('store_products', [
                'id' => $this->testProduct->id,
            ]);
            // deleted_at이 null이 아닌지 확인
            $deletedProduct = DB::table('store_products')->where('id', $this->testProduct->id)->first();
            $this->assertNotNull($deletedProduct->deleted_at ?? null);
        } else {
            // 하드 삭제의 경우
            $this->assertDatabaseMissing('store_products', [
                'id' => $this->testProduct->id,
            ]);
        }
    }

    /**
     * 테스트: 존재하지 않는 상품 삭제
     *
     * @test
     */
    public function test_delete_nonexistent_product_returns_404()
    {
        $nonExistentId = 99999;

        $response = $this->actingAs($this->adminUser, 'admin')
                         ->delete("/admin/store/products/{$nonExistentId}");

        $response->assertStatus(404);
    }

    // ==================== 권한 및 보안 테스트 ====================

    /**
     * 테스트: 비인증 사용자 접근 거부
     *
     * @test
     */
    public function test_unauthenticated_user_cannot_access_products()
    {
        $response = $this->get('/admin/store/products');
        $response->assertRedirect('/admin/login');

        $response = $this->post('/admin/store/products', []);
        $response->assertRedirect('/admin/login');
    }

    /**
     * 테스트: 일반 사용자 접근 거부
     *
     * @test
     */
    public function test_regular_user_cannot_access_products()
    {
        $regularUser = User::factory()->create([
            'isAdmin' => false,
            'utype' => 'user'
        ]);

        $response = $this->actingAs($regularUser)->get('/admin/store/products');
        $this->assertTrue(
            $response->status() === 403 ||
            $response->status() === 302
        );
    }

    // ==================== 성능 및 기타 테스트 ====================

    /**
     * 테스트: 상품 목록 페이지 성능
     *
     * @test
     */
    public function test_products_index_performance()
    {
        $startTime = microtime(true);

        $response = $this->actingAs($this->adminUser, 'admin')
                         ->get('/admin/store/products');

        $endTime = microtime(true);
        $responseTime = $endTime - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(3.0, $responseTime, 'Products index should load within 3 seconds');
    }

    /**
     * 테스트: 상품 검색 기능
     *
     * @test
     */
    public function test_products_search_functionality()
    {
        if (!$this->testProduct) {
            $this->markTestSkipped('No test product available');
        }

        $response = $this->actingAs($this->adminUser, 'admin')
                         ->get('/admin/store/products?search=' . urlencode($this->testProduct->title));

        $response->assertStatus(200);
        $response->assertSee($this->testProduct->title);
    }

    /**
     * 테스트: 상품 필터링 기능
     *
     * @test
     */
    public function test_products_filtering_functionality()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
                         ->get('/admin/store/products?enable=1');

        $response->assertStatus(200);
    }
}