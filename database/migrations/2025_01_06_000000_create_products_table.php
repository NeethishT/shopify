<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->string('shopify_product_id')->nullable();
            $table->string('title')->nullable();
            $table->text('body_html')->nullable();
            $table->string('vendor')->nullable();
            $table->string('product_type')->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->decimal('price_min', 8, 2)->nullable();
            $table->decimal('price_max', 8, 2)->nullable();
            $table->decimal('compare_price', 8, 2)->nullable();
            $table->decimal('compare_price_min', 8, 2)->nullable();
            $table->decimal('compare_price_max', 8, 2)->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->integer('totalInventory')->nullable();
            $table->string('full_url')->nullable();
            $table->string('handle')->nullable();
            $table->timestamp('product_published_at')->nullable();
            $table->timestamp('product_created_at')->nullable();
            $table->timestamp('product_updated_at')->nullable();
            $table->string('tags')->nullable();
            $table->json('additional_details')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
}
