<?php

namespace Jiny\Store\Http\Controllers\Store;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 스토어 메인 페이지 컨트롤러
 */
class IndexController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->config = [
            'view' => 'jiny-store::store.index',
            'title' => 'JinyShop - 온라인 스토어',
            'subtitle' => '다양한 상품과 서비스를 만나보세요',
        ];
    }

    public function __invoke(Request $request)
    {
        // 인기 카테고리
        $categories = $this->getFeaturedCategories();

        // 추천 상품
        $featuredProducts = $this->getFeaturedProducts();

        // 인기 상품
        $popularProducts = $this->getPopularProducts();

        // 최신 상품
        $latestProducts = $this->getLatestProducts();

        // 추천 서비스
        $featuredServices = $this->getFeaturedServices();

        // 고객 후기
        $testimonials = $this->getTestimonials();

        // 배너/프로모션
        $banners = $this->getBanners();

        return view($this->config['view'], [
            'config' => $this->config,
            'categories' => $categories,
            'featuredProducts' => $featuredProducts,
            'popularProducts' => $popularProducts,
            'latestProducts' => $latestProducts,
            'featuredServices' => $featuredServices,
            'testimonials' => $testimonials,
            'banners' => $banners,
        ]);
    }

    /**
     * 인기 카테고리 조회
     */
    protected function getFeaturedCategories()
    {
        return DB::table('store_product_categories')
            ->where('enable', true)
            ->where('featured', true)
            ->orderBy('order', 'asc')
            ->limit(8)
            ->get();
    }

    /**
     * 추천 상품 조회
     */
    protected function getFeaturedProducts()
    {
        return DB::table('store_products')
            ->leftJoin('store_product_categories', 'store_products.category_id', '=', 'store_product_categories.id')
            ->select('store_products.*', 'store_product_categories.title as category_name')
            ->where('store_products.enable', true)
            ->where('store_products.featured', true)
            ->whereNull('store_products.deleted_at')
            ->orderBy('store_products.order', 'asc')
            ->limit(12)
            ->get();
    }

    /**
     * 인기 상품 조회 (주문 수 기준)
     */
    protected function getPopularProducts()
    {
        return DB::table('store_products')
            ->leftJoin('store_product_categories', 'store_products.category_id', '=', 'store_product_categories.id')
            ->leftJoin('store_order_items', function($join) {
                $join->on('store_products.id', '=', 'store_order_items.product_id')
                     ->where('store_order_items.type', '=', 'product');
            })
            ->select(
                'store_products.*',
                'store_product_categories.title as category_name',
                DB::raw('COUNT(store_order_items.id) as order_count')
            )
            ->where('store_products.enable', true)
            ->whereNull('store_products.deleted_at')
            ->groupBy('store_products.id', 'store_product_categories.title')
            ->orderBy('order_count', 'desc')
            ->limit(8)
            ->get();
    }

    /**
     * 최신 상품 조회
     */
    protected function getLatestProducts()
    {
        return DB::table('store_products')
            ->leftJoin('store_product_categories', 'store_products.category_id', '=', 'store_product_categories.id')
            ->select('store_products.*', 'store_product_categories.title as category_name')
            ->where('store_products.enable', true)
            ->whereNull('store_products.deleted_at')
            ->orderBy('store_products.created_at', 'desc')
            ->limit(8)
            ->get();
    }

    /**
     * 추천 서비스 조회
     */
    protected function getFeaturedServices()
    {
        return DB::table('store_services')
            ->leftJoin('store_service_categories', 'store_services.category_id', '=', 'store_service_categories.id')
            ->select('store_services.*', 'store_service_categories.title as category_name')
            ->where('store_services.enable', true)
            ->where('store_services.featured', true)
            ->whereNull('store_services.deleted_at')
            ->orderBy('store_services.order', 'asc')
            ->limit(6)
            ->get();
    }

    /**
     * 고객 후기 조회
     */
    protected function getTestimonials()
    {
        return DB::table('store_testimonials')
            ->leftJoin('users', 'store_testimonials.user_id', '=', 'users.id')
            ->select(
                'store_testimonials.*',
                'users.name as customer_name',
                'users.email as customer_email'
            )
            ->where('store_testimonials.enable', true)
            ->where('store_testimonials.featured', true)
            ->whereNull('store_testimonials.deleted_at')
            ->orderBy('store_testimonials.created_at', 'desc')
            ->limit(6)
            ->get();
    }

    /**
     * 배너/프로모션 조회
     */
    protected function getBanners()
    {
        // 실제 배너 테이블이 있다면 해당 테이블에서 조회
        // 현재는 샘플 데이터 반환
        return collect([
            (object) [
                'id' => 1,
                'title' => '신규 회원 특가',
                'subtitle' => '첫 구매 시 20% 할인',
                'image' => '/images/banner1.jpg',
                'link' => '/store/products',
                'button_text' => '지금 쇼핑하기'
            ],
            (object) [
                'id' => 2,
                'title' => '무료 배송',
                'subtitle' => '5만원 이상 구매 시',
                'image' => '/images/banner2.jpg',
                'link' => '/store/products',
                'button_text' => '상품 보기'
            ]
        ]);
    }
}
