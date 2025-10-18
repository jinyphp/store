<?php

namespace Jiny\Store\Http\Controllers\Store;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Store\Helpers\CurrencyHelper;

/**
 * 서비스 목록 및 상세 컨트롤러
 */
class ServiceController extends Controller
{
    public function __invoke(Request $request, $id = null)
    {
        if ($id) {
            return $this->show($request, $id);
        }

        return $this->index($request);
    }

    /**
     * 서비스 목록
     */
    public function index(Request $request)
    {
        // 카테고리 목록 조회
        $categories = DB::table('store_categories')
            ->where('type', 'service')
            ->where('enable', true)
            ->whereNull('deleted_at')
            ->orderBy('pos')
            ->orderBy('title')
            ->get();

        // 서비스 목록 조회
        $query = DB::table('store_services')
            ->leftJoin('store_categories', 'store_services.category_id', '=', 'store_categories.id')
            ->select(
                'store_services.*',
                'store_categories.title as category_name',
                'store_categories.slug as category_slug'
            )
            ->where('store_services.enable', true)
            ->whereNull('store_services.deleted_at');

        // 검색 필터
        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('store_services.title', 'like', "%{$search}%")
                  ->orWhere('store_services.description', 'like', "%{$search}%");
            });
        }

        // 카테고리 필터
        if ($category = $request->get('category')) {
            $query->where('store_categories.slug', $category);
        }

        // 가격 필터
        if ($minPrice = $request->get('price_min')) {
            $query->where('store_services.price', '>=', $minPrice);
        }
        if ($maxPrice = $request->get('price_max')) {
            $query->where('store_services.price', '<=', $maxPrice);
        }

        // 정렬
        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'price_low':
                $query->orderBy('store_services.price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('store_services.price', 'desc');
                break;
            case 'popular':
                $query->orderBy('store_services.view_count', 'desc');
                break;
            case 'rating':
                $query->orderBy('store_services.rating', 'desc');
                break;
            case 'name':
                $query->orderBy('store_services.title', 'asc');
                break;
            case 'newest':
            default:
                $query->orderBy('store_services.created_at', 'desc');
                break;
        }

        $services = $query->paginate(12);

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
                ['min' => 0, 'max' => 50000, 'label' => '5만원 이하'],
                ['min' => 50000, 'max' => 100000, 'label' => '5만원 - 10만원'],
                ['min' => 100000, 'max' => 200000, 'label' => '10만원 - 20만원'],
                ['min' => 200000, 'max' => null, 'label' => '20만원 이상']
            ]
        ];

        return view('jiny-store::store.services.index', [
            'services' => $services,
            'categories' => $categories,
            'filters' => $filters,
            'config' => [
                'title' => '서비스',
                'subtitle' => '전문적인 서비스를 경험해보세요'
            ]
        ]);
    }

    /**
     * 서비스 상세
     */
    public function show(Request $request, $id)
    {
        // 서비스 정보 조회 (slug 또는 ID)
        $service = DB::table('store_services')
            ->leftJoin('store_categories', 'store_services.category_id', '=', 'store_categories.id')
            ->select(
                'store_services.*',
                'store_categories.title as category_name',
                'store_categories.slug as category_slug'
            )
            ->where(function($query) use ($id) {
                if (is_numeric($id)) {
                    $query->where('store_services.id', $id)->orWhere('store_services.slug', $id);
                } else {
                    $query->where('store_services.slug', $id);
                }
            })
            ->where('store_services.enable', true)
            ->whereNull('store_services.deleted_at')
            ->first();

        if (!$service) {
            abort(404, 'Service not found');
        }

        // 조회수 증가
        DB::table('store_services')
            ->where('id', $service->id)
            ->increment('view_count');

        // 서비스 가격 옵션 조회
        $pricingOptions = DB::table('site_service_pricing')
            ->where('service_id', $service->id)
            ->where('enable', true)
            ->whereNull('deleted_at')
            ->orderBy('pos')
            ->orderBy('price')
            ->get();

        // 서비스 이미지 갤러리 조회
        $images = DB::table('store_service_images')
            ->where('service_id', $service->id)
            ->where('enable', true)
            ->whereNull('deleted_at')
            ->orderBy('featured', 'desc')
            ->orderBy('pos')
            ->get();

        // 관련 서비스 조회
        $relatedServices = collect();
        if ($service->category_id) {
            $relatedServices = DB::table('store_services')
                ->where('category_id', $service->category_id)
                ->where('id', '!=', $service->id)
                ->where('enable', true)
                ->whereNull('deleted_at')
                ->orderBy('featured', 'desc')
                ->orderBy('view_count', 'desc')
                ->limit(4)
                ->get();
        }

        // 고객 후기 조회
        $testimonials = DB::table('store_testimonials')
            ->where('type', 'service')
            ->where('item_id', $service->id)
            ->where('enable', true)
            ->whereNull('deleted_at')
            ->orderBy('featured', 'desc')
            ->orderBy('rating', 'desc')
            ->orderBy('likes_count', 'desc')
            ->limit(6)
            ->get();

        return view('jiny-store::store.services.show', [
            'service' => $service,
            'pricingOptions' => $pricingOptions,
            'images' => $images,
            'testimonials' => $testimonials,
            'relatedServices' => $relatedServices
        ]);
    }
}