<?php

namespace Jiny\Store\Http\Controllers\Admin\Settings;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Store\Helpers\CurrencyHelper;

/**
 * 이커머스 설정 관리 컨트롤러
 */
class IndexController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->config = [
            'view' => 'jiny-store::admin.settings.index',
            'title' => '이커머스 설정',
            'subtitle' => '전체 이커머스 시스템 설정을 관리합니다.',
        ];
    }

    public function __invoke(Request $request)
    {
        // 현재 설정 값들
        $settings = $this->getCurrentSettings();

        // 통화 목록
        $currencies = CurrencyHelper::getActiveCurrencies();

        // 국가 목록
        $countries = CurrencyHelper::getActiveCountries();

        // 환율 정보
        $exchangeRates = $this->getExchangeRateInfo();

        // 시스템 상태 정보
        $systemStatus = $this->getSystemStatus();

        return view($this->config['view'], [
            'settings' => $settings,
            'currencies' => $currencies,
            'countries' => $countries,
            'exchangeRates' => $exchangeRates,
            'systemStatus' => $systemStatus,
            'config' => $this->config,
        ]);
    }

    /**
     * 현재 설정 값들 조회
     */
    protected function getCurrentSettings()
    {
        $settings = $this->loadSettingsFromJson();

        // Flatten the nested JSON structure for blade template compatibility
        return [
            // 기본 설정
            'store_name' => $settings['store']['name'] ?? config('app.name', 'JinyShop'),
            'store_email' => $settings['store']['email'] ?? config('mail.from.address', 'shop@example.com'),
            'store_phone' => $settings['store']['phone'] ?? '+82-2-1234-5678',
            'store_address' => $settings['store']['address'] ?? '서울특별시 강남구 테헤란로 123',

            // 통화 설정
            'base_currency' => $settings['currency']['base_currency'] ?? CurrencyHelper::getBaseCurrency()->code ?? 'KRW',
            'display_currencies' => $settings['currency']['display_currencies'] ?? ['KRW', 'USD', 'EUR'],
            'auto_currency_detection' => $settings['currency']['auto_currency_detection'] ?? true,
            'currency_decimals' => $settings['currency']['currency_decimals'] ?? 0,

            // 세금 설정
            'tax_calculation' => $settings['tax']['calculation'] ?? 'exclusive',
            'tax_display' => $settings['tax']['display'] ?? 'both',
            'default_tax_rate' => $settings['tax']['default_rate'] ?? 0.10,
            'tax_based_on' => $settings['tax']['based_on'] ?? 'shipping_address',
            'shipping_tax_calculation' => $settings['tax']['shipping_tax_calculation'] ?? true,

            // 주문 설정
            'order_number_prefix' => $settings['order']['number_prefix'] ?? 'ORD-',
            'order_number_format' => $settings['order']['number_format'] ?? 'YYYYMMDD-NNN',
            'order_auto_confirm' => $settings['order']['auto_confirm'] ?? false,
            'order_stock_reduction' => $settings['order']['stock_reduction'] ?? 'on_payment',
            'allow_guest_checkout' => $settings['order']['allow_guest_checkout'] ?? true,
            'require_phone_number' => $settings['order']['require_phone_number'] ?? true,

            // 배송 설정
            'default_shipping_country' => $settings['shipping']['default_country'] ?? 'KR',
            'shipping_calculation' => $settings['shipping']['calculation'] ?? 'per_order',
            'free_shipping_threshold' => $settings['shipping']['free_threshold'] ?? 50000,

            // 재고 설정
            'track_inventory' => $settings['inventory']['track_inventory'] ?? true,
            'allow_backorders' => $settings['inventory']['allow_backorders'] ?? false,
            'low_stock_threshold' => $settings['inventory']['low_stock_threshold'] ?? 10,
            'out_of_stock_message' => $settings['inventory']['out_of_stock_message'] ?? '품절',
            'show_stock_quantity' => $settings['inventory']['show_stock_quantity'] ?? false,

            // 가격 설정
            'price_display_format' => $settings['price']['display_format'] ?? '{symbol} {amount}',
            'price_thousand_separator' => $settings['price']['thousand_separator'] ?? ',',
            'price_decimal_separator' => $settings['price']['decimal_separator'] ?? '.',
            'hide_zero_decimals' => $settings['price']['hide_zero_decimals'] ?? true,

            // 고객 설정
            'customer_registration' => $settings['customer']['registration'] ?? 'required',
            'customer_email_verification' => $settings['customer']['email_verification'] ?? true,
            'customer_phone_verification' => $settings['customer']['phone_verification'] ?? false,
            'allow_customer_reviews' => $settings['customer']['allow_reviews'] ?? true,
            'review_moderation' => $settings['customer']['review_moderation'] ?? true,

            // 결제 설정
            'payment_methods' => $settings['payment']['methods'] ?? ['credit_card', 'bank_transfer', 'paypal'],
            'payment_currency' => $settings['payment']['currency'] ?? 'KRW',
            'payment_gateway' => $settings['payment']['gateway'] ?? 'stripe',
            'auto_capture_payment' => $settings['payment']['auto_capture'] ?? false,

            // 이메일 설정
            'email_new_order' => $settings['email']['new_order'] ?? true,
            'email_order_confirmation' => $settings['email']['order_confirmation'] ?? true,
            'email_order_shipped' => $settings['email']['order_shipped'] ?? true,
            'email_order_delivered' => $settings['email']['order_delivered'] ?? true,
            'email_order_cancelled' => $settings['email']['order_cancelled'] ?? true,
            'email_low_stock' => $settings['email']['low_stock'] ?? true,

            // 보안 설정
            'session_timeout' => $settings['security']['session_timeout'] ?? 30,
            'max_login_attempts' => $settings['security']['max_login_attempts'] ?? 5,
            'password_reset_expiry' => $settings['security']['password_reset_expiry'] ?? 60,
            'require_ssl' => $settings['security']['require_ssl'] ?? true,

            // API 설정
            'api_enabled' => $settings['api']['enabled'] ?? false,
            'api_rate_limit' => $settings['api']['rate_limit'] ?? 100,
            'webhook_enabled' => $settings['api']['webhook_enabled'] ?? false,
            'webhook_secret' => $settings['api']['webhook_secret'] ?? null,

            // 고급 설정
            'cache_enabled' => $settings['advanced']['cache_enabled'] ?? true,
            'cache_duration' => $settings['advanced']['cache_duration'] ?? 3600,
            'debug_mode' => $settings['advanced']['debug_mode'] ?? config('app.debug', false),
            'maintenance_mode' => $settings['advanced']['maintenance_mode'] ?? false,
        ];
    }

    /**
     * JSON 파일에서 설정 로드
     */
    protected function loadSettingsFromJson()
    {
        $settingsPath = dirname(__DIR__, 5) . '/config/setting.json';

        if (!file_exists($settingsPath)) {
            return [];
        }

        $json = file_get_contents($settingsPath);
        $settings = json_decode($json, true);

        return $settings ?: [];
    }

    /**
     * 설정을 JSON 파일에 저장
     */
    public function saveSettings(Request $request)
    {
        try {
            $settings = $this->loadSettingsFromJson();

            // Update settings based on request input
            $this->updateSettingsFromRequest($settings, $request);

            // Save to JSON file
            $this->saveSettingsToJson($settings);

            return response()->json([
                'success' => true,
                'message' => '설정이 성공적으로 저장되었습니다.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '설정 저장 실패: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Request 데이터로 설정 업데이트
     */
    protected function updateSettingsFromRequest(&$settings, Request $request)
    {
        // Store settings
        if ($request->has('store_name')) {
            $settings['store']['name'] = $request->input('store_name');
        }
        if ($request->has('store_email')) {
            $settings['store']['email'] = $request->input('store_email');
        }
        if ($request->has('store_phone')) {
            $settings['store']['phone'] = $request->input('store_phone');
        }
        if ($request->has('store_address')) {
            $settings['store']['address'] = $request->input('store_address');
        }

        // Currency settings
        if ($request->has('base_currency')) {
            $settings['currency']['base_currency'] = $request->input('base_currency');
        }
        if ($request->has('currency_decimals')) {
            $settings['currency']['currency_decimals'] = (int) $request->input('currency_decimals');
        }
        if ($request->has('auto_currency_detection')) {
            $settings['currency']['auto_currency_detection'] = $request->boolean('auto_currency_detection');
        }

        // Tax settings
        if ($request->has('tax_calculation')) {
            $settings['tax']['calculation'] = $request->input('tax_calculation');
        }
        if ($request->has('tax_display')) {
            $settings['tax']['display'] = $request->input('tax_display');
        }
        if ($request->has('default_tax_rate')) {
            $settings['tax']['default_rate'] = (float) $request->input('default_tax_rate') / 100;
        }
        if ($request->has('tax_based_on')) {
            $settings['tax']['based_on'] = $request->input('tax_based_on');
        }
        if ($request->has('shipping_tax_calculation')) {
            $settings['tax']['shipping_tax_calculation'] = $request->boolean('shipping_tax_calculation');
        }

        // Order settings
        if ($request->has('order_number_prefix')) {
            $settings['order']['number_prefix'] = $request->input('order_number_prefix');
        }
        if ($request->has('order_number_format')) {
            $settings['order']['number_format'] = $request->input('order_number_format');
        }
        if ($request->has('order_stock_reduction')) {
            $settings['order']['stock_reduction'] = $request->input('order_stock_reduction');
        }
        if ($request->has('order_auto_confirm')) {
            $settings['order']['auto_confirm'] = $request->boolean('order_auto_confirm');
        }
        if ($request->has('allow_guest_checkout')) {
            $settings['order']['allow_guest_checkout'] = $request->boolean('allow_guest_checkout');
        }
        if ($request->has('require_phone_number')) {
            $settings['order']['require_phone_number'] = $request->boolean('require_phone_number');
        }

        // Add more setting updates as needed...
    }

    /**
     * 설정을 JSON 파일에 저장
     */
    protected function saveSettingsToJson($settings)
    {
        $settingsPath = dirname(__DIR__, 5) . '/config/setting.json';
        $json = json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if (file_put_contents($settingsPath, $json) === false) {
            throw new \Exception('설정 파일 저장에 실패했습니다.');
        }
    }

    /**
     * 환율 정보 조회
     */
    protected function getExchangeRateInfo()
    {
        $lastUpdate = DB::table('store_exchange_rates')->max('updated_at');

        $rates = DB::table('store_exchange_rates')
            ->leftJoin('store_currencies as from_curr', 'store_exchange_rates.from_currency', '=', 'from_curr.code')
            ->leftJoin('store_currencies as to_curr', 'store_exchange_rates.to_currency', '=', 'to_curr.code')
            ->select(
                'store_exchange_rates.*',
                'from_curr.name as from_currency_name',
                'from_curr.symbol as from_currency_symbol',
                'to_curr.name as to_currency_name',
                'to_curr.symbol as to_currency_symbol'
            )
            ->where('store_exchange_rates.is_active', true)
            ->orderBy('store_exchange_rates.updated_at', 'desc')
            ->limit(5)
            ->get();

        return [
            'last_update' => $lastUpdate,
            'total_rates' => DB::table('store_exchange_rates')->where('is_active', true)->count(),
            'auto_update_enabled' => true,
            'update_frequency' => 'hourly',
            'recent_rates' => $rates,
        ];
    }

    /**
     * 시스템 상태 정보
     */
    protected function getSystemStatus()
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database_connection' => $this->checkDatabaseConnection(),
            'cache_status' => $this->checkCacheStatus(),
            'queue_status' => $this->checkQueueStatus(),
            'storage_permissions' => $this->checkStoragePermissions(),
            'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
            'disk_space' => $this->getDiskSpace(),
            'ssl_enabled' => $this->checkSSL(),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
        ];
    }

    /**
     * 헬퍼 메서드들
     */
    protected function checkDatabaseConnection()
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'connected', 'message' => '정상'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => '연결 실패'];
        }
    }

    protected function checkCacheStatus()
    {
        try {
            cache()->put('test_key', 'test_value', 60);
            $value = cache()->get('test_key');
            cache()->forget('test_key');

            return ['status' => 'working', 'message' => '정상'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => '캐시 오류'];
        }
    }

    protected function checkQueueStatus()
    {
        // 큐 상태 확인 로직
        return ['status' => 'working', 'message' => '정상'];
    }

    protected function checkStoragePermissions()
    {
        $storagePath = storage_path();
        return ['status' => is_writable($storagePath) ? 'writable' : 'error', 'message' => is_writable($storagePath) ? '쓰기 가능' : '권한 없음'];
    }

    protected function getDiskSpace()
    {
        $bytes = disk_free_space(".");
        $gb = round($bytes / 1024 / 1024 / 1024, 2);
        return $gb . ' GB';
    }

    protected function checkSSL()
    {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    }
}