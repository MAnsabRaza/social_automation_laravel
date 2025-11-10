<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaptchaSettings extends Model
{
    protected $table = 'captcha_settings';
    protected $fillable = [
        'user_id',
        'service_name',
        'api_key',
        'status',
    ];
}
