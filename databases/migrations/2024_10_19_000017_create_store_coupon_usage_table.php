<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_coupon_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained('store_coupons')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained('store_orders')->onDelete('cascade');
            $table->string('session_id')->nullable(); // For guest users
            $table->decimal('discount_amount', 10, 2);
            $table->decimal('order_amount', 10, 2); // Order amount when coupon was applied
            $table->string('customer_email')->nullable();
            $table->json('order_items')->nullable(); // Items the coupon was applied to
            $table->timestamp('used_at');
            $table->timestamps();

            $table->index(['coupon_id', 'user_id']);
            $table->index(['coupon_id', 'session_id']);
            $table->index(['used_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_coupon_usage');
    }
};
