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
        'status', // Added status field
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'executed_at' => 'datetime',
        'media_urls' => 'array',
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

    public function postContent()
    {
        return $this->belongsTo(PostContent::class, 'post_content_id');
    }

    // Scopes
    public function scopeRunning($query)
    {
        return $query->where('status', 'running');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
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

    // Helper Methods
    public function isRunning()
    {
        return $this->status === 'running';
    }

    public function isScheduled()
    {
        return $this->status === 'scheduled';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isFailed()
    {
        return $this->status === 'failed';
    }

    public function markAsRunning()
    {
        $this->update(['status' => 'running']);
    }

    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'executed_at' => now()
        ]);
    }

    public function markAsFailed()
    {
        $this->update([
            'status' => 'failed',
            'executed_at' => now()
        ]);
    }
}