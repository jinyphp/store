<?php

namespace Jiny\Store\Http\Controllers\Admin\Testimonials;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Testimonials 상세보기 컨트롤러
 */
class ShowController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->config = [
            'table' => 'store_testimonials',
            'view' => 'jiny-store::admin.testimonials.show',
            'title' => 'Testimonial 상세보기',
        ];
    }

    public function __invoke(Request $request, $id)
    {
        $testimonial = DB::table($this->config['table'])
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
                'store_products.slug as product_slug',
                'store_services.title as service_title',
                'store_services.slug as service_slug'
            )
            ->where('store_testimonials.id', $id)
            ->whereNull('store_testimonials.deleted_at')
            ->first();

        if (!$testimonial) {
            return redirect()
                ->route('admin.store.testimonials.index')
                ->with('error', 'Testimonial을 찾을 수 없습니다.');
        }

        // Get likes for this testimonial
        $likes = DB::table('site_testimonial_likes')
            ->leftJoin('users', 'site_testimonial_likes.user_id', '=', 'users.id')
            ->select(
                'site_testimonial_likes.*',
                'users.name as user_name'
            )
            ->where('testimonial_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view($this->config['view'], [
            'testimonial' => $testimonial,
            'likes' => $likes,
            'config' => $this->config,
        ]);
    }
}