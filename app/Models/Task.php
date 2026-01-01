<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Task extends Model
{
    use HasFactory;

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
        'comment',
        'status',
        'error_message',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'executed_at'  => 'datetime',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'hashtags'     => 'array',
    ];

    /* ---------------- Status Helpers ---------------- */

    public function markAsRunning()
    {
        $this->update(['status' => 'running']);
    }

    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'error_message' => null
        ]);
    }

    public function markAsFailed($error)
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $error
        ]);
    }

    /* ---------------- Due Logic ---------------- */

    public function isDue(): bool
    {
        return $this->status === 'pending'
            && $this->executed_at
            && $this->executed_at->lte(now());
    }

    public function scopeDueTasks($query)
    {
        return $query
            ->where('status', 'pending')
            ->whereNotNull('executed_at')
            ->where('executed_at', '<=', now());
    }
}
