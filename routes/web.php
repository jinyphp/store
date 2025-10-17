<?php

use Illuminate\Support\Facades\Route;

/**
 * Product (상품) 사용자 페이지 라우트
 *
 * @description
 * 사용자가 접근할 수 있는 상품 기능을 제공합니다.
 */
Route::middleware('web')->prefix('products')->name('products.')->group(function () {
    // 상품 목록
    Route::get('/', \Jiny\Store\Http\Controllers\Site\Products\IndexController::class)
        ->name('index');

    // 상품 상세보기
    Route::get('/{id}', \Jiny\Store\Http\Controllers\Site\Products\ShowController::class)
        ->name('show');

    // 상품 카테고리별 목록
    Route::get('/category/{category}', \Jiny\Store\Http\Controllers\Site\Products\CategoryController::class)
        ->name('category');

    // 상품 검색
    Route::get('/search', \Jiny\Store\Http\Controllers\Site\Products\SearchController::class)
        ->name('search');
});

/**
 * Cart (장바구니) 사용자 페이지 라우트
 *
 * @description
 * 장바구니 기능을 제공합니다.
 */
Route::middleware('web')->prefix('cart')->name('cart.')->group(function () {
    // 장바구니 목록
    Route::get('/', \Jiny\Store\Http\Controllers\Site\Cart\IndexController::class)
        ->name('index');

    // 장바구니에 상품 추가
    Route::post('/add', \Jiny\Store\Http\Controllers\Site\Cart\AddController::class)
        ->name('add');

    // 장바구니 상품 수량 업데이트
    Route::put('/{id}', \Jiny\Store\Http\Controllers\Site\Cart\UpdateController::class)
        ->name('update');

    // 장바구니에서 상품 제거
    Route::delete('/{id}', \Jiny\Store\Http\Controllers\Site\Cart\RemoveController::class)
        ->name('remove');

    // 장바구니 비우기
    Route::delete('/', \Jiny\Store\Http\Controllers\Site\Cart\ClearController::class)
        ->name('clear');

    // 장바구니 요약 정보 (AJAX)
    Route::get('/summary', \Jiny\Store\Http\Controllers\Site\Cart\SummaryController::class)
        ->name('summary');
});

/**
 * Checkout (주문결제) 사용자 페이지 라우트
 *
 * @description
 * 주문 및 결제 기능을 제공합니다.
 */
Route::middleware(['web', 'auth'])->prefix('checkout')->name('checkout.')->group(function () {
    // 주문결제 시작
    Route::get('/', \Jiny\Store\Http\Controllers\Site\Orders\CheckoutController::class)
        ->name('index');

    // 주문 정보 입력
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

/**
 * Orders (주문내역) 사용자 페이지 라우트
 *
 * @description
 * 사용자 주문 내역을 조회하는 기능을 제공합니다.
 */
Route::middleware(['web', 'auth'])->prefix('orders')->name('orders.')->group(function () {
    // 주문 목록
    Route::get('/', \Jiny\Store\Http\Controllers\Site\Orders\IndexController::class)
        ->name('index');

    // 주문 상세보기
    Route::get('/{id}', \Jiny\Store\Http\Controllers\Site\Orders\ShowController::class)
        ->name('show');

    // 주문 취소 요청
    Route::post('/{id}/cancel', \Jiny\Store\Http\Controllers\Site\Orders\CancelRequestController::class)
        ->name('cancel');

    // 주문 반품 요청
    Route::post('/{id}/return', \Jiny\Store\Http\Controllers\Site\Orders\ReturnRequestController::class)
        ->name('return');
});

/**
 * Wishlist (위시리스트) 사용자 페이지 라우트 (추후 구현)
 *
 * @description
 * 위시리스트 기능을 제공합니다.
 */
Route::middleware(['web', 'auth'])->prefix('wishlist')->name('wishlist.')->group(function () {
    // 위시리스트 목록
    // Route::get('/', WishlistIndexController::class)->name('index');

    // 위시리스트에 상품 추가
    // Route::post('/add/{productId}', WishlistAddController::class)->name('add');

    // 위시리스트에서 상품 제거
    // Route::delete('/{id}', WishlistRemoveController::class)->name('remove');
});

/**
 * Shop 메인 페이지 라우트
 *
 * @description
 * 쇼핑몰 메인 페이지를 제공합니다.
 */
Route::middleware('web')->prefix('shop')->name('shop.')->group(function () {
    // 쇼핑몰 메인 페이지
    Route::get('/', \Jiny\Store\Http\Controllers\Site\ShopController::class)
        ->name('index');

    // 특가 상품
    Route::get('/deals', \Jiny\Store\Http\Controllers\Site\DealsController::class)
        ->name('deals');

    // 신상품
    Route::get('/new-arrivals', \Jiny\Store\Http\Controllers\Site\NewArrivalsController::class)
        ->name('new-arrivals');

    // 베스트셀러
    Route::get('/bestsellers', \Jiny\Store\Http\Controllers\Site\BestsellersController::class)
        ->name('bestsellers');
});