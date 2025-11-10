<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialAccounts extends Model
{
    protected $table = 'social_accounts';
    protected $fillable = [
        'user_id',
        'platform',
        'account_username',
        'account_email',
        'account_password',
        'account_phone',
        'cookies',
        'auth_token',
        'session_data',
        'proxy_id',
        'status',
        'last_login',
        'warmup_level',
        'daily_actions_count',
    ];
}
