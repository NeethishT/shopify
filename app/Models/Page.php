<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'author',
        'body_html',
        'created_at',
        'handle',
        'metafield',
        'published_at',
        'shop_id',
        'template_suffix',
        'title',
        'updated_at',
        'admin_graphql_api_id',
        'store_id'
    ];
}
