<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagesTable extends Migration
{
    public function up()
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->string('shopify_page_id')->nullable();
            $table->string('title')->nullable();
            $table->string('handle')->nullable();
            $table->text('body')->nullable();
            $table->text('body_summary')->nullable();
            $table->string('is_published')->nullable();
            $table->string('template_suffix')->nullable();
            $table->timestamp('page_published_at')->nullable();
            $table->timestamp('page_created_at')->nullable();
            $table->timestamp('page_updated_at')->nullable();
            $table->timestamps();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

        });
    }

    public function down()
    {
        Schema::dropIfExists('pages');
    }
}
