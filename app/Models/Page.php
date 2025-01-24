<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'shopify_page_id',
        'title',
        'handle',
        'body',
        'body_summary',
        'is_published',
        'template_suffix',
        'page_published_at',
        'page_created_at',
        'page_updated_at'
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
