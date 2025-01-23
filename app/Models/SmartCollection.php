<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmartCollection extends Model
{
    use HasFactory;

    protected $fillable = [
        'main_collection_id',
        'body_html',
        'handle',
        'image_src',
        'image_alt',
        'published_at',
        'published_scope',
        'rules',
        'disjunctive',
        'sort_order',
        'template_suffix',
        'title',
        'updated_at',
        'collection_id'
    ];
    public function mainCollection()
    {
        return $this->belongsTo(Collection::class, 'main_collection_id');
    }
}
