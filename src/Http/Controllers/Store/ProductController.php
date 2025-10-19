<?php

namespace Jiny\Store\Http\Controllers\Store;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 상품 페이지 컨트롤러
 */
class ProductController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->config = [
            'view' => 'jiny-store::store.products.index',
            'detail_view' => 'jiny-store::store.products.show',
            'title' => '상품 목록',
            'per_page' => 20,
        ];
    }

    /**
     * 상품 목록
     */
    public function __invoke(Request $request)
    {
        $query = $this->buildProductQuery();
        $query = $this->applyFilters($query, $request);

        $products = $query->paginate($this->config['per_page'])
            ->withQueryString();

        $categories = $this->getCategories();
        $filters = $this->getFilterOptions();

        return view($this->config['view'], [
            'config' => $this->config,
            'products' => $products,
            'categories' => $categories,
            'filters' => $filters,
        ]);
    }

    /**
     * 상품 상세
     */
    public function show(Request $request, $id)
    {
        $product = $this->getProductDetail($id);

        if (!$product) {
            abort(404, '상품을 찾을 수 없습니다.');
        }

        // 관련 상품
        $relatedProducts = $this->getRelatedProducts($product->category_id, $id);

        // 상품 리뷰
        $reviews = $this->getProductReviews($id);
        $reviewStats = $this->getReviewStats($id);

        // 상품 옵션 (가격, 재고 등)
        $pricing = $this->getProductPricing($id);

        return view($this->config['detail_view'], [
            'config' => $this->config,
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'reviews' => $reviews,
            'reviewStats' => $reviewStats,
            'pricing' => $pricing,
        ]);
    }

    /**
     * 상품 리뷰 페이지
     */
    public function reviews(Request $request, $id)
    {
        $product = $this->getProductDetail($id);

        if (!$product) {
            abort(404, '상품을 찾을 수 없습니다.');
        }

        $reviews = $this->getProductReviews($id, true); // 페이지네이션 포함
        $reviewStats = $this->getReviewStats($id);

        return view('jiny-store::store.products.reviews', [
            'config' => $this->config,
            'product' => $product,
            'reviews' => $reviews,
            'reviewStats' => $reviewStats,
        ]);
    }

    /**
     * 상품 쿼리 빌드
     */
    protected function buildProductQuery()
    {
        return DB::table('store_products')
            ->leftJoin('store_categories', 'store_products.category_id', '=', 'store_categories.id')
            ->leftJoin('store_product_pricing', 'store_products.id', '=', 'store_product_pricing.product_id')
            ->select(
                'store_products.*',
                'store_categories.title as category_name',
                'store_categories.slug as category_slug',
                'store_product_pricing.price',
                'store_product_pricing.sale_price',
                'store_product_pricing.currency'
            )
            ->where('store_products.enable', true)
            ->whereNull('store_products.deleted_at');
    }

    /**
     * 필터 적용
     */
    protected function applyFilters($query, Request $request)
    {
        // 카테고리 필터
        if ($request->filled('category')) {
            $query->where('store_categories.slug', $request->get('category'));
        }

        // 가격 범위 필터
        if ($request->filled('price_min')) {
            $query->where('store_product_pricing.price', '>=', $request->get('price_min'));
        }
        if ($request->filled('price_max')) {
            $query->where('store_product_pricing.price', '<=', $request->get('price_max'));
        }

        // 검색어 필터
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('store_products.title', 'like', "%{$search}%")
                  ->orWhere('store_products.description', 'like', "%{$search}%")
                  ->orWhere('store_products.tags', 'like', "%{$search}%");
            });
        }

        // 정렬
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'price_low':
                $query->orderBy('store_product_pricing.price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('store_product_pricing.price', 'desc');
                break;
            case 'name':
                $query->orderBy('store_products.title', 'asc');
                break;
            case 'popular':
                // 인기순 (주문 수 기준)
                $query->leftJoin('store_order_items', function($join) {
                    $join->on('store_products.id', '=', 'store_order_items.product_id')
                         ->where('store_order_items.type', '=', 'product');
                })
                ->groupBy('store_products.id')
                ->orderBy(DB::raw('COUNT(store_order_items.id)'), 'desc');
                break;
            default: // latest
                $query->orderBy('store_products.created_at', 'desc');
        }

        return $query;
    }

    /**
     * 상품 상세 조회
     */
    protected function getProductDetail($id)
    {
        return DB::table('store_products')
            ->leftJoin('store_categories', 'store_products.category_id', '=', 'store_categories.id')
            ->leftJoin('store_product_pricing', 'store_products.id', '=', 'store_product_pricing.product_id')
            ->select(
                'store_products.*',
                'store_categories.title as category_name',
                'store_categories.slug as category_slug',
                'store_product_pricing.price',
                'store_product_pricing.sale_price',
                'store_product_pricing.currency'
            )
            ->where('store_products.id', $id)
            ->where('store_products.enable', true)
            ->whereNull('store_products.deleted_at')
            ->first();
    }

    /**
     * 관련 상품 조회
     */
    protected function getRelatedProducts($categoryId, $excludeId)
    {
        return DB::table('store_products')
            ->leftJoin('store_product_pricing', 'store_products.id', '=', 'store_product_pricing.product_id')
            ->select(
                'store_products.*',
                'store_product_pricing.price',
                'store_product_pricing.sale_price',
                'store_product_pricing.currency'
            )
            ->where('store_products.category_id', $categoryId)
            ->where('store_products.id', '!=', $excludeId)
            ->where('store_products.enable', true)
            ->whereNull('store_products.deleted_at')
            ->limit(8)
            ->get();
    }

    /**
     * 상품 리뷰 조회
     */
    protected function getProductReviews($productId, $paginate = false)
    {
        $query = DB::table('store_testimonials')
            ->leftJoin('users', 'store_testimonials.user_id', '=', 'users.id')
            ->select(
                'store_testimonials.*',
                'users.name as customer_name',
                'users.email as customer_email'
            )
            ->where('store_testimonials.type', 'product')
            ->where('store_testimonials.item_id', $productId)
            ->where('store_testimonials.enable', true)
            ->whereNull('store_testimonials.deleted_at')
            ->orderBy('store_testimonials.created_at', 'desc');

        return $paginate ? $query->paginate(10) : $query->limit(5)->get();
    }

    /**
     * 리뷰 통계
     */
    protected function getReviewStats($productId)
    {
        $stats = DB::table('store_testimonials')
            ->where('type', 'product')
            ->where('item_id', $productId)
            ->where('enable', true)
            ->whereNull('deleted_at')
            ->selectRaw('
                COUNT(*) as total_reviews,
                AVG(rating) as average_rating,
                SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
            ')
            ->first();

        return $stats;
    }

    /**
     * 상품 가격 정보
     */
    protected function getProductPricing($productId)
    {
        return DB::table('store_product_pricing')
            ->where('product_id', $productId)
            ->where('enable', true)
            ->orderBy('is_default', 'desc')
            ->get();
    }

    /**
     * 카테고리 목록
     */
    protected function getCategories()
    {
        return DB::table('store_categories')
            ->where('enable', true)
            ->orderBy('pos', 'asc')
            ->get();
    }

    /**
     * 필터 옵션
     */
    protected function getFilterOptions()
    {
        return [
            'price_ranges' => [
                ['min' => 0, 'max' => 10000, 'label' => '1만원 이하'],
                ['min' => 10000, 'max' => 50000, 'label' => '1만원 - 5만원'],
                ['min' => 50000, 'max' => 100000, 'label' => '5만원 - 10만원'],
                ['min' => 100000, 'max' => 500000, 'label' => '10만원 - 50만원'],
                ['min' => 500000, 'max' => null, 'label' => '50만원 이상'],
            ],
            'sort_options' => [
                'latest' => '최신순',
                'popular' => '인기순',
                'price_low' => '가격 낮은순',
                'price_high' => '가격 높은순',
                'name' => '이름순',
            ]
        ];
    }
}
