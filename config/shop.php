<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Jiny Shop Package Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for the Jiny Shop package
    | which provides ecommerce functionality including products, cart, orders,
    | inventory, and payment processing.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Package Settings
    |--------------------------------------------------------------------------
    */
    'enable' => env('JINY_SHOP_ENABLE', true),

    /*
    |--------------------------------------------------------------------------
    | Product Settings
    |--------------------------------------------------------------------------
    */
    'products' => [
        'enable' => env('JINY_PRODUCT_ENABLE', true),
        'pagination' => env('JINY_PRODUCT_PAGINATION', 20),
        'enable_reviews' => env('JINY_PRODUCT_ENABLE_REVIEWS', true),
        'enable_ratings' => env('JINY_PRODUCT_ENABLE_RATINGS', true),
        'enable_wishlist' => env('JINY_PRODUCT_ENABLE_WISHLIST', true),
        'enable_compare' => env('JINY_PRODUCT_ENABLE_COMPARE', true),
        'enable_quick_view' => env('JINY_PRODUCT_ENABLE_QUICK_VIEW', true),
        'enable_zoom' => env('JINY_PRODUCT_ENABLE_ZOOM', true),
        'image_sizes' => [
            'thumbnail' => [150, 150],
            'medium' => [400, 400],
            'large' => [800, 800],
        ],
        'enable_variants' => env('JINY_PRODUCT_ENABLE_VARIANTS', true),
        'enable_bundles' => env('JINY_PRODUCT_ENABLE_BUNDLES', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cart Settings
    |--------------------------------------------------------------------------
    */
    'cart' => [
        'enable' => env('JINY_CART_ENABLE', true),
        'session_key' => env('JINY_CART_SESSION_KEY', 'jiny_cart'),
        'persistent' => env('JINY_CART_PERSISTENT', true),
        'expire_days' => env('JINY_CART_EXPIRE_DAYS', 30),
        'max_items' => env('JINY_CART_MAX_ITEMS', 100),
        'enable_guest_cart' => env('JINY_CART_ENABLE_GUEST', true),
        'enable_saved_carts' => env('JINY_CART_ENABLE_SAVED', true),
        'auto_cleanup' => env('JINY_CART_AUTO_CLEANUP', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Order Settings
    |--------------------------------------------------------------------------
    */
    'orders' => [
        'enable' => env('JINY_ORDER_ENABLE', true),
        'pagination' => env('JINY_ORDER_PAGINATION', 15),
        'order_number_prefix' => env('JINY_ORDER_NUMBER_PREFIX', 'ORD'),
        'order_number_length' => env('JINY_ORDER_NUMBER_LENGTH', 10),
        'enable_guest_orders' => env('JINY_ORDER_ENABLE_GUEST', true),
        'require_phone' => env('JINY_ORDER_REQUIRE_PHONE', true),
        'enable_order_notes' => env('JINY_ORDER_ENABLE_NOTES', true),
        'enable_order_tracking' => env('JINY_ORDER_ENABLE_TRACKING', true),
        'auto_complete_days' => env('JINY_ORDER_AUTO_COMPLETE_DAYS', 7),
        'cancellation_window_hours' => env('JINY_ORDER_CANCELLATION_WINDOW_HOURS', 24),
    ],

    /*
    |--------------------------------------------------------------------------
    | Inventory Settings
    |--------------------------------------------------------------------------
    */
    'inventory' => [
        'enable' => env('JINY_INVENTORY_ENABLE', true),
        'track_stock' => env('JINY_INVENTORY_TRACK_STOCK', true),
        'allow_backorders' => env('JINY_INVENTORY_ALLOW_BACKORDERS', false),
        'low_stock_threshold' => env('JINY_INVENTORY_LOW_STOCK_THRESHOLD', 5),
        'out_of_stock_threshold' => env('JINY_INVENTORY_OUT_OF_STOCK_THRESHOLD', 0),
        'enable_stock_notifications' => env('JINY_INVENTORY_ENABLE_NOTIFICATIONS', true),
        'auto_reduce_stock' => env('JINY_INVENTORY_AUTO_REDUCE_STOCK', true),
        'restore_stock_on_cancel' => env('JINY_INVENTORY_RESTORE_ON_CANCEL', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Settings
    |--------------------------------------------------------------------------
    */
    'payment' => [
        'enable' => env('JINY_PAYMENT_ENABLE', true),
        'default_currency' => env('JINY_PAYMENT_DEFAULT_CURRENCY', 'KRW'),
        'supported_currencies' => ['KRW', 'USD', 'EUR', 'JPY'],
        'enable_multiple_currencies' => env('JINY_PAYMENT_ENABLE_MULTIPLE_CURRENCIES', false),
        'payment_methods' => [
            'card' => env('JINY_PAYMENT_CARD_ENABLE', true),
            'bank_transfer' => env('JINY_PAYMENT_BANK_TRANSFER_ENABLE', true),
            'virtual_account' => env('JINY_PAYMENT_VIRTUAL_ACCOUNT_ENABLE', false),
            'mobile' => env('JINY_PAYMENT_MOBILE_ENABLE', true),
        ],
        'enable_payment_plans' => env('JINY_PAYMENT_ENABLE_PLANS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Shipping Settings
    |--------------------------------------------------------------------------
    */
    'shipping' => [
        'enable' => env('JINY_SHIPPING_ENABLE', true),
        'default_method' => env('JINY_SHIPPING_DEFAULT_METHOD', 'standard'),
        'free_shipping_threshold' => env('JINY_SHIPPING_FREE_THRESHOLD', 50000),
        'enable_pickup' => env('JINY_SHIPPING_ENABLE_PICKUP', false),
        'enable_express' => env('JINY_SHIPPING_ENABLE_EXPRESS', true),
        'calculate_real_time' => env('JINY_SHIPPING_CALCULATE_REAL_TIME', false),
        'enable_tracking' => env('JINY_SHIPPING_ENABLE_TRACKING', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Tax Settings
    |--------------------------------------------------------------------------
    */
    'tax' => [
        'enable' => env('JINY_TAX_ENABLE', true),
        'default_rate' => env('JINY_TAX_DEFAULT_RATE', 10.0),
        'include_in_price' => env('JINY_TAX_INCLUDE_IN_PRICE', true),
        'calculate_on_shipping' => env('JINY_TAX_CALCULATE_ON_SHIPPING', true),
        'digital_tax_rate' => env('JINY_TAX_DIGITAL_RATE', 10.0),
    ],

    /*
    |--------------------------------------------------------------------------
    | Promotion Settings
    |--------------------------------------------------------------------------
    */
    'promotions' => [
        'enable' => env('JINY_PROMOTION_ENABLE', true),
        'enable_coupons' => env('JINY_PROMOTION_ENABLE_COUPONS', true),
        'enable_discounts' => env('JINY_PROMOTION_ENABLE_DISCOUNTS', true),
        'enable_buy_x_get_y' => env('JINY_PROMOTION_ENABLE_BUY_X_GET_Y', true),
        'enable_bulk_discounts' => env('JINY_PROMOTION_ENABLE_BULK_DISCOUNTS', true),
        'max_coupon_usage' => env('JINY_PROMOTION_MAX_COUPON_USAGE', 1),
        'enable_stackable_coupons' => env('JINY_PROMOTION_ENABLE_STACKABLE_COUPONS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    */
    'security' => [
        'enable_csrf' => env('JINY_SHOP_ENABLE_CSRF', true),
        'enable_rate_limiting' => env('JINY_SHOP_ENABLE_RATE_LIMITING', true),
        'rate_limit' => env('JINY_SHOP_RATE_LIMIT', '60,1'), // 60 requests per minute
        'enable_fraud_detection' => env('JINY_SHOP_ENABLE_FRAUD_DETECTION', true),
        'max_order_amount' => env('JINY_SHOP_MAX_ORDER_AMOUNT', 10000000), // 10,000,000 KRW
        'enable_order_verification' => env('JINY_SHOP_ENABLE_ORDER_VERIFICATION', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enable' => env('JINY_SHOP_CACHE_ENABLE', true),
        'ttl' => env('JINY_SHOP_CACHE_TTL', 3600), // 1 hour
        'tags' => ['jiny-shop', 'products', 'cart', 'orders'],
        'enable_product_cache' => env('JINY_SHOP_CACHE_PRODUCTS', true),
        'enable_cart_cache' => env('JINY_SHOP_CACHE_CART', false),
        'enable_pricing_cache' => env('JINY_SHOP_CACHE_PRICING', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | View Settings
    |--------------------------------------------------------------------------
    */
    'views' => [
        'theme' => env('JINY_SHOP_THEME', 'default'),
        'layout' => env('JINY_SHOP_LAYOUT', 'app'),
        'products_per_page' => env('JINY_SHOP_PRODUCTS_PER_PAGE', 20),
        'enable_product_comparison' => env('JINY_SHOP_ENABLE_PRODUCT_COMPARISON', true),
        'enable_recently_viewed' => env('JINY_SHOP_ENABLE_RECENTLY_VIEWED', true),
        'max_recently_viewed' => env('JINY_SHOP_MAX_RECENTLY_VIEWED', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    */
    'notifications' => [
        'enable' => env('JINY_SHOP_NOTIFICATIONS_ENABLE', true),
        'order_placed' => env('JINY_SHOP_NOTIFY_ORDER_PLACED', true),
        'order_confirmed' => env('JINY_SHOP_NOTIFY_ORDER_CONFIRMED', true),
        'order_shipped' => env('JINY_SHOP_NOTIFY_ORDER_SHIPPED', true),
        'order_delivered' => env('JINY_SHOP_NOTIFY_ORDER_DELIVERED', true),
        'low_stock' => env('JINY_SHOP_NOTIFY_LOW_STOCK', true),
        'payment_failed' => env('JINY_SHOP_NOTIFY_PAYMENT_FAILED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Analytics Settings
    |--------------------------------------------------------------------------
    */
    'analytics' => [
        'enable' => env('JINY_SHOP_ANALYTICS_ENABLE', true),
        'track_page_views' => env('JINY_SHOP_TRACK_PAGE_VIEWS', true),
        'track_add_to_cart' => env('JINY_SHOP_TRACK_ADD_TO_CART', true),
        'track_purchases' => env('JINY_SHOP_TRACK_PURCHASES', true),
        'track_search' => env('JINY_SHOP_TRACK_SEARCH', true),
        'enable_google_analytics' => env('JINY_SHOP_ENABLE_GA', false),
        'google_analytics_id' => env('JINY_SHOP_GA_ID', ''),
    ],
];