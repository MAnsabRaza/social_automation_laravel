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
        'comment',
        'status',
        'error_message',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'executed_at' => 'datetime',
        'hashtags' => 'array',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function socialAccount()
    {
        return $this->belongsTo(SocialAccounts::class, 'account_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeQueued($query)
    {
        return $query->where('status', 'queued');
    }

    public function scopeRunning($query)
    {
        return $query->where('status', 'running');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeDueForExecution($query)
    {
        return $query->where('status', 'pending')
            ->whereNotNull('executed_at')
            ->where('executed_at', '<=', now())
            ->orderBy('executed_at', 'asc');
    }

    // Helper Methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isQueued()
    {
        return $this->status === 'queued';
    }

    public function isRunning()
    {
        return $this->status === 'running';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isFailed()
    {
        return $this->status === 'failed';
    }

    public function markAsPending()
    {
        $this->update(['status' => 'pending', 'error_message' => null]);
    }

    public function markAsQueued()
    {
        $this->update(['status' => 'queued']);
    }

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

    public function markAsFailed($errorMessage = null)
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage
        ]);
    }
}