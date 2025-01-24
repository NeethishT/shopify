
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCollectionsTable extends Migration
{
    public function up()
    {
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->string('shopify_collection_id')->nullable();
            $table->string('title')->nullable();
            $table->string('handle')->nullable();
            $table->string('description')->nullable();
            $table->string('description_html')->nullable();
            $table->string('sort_order')->nullable();
            $table->string('template_suffix')->nullable();
            $table->string('store_products_ids')->nullable();
            $table->text('shopify_products_ids')->nullable();
            $table->text('shopify_products_title')->nullable();
            $table->text('shopify_products_handle')->nullable();
            $table->string('image_id')->nullable();
            $table->string('image_url')->nullable();
            $table->integer('image_width')->nullable();
            $table->integer('image_height')->nullable();
            $table->timestamp('collection_published_at')->nullable();
            $table->timestamp('collection_created_at')->nullable();
            $table->timestamp('collection_updated_at')->nullable();
            $table->timestamps();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

        });
    }

    public function down()
    {
        Schema::dropIfExists('collections');
    }
}
