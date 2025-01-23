<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookSettings extends Model
{
    use HasFactory;

    protected $table = 'webhook_settings';
    protected $fillable = [
        'topic',
        'endpoint',
        'status'
    ];
}
