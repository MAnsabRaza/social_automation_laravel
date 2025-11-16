<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaptchaSettings extends Model
{
    protected $table = 'captcha_settings';
    protected $fillable = [
        'current_date',
        'user_id',
        'service_name',
        'api_key',
        'status',
    ];
     public function user()
    {
        return $this->belongsTo(User::class);
    }
}
