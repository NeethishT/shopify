<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'name',
        'handle',
        'collection_type',
        'collection_id'
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function customCollection()
    {
        return $this->hasOne(CustomCollection::class, 'collection_id', 'collection_id');
    }

    public function smartCollection()
    {
        return $this->hasOne(SmartCollection::class, 'collection_id', 'collection_id');
    }
}
