<?php

use Illuminate\Support\Facades\Route;

/**
 * Store (스토어) 사용자 페이지 라우트
 *
 * @description
 * /store/* 경로로 사용자용 스토어 기능을 제공합니다.
 */
/**
 * Store 사용자 라우트 - 임시 비활성화 (컨트롤러 구현 필요)
 */
/*
Route::middleware('web')->prefix('store')->name('store.')->group(function () {

    // 스토어 메인 페이지
    Route::get('/', \Jiny\Store\Http\Controllers\Site\Store\IndexController::class)
        ->name('index');

    // 상품 관련 라우트
    Route::prefix('products')->name('products.')->group(function () {
        // 상품 목록
        Route::get('/', \Jiny\Store\Http\Controllers\Site\Products\IndexController::class)
            ->name('index');

        // 상품 상세보기
        Route::get('/{id}', \Jiny\Store\Http\Controllers\Site\Products\ShowController::class)
            ->name('show')->where('id', '[0-9]+');

        // 상품 카테고리별 목록
        Route::get('/category/{category}', \Jiny\Store\Http\Controllers\Site\Products\CategoryController::class)
            ->name('category');

        // 상품 검색
        Route::get('/search', \Jiny\Store\Http\Controllers\Site\Products\SearchController::class)
            ->name('search');
    });

    // 서비스 관련 라우트
    Route::prefix('services')->name('services.')->group(function () {
        // 서비스 목록
        Route::get('/', \Jiny\Store\Http\Controllers\Site\Services\IndexController::class)
            ->name('index');

        // 서비스 상세보기
        Route::get('/{id}', \Jiny\Store\Http\Controllers\Site\Services\ShowController::class)
            ->name('show')->where('id', '[0-9]+');

        // 서비스 카테고리별 목록
        Route::get('/category/{category}', \Jiny\Store\Http\Controllers\Site\Services\CategoryController::class)
            ->name('category');
    });

    // 장바구니 관련 라우트
    Route::prefix('cart')->name('cart.')->group(function () {
        // 장바구니 목록
        Route::get('/', \Jiny\Store\Http\Controllers\Site\Cart\IndexController::class)
            ->name('index');

        // 장바구니에 상품 추가
        Route::post('/add', \Jiny\Store\Http\Controllers\Site\Cart\AddController::class)
            ->name('add');

        // 장바구니 상품 수량 업데이트
        Route::put('/{id}', \Jiny\Store\Http\Controllers\Site\Cart\UpdateController::class)
            ->name('update')->where('id', '[0-9]+');

        // 장바구니에서 상품 제거
        Route::delete('/{id}', \Jiny\Store\Http\Controllers\Site\Cart\RemoveController::class)
            ->name('remove')->where('id', '[0-9]+');

        // 장바구니 개수 조회 (AJAX)
        Route::get('/count', \Jiny\Store\Http\Controllers\Site\Cart\CountController::class)
            ->name('count');
    });

    // 주문/결제 관련 라우트 (로그인 필요)
    Route::middleware('auth')->prefix('checkout')->name('checkout.')->group(function () {
        // 주문결제 시작
        Route::get('/', \Jiny\Store\Http\Controllers\Site\Orders\CheckoutController::class)
            ->name('index');

        // 주문 정보 입력 단계
        Route::get('/step/{step}', \Jiny\Store\Http\Controllers\Site\Orders\CheckoutStepController::class)
            ->name('step')->where('step', '[1-4]');

        // 주문 정보 저장
        Route::post('/step/{step}', \Jiny\Store\Http\Controllers\Site\Orders\CheckoutStepController::class)
            ->name('step.store')->where('step', '[1-4]');

        // 주문 완료
        Route::post('/complete', \Jiny\Store\Http\Controllers\Site\Orders\CompleteController::class)
            ->name('complete');

        // 주문 취소
        Route::post('/cancel', \Jiny\Store\Http\Controllers\Site\Orders\CancelController::class)
            ->name('cancel');
    });

    // 사용자 주문 내역 (로그인 필요)
    Route::middleware('auth')->prefix('orders')->name('orders.')->group(function () {
        // 주문 목록
        Route::get('/', \Jiny\Store\Http\Controllers\Site\Orders\IndexController::class)
            ->name('index');

        // 주문 상세보기
        Route::get('/{id}', \Jiny\Store\Http\Controllers\Site\Orders\ShowController::class)
            ->name('show')->where('id', '[0-9]+');

        // 주문 취소 요청
        Route::post('/{id}/cancel', \Jiny\Store\Http\Controllers\Site\Orders\CancelRequestController::class)
            ->name('cancel')->where('id', '[0-9]+');

        // 주문 반품 요청
        Route::post('/{id}/return', \Jiny\Store\Http\Controllers\Site\Orders\ReturnRequestController::class)
            ->name('return')->where('id', '[0-9]+');
    });

    // 위시리스트 (로그인 필요)
    Route::middleware('auth')->prefix('wishlist')->name('wishlist.')->group(function () {
        // 위시리스트 목록
        Route::get('/', \Jiny\Store\Http\Controllers\Site\Wishlist\IndexController::class)
            ->name('index');

        // 위시리스트에 상품 추가
        Route::post('/add/{productId}', \Jiny\Store\Http\Controllers\Site\Wishlist\AddController::class)
            ->name('add')->where('productId', '[0-9]+');

        // 위시리스트에서 상품 제거
        Route::delete('/{id}', \Jiny\Store\Http\Controllers\Site\Wishlist\RemoveController::class)
            ->name('remove')->where('id', '[0-9]+');
    });

    // 고객 후기
    Route::prefix('testimonials')->name('testimonials.')->group(function () {
        // 후기 목록
        Route::get('/', \Jiny\Store\Http\Controllers\Site\Testimonials\IndexController::class)
            ->name('index');

        // 후기 작성 (로그인 필요)
        Route::middleware('auth')->post('/', \Jiny\Store\Http\Controllers\Site\Testimonials\StoreController::class)
            ->name('store');

        // 후기 좋아요 (로그인 필요)
        Route::middleware('auth')->post('/{id}/like', \Jiny\Store\Http\Controllers\Site\Testimonials\LikeController::class)
            ->name('like')->where('id', '[0-9]+');
    });

    // 특별 페이지들
    Route::prefix('specials')->name('specials.')->group(function () {
        // 특가 상품
        Route::get('/deals', \Jiny\Store\Http\Controllers\Site\Specials\DealsController::class)
            ->name('deals');

        // 신상품
        Route::get('/new-arrivals', \Jiny\Store\Http\Controllers\Site\Specials\NewArrivalsController::class)
            ->name('new-arrivals');

        // 베스트셀러
        Route::get('/bestsellers', \Jiny\Store\Http\Controllers\Site\Specials\BestsellersController::class)
            ->name('bestsellers');

        // 추천 상품
        Route::get('/featured', \Jiny\Store\Http\Controllers\Site\Specials\FeaturedController::class)
            ->name('featured');
    });

});
*/