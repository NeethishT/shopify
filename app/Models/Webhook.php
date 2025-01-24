<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    use HasFactory;

    protected $table = 'webhooks';
    protected $fillable = [
        'store_id',
        'access_token',
        'topic',
        'endpoint',
        'status',
        'format',
        'webhook_id',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
