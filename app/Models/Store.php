<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;
    protected $table = 'stores';
    protected $fillable = [
        'shopify_store_id',
        'name',
        'email',
        'access_token',
        'myshopify_domain',
        'phone',
        'address1',
        'address2',
        'zip',
        'verification_token',
        'script_tag_id',
    ];

    public function users()
    {
        return $this->hasOne(User::class);
    }
}
