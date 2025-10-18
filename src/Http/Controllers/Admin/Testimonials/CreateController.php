<?php

namespace Jiny\Store\Http\Controllers\Admin\Testimonials;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Testimonials 생성 폼 컨트롤러
 */
class CreateController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->config = [
            'view' => 'jiny-store::admin.testimonials.create',
            'title' => 'Testimonial 생성',
        ];
    }

    public function __invoke(Request $request, $type = null, $itemId = null)
    {
        // Get products and services for selection
        $products = DB::table('store_products')
            ->select('id', 'title')
            ->where('enable', true)
            ->whereNull('deleted_at')
            ->orderBy('title')
            ->get();

        $services = DB::table('store_services')
            ->select('id', 'title')
            ->where('enable', true)
            ->whereNull('deleted_at')
            ->orderBy('title')
            ->get();

        // Get specific item if type and itemId provided
        $selectedItem = null;
        if ($type && $itemId) {
            if ($type === 'product') {
                $selectedItem = DB::table('store_products')
                    ->where('id', $itemId)
                    ->first();
            } elseif ($type === 'service') {
                $selectedItem = DB::table('store_services')
                    ->where('id', $itemId)
                    ->first();
            }
        }

        return view($this->config['view'], [
            'products' => $products,
            'services' => $services,
            'selectedItem' => $selectedItem,
            'selectedType' => $type,
            'selectedItemId' => $itemId,
            'config' => $this->config,
        ]);
    }
}