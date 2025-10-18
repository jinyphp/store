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
        Schema::create('store_products', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();

            $table->boolean('enable')->default(true);
            $table->boolean('featured')->default(false);
            $table->unsignedInteger('view_count')->default(0);

            $table->string('slug')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->string('category')->nullable();
            $table->unsignedBigInteger('category_id')->nullable(); // Foreign key to store_categories

            // 가격 정보
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('sale_price', 10, 2)->nullable();

            // 이미지
            $table->string('image', 500)->nullable();
            $table->text('images')->nullable(); // JSON 배열

            // 상품 상세 정보
            $table->text('features')->nullable(); // JSON 배열
            $table->text('specifications')->nullable(); // JSON 배열
            $table->string('tags')->nullable();

            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            // Action button settings
            $table->boolean('enable_purchase')->default(true);
            $table->boolean('enable_cart')->default(true);
            $table->boolean('enable_quote')->default(true);
            $table->boolean('enable_contact')->default(true);
            $table->boolean('enable_social_share')->default(true);

            // 관리
            $table->string('manager')->nullable();

            // Foreign key constraints
            $table->foreign('category_id')->references('id')->on('store_categories')->onDelete('set null');

            // Indexes
            $table->index(['enable', 'deleted_at']);
            $table->index(['category', 'deleted_at']);
            $table->index(['featured', 'deleted_at']);
            $table->index(['view_count']);
            $table->index(['enable_purchase', 'enable_cart', 'enable_quote', 'enable_contact']);
            $table->index(['category_id', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('store_products');
    }
};