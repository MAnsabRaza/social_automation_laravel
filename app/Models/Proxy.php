<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proxy extends Model
{
    protected $table = 'proxies';
    protected $fillable = [
        'user_id',
        'proxy_type',
        'proxy_port',
        'proxy_username',
        'proxy_password',
        'status',
        'last_used',
    ];
}
