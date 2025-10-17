<?php

use Illuminate\Support\Facades\Route;

/**
 * Cart (장바구니) 관리 라우트
 *
 * @description
 * 고객 장바구니 내역을 관리하는 라우트입니다.
 * Single Action Controllers 방식으로 구현됨
 */
Route::prefix('admin/cms/cart')->name('admin.cms.cart.')->middleware(['web', 'admin'])->group(function () {
    // 장바구니 목록
    Route::get('/', \Jiny\Shop\Http\Controllers\Admin\Cart\IndexController::class)->name('index');

    // 장바구니 아이템 삭제
    Route::delete('/{id}', \Jiny\Shop\Http\Controllers\Admin\Cart\DestroyController::class)->name('destroy')->where(['id' => '[0-9]+']);

    // 일괄 작업 (대량 삭제 등)
    Route::post('/bulk-action', \Jiny\Shop\Http\Controllers\Admin\Cart\BulkActionController::class)->name('bulkAction');

    // 장바구니 통계 (AJAX) - 추후 구현 예정
    // Route::get('/stats', \Jiny\Shop\Http\Controllers\Admin\Cart\StatsController::class)->name('stats');
});

/**
 * Ecommerce (이커머스) 관리 라우트
 *
 * @description
 * 이커머스 시스템 전체를 관리하는 라우트입니다.
 * Single Action Controllers 방식으로 구현됨
 */
Route::prefix('admin/cms/ecommerce')->name('admin.cms.ecommerce.')->middleware(['web', 'admin'])->group(function () {
    // 이커머스 대시보드
    Route::get('/', \Jiny\Shop\Http\Controllers\Admin\Dashboard\IndexController::class)->name('dashboard');

    // 주문 관리
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', \Jiny\Shop\Http\Controllers\Admin\Orders\IndexController::class)->name('index');
        Route::get('/create', \Jiny\Shop\Http\Controllers\Admin\Orders\CreateController::class)->name('create');
        Route::post('/', \Jiny\Shop\Http\Controllers\Admin\Orders\StoreController::class)->name('store');
        Route::get('/{id}', \Jiny\Shop\Http\Controllers\Admin\Orders\ShowController::class)->name('show')->where('id', '[0-9]+');
        Route::post('/{id}', \Jiny\Shop\Http\Controllers\Admin\Orders\ShowController::class)->name('update_status')->where('id', '[0-9]+');

        // 단계별 주문 생성
        Route::get('/step/{step?}', \Jiny\Shop\Http\Controllers\Admin\Orders\StepController::class)->name('step')->where('step', '[1-4]');
        Route::post('/step/{step}', \Jiny\Shop\Http\Controllers\Admin\Orders\StepController::class)->name('step.submit')->where('step', '[1-4]');
        Route::get('/reset', [\Jiny\Shop\Http\Controllers\Admin\Orders\StepController::class, 'reset'])->name('reset');
    });

    // 프로모션 관리
    Route::prefix('promotions')->name('promotions.')->group(function () {
        Route::get('/', \Jiny\Shop\Http\Controllers\Admin\Promotions\IndexController::class)->name('index');
        Route::get('/create', \Jiny\Shop\Http\Controllers\Admin\Promotions\CreateController::class)->name('create');
        Route::post('/', \Jiny\Shop\Http\Controllers\Admin\Promotions\StoreController::class)->name('store');
        Route::get('/{id}', \Jiny\Shop\Http\Controllers\Admin\Promotions\ShowController::class)->name('show')->where('id', '[0-9]+');
        Route::get('/{id}/edit', \Jiny\Shop\Http\Controllers\Admin\Promotions\EditController::class)->name('edit')->where('id', '[0-9]+');
        Route::put('/{id}', \Jiny\Shop\Http\Controllers\Admin\Promotions\UpdateController::class)->name('update')->where('id', '[0-9]+');
        Route::delete('/{id}', \Jiny\Shop\Http\Controllers\Admin\Promotions\DestroyController::class)->name('destroy')->where('id', '[0-9]+');
    });

    // 쿠폰 관리
    Route::prefix('coupons')->name('coupons.')->group(function () {
        Route::get('/', \Jiny\Shop\Http\Controllers\Admin\Coupons\IndexController::class)->name('index');
        Route::get('/create', \Jiny\Shop\Http\Controllers\Admin\Coupons\CreateController::class)->name('create');
        Route::post('/', \Jiny\Shop\Http\Controllers\Admin\Coupons\StoreController::class)->name('store');
        Route::get('/{coupon}', \Jiny\Shop\Http\Controllers\Admin\Coupons\ShowController::class)->name('show')->where('coupon', '[0-9]+');
        Route::get('/{coupon}/edit', \Jiny\Shop\Http\Controllers\Admin\Coupons\EditController::class)->name('edit')->where('coupon', '[0-9]+');
        Route::put('/{coupon}', \Jiny\Shop\Http\Controllers\Admin\Coupons\UpdateController::class)->name('update')->where('coupon', '[0-9]+');
        Route::delete('/{coupon}', \Jiny\Shop\Http\Controllers\Admin\Coupons\DestroyController::class)->name('destroy')->where('coupon', '[0-9]+');
    });

    // 재고 관리
    Route::prefix('inventory')->name('inventory.')->group(function () {
        // 재고 대시보드 (누락된 라우트 추가)
        Route::get('/dashboard', \Jiny\Shop\Http\Controllers\Admin\Inventory\IndexController::class)->name('dashboard');

        // 재고 입고 관리
        Route::get('/stock-in', \Jiny\Shop\Http\Controllers\Admin\Inventory\StockInController::class)->name('stock-in');
        Route::post('/stock-in/process', [\Jiny\Shop\Http\Controllers\Admin\Inventory\StockInController::class, 'process'])->name('stock-in.process');

        // 재고 출고 관리
        Route::get('/stock-out', \Jiny\Shop\Http\Controllers\Admin\Inventory\StockOutController::class)->name('stock-out');
        Route::post('/stock-out/process', [\Jiny\Shop\Http\Controllers\Admin\Inventory\StockOutController::class, 'process'])->name('stock-out.process');

        // 재고 알림 관리
        Route::get('/alerts', \Jiny\Shop\Http\Controllers\Admin\Inventory\AlertsController::class)->name('alerts');
        Route::post('/alerts/update-threshold', [\Jiny\Shop\Http\Controllers\Admin\Inventory\AlertsController::class, 'updateThreshold'])->name('alerts.update-threshold');
        Route::post('/alerts/update-settings', [\Jiny\Shop\Http\Controllers\Admin\Inventory\AlertsController::class, 'updateSettings'])->name('alerts.update-settings');

        // 재고 CRUD
        Route::get('/', \Jiny\Shop\Http\Controllers\Admin\Inventory\IndexController::class)->name('index');
        Route::get('/create', \Jiny\Shop\Http\Controllers\Admin\Inventory\CreateController::class)->name('create');
        Route::post('/', \Jiny\Shop\Http\Controllers\Admin\Inventory\StoreController::class)->name('store');
        Route::get('/{id}', \Jiny\Shop\Http\Controllers\Admin\Inventory\ShowController::class)->name('show')->where(['id' => '[0-9]+']);
        Route::get('/{id}/edit', \Jiny\Shop\Http\Controllers\Admin\Inventory\EditController::class)->name('edit')->where(['id' => '[0-9]+']);
        Route::put('/{id}', \Jiny\Shop\Http\Controllers\Admin\Inventory\UpdateController::class)->name('update')->where(['id' => '[0-9]+']);
        Route::delete('/{id}', \Jiny\Shop\Http\Controllers\Admin\Inventory\DestroyController::class)->name('destroy')->where(['id' => '[0-9]+']);
    });

    // 배송 관리
    Route::prefix('shipping')->name('shipping.')->group(function () {
        // 배송 대시보드
        Route::get('/', \Jiny\Shop\Http\Controllers\Admin\Shipping\IndexController::class)->name('index');

        // 배송 지역 관리
        Route::prefix('zones')->name('zones.')->group(function () {
            Route::get('/', \Jiny\Shop\Http\Controllers\Admin\Shipping\Zones\IndexController::class)->name('index');
            Route::get('/create', \Jiny\Shop\Http\Controllers\Admin\Shipping\Zones\CreateController::class)->name('create');
        });

        // 배송 방식 관리
        Route::prefix('methods')->name('methods.')->group(function () {
            Route::get('/', \Jiny\Shop\Http\Controllers\Admin\Shipping\Methods\IndexController::class)->name('index');
        });

        // 배송 요금 관리
        Route::prefix('rates')->name('rates.')->group(function () {
            Route::get('/', \Jiny\Shop\Http\Controllers\Admin\Shipping\Rates\IndexController::class)->name('index');
        });

        // 배송비 계산기
        Route::prefix('calculator')->name('calculator.')->group(function () {
            Route::get('/', \Jiny\Shop\Http\Controllers\Admin\Shipping\Calculator\IndexController::class)->name('index');
            Route::post('/', \Jiny\Shop\Http\Controllers\Admin\Shipping\Calculator\IndexController::class)->name('calculate');
        });
    });

    // 이커머스 설정
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', \Jiny\Shop\Http\Controllers\Admin\Settings\IndexController::class)->name('index');
    });
});

/**
 * Tax (세율) 관리 라우트
 *
 * @description
 * 국가별 세율 정보를 관리하는 라우트입니다.
 */
Route::prefix('admin/cms/tax')->name('admin.cms.tax.')->middleware(['web', 'admin'])->group(function () {
    Route::get('/', \Jiny\Shop\Http\Controllers\Admin\Tax\IndexController::class)->name('index');
    Route::post('/{id}/update', [\Jiny\Shop\Http\Controllers\Admin\Tax\IndexController::class, 'updateTaxRate'])->name('update')->where(['id' => '[0-9]+']);
    Route::post('/bulk-update', [\Jiny\Shop\Http\Controllers\Admin\Tax\IndexController::class, 'bulkUpdateTaxRate'])->name('bulkUpdate');
});

/**
 * Products 관리 라우트
 *
 * @description
 * 상품 관리 시스템을 위한 라우트입니다.
 * 상품 CRUD 기능을 제공합니다.
 */
Route::prefix('admin/site/products')->middleware(['web', 'admin'])->name('admin.site.products.')->group(function () {
    Route::get('/', \Jiny\Shop\Http\Controllers\Admin\Products\IndexController::class)->name('index');
    Route::get('/create', \Jiny\Shop\Http\Controllers\Admin\Products\CreateController::class)->name('create');
    Route::post('/', \Jiny\Shop\Http\Controllers\Admin\Products\StoreController::class)->name('store');
    Route::get('/{id}', \Jiny\Shop\Http\Controllers\Admin\Products\ShowController::class)->name('show')->where(['id' => '[0-9]+']);
    Route::get('/{id}/edit', \Jiny\Shop\Http\Controllers\Admin\Products\EditController::class)->name('edit')->where(['id' => '[0-9]+']);
    Route::put('/{id}', \Jiny\Shop\Http\Controllers\Admin\Products\UpdateController::class)->name('update')->where(['id' => '[0-9]+']);
    Route::delete('/{id}', \Jiny\Shop\Http\Controllers\Admin\Products\DestroyController::class)->name('destroy')->where(['id' => '[0-9]+']);

    // Product Categories 관리
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', \Jiny\Shop\Http\Controllers\Admin\Products\Categories\IndexController::class)->name('index');
        Route::get('/create', \Jiny\Shop\Http\Controllers\Admin\Products\Categories\CreateController::class)->name('create');
        Route::post('/', \Jiny\Shop\Http\Controllers\Admin\Products\Categories\StoreController::class)->name('store');
        Route::get('/{id}', \Jiny\Shop\Http\Controllers\Admin\Products\Categories\ShowController::class)->name('show')->where(['id' => '[0-9]+']);
        Route::get('/{id}/edit', \Jiny\Shop\Http\Controllers\Admin\Products\Categories\EditController::class)->name('edit')->where(['id' => '[0-9]+']);
        Route::put('/{id}', \Jiny\Shop\Http\Controllers\Admin\Products\Categories\UpdateController::class)->name('update')->where(['id' => '[0-9]+']);
        Route::delete('/{id}', \Jiny\Shop\Http\Controllers\Admin\Products\Categories\DestroyController::class)->name('destroy')->where(['id' => '[0-9]+']);
    });

    // Product Images 관리
    Route::prefix('{productId}/images')->name('images.')->where(['productId' => '[0-9]+'])->group(function () {
        Route::get('/', \Jiny\Shop\Http\Controllers\Admin\Products\Images\IndexController::class)->name('index');
        Route::post('/', \Jiny\Shop\Http\Controllers\Admin\Products\Images\StoreController::class)->name('store');
        Route::get('/{id}', \Jiny\Shop\Http\Controllers\Admin\Products\Images\ShowController::class)->name('show')->where(['id' => '[0-9]+']);
        Route::put('/{id}', \Jiny\Shop\Http\Controllers\Admin\Products\Images\UpdateController::class)->name('update')->where(['id' => '[0-9]+']);
        Route::delete('/{id}', \Jiny\Shop\Http\Controllers\Admin\Products\Images\DestroyController::class)->name('destroy')->where(['id' => '[0-9]+']);
        Route::post('/reorder', \Jiny\Shop\Http\Controllers\Admin\Products\Images\ReorderController::class)->name('reorder');
        Route::post('/{id}/toggle-featured', \Jiny\Shop\Http\Controllers\Admin\Products\Images\ToggleFeaturedController::class)->name('toggle-featured')->where(['id' => '[0-9]+']);
        Route::post('/{id}/toggle-enable', \Jiny\Shop\Http\Controllers\Admin\Products\Images\ToggleEnableController::class)->name('toggle-enable')->where(['id' => '[0-9]+']);
    });

    // Product Pricing 관리
    Route::prefix('{productId}/pricing')->name('pricing.')->where(['productId' => '[0-9]+'])->group(function () {
        Route::get('/', \Jiny\Shop\Http\Controllers\Admin\Products\Pricing\IndexController::class)->name('index');
        Route::get('/create', \Jiny\Shop\Http\Controllers\Admin\Products\Pricing\CreateController::class)->name('create');
        Route::post('/', \Jiny\Shop\Http\Controllers\Admin\Products\Pricing\StoreController::class)->name('store');
        Route::get('/{id}/edit', \Jiny\Shop\Http\Controllers\Admin\Products\Pricing\EditController::class)->name('edit')->where(['id' => '[0-9]+']);
        Route::put('/{id}', \Jiny\Shop\Http\Controllers\Admin\Products\Pricing\UpdateController::class)->name('update')->where(['id' => '[0-9]+']);
        Route::delete('/{id}', \Jiny\Shop\Http\Controllers\Admin\Products\Pricing\DestroyController::class)->name('destroy')->where(['id' => '[0-9]+']);
    });
});

/**
 * Site Ecommerce Inventory 관리 라우트
 *
 * @description
 * 사이트 이커머스 재고 관리 시스템을 위한 라우트입니다.
 * admin.site.ecommerce.inventory 네임스페이스로 접근 가능합니다.
 */
Route::prefix('admin/site/ecommerce')->middleware(['web', 'admin'])->name('admin.site.ecommerce.')->group(function () {
    // 재고 관리
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', \Jiny\Shop\Http\Controllers\Admin\Inventory\IndexController::class)->name('index');
        Route::get('/create', \Jiny\Shop\Http\Controllers\Admin\Inventory\CreateController::class)->name('create');
        Route::post('/', \Jiny\Shop\Http\Controllers\Admin\Inventory\StoreController::class)->name('store');
        Route::get('/{id}', \Jiny\Shop\Http\Controllers\Admin\Inventory\ShowController::class)->name('show')->where(['id' => '[0-9]+']);
        Route::get('/{id}/edit', \Jiny\Shop\Http\Controllers\Admin\Inventory\EditController::class)->name('edit')->where(['id' => '[0-9]+']);
        Route::put('/{id}', \Jiny\Shop\Http\Controllers\Admin\Inventory\UpdateController::class)->name('update')->where(['id' => '[0-9]+']);
        Route::delete('/{id}', \Jiny\Shop\Http\Controllers\Admin\Inventory\DestroyController::class)->name('destroy')->where(['id' => '[0-9]+']);
    });
});