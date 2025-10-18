<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Jiny Store Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for the Jiny Store package.
    | You can modify these settings to customize the behavior of the store.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Store Settings
    |--------------------------------------------------------------------------
    |
    | Basic store configuration settings
    |
    */
    'name' => env('STORE_NAME', 'Jiny Store'),
    'description' => env('STORE_DESCRIPTION', 'A Laravel E-commerce Store'),
    'version' => '1.0.0',

    /*
    |--------------------------------------------------------------------------
    | Currency Settings
    |--------------------------------------------------------------------------
    |
    | Default currency and formatting options
    |
    */
    'currency' => [
        'default' => env('STORE_CURRENCY', 'KRW'),
        'symbol' => env('STORE_CURRENCY_SYMBOL', '₩'),
        'position' => env('STORE_CURRENCY_POSITION', 'before'), // before or after
    ],

    /*
    |--------------------------------------------------------------------------
    | Product Settings
    |--------------------------------------------------------------------------
    |
    | Product related configuration
    |
    */
    'products' => [
        'per_page' => env('STORE_PRODUCTS_PER_PAGE', 20),
        'image_sizes' => [
            'thumbnail' => [150, 150],
            'medium' => [300, 300],
            'large' => [800, 800],
        ],
        'allow_reviews' => env('STORE_ALLOW_REVIEWS', true),
        'require_approval' => env('STORE_REQUIRE_REVIEW_APPROVAL', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cart Settings
    |--------------------------------------------------------------------------
    |
    | Shopping cart configuration
    |
    */
    'cart' => [
        'session_key' => 'jiny_store_cart',
        'abandon_days' => env('STORE_CART_ABANDON_DAYS', 7),
        'max_items' => env('STORE_CART_MAX_ITEMS', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | Order Settings
    |--------------------------------------------------------------------------
    |
    | Order processing configuration
    |
    */
    'orders' => [
        'prefix' => env('STORE_ORDER_PREFIX', 'ORD'),
        'auto_confirm' => env('STORE_AUTO_CONFIRM_ORDERS', false),
        'statuses' => [
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Inventory Settings
    |--------------------------------------------------------------------------
    |
    | Inventory management configuration
    |
    */
    'inventory' => [
        'track_stock' => env('STORE_TRACK_STOCK', true),
        'allow_backorders' => env('STORE_ALLOW_BACKORDERS', false),
        'low_stock_threshold' => env('STORE_LOW_STOCK_THRESHOLD', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Tax Settings
    |--------------------------------------------------------------------------
    |
    | Tax calculation configuration
    |
    */
    'tax' => [
        'enabled' => env('STORE_TAX_ENABLED', true),
        'default_rate' => env('STORE_DEFAULT_TAX_RATE', 10.0),
        'inclusive' => env('STORE_TAX_INCLUSIVE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Shipping Settings
    |--------------------------------------------------------------------------
    |
    | Shipping calculation configuration
    |
    */
    'shipping' => [
        'enabled' => env('STORE_SHIPPING_ENABLED', true),
        'free_threshold' => env('STORE_FREE_SHIPPING_THRESHOLD', 50000),
        'default_method' => env('STORE_DEFAULT_SHIPPING_METHOD', 'standard'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Settings
    |--------------------------------------------------------------------------
    |
    | Payment gateway configuration
    |
    */
    'payment' => [
        'gateways' => [
            'cash' => [
                'name' => 'Cash on Delivery',
                'enabled' => true,
            ],
            'bank_transfer' => [
                'name' => 'Bank Transfer',
                'enabled' => true,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Settings
    |--------------------------------------------------------------------------
    |
    | Email notification settings
    |
    */
    'emails' => [
        'order_confirmation' => env('STORE_EMAIL_ORDER_CONFIRMATION', true),
        'order_status_update' => env('STORE_EMAIL_ORDER_STATUS_UPDATE', true),
        'low_stock_alert' => env('STORE_EMAIL_LOW_STOCK_ALERT', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Enable or disable specific features
    |
    */
    'features' => [
        'wishlist' => env('STORE_FEATURE_WISHLIST', true),
        'compare' => env('STORE_FEATURE_COMPARE', true),
        'reviews' => env('STORE_FEATURE_REVIEWS', true),
        'coupons' => env('STORE_FEATURE_COUPONS', true),
        'promotions' => env('STORE_FEATURE_PROMOTIONS', true),
    ],
];
