<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductVariantsTable extends Migration
{
    public function up()
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('shopify_variant_id')->nullable();
            $table->string('title')->nullable();
            $table->string('display_name')->nullable();
            $table->string('image')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('compare_at_price', 8, 2)->nullable();
            $table->integer('position')->nullable();
            $table->string('inventory_policy')->nullable();
            $table->string('inventory_quantity')->nullable();
            $table->string('sku')->nullable();
            $table->timestamp('variant_created_at')->nullable();
            $table->timestamp('variant_updated_at')->nullable();
            $table->timestamps();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_variants');
    }
}
