<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';
    protected $fillable = [
        'user_id',
        'account_id',
        'activity_type',
        'description',
        'ip_address',
        'user_agent',
    ];
}
