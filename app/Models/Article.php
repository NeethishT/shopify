<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id', 'blog_id', 'shopify_article_id', 'title', 'handle', 'author', 'body_html', 'tags', 'is_published', 'template_suffix', 'article_published_at', 'article_created_at', 'article_updated_at', 'image_id', 'image_url', 'image_height', 'image_width'
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }
}
