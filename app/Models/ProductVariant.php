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
        'price',
        'position',
        'inventory_policy',
        'compare_at_price',
        'option1',
        'option2',
        'option3',
        'taxable',
        'barcode',
        'fulfillment_service',
        'grams',
        'inventory_management',
        'requires_shipping',
        'sku',
        'weight',
        'weight_unit',
        'inventory_item_id',
        'inventory_quantity',
        'old_inventory_quantity',
        'admin_graphql_api_id',
        'image_id'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
