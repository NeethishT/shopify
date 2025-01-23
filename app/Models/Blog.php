<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'commentable',
        'created_at',
        'feedburner',
        'feedburner_location',
        'handle',
        'shopify_id',
        'tags',
        'template_suffix',
        'title',
        'updated_at',
        'admin_graphql_api_id',
        'store_id'
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
