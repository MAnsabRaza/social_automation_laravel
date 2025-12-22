<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialAccounts extends Model
{
    protected $table = 'social_accounts';

    protected $fillable = [
        'current_date',
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

    protected $casts = [
        'last_login' => 'datetime',
        'cookies' => 'array', // Auto-convert JSON to array
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function proxy()
    {
        return $this->belongsTo(Proxy::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'account_id');
    }

    // Helper method to get Twitter credentials
    public function getTwitterCredentials()
    {
        if ($this->platform !== 'twitter') {
            return null;
        }

        return [
            'email' => $this->account_email,
            'username' => $this->account_username,
            'password' => $this->account_password,
        ];
    }
}