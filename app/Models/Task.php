<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $table = 'tasks';
    protected $fillable = [
        'current_date',
        'user_id',
        'account_id',
        'task_type',
        'target_url',
        'scheduled_at',
        'executed_at',
        'content',
        'hashtags',
        'media_urls',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

     public function socialAccount()
    {
        return $this->belongsTo(SocialAccounts::class, 'account_id');
    }

    public function postContent()
    {
        return $this->belongsTo(PostContent::class, 'post_content_id');
    }
}
