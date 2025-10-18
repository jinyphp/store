<?php

namespace Jiny\Store\Http\Controllers\Store;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 통합 검색 컨트롤러
 */
class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = $request->get('q', '');
        $type = $request->get('type', 'all'); // all, product, service
        $page = $request->get('page', 1);
        $perPage = 12;

        $results = [];
        $totalCount = 0;

        if (strlen($query) >= 2) {
            if ($type === 'all' || $type === 'product') {
                $products = $this->searchProducts($query, $type === 'product' ? $page : 1, $type === 'product' ? $perPage : 6);
                $results['products'] = $products['data'];
                $totalCount += $products['total'];
            }

            if ($type === 'all' || $type === 'service') {
                $services = $this->searchServices($query, $type === 'service' ? $page : 1, $type === 'service' ? $perPage : 6);
                $results['services'] = $services['data'];
                $totalCount += $services['total'];
            }
        }

        // 검색어 저장 (통계용)
        if (!empty($query)) {
            $this->saveSearchQuery($query);
        }

        // 인기 검색어
        $popularSearches = $this->getPopularSearches();

        // 추천 검색어
        $suggestions = $this->getSearchSuggestions($query);

        return view('jiny-store::store.search.index', [
            'query' => $query,
            'type' => $type,
            'results' => $results,
            'totalCount' => $totalCount,
            'popularSearches' => $popularSearches,
            'suggestions' => $suggestions,
            'hasResults' => $totalCount > 0,
            'currentPage' => $page
        ]);
    }

    /**
     * 상품 검색
     */
    protected function searchProducts($query, $page = 1, $perPage = 12)
    {
        $offset = ($page - 1) * $perPage;

        $searchQuery = DB::table('store_products')
            ->leftJoin('store_categories', 'store_products.category_id', '=', 'store_categories.id')
            ->select(
                'store_products.*',
                'store_categories.title as category_name',
                'store_categories.slug as category_slug'
            )
            ->where('store_products.enable', true)
            ->whereNull('store_products.deleted_at')
            ->where(function($q) use ($query) {
                $q->where('store_products.title', 'like', "%{$query}%")
                  ->orWhere('store_products.description', 'like', "%{$query}%")
                  ->orWhere('store_products.tags', 'like', "%{$query}%")
                  ->orWhere('store_categories.title', 'like', "%{$query}%");
            });

        $total = $searchQuery->count();
        $products = $searchQuery
            ->orderByRaw("CASE
                WHEN store_products.title LIKE '%{$query}%' THEN 1
                WHEN store_categories.title LIKE '%{$query}%' THEN 2
                WHEN store_products.description LIKE '%{$query}%' THEN 3
                ELSE 4
            END")
            ->orderBy('store_products.featured', 'desc')
            ->orderBy('store_products.view_count', 'desc')
            ->offset($offset)
            ->limit($perPage)
            ->get();

        return [
            'data' => $products,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ];
    }

    /**
     * 서비스 검색
     */
    protected function searchServices($query, $page = 1, $perPage = 12)
    {
        $offset = ($page - 1) * $perPage;

        $searchQuery = DB::table('store_services')
            ->leftJoin('store_categories', 'store_services.category_id', '=', 'store_categories.id')
            ->select(
                'store_services.*',
                'store_categories.title as category_name',
                'store_categories.slug as category_slug'
            )
            ->where('store_services.enable', true)
            ->whereNull('store_services.deleted_at')
            ->where(function($q) use ($query) {
                $q->where('store_services.title', 'like', "%{$query}%")
                  ->orWhere('store_services.description', 'like', "%{$query}%")
                  ->orWhere('store_services.tags', 'like', "%{$query}%")
                  ->orWhere('store_categories.title', 'like', "%{$query}%");
            });

        $total = $searchQuery->count();
        $services = $searchQuery
            ->orderByRaw("CASE
                WHEN store_services.title LIKE '%{$query}%' THEN 1
                WHEN store_categories.title LIKE '%{$query}%' THEN 2
                WHEN store_services.description LIKE '%{$query}%' THEN 3
                ELSE 4
            END")
            ->orderBy('store_services.featured', 'desc')
            ->orderBy('store_services.view_count', 'desc')
            ->offset($offset)
            ->limit($perPage)
            ->get();

        return [
            'data' => $services,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ];
    }

    /**
     * 검색어 저장
     */
    protected function saveSearchQuery($query)
    {
        try {
            $existingQuery = DB::table('store_search_queries')
                ->where('query', $query)
                ->first();

            if ($existingQuery) {
                DB::table('store_search_queries')
                    ->where('id', $existingQuery->id)
                    ->increment('count');
            } else {
                DB::table('store_search_queries')->insert([
                    'query' => $query,
                    'count' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        } catch (\Exception $e) {
            // 테이블이 없는 경우 무시
        }
    }

    /**
     * 인기 검색어 조회
     */
    protected function getPopularSearches($limit = 10)
    {
        try {
            return DB::table('store_search_queries')
                ->orderBy('count', 'desc')
                ->orderBy('updated_at', 'desc')
                ->limit($limit)
                ->pluck('query')
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 검색 추천어 생성
     */
    protected function getSearchSuggestions($query, $limit = 5)
    {
        if (strlen($query) < 2) {
            return [];
        }

        try {
            // 제품명에서 유사한 검색어 찾기
            $suggestions = DB::table('store_products')
                ->select('title')
                ->where('title', 'like', "%{$query}%")
                ->where('enable', true)
                ->whereNull('deleted_at')
                ->limit($limit)
                ->pluck('title')
                ->map(function($title) {
                    return strtolower(trim($title));
                })
                ->unique()
                ->values()
                ->toArray();

            // 서비스명에서도 검색
            $serviceSuggestions = DB::table('store_services')
                ->select('title')
                ->where('title', 'like', "%{$query}%")
                ->where('enable', true)
                ->whereNull('deleted_at')
                ->limit($limit - count($suggestions))
                ->pluck('title')
                ->map(function($title) {
                    return strtolower(trim($title));
                })
                ->unique()
                ->values()
                ->toArray();

            return array_slice(array_merge($suggestions, $serviceSuggestions), 0, $limit);
        } catch (\Exception $e) {
            return [];
        }
    }
}