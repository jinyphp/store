<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // 관계 필드
            $table->foreignId('shipping_zone_id')->constrained('store_shipping_zones')->onDelete('cascade')->comment('배송 지역 ID');
            $table->foreignId('shipping_method_id')->constrained('store_shipping_methods')->onDelete('cascade')->comment('배송 방식 ID');

            // 요금 정보
            $table->decimal('base_cost', 10, 2)->default(0)->comment('기본 배송비');
            $table->decimal('per_kg_cost', 8, 2)->default(0)->comment('kg당 추가 비용');
            $table->decimal('per_item_cost', 8, 2)->default(0)->comment('개당 추가 비용');

            // 조건부 요금
            $table->decimal('free_shipping_threshold', 10, 2)->nullable()->comment('무료배송 최소 주문금액');
            $table->decimal('min_order_amount', 10, 2)->nullable()->comment('최소 주문금액');
            $table->decimal('max_order_amount', 10, 2)->nullable()->comment('최대 주문금액');

            // 무게 조건
            $table->decimal('min_weight', 8, 2)->default(0)->comment('최소 무게 (kg)');
            $table->decimal('max_weight', 8, 2)->nullable()->comment('최대 무게 (kg)');

            // 통화
            $table->string('currency', 3)->default('KRW')->comment('통화 코드');

            // 상태 관련
            $table->boolean('enable')->default(true)->comment('활성화 여부');

            // 유니크 제약
            $table->unique(['shipping_zone_id', 'shipping_method_id'], 'unique_zone_method');

            $table->index(['enable']);
            $table->index(['currency']);
            $table->index(['free_shipping_threshold']);
        });

        $this->insertDefaultShippingRates();
    }

    private function insertDefaultShippingRates()
    {
        $domesticZone = DB::table('store_shipping_zones')->where('name', 'Domestic')->first();
        $asiaZone = DB::table('store_shipping_zones')->where('name', 'Asia')->first();
        $northAmericaZone = DB::table('store_shipping_zones')->where('name', 'North America')->first();
        $europeZone = DB::table('store_shipping_zones')->where('name', 'Europe')->first();
        $restOfWorldZone = DB::table('store_shipping_zones')->where('name', 'Rest of World')->first();

        $standardMethod = DB::table('store_shipping_methods')->where('code', 'standard')->first();
        $expressMethod = DB::table('store_shipping_methods')->where('code', 'express')->first();
        $overnightMethod = DB::table('store_shipping_methods')->where('code', 'overnight')->first();
        $economyMethod = DB::table('store_shipping_methods')->where('code', 'economy')->first();
        $freeMethod = DB::table('store_shipping_methods')->where('code', 'free')->first();

        $rates = [];

        if ($domesticZone) {
            if ($standardMethod) {
                $rates[] = [
                    'shipping_zone_id' => $domesticZone->id,
                    'shipping_method_id' => $standardMethod->id,
                    'base_cost' => 3000.00,
                    'per_kg_cost' => 500.00,
                    'per_item_cost' => 0.00,
                    'free_shipping_threshold' => 50000.00,
                    'min_order_amount' => null,
                    'max_order_amount' => null,
                    'min_weight' => 0.00,
                    'max_weight' => 30.00,
                    'currency' => 'KRW',
                    'enable' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if ($expressMethod) {
                $rates[] = [
                    'shipping_zone_id' => $domesticZone->id,
                    'shipping_method_id' => $expressMethod->id,
                    'base_cost' => 5000.00,
                    'per_kg_cost' => 1000.00,
                    'per_item_cost' => 0.00,
                    'free_shipping_threshold' => 100000.00,
                    'min_order_amount' => null,
                    'max_order_amount' => null,
                    'min_weight' => 0.00,
                    'max_weight' => 20.00,
                    'currency' => 'KRW',
                    'enable' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if ($overnightMethod) {
                $rates[] = [
                    'shipping_zone_id' => $domesticZone->id,
                    'shipping_method_id' => $overnightMethod->id,
                    'base_cost' => 10000.00,
                    'per_kg_cost' => 2000.00,
                    'per_item_cost' => 0.00,
                    'free_shipping_threshold' => null,
                    'min_order_amount' => 20000.00,
                    'max_order_amount' => null,
                    'min_weight' => 0.00,
                    'max_weight' => 10.00,
                    'currency' => 'KRW',
                    'enable' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if ($freeMethod) {
                $rates[] = [
                    'shipping_zone_id' => $domesticZone->id,
                    'shipping_method_id' => $freeMethod->id,
                    'base_cost' => 0.00,
                    'per_kg_cost' => 0.00,
                    'per_item_cost' => 0.00,
                    'free_shipping_threshold' => null,
                    'min_order_amount' => 50000.00,
                    'max_order_amount' => null,
                    'min_weight' => 0.00,
                    'max_weight' => 25.00,
                    'currency' => 'KRW',
                    'enable' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if ($asiaZone && $standardMethod) {
            $rates[] = [
                'shipping_zone_id' => $asiaZone->id,
                'shipping_method_id' => $standardMethod->id,
                'base_cost' => 15000.00,
                'per_kg_cost' => 2000.00,
                'per_item_cost' => 1000.00,
                'free_shipping_threshold' => 200000.00,
                'min_order_amount' => 30000.00,
                'max_order_amount' => null,
                'min_weight' => 0.00,
                'max_weight' => 20.00,
                'currency' => 'KRW',
                'enable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($asiaZone && $expressMethod) {
            $rates[] = [
                'shipping_zone_id' => $asiaZone->id,
                'shipping_method_id' => $expressMethod->id,
                'base_cost' => 25000.00,
                'per_kg_cost' => 3000.00,
                'per_item_cost' => 1500.00,
                'free_shipping_threshold' => null,
                'min_order_amount' => 50000.00,
                'max_order_amount' => null,
                'min_weight' => 0.00,
                'max_weight' => 15.00,
                'currency' => 'KRW',
                'enable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($northAmericaZone && $standardMethod) {
            $rates[] = [
                'shipping_zone_id' => $northAmericaZone->id,
                'shipping_method_id' => $standardMethod->id,
                'base_cost' => 30000.00,
                'per_kg_cost' => 4000.00,
                'per_item_cost' => 2000.00,
                'free_shipping_threshold' => 300000.00,
                'min_order_amount' => 50000.00,
                'max_order_amount' => null,
                'min_weight' => 0.00,
                'max_weight' => 20.00,
                'currency' => 'KRW',
                'enable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($europeZone && $standardMethod) {
            $rates[] = [
                'shipping_zone_id' => $europeZone->id,
                'shipping_method_id' => $standardMethod->id,
                'base_cost' => 35000.00,
                'per_kg_cost' => 5000.00,
                'per_item_cost' => 2500.00,
                'free_shipping_threshold' => 400000.00,
                'min_order_amount' => 70000.00,
                'max_order_amount' => null,
                'min_weight' => 0.00,
                'max_weight' => 15.00,
                'currency' => 'KRW',
                'enable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($restOfWorldZone && $economyMethod) {
            $rates[] = [
                'shipping_zone_id' => $restOfWorldZone->id,
                'shipping_method_id' => $economyMethod->id,
                'base_cost' => 40000.00,
                'per_kg_cost' => 6000.00,
                'per_item_cost' => 3000.00,
                'free_shipping_threshold' => null,
                'min_order_amount' => 100000.00,
                'max_order_amount' => null,
                'min_weight' => 0.00,
                'max_weight' => 10.00,
                'currency' => 'KRW',
                'enable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($rates)) {
            DB::table('store_shipping_rates')->insert($rates);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('store_shipping_rates');
    }
};
