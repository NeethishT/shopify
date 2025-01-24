<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticlesTable extends Migration
{
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->unsignedBigInteger('blog_id');
            $table->string('shopify_article_id')->nullable();
            $table->string('title')->nullable();
            $table->string('handle')->nullable();
            $table->string('author')->nullable();
            $table->text('body_html')->nullable();
            $table->string('tags')->nullable();
            $table->string('is_published')->nullable();
            $table->string('template_suffix')->nullable();
            $table->timestamp('article_published_at')->nullable();
            $table->timestamp('article_created_at')->nullable();
            $table->timestamp('article_updated_at')->nullable();
            $table->string('image_id')->nullable();
            $table->string('image_url')->nullable();
            $table->integer('image_height')->nullable();
            $table->integer('image_width')->nullable();
            $table->timestamps();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->foreign('blog_id')->references('id')->on('blogs')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('articles');
    }
}
