<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 배송 지역 테이블
        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 지역명
            $table->text('description')->nullable();
            $table->json('countries')->nullable(); // 포함 국가 리스트
            $table->json('regions')->nullable(); // 포함 지역 리스트
            $table->json('postcodes')->nullable(); // 포함 우편번호 패턴
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 배송 방법 테이블
        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 배송 방법명
            $table->string('code')->unique(); // 배송 방법 코드
            $table->text('description')->nullable();
            $table->enum('type', ['flat_rate', 'weight_based', 'price_based', 'distance_based', 'free']);
            $table->decimal('base_cost', 10, 2)->default(0);
            $table->decimal('min_order_amount', 10, 2)->nullable(); // 최소 주문 금액
            $table->decimal('max_order_amount', 10, 2)->nullable(); // 최대 주문 금액
            $table->decimal('max_weight', 8, 2)->nullable(); // 최대 무게
            $table->integer('estimated_days_min')->nullable(); // 최소 배송일
            $table->integer('estimated_days_max')->nullable(); // 최대 배송일
            $table->boolean('requires_signature')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('options')->nullable(); // 추가 옵션
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 배송 요금 테이블
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('zone_id');
            $table->unsignedBigInteger('method_id');
            $table->decimal('min_value', 10, 2)->default(0); // 최소값 (무게/가격/거리)
            $table->decimal('max_value', 10, 2)->nullable(); // 최대값
            $table->decimal('cost', 10, 2); // 배송비
            $table->decimal('additional_cost', 10, 2)->default(0); // 추가 비용
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('zone_id')->references('id')->on('shipping_zones')->onDelete('cascade');
            $table->foreign('method_id')->references('id')->on('shipping_methods')->onDelete('cascade');
        });

        // 배송 추적 테이블
        Schema::create('shipping_trackings', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_number')->unique();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('method_id');
            $table->string('carrier')->nullable(); // 택배사
            $table->enum('status', [
                'pending', 'picked_up', 'in_transit', 'out_for_delivery',
                'delivered', 'failed', 'returned'
            ])->default('pending');
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->string('recipient_name')->nullable();
            $table->text('delivery_address')->nullable();
            $table->json('tracking_events')->nullable(); // 추적 이벤트 히스토리
            $table->timestamps();

            $table->foreign('method_id')->references('id')->on('shipping_methods')->onDelete('cascade');
            $table->index(['status', 'created_at']);
        });

        // 배송 라벨 테이블
        Schema::create('shipping_labels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tracking_id');
            $table->string('label_format'); // PDF, PNG, ZPL 등
            $table->text('label_data'); // 라벨 데이터 (base64 또는 URL)
            $table->string('label_size')->default('4x6'); // 라벨 크기
            $table->boolean('is_thermal')->default(false); // 열전사 프린터용 여부
            $table->timestamps();

            $table->foreign('tracking_id')->references('id')->on('shipping_trackings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shipping_labels');
        Schema::dropIfExists('shipping_trackings');
        Schema::dropIfExists('shipping_rates');
        Schema::dropIfExists('shipping_methods');
        Schema::dropIfExists('shipping_zones');
    }
};