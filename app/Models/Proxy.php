<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Proxy extends Model
{
    protected $table = 'proxies';
    protected $fillable = [
        'current_date',
        'user_id',
        'proxy_host',
        'proxy_type',
        'proxy_port',
        'proxy_username',
        'proxy_password',
        'is_active',
        'last_used',
    ];
      public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function socialAccounts()
    {
        return $this->hasMany(SocialAccounts::class);
    }
}
