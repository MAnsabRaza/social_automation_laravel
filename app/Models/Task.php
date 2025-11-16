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
        'task_content',
        'target_url',
        'scheduled_at',
        'status',
        'priority',
        'retry_count',
        'error_message',
        'executed_at',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function socialAccount()
    {
        return $this->belongsTo(SocialAccounts::class, 'account_id');
    }
}
