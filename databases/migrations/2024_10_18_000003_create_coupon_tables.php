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
        // 쿠폰 테이블
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 쿠폰명
            $table->string('code')->unique(); // 쿠폰 코드
            $table->text('description')->nullable();
            $table->enum('type', ['fixed', 'percentage', 'free_shipping', 'buy_x_get_y']); // 할인 타입
            $table->decimal('value', 10, 2); // 할인 값
            $table->decimal('minimum_amount', 10, 2)->nullable(); // 최소 주문 금액
            $table->decimal('maximum_discount', 10, 2)->nullable(); // 최대 할인 금액
            $table->integer('usage_limit')->nullable(); // 전체 사용 제한
            $table->integer('usage_limit_per_user')->nullable(); // 사용자당 사용 제한
            $table->integer('used_count')->default(0); // 사용된 횟수
            $table->datetime('starts_at')->nullable(); // 시작일
            $table->datetime('expires_at')->nullable(); // 만료일
            $table->boolean('is_active')->default(true);
            $table->json('conditions')->nullable(); // 적용 조건 (상품, 카테고리 등)
            $table->json('metadata')->nullable(); // 추가 메타데이터
            $table->timestamps();

            $table->index(['code', 'is_active']);
            $table->index(['starts_at', 'expires_at']);
            $table->index('is_active');
        });

        // 쿠폰 사용 내역 테이블
        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coupon_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->decimal('discount_amount', 10, 2); // 실제 할인된 금액
            $table->decimal('order_total', 10, 2); // 주문 총액
            $table->json('applied_items')->nullable(); // 적용된 상품들
            $table->timestamp('used_at');
            $table->timestamps();

            $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['coupon_id', 'user_id']);
            $table->index('used_at');
        });

        // 쿠폰 카테고리 테이블
        Schema::create('coupon_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('color')->default('#007bff'); // 카테고리 색상
            $table->string('icon')->nullable(); // 아이콘
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 쿠폰-카테고리 관계 테이블
        Schema::create('coupon_category_relations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coupon_id');
            $table->unsignedBigInteger('category_id');
            $table->timestamps();

            $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('coupon_categories')->onDelete('cascade');
            $table->unique(['coupon_id', 'category_id']);
        });

        // 쿠폰 배포 테이블
        Schema::create('coupon_distributions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coupon_id');
            $table->string('distribution_type'); // email, sms, manual, automatic
            $table->json('target_criteria')->nullable(); // 대상 기준
            $table->integer('target_count')->default(0); // 대상 수
            $table->integer('sent_count')->default(0); // 발송 수
            $table->datetime('scheduled_at')->nullable(); // 예약 발송일
            $table->datetime('completed_at')->nullable(); // 완료일
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('notes')->nullable();
            $table->json('results')->nullable(); // 발송 결과
            $table->timestamps();

            $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('cascade');
            $table->index(['status', 'scheduled_at']);
        });

        // 개인화 쿠폰 테이블 (사용자별 고유 쿠폰)
        Schema::create('personal_coupons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('coupon_id');
            $table->string('personal_code')->unique(); // 개인화된 쿠폰 코드
            $table->boolean('is_used')->default(false);
            $table->timestamp('used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('cascade');
            $table->index(['user_id', 'is_used']);
            $table->index('personal_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('personal_coupons');
        Schema::dropIfExists('coupon_distributions');
        Schema::dropIfExists('coupon_category_relations');
        Schema::dropIfExists('coupon_categories');
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('coupons');
    }
};