<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlogsTable extends Migration
{
    public function up()
    {
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->string('shopify_blog_id')->nullable();
            $table->string('title')->nullable();
            $table->string('handle')->nullable();
            $table->string('tags')->nullable();
            $table->string('template_suffix')->nullable();
            $table->timestamp('blog_created_at')->nullable();
            $table->timestamp('blog_updated_at')->nullable();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('blogs');
    }
}
