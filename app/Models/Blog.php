<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'shopify_blog_id',
        'title',
        'handle',
        'tags',
        'template_suffix',
        'blog_created_at',
        'blog_updated_at',
        'store_id'
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
