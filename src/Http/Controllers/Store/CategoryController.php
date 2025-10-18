<?php

namespace Jiny\Store\Http\Controllers\Store;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Store\Helpers\CurrencyHelper;

/**
 * 카테고리별 상품 목록 컨트롤러
 */
class CategoryController extends Controller
{
    public function __invoke(Request $request, $category)
    {
        // 카테고리 정보 조회 (slug 또는 ID)
        $categoryInfo = DB::table('store_categories')
            ->where(function($query) use ($category) {
                if (is_numeric($category)) {
                    $query->where('id', $category)->orWhere('slug', $category);
                } else {
                    $query->where('slug', $category);
                }
            })
            ->where('enable', true)
            ->whereNull('deleted_at')
            ->first();

        if (!$categoryInfo) {
            abort(404, 'Category not found');
        }

        // 페이지네이션 파라미터
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 12);
        $offset = ($page - 1) * $perPage;

        // 상품 목록 조회
        $query = DB::table('store_products')
            ->leftJoin('store_categories', 'store_products.category_id', '=', 'store_categories.id')
            ->select(
                'store_products.*',
                'store_categories.title as category_name',
                'store_categories.slug as category_slug'
            )
            ->where('store_products.category_id', $categoryInfo->id)
            ->where('store_products.enable', true)
            ->whereNull('store_products.deleted_at');

        // 검색 필터
        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('store_products.title', 'like', "%{$search}%")
                  ->orWhere('store_products.description', 'like', "%{$search}%");
            });
        }

        // 가격 필터
        if ($minPrice = $request->get('price_min')) {
            $query->where('store_products.price', '>=', $minPrice);
        }
        if ($maxPrice = $request->get('price_max')) {
            $query->where('store_products.price', '<=', $maxPrice);
        }

        // 정렬
        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'price_low':
                $query->orderBy('store_products.price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('store_products.price', 'desc');
                break;
            case 'popular':
                $query->orderBy('store_products.view_count', 'desc');
                break;
            case 'rating':
                $query->orderBy('store_products.rating', 'desc');
                break;
            case 'name':
                $query->orderBy('store_products.title', 'asc');
                break;
            case 'newest':
            default:
                $query->orderBy('store_products.created_at', 'desc');
                break;
        }

        // 총 개수 조회
        $total = $query->count();

        // 상품 목록 조회
        $products = $query->offset($offset)->limit($perPage)->get();

        // 페이지네이션 정보
        $pagination = [
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total)
        ];

        // 필터 옵션
        $filters = [
            'sort_options' => [
                'newest' => '최신순',
                'popular' => '인기순',
                'price_low' => '가격 낮은순',
                'price_high' => '가격 높은순',
                'rating' => '평점순',
                'name' => '이름순'
            ],
            'price_ranges' => [
                ['min' => 0, 'max' => 10000, 'label' => '1만원 이하'],
                ['min' => 10000, 'max' => 50000, 'label' => '1만원 - 5만원'],
                ['min' => 50000, 'max' => 100000, 'label' => '5만원 - 10만원'],
                ['min' => 100000, 'max' => null, 'label' => '10만원 이상']
            ]
        ];

        return view('jiny-store::store.category.index', [
            'category' => $categoryInfo,
            'products' => $products,
            'pagination' => $pagination,
            'filters' => $filters,
            'currentFilters' => [
                'search' => $request->get('search'),
                'sort' => $sort,
                'price_min' => $request->get('price_min'),
                'price_max' => $request->get('price_max')
            ]
        ]);
    }
}