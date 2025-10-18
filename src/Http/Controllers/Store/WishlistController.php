<?php

namespace Jiny\Store\Http\Controllers\Store;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

/**
 * 찜목록(위시리스트) 컨트롤러
 */
class WishlistController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->config = [
            'view' => 'jiny-store::store.wishlist.index',
            'title' => '찜목록',
        ];
    }

    /**
     * 찜목록 페이지
     */
    public function __invoke(Request $request)
    {
        $wishlistItems = $this->getWishlistItems();

        return view($this->config['view'], [
            'config' => $this->config,
            'wishlistItems' => $wishlistItems,
        ]);
    }

    /**
     * 찜목록에 추가
     */
    public function add(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:product,service',
            'item_id' => 'required|integer',
        ]);

        $type = $validated['type'];
        $itemId = $validated['item_id'];

        // 상품/서비스 정보 확인
        $item = $this->getItemInfo($type, $itemId);
        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => '상품/서비스를 찾을 수 없습니다.'
            ], 404);
        }

        // 찜목록에 추가
        $wishlistId = $this->addToWishlist([
            'type' => $type,
            'item_id' => $itemId,
            'title' => $item->title,
            'image' => $item->image,
            'description' => $item->description,
        ]);

        $wishlistCount = $this->getWishlistCount();

        return response()->json([
            'success' => true,
            'message' => '찜목록에 추가되었습니다.',
            'wishlist_count' => $wishlistCount,
            'wishlist_id' => $wishlistId,
        ]);
    }

    /**
     * 찜목록에서 제거
     */
    public function remove(Request $request, $id)
    {
        $wishlist = $this->getWishlist();

        if (!isset($wishlist[$id])) {
            return response()->json([
                'success' => false,
                'message' => '찜목록 항목을 찾을 수 없습니다.'
            ], 404);
        }

        unset($wishlist[$id]);
        Session::put('store_wishlist', $wishlist);

        $wishlistCount = $this->getWishlistCount();

        return response()->json([
            'success' => true,
            'message' => '찜목록에서 제거되었습니다.',
            'wishlist_count' => $wishlistCount,
        ]);
    }

    /**
     * 찜목록 비우기
     */
    public function clear(Request $request)
    {
        Session::forget('store_wishlist');

        return response()->json([
            'success' => true,
            'message' => '찜목록이 비워졌습니다.',
            'wishlist_count' => 0,
        ]);
    }

    /**
     * 찜목록 상품 개수
     */
    public function count(Request $request)
    {
        $count = $this->getWishlistCount();

        return response()->json([
            'count' => $count
        ]);
    }

    /**
     * 찜목록 데이터 가져오기
     */
    protected function getWishlist()
    {
        return Session::get('store_wishlist', []);
    }

    /**
     * 찜목록에 아이템 추가
     */
    protected function addToWishlist($item)
    {
        $wishlist = $this->getWishlist();
        
        // 중복 체크
        $existingKey = null;
        foreach ($wishlist as $key => $wishlistItem) {
            if ($wishlistItem['type'] === $item['type'] && 
                $wishlistItem['item_id'] === $item['item_id']) {
                $existingKey = $key;
                break;
            }
        }

        if ($existingKey) {
            // 이미 존재하는 경우 업데이트
            $wishlist[$existingKey]['updated_at'] = now();
            $wishlistId = $existingKey;
        } else {
            // 새 항목 추가
            $wishlistId = uniqid('wishlist_');
            $item['id'] = $wishlistId;
            $item['created_at'] = now();
            $item['updated_at'] = now();
            $wishlist[$wishlistId] = $item;
        }

        Session::put('store_wishlist', $wishlist);
        return $wishlistId;
    }

    /**
     * 찜목록 아이템 목록 가져오기
     */
    protected function getWishlistItems()
    {
        $wishlist = $this->getWishlist();
        $items = collect($wishlist)->map(function ($item) {
            // 현재 가격 정보 가져오기
            $pricing = $this->getItemPricing($item['type'], $item['item_id']);
            if ($pricing) {
                $item['price'] = $pricing->price;
                $item['sale_price'] = $pricing->sale_price;
                $item['currency'] = $pricing->currency;
                $item['price_formatted'] = \Jiny\Store\Helpers\CurrencyHelper::formatCurrency(
                    $pricing->sale_price ?? $pricing->price, 
                    $pricing->currency
                );
            }

            // 상품 상태 확인 (재고, 활성화 등)
            $itemInfo = $this->getItemInfo($item['type'], $item['item_id']);
            $item['available'] = $itemInfo && $itemInfo->enable;
            $item['stock_status'] = $this->getStockStatus($item['type'], $item['item_id']);

            return (object) $item;
        })->sortByDesc('updated_at');

        return $items;
    }

    /**
     * 찜목록 상품 개수
     */
    protected function getWishlistCount()
    {
        $wishlist = $this->getWishlist();
        return count($wishlist);
    }

    /**
     * 상품/서비스 정보 가져오기
     */
    protected function getItemInfo($type, $itemId)
    {
        $table = $type === 'product' ? 'store_products' : 'store_services';

        return DB::table($table)
            ->where('id', $itemId)
            ->where('enable', true)
            ->whereNull('deleted_at')
            ->first();
    }

    /**
     * 가격 정보 가져오기
     */
    protected function getItemPricing($type, $itemId)
    {
        $table = $type === 'product' ? 'store_product_pricing' : 'store_service_pricing';

        return DB::table($table)
            ->where($type . '_id', $itemId)
            ->where('enable', true)
            ->where('is_default', true)
            ->first();
    }

    /**
     * 재고 상태 확인
     */
    protected function getStockStatus($type, $itemId)
    {
        if ($type === 'service') {
            return 'available'; // 서비스는 일반적으로 재고 제한 없음
        }

        // 상품 재고 확인
        $inventory = DB::table('store_product_inventory')
            ->where('product_id', $itemId)
            ->first();

        if (!$inventory) {
            return 'unknown';
        }

        if ($inventory->stock_quantity <= 0) {
            return 'out_of_stock';
        } elseif ($inventory->stock_quantity <= $inventory->low_stock_threshold) {
            return 'low_stock';
        } else {
            return 'in_stock';
        }
    }
}
