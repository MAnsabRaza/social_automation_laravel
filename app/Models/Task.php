<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $table = 'tasks';
    protected $fillable = [
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
}
