<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'author', 'blog_id', 'body_html', 'created_at', 'handle', 'image_src', 'image_created_at', 'metafields', 'published', 'published_at', 'summary_html', 'tags', 'template_suffix', 'title', 'updated_at', 'user_id', 'store_id'
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
