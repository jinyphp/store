<?php

namespace Jiny\Store\Http\Controllers\Admin\Products;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Products 상세보기 컨트롤러
 */
class ShowController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->config = [
            'table' => 'store_products',
            'view' => 'jiny-store::admin.products.show',
            'title' => 'Product 상세보기',
        ];
    }

    public function __invoke(Request $request, $id)
    {
        // 상품 정보 조회 (카테고리 포함)
        $product = DB::table($this->config['table'])
            ->leftJoin('store_categories', 'store_products.category_id', '=', 'store_categories.id')
            ->select(
                'store_products.id',
                'store_products.slug',
                'store_products.title',
                'store_products.description',
                'store_products.content',
                'store_products.price',
                'store_products.sale_price',
                'store_products.image',
                'store_products.images',
                'store_products.features',
                'store_products.specifications',
                'store_products.tags',
                'store_products.meta_title',
                'store_products.meta_description',
                'store_products.enable',
                'store_products.featured',
                'store_products.category_id',
                'store_products.view_count',
                'store_products.created_at',
                'store_products.updated_at',
                'store_categories.title as category_name',
                'store_categories.slug as category_slug'
            )
            ->where('store_products.id', $id)
            ->whereNull('store_products.deleted_at')
            ->first();

        if (!$product) {
            return redirect()
                ->route('admin.store.products.index')
                ->with('error', 'Product를 찾을 수 없습니다.');
        }

        // 가격 옵션 조회
        $pricingOptions = DB::table('site_product_pricing')
            ->where('product_id', $id)
            ->whereNull('deleted_at')
            ->orderBy('pos')
            ->orderBy('price')
            ->get();

        // 이미지 갤러리 조회
        $images = DB::table('store_product_images')
            ->where('product_id', $id)
            ->whereNull('deleted_at')
            ->where('enable', true)
            ->orderBy('featured', 'desc')
            ->orderBy('pos')
            ->get();

        return view($this->config['view'], [
            'product' => $product,
            'pricingOptions' => $pricingOptions,
            'images' => $images,
            'config' => $this->config,
        ]);
    }
}