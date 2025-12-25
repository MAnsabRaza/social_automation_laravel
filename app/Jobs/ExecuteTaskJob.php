<?php

namespace App\Jobs;

use App\Models\Task;
use App\Models\SocialAccounts;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExecuteTaskJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 180; // 3 minutes timeout
    public $tries = 2; // Agar fail ho to 2 baar retry karega

    public function __construct(public Task $task)
    {
    }

    public function handle()
    {
        // Fresh data load karein database se
        $this->task->refresh();

        // Agar task queued status mein nahi hai to skip karein
        if (!$this->task->isQueued()) {
            Log::info("Task {$this->task->id} is not queued. Current status: {$this->task->status}");
            return;
        }

        // Status running karein
        $this->task->markAsRunning();

        Log::info("Starting execution for Task ID: {$this->task->id}");

        // Account aur proxy data load karein
        $account = SocialAccounts::with('proxy')->find($this->task->account_id);

        if (!$account) {
            Log::error("Account not found for Task ID: {$this->task->id}");
            $this->task->markAsFailed('Account not found');
            return;
        }

        // Payload banayein Node.js server ke liye
        $payload = [
            'task' => [
                'id' => $this->task->id,
                'task_type' => $this->task->task_type,
                'target_url' => $this->task->target_url,
                'content' => $this->task->content,
                'hashtags' => $this->task->hashtags,
                'media_urls' => $this->task->media_urls ? url($this->task->media_urls) : null,
                'comment' => $this->task->comment,
            ],
            'account' => [
                'id' => $account->id,
                'platform' => $account->platform,
                'session_data' => $account->session_data,
                'proxy' => $account->proxy ? [
                    'host' => $account->proxy->host,
                    'port' => $account->proxy->port,
                    'username' => $account->proxy->username,
                    'password' => $account->proxy->password,
                ] : null,
            ],
        ];

        try {
            // Node.js server ko request bhejein
            $response = Http::timeout(120)->post(
                'http://127.0.0.1:3000/execute-task',
                $payload
            );

            if ($response->successful()) {
                Log::info("Task {$this->task->id} completed successfully");
                $this->task->markAsCompleted();
            } else {
                $errorMsg = "HTTP Error: " . $response->status() . " - " . $response->body();
                Log::error("Task {$this->task->id} failed: {$errorMsg}");
                $this->task->markAsFailed($errorMsg);
            }

        } catch (\Throwable $e) {
            Log::error("Task {$this->task->id} exception: " . $e->getMessage());
            $this->task->markAsFailed($e->getMessage());
        }
    }

    // Agar job fail ho jaye
    public function failed(\Throwable $exception)
    {
        Log::error("Job failed for Task {$this->task->id}: " . $exception->getMessage());
        $this->task->markAsFailed('Job failed: ' . $exception->getMessage());
    }
}