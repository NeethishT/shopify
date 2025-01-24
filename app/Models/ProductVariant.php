<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'shopify_variant_id',
        'title',
        'display_name',
        'image',
        'price',
        'compare_at_price',
        'position',
        'inventory_policy',
        'inventory_quantity',
        'sku',
        'variant_created_at',
        'variant_updated_at'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
