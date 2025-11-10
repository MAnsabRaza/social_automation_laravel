<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarmupSetting extends Model
{
    protected $table = 'warmup_settings';
    protected $fillable = [
        'user_id',
        'day_number',
        'max_posts',
        'max_likes',
        'max_comments',
        'max_follows',
        'max_unfollows',
    ];
}
