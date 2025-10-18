<?php

namespace Jiny\Store\Http\Controllers\Store\Main;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Store 메인 페이지 컨트롤러
 *
 * 사용자용 스토어 메인 페이지를 담당합니다.
 * 경로: /store
 */
class IndexController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->loadConfig();
    }

    protected function loadConfig()
    {
        $this->config = [
            'title' => '온라인 스토어',
            'subtitle' => '다양한 상품과 서비스를 만나보세요',
            'view' => 'jiny-store::site.store.index',
        ];
    }

    public function __invoke(Request $request)
    {
        // 메인 페이지 데이터 수집
        $featuredProducts = $this->getFeaturedProducts();
        $newArrivals = $this->getNewArrivals();
        $bestSellers = $this->getBestSellers();
        $categories = $this->getCategories();
        $testimonials = $this->getRecentTestimonials();

        return view('jiny-store::store.index', [
            'config' => $this->config,
            'featuredProducts' => $featuredProducts,
            'newArrivals' => $newArrivals,
            'bestSellers' => $bestSellers,
            'categories' => $categories,
            'testimonials' => $testimonials,
        ]);
    }

    /**
     * 추천 상품 조회
     */
    protected function getFeaturedProducts()
    {
        try {
            return DB::table('store_products')
                ->select(
                    'id',
                    'title',
                    'price',
                    'sale_price',
                    'image',
                    'description'
                )
                ->where('enable', true)
                ->where('featured', true)
                ->whereNull('deleted_at')
                ->orderBy('pos', 'asc')
                ->limit(8)
                ->get();
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    /**
     * 신상품 조회
     */
    protected function getNewArrivals()
    {
        try {
            return DB::table('store_products')
                ->select(
                    'id',
                    'title',
                    'price',
                    'sale_price',
                    'image',
                    'created_at'
                )
                ->where('enable', true)
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'desc')
                ->limit(6)
                ->get();
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    /**
     * 베스트셀러 조회
     */
    protected function getBestSellers()
    {
        try {
            return DB::table('store_products')
                ->select(
                    'id',
                    'title',
                    'price',
                    'sale_price',
                    'image',
                    'view_count'
                )
                ->where('enable', true)
                ->whereNull('deleted_at')
                ->orderBy('view_count', 'desc')
                ->limit(6)
                ->get();
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    /**
     * 카테고리 조회
     */
    protected function getCategories()
    {
        try {
            return DB::table('store_categories')
                ->select(
                    'id',
                    'title',
                    'slug',
                    'image',
                    'description'
                )
                ->where('enable', true)
                ->orderBy('pos', 'asc')
                ->limit(8)
                ->get();
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    /**
     * 최근 고객 후기 조회
     */
    protected function getRecentTestimonials()
    {
        try {
            return DB::table('testimonials')
                ->leftJoin('users', 'testimonials.user_id', '=', 'users.id')
                ->select(
                    'testimonials.id',
                    'testimonials.title',
                    'testimonials.content',
                    'testimonials.rating',
                    'testimonials.created_at',
                    'users.name as customer_name'
                )
                ->where('testimonials.enable', true)
                ->orderBy('testimonials.created_at', 'desc')
                ->limit(3)
                ->get();
        } catch (\Exception $e) {
            return collect([]);
        }
    }
}