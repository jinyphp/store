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
        // 프로모션 테이블
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 프로모션명
            $table->string('slug')->unique(); // URL 슬러그
            $table->text('description')->nullable();
            $table->text('terms_conditions')->nullable(); // 이용약관
            $table->enum('type', [
                'discount', 'flash_sale', 'bundle', 'seasonal',
                'loyalty', 'referral', 'first_purchase', 'clearance'
            ]);
            $table->enum('status', ['draft', 'scheduled', 'active', 'paused', 'completed', 'cancelled'])->default('draft');
            $table->datetime('starts_at');
            $table->datetime('ends_at');
            $table->integer('participant_limit')->nullable(); // 참여자 수 제한
            $table->integer('participant_count')->default(0); // 현재 참여자 수
            $table->decimal('budget', 12, 2)->nullable(); // 프로모션 예산
            $table->decimal('spent_amount', 12, 2)->default(0); // 사용된 금액
            $table->json('target_audience')->nullable(); // 대상 고객 조건
            $table->json('rules')->nullable(); // 프로모션 규칙
            $table->json('rewards')->nullable(); // 보상 내용
            $table->json('metadata')->nullable(); // 추가 메타데이터
            $table->string('banner_image')->nullable(); // 배너 이미지
            $table->json('marketing_materials')->nullable(); // 마케팅 자료
            $table->boolean('is_featured')->default(false); // 추천 프로모션
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['status', 'starts_at', 'ends_at']);
            $table->index(['type', 'is_featured']);
        });

        // 프로모션 참여 내역 테이블
        Schema::create('promotion_participations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('promotion_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->decimal('reward_amount', 10, 2)->default(0); // 받은 보상 금액
            $table->json('reward_details')->nullable(); // 보상 상세 내용
            $table->enum('status', ['participated', 'rewarded', 'cancelled'])->default('participated');
            $table->timestamp('participated_at');
            $table->timestamp('rewarded_at')->nullable();
            $table->json('participation_data')->nullable(); // 참여 관련 데이터
            $table->timestamps();

            $table->foreign('promotion_id')->references('id')->on('promotions')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['promotion_id', 'user_id']); // 중복 참여 방지
            $table->index(['participated_at', 'status']);
        });

        // 프로모션 코드 테이블
        Schema::create('promotion_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('promotion_id');
            $table->string('code')->unique(); // 프로모션 코드
            $table->integer('usage_limit')->nullable(); // 사용 제한
            $table->integer('used_count')->default(0); // 사용된 횟수
            $table->boolean('is_active')->default(true);
            $table->datetime('expires_at')->nullable();
            $table->json('restrictions')->nullable(); // 사용 제한 조건
            $table->timestamps();

            $table->foreign('promotion_id')->references('id')->on('promotions')->onDelete('cascade');
            $table->index(['code', 'is_active']);
        });

        // 프로모션 상품 관계 테이블
        Schema::create('promotion_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('promotion_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('discount_percentage', 5, 2)->nullable(); // 할인율
            $table->decimal('discount_amount', 10, 2)->nullable(); // 할인 금액
            $table->decimal('special_price', 10, 2)->nullable(); // 특가
            $table->integer('stock_limit')->nullable(); // 프로모션 재고 제한
            $table->integer('sold_count')->default(0); // 판매된 수량
            $table->boolean('is_featured')->default(false); // 추천 상품
            $table->timestamps();

            $table->foreign('promotion_id')->references('id')->on('promotions')->onDelete('cascade');
            $table->index(['promotion_id', 'product_id']);
            $table->index('is_featured');
        });

        // 프로모션 분석 데이터 테이블
        Schema::create('promotion_analytics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('promotion_id');
            $table->date('date');
            $table->integer('views')->default(0); // 조회수
            $table->integer('clicks')->default(0); // 클릭수
            $table->integer('participations')->default(0); // 참여수
            $table->integer('conversions')->default(0); // 전환수
            $table->decimal('revenue', 12, 2)->default(0); // 매출
            $table->decimal('cost', 12, 2)->default(0); // 비용
            $table->json('metrics')->nullable(); // 추가 지표
            $table->timestamps();

            $table->foreign('promotion_id')->references('id')->on('promotions')->onDelete('cascade');
            $table->unique(['promotion_id', 'date']);
            $table->index('date');
        });

        // 프로모션 승인 워크플로우 테이블
        Schema::create('promotion_approvals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('promotion_id');
            $table->unsignedBigInteger('requested_by'); // 승인 요청자
            $table->unsignedBigInteger('approved_by')->nullable(); // 승인자
            $table->enum('status', ['pending', 'approved', 'rejected', 'revision_required'])->default('pending');
            $table->text('request_notes')->nullable(); // 요청 메모
            $table->text('approval_notes')->nullable(); // 승인/거부 메모
            $table->json('changes_requested')->nullable(); // 요청된 변경사항
            $table->timestamp('requested_at');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->foreign('promotion_id')->references('id')->on('promotions')->onDelete('cascade');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['status', 'requested_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('promotion_approvals');
        Schema::dropIfExists('promotion_analytics');
        Schema::dropIfExists('promotion_products');
        Schema::dropIfExists('promotion_codes');
        Schema::dropIfExists('promotion_participations');
        Schema::dropIfExists('promotions');
    }
};