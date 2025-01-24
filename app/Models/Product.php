<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'shopify_product_id',
        'title',
        'body_html',
        'vendor',
        'product_type',
        'handle',
        'price',
        'price_min',
        'price_max',
        'compare_price',
        'compare_price_min',
        'compare_price_max',
        'seo_title',
        'seo_description',
        'totalInventory',
        'full_url',
        'product_published_at',
        'product_created_at',
        'product_updated_at',
        'additional_details',
        'tags',
        'status',
        'admin_graphql_api_id'
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
}
