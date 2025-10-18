<?php

namespace Jiny\Store\Http\Controllers\Store;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 상품 비교 컨트롤러
 */
class CompareController extends Controller
{
    public function __invoke(Request $request)
    {
        // 세션에서 비교 목록 가져오기
        $compareList = session()->get('compare_list', []);

        if (empty($compareList)) {
            return view('jiny-store::store.compare.index', [
                'products' => collect(),
                'compareList' => []
            ]);
        }

        // 비교 상품 정보 조회
        $products = DB::table('store_products')
            ->leftJoin('store_categories', 'store_products.category_id', '=', 'store_categories.id')
            ->select(
                'store_products.*',
                'store_categories.title as category_name'
            )
            ->whereIn('store_products.id', $compareList)
            ->where('store_products.enable', true)
            ->whereNull('store_products.deleted_at')
            ->get();

        // 각 제품의 가격 옵션 조회
        foreach ($products as $product) {
            $product->pricing_options = DB::table('site_product_pricing')
                ->where('product_id', $product->id)
                ->where('enable', true)
                ->whereNull('deleted_at')
                ->orderBy('price')
                ->get();
        }

        // 비교 항목 정의
        $compareFields = [
            'basic_info' => [
                'title' => '기본 정보',
                'fields' => [
                    'title' => '상품명',
                    'category_name' => '카테고리',
                    'price' => '가격',
                    'rating' => '평점'
                ]
            ],
            'description' => [
                'title' => '설명',
                'fields' => [
                    'description' => '상품 설명',
                    'features' => '주요 특징'
                ]
            ],
            'specifications' => [
                'title' => '사양',
                'fields' => [
                    'specifications' => '상세 사양'
                ]
            ],
            'pricing' => [
                'title' => '가격 옵션',
                'fields' => [
                    'pricing_options' => '가격 옵션'
                ]
            ]
        ];

        return view('jiny-store::store.compare.index', [
            'products' => $products,
            'compareList' => $compareList,
            'compareFields' => $compareFields
        ]);
    }

    /**
     * 비교 목록에 상품 추가
     */
    public function add(Request $request)
    {
        $productId = $request->input('product_id');
        $compareList = session()->get('compare_list', []);

        // 최대 4개까지만 비교 가능
        if (count($compareList) >= 4) {
            return response()->json([
                'success' => false,
                'message' => '최대 4개 상품까지 비교할 수 있습니다.'
            ]);
        }

        // 이미 추가된 상품인지 확인
        if (in_array($productId, $compareList)) {
            return response()->json([
                'success' => false,
                'message' => '이미 비교 목록에 추가된 상품입니다.'
            ]);
        }

        // 비교 목록에 추가
        $compareList[] = $productId;
        session()->put('compare_list', $compareList);

        return response()->json([
            'success' => true,
            'message' => '비교 목록에 추가되었습니다.',
            'count' => count($compareList)
        ]);
    }

    /**
     * 비교 목록에서 상품 제거
     */
    public function remove(Request $request)
    {
        $productId = $request->input('product_id');
        $compareList = session()->get('compare_list', []);

        $compareList = array_values(array_filter($compareList, function($id) use ($productId) {
            return $id != $productId;
        }));

        session()->put('compare_list', $compareList);

        return response()->json([
            'success' => true,
            'message' => '비교 목록에서 제거되었습니다.',
            'count' => count($compareList)
        ]);
    }

    /**
     * 비교 목록 비우기
     */
    public function clear(Request $request)
    {
        session()->forget('compare_list');

        return response()->json([
            'success' => true,
            'message' => '비교 목록이 비워졌습니다.'
        ]);
    }

    /**
     * 비교 목록 개수 조회 (AJAX)
     */
    public function count(Request $request)
    {
        $compareList = session()->get('compare_list', []);

        return response()->json([
            'count' => count($compareList)
        ]);
    }
}