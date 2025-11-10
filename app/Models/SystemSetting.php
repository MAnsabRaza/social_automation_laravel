<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $table = 'system_settings';
    protected $fillable = [
        'user_id',
        'setting_key',
        'setting_value',
        'setting_type',
    ];
}
