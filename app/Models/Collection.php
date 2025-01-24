<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'shopify_collection_id',
        'title',
        'handle',
        'description',
        'description_html',
        'sort_order',
        'template_suffix',
        'store_products_ids',
        'shopify_products_ids',
        'shopify_products_title',
        'shopify_products_handle',
        'image_id',
        'image_url',
        'image_width',
        'image_height',
        'collection_published_at',
        'collection_created_at',
        'collection_updated_at'
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
