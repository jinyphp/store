<?php

namespace Jiny\Store\Http\Controllers\Store;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * 상품/서비스 리뷰 컨트롤러
 */
class ReviewController extends Controller
{
    /**
     * 리뷰 작성
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => '로그인이 필요합니다.'
            ], 401);
        }

        $request->validate([
            'item_type' => 'required|in:product,service',
            'item_id' => 'required|integer',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'required|string|max:200',
            'content' => 'required|string|max:1000'
        ]);

        // 이미 리뷰를 작성했는지 확인
        $existingReview = DB::table('store_reviews')
            ->where('user_id', $user->id)
            ->where('item_type', $request->item_type)
            ->where('item_id', $request->item_id)
            ->whereNull('deleted_at')
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => '이미 리뷰를 작성하셨습니다.'
            ], 422);
        }

        // 리뷰 저장
        $reviewId = DB::table('store_reviews')->insertGetId([
            'user_id' => $user->id,
            'item_type' => $request->item_type,
            'item_id' => $request->item_id,
            'rating' => $request->rating,
            'title' => $request->title,
            'content' => $request->content,
            'enable' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => '리뷰가 작성되었습니다.',
            'review_id' => $reviewId
        ]);
    }

    /**
     * 리뷰 수정
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => '로그인이 필요합니다.'
            ], 401);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'required|string|max:200',
            'content' => 'required|string|max:1000'
        ]);

        // 본인의 리뷰인지 확인
        $review = DB::table('store_reviews')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => '리뷰를 찾을 수 없습니다.'
            ], 404);
        }

        // 리뷰 수정
        DB::table('store_reviews')
            ->where('id', $id)
            ->update([
                'rating' => $request->rating,
                'title' => $request->title,
                'content' => $request->content,
                'updated_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => '리뷰가 수정되었습니다.'
        ]);
    }

    /**
     * 리뷰 삭제
     */
    public function destroy(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => '로그인이 필요합니다.'
            ], 401);
        }

        // 본인의 리뷰인지 확인
        $review = DB::table('store_reviews')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => '리뷰를 찾을 수 없습니다.'
            ], 404);
        }

        // 리뷰 삭제 (소프트 삭제)
        DB::table('store_reviews')
            ->where('id', $id)
            ->update([
                'deleted_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => '리뷰가 삭제되었습니다.'
        ]);
    }

    /**
     * 리뷰 좋아요/취소
     */
    public function like(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => '로그인이 필요합니다.'
            ], 401);
        }

        // 리뷰 존재 확인
        $review = DB::table('store_reviews')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => '리뷰를 찾을 수 없습니다.'
            ], 404);
        }

        // 이미 좋아요를 눌렀는지 확인
        $existingLike = DB::table('store_review_likes')
            ->where('review_id', $id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingLike) {
            // 좋아요 취소
            DB::table('store_review_likes')
                ->where('id', $existingLike->id)
                ->delete();

            // 리뷰 좋아요 수 감소
            DB::table('store_reviews')
                ->where('id', $id)
                ->decrement('likes_count');

            $message = '좋아요를 취소했습니다.';
            $liked = false;
        } else {
            // 좋아요 추가
            DB::table('store_review_likes')->insert([
                'review_id' => $id,
                'user_id' => $user->id,
                'created_at' => now()
            ]);

            // 리뷰 좋아요 수 증가
            DB::table('store_reviews')
                ->where('id', $id)
                ->increment('likes_count');

            $message = '좋아요를 눌렀습니다.';
            $liked = true;
        }

        // 현재 좋아요 수 조회
        $likesCount = DB::table('store_reviews')
            ->where('id', $id)
            ->value('likes_count') ?? 0;

        return response()->json([
            'success' => true,
            'message' => $message,
            'liked' => $liked,
            'likes_count' => $likesCount
        ]);
    }
}