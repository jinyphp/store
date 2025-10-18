<?php

use Illuminate\Support\Facades\Route;

/**
 * Store Frontend Routes
 *
 * 모든 스토어 프론트엔드 라우트는 /store/* 패턴을 사용합니다.
 */

Route::prefix('store')->name('store.')->group(function () {

    // 스토어 메인 페이지
    Route::get('/', \Jiny\Store\Http\Controllers\Store\Main\IndexController::class)->name('index');

    // 카테고리별 상품 목록
    Route::get('/category/{category}', \Jiny\Store\Http\Controllers\Store\CategoryController::class)->name('category');

    // 상품 관련
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', \Jiny\Store\Http\Controllers\Store\ProductController::class)->name('index');
        Route::get('/{id}', [\Jiny\Store\Http\Controllers\Store\ProductController::class, 'show'])->name('show');
        Route::get('/{id}/reviews', [\Jiny\Store\Http\Controllers\Store\ProductController::class, 'reviews'])->name('reviews');
    });

    // 서비스 관련
    Route::prefix('services')->name('services.')->group(function () {
        Route::get('/', \Jiny\Store\Http\Controllers\Store\ServiceController::class)->name('index');
        Route::get('/{id}', [\Jiny\Store\Http\Controllers\Store\ServiceController::class, 'show'])->name('show');
        Route::get('/{id}/reviews', [\Jiny\Store\Http\Controllers\Store\ServiceController::class, 'reviews'])->name('reviews');
    });

    // 장바구니
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', \Jiny\Store\Http\Controllers\Store\CartController::class)->name('index');
        Route::post('/add', [\Jiny\Store\Http\Controllers\Store\CartController::class, 'add'])->name('add');
        Route::put('/{id}', [\Jiny\Store\Http\Controllers\Store\CartController::class, 'update'])->name('update');
        Route::delete('/{id}', [\Jiny\Store\Http\Controllers\Store\CartController::class, 'remove'])->name('remove');
        Route::post('/clear', [\Jiny\Store\Http\Controllers\Store\CartController::class, 'clear'])->name('clear');
        Route::get('/count', [\Jiny\Store\Http\Controllers\Store\CartController::class, 'count'])->name('count');
    });

    // 찜목록 (위시리스트)
    Route::prefix('wishlist')->name('wishlist.')->group(function () {
        Route::get('/', \Jiny\Store\Http\Controllers\Store\WishlistController::class)->name('index');
        Route::post('/add', [\Jiny\Store\Http\Controllers\Store\WishlistController::class, 'add'])->name('add');
        Route::delete('/{id}', [\Jiny\Store\Http\Controllers\Store\WishlistController::class, 'remove'])->name('remove');
        Route::post('/clear', [\Jiny\Store\Http\Controllers\Store\WishlistController::class, 'clear'])->name('clear');
        Route::get('/count', [\Jiny\Store\Http\Controllers\Store\WishlistController::class, 'count'])->name('count');
    });

    // 주문 프로세스
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/', \Jiny\Store\Http\Controllers\Store\CheckoutController::class)->name('index');
        Route::get('/shipping', [\Jiny\Store\Http\Controllers\Store\CheckoutController::class, 'shipping'])->name('shipping');
        Route::post('/shipping', [\Jiny\Store\Http\Controllers\Store\CheckoutController::class, 'updateShipping'])->name('shipping.update');
        Route::get('/payment', [\Jiny\Store\Http\Controllers\Store\CheckoutController::class, 'payment'])->name('payment');
        Route::post('/payment', [\Jiny\Store\Http\Controllers\Store\CheckoutController::class, 'processPayment'])->name('payment.process');
        Route::get('/review', [\Jiny\Store\Http\Controllers\Store\CheckoutController::class, 'review'])->name('review');
        Route::post('/complete', [\Jiny\Store\Http\Controllers\Store\CheckoutController::class, 'complete'])->name('complete');
    });

    // 주문 관리 (사용자)
    Route::prefix('orders')->name('orders.')->middleware('auth')->group(function () {
        Route::get('/', \Jiny\Store\Http\Controllers\Store\OrderController::class)->name('index');
        Route::get('/{id}', [\Jiny\Store\Http\Controllers\Store\OrderController::class, 'show'])->name('show');
        Route::post('/{id}/cancel', [\Jiny\Store\Http\Controllers\Store\OrderController::class, 'cancel'])->name('cancel');
        Route::get('/{id}/invoice', [\Jiny\Store\Http\Controllers\Store\OrderController::class, 'invoice'])->name('invoice');
    });

    // 검색
    Route::get('/search', \Jiny\Store\Http\Controllers\Store\SearchController::class)->name('search');

    // 비교
    Route::prefix('compare')->name('compare.')->group(function () {
        Route::get('/', \Jiny\Store\Http\Controllers\Store\CompareController::class)->name('index');
        Route::post('/add', [\Jiny\Store\Http\Controllers\Store\CompareController::class, 'add'])->name('add');
        Route::delete('/{id}', [\Jiny\Store\Http\Controllers\Store\CompareController::class, 'remove'])->name('remove');
        Route::post('/clear', [\Jiny\Store\Http\Controllers\Store\CompareController::class, 'clear'])->name('clear');
    });

    // 리뷰 및 평가
    Route::prefix('reviews')->name('reviews.')->middleware('auth')->group(function () {
        Route::post('/', [\Jiny\Store\Http\Controllers\Store\ReviewController::class, 'store'])->name('store');
        Route::put('/{id}', [\Jiny\Store\Http\Controllers\Store\ReviewController::class, 'update'])->name('update');
        Route::delete('/{id}', [\Jiny\Store\Http\Controllers\Store\ReviewController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/like', [\Jiny\Store\Http\Controllers\Store\ReviewController::class, 'like'])->name('like');
    });

    // API 엔드포인트 (AJAX용)
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/products/{id}', [\Jiny\Store\Http\Controllers\Store\ApiController::class, 'product'])->name('product');
        Route::get('/cart/summary', [\Jiny\Store\Http\Controllers\Store\ApiController::class, 'cartSummary'])->name('cart.summary');
        Route::get('/shipping/calculate', [\Jiny\Store\Http\Controllers\Store\ApiController::class, 'calculateShipping'])->name('shipping.calculate');
        Route::get('/inventory/{id}', [\Jiny\Store\Http\Controllers\Store\ApiController::class, 'inventory'])->name('inventory');
    });
});
