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
        // 스토어 담당자 할당 테이블 생성
        Schema::create('store_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('assignable_type'); // 할당 가능한 엔티티 타입 (shipping, coupon, promotion, testimonial)
            $table->unsignedBigInteger('assignable_id'); // 할당 가능한 엔티티 ID
            $table->unsignedBigInteger('assigned_to'); // 담당자 ID (users 테이블 참조)
            $table->unsignedBigInteger('assigned_by'); // 할당한 사람 ID
            $table->timestamp('assigned_at'); // 할당 시간
            $table->timestamp('due_date')->nullable(); // 마감일
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable(); // 할당 메모
            $table->json('metadata')->nullable(); // 추가 메타데이터
            $table->timestamps();

            $table->index(['assignable_type', 'assignable_id']);
            $table->index(['assigned_to', 'status']);
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('cascade');
        });

        // 스토어 작업 로그 테이블 생성
        Schema::create('store_assignment_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assignment_id');
            $table->unsignedBigInteger('user_id');
            $table->string('action'); // assigned, started, completed, cancelled, transferred
            $table->string('old_status')->nullable();
            $table->string('new_status')->nullable();
            $table->text('comment')->nullable();
            $table->json('changes')->nullable(); // 변경 사항 기록
            $table->timestamps();

            $table->foreign('assignment_id')->references('id')->on('store_assignments')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // 스토어 권한 매트릭스 테이블 생성
        Schema::create('store_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('module'); // shipping, coupon, promotion, testimonial
            $table->json('permissions'); // ['read', 'create', 'update', 'delete', 'assign']
            $table->boolean('is_manager')->default(false); // 해당 모듈의 관리자 여부
            $table->timestamps();

            $table->unique(['user_id', 'module']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('store_permissions');
        Schema::dropIfExists('store_assignment_logs');
        Schema::dropIfExists('store_assignments');
    }
};
