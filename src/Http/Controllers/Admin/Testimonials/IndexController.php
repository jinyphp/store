<?php

namespace Jiny\Store\Http\Controllers\Admin\Testimonials;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Testimonials 목록 컨트롤러
 */
class IndexController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->config = [
            'table' => 'store_testimonials',
            'view' => 'jiny-store::admin.testimonials.index',
            'title' => 'Testimonials 관리',
            'subtitle' => '고객 후기와 평가를 관리합니다.',
            'per_page' => 15,
        ];
    }

    public function __invoke(Request $request, $type = null, $itemId = null)
    {
        $query = $this->buildQuery();

        // Filter by specific product/service if provided
        if ($type && $itemId) {
            $query->where('type', $type)->where('item_id', $itemId);
        }

        $query = $this->applyFilters($query, $request);

        $testimonials = $query->orderBy('featured', 'desc')
            ->orderBy('rating', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($this->config['per_page'])
            ->withQueryString();

        $stats = $this->getStatistics($type, $itemId);

        return view($this->config['view'], [
            'testimonials' => $testimonials,
            'stats' => $stats,
            'config' => $this->config,
            'type' => $type,
            'itemId' => $itemId,
        ]);
    }

    protected function buildQuery()
    {
        return DB::table($this->config['table'])
            ->leftJoin('users', 'store_testimonials.user_id', '=', 'users.id')
            ->leftJoin('store_products', function($join) {
                $join->on('store_testimonials.item_id', '=', 'store_products.id')
                     ->where('store_testimonials.type', '=', 'product');
            })
            ->leftJoin('store_services', function($join) {
                $join->on('store_testimonials.item_id', '=', 'store_services.id')
                     ->where('store_testimonials.type', '=', 'service');
            })
            ->select(
                'store_testimonials.*',
                'users.name as user_name',
                'users.email as user_email',
                'store_products.title as product_title',
                'store_services.title as service_title'
            )
            ->whereNull('store_testimonials.deleted_at');
    }

    protected function applyFilters($query, Request $request)
    {
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('store_testimonials.headline', 'like', "%{$search}%")
                  ->orWhere('store_testimonials.content', 'like', "%{$search}%")
                  ->orWhere('store_testimonials.name', 'like', "%{$search}%")
                  ->orWhere('store_testimonials.company', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('store_testimonials.type', $request->get('type'));
        }

        if ($request->filled('rating')) {
            $query->where('store_testimonials.rating', $request->get('rating'));
        }

        if ($request->filled('featured')) {
            $query->where('store_testimonials.featured', $request->get('featured') === '1');
        }

        if ($request->filled('verified')) {
            $query->where('store_testimonials.verified', $request->get('verified') === '1');
        }

        if ($request->filled('enable')) {
            $query->where('store_testimonials.enable', $request->get('enable') === '1');
        }

        return $query;
    }

    protected function getStatistics($type = null, $itemId = null)
    {
        $table = $this->config['table'];
        $query = DB::table($table)->whereNull('deleted_at');

        if ($type && $itemId) {
            $query->where('type', $type)->where('item_id', $itemId);
        }

        $base = clone $query;

        return [
            'total' => $base->count(),
            'enabled' => (clone $query)->where('enable', true)->count(),
            'featured' => (clone $query)->where('featured', true)->count(),
            'verified' => (clone $query)->where('verified', true)->count(),
            'five_stars' => (clone $query)->where('rating', 5)->count(),
            'four_stars' => (clone $query)->where('rating', 4)->count(),
            'three_stars' => (clone $query)->where('rating', 3)->count(),
            'two_stars' => (clone $query)->where('rating', 2)->count(),
            'one_star' => (clone $query)->where('rating', 1)->count(),
            'average_rating' => round((clone $query)->avg('rating'), 1),
            'total_likes' => (clone $query)->sum('likes_count'),
        ];
    }
}