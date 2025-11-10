<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProxyApiSetting extends Model
{
    protected $table = 'proxy_api_settings';
    protected $fillable = [
        'user_id',
        'api_url',
        'api_name',
        'api_key',
        'rotation_enabled',
    ];
}
