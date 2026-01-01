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
use Carbon\Carbon;

class ExecuteTaskJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes
    public $tries = 2; // Retry 2 times
    public $backoff = 30; // Wait 30 seconds before retry

    public function __construct(public Task $task) {}

    public function handle()
    {
        Log::info("══════════════════════════════════════");
        Log::info("🚀 JOB STARTED - Task #{$this->task->id}");
        Log::info("══════════════════════════════════════");
        
        // Refresh task to get latest status from database
        $this->task->refresh();
        
        Log::info("Task Details:");
        Log::info("  - ID: {$this->task->id}");
        Log::info("  - Type: {$this->task->task_type}");
        Log::info("  - Status: {$this->task->status}");
        Log::info("  - Execute Time: {$this->task->executed_at}");
        Log::info("  - Current Time: " . Carbon::now()->toDateTimeString());
        
        // Check if task is already processed
        if (!in_array($this->task->status, ['pending', 'queued'])) {
            Log::warning("⚠️ Task already processed - Current status: {$this->task->status}");
            Log::info("══════════════════════════════════════\n");
            return;
        }
        
        // Mark as running
        $this->task->markAsRunning();
        Log::info("✅ Task status changed to RUNNING");
        
        // Load social account with proxy relationship
        $account = SocialAccounts::with('proxy')->find($this->task->account_id);
        
        if (!$account) {
            Log::error("❌ Social account not found for ID: {$this->task->account_id}");
            $this->task->markAsFailed('Social account not found');
            Log::info("══════════════════════════════════════\n");
            return;
        }
        
        Log::info("✅ Account loaded: {$account->username} (Platform: {$account->platform})");
        
        // Prepare payload for Node.js API
        $payload = [
            'task' => [
                'id' => $this->task->id,
                'task_type' => $this->task->task_type,
                'target_url' => $this->task->target_url,
                'content' => $this->task->content,
                'comment' => $this->task->comment,
                'hashtags' => $this->task->hashtags,
                'media_urls' => $this->task->media_urls ? url($this->task->media_urls) : null,
            ],
            'account' => [
                'id' => $account->id,
                'platform' => $account->platform,
                'username' => $account->username,
                'session_data' => $account->session_data,
                'proxy' => $account->proxy ? [
                    'host' => $account->proxy->host,
                    'port' => $account->proxy->port,
                    'username' => $account->proxy->username,
                    'password' => $account->proxy->password,
                    'protocol' => $account->proxy->protocol ?? 'http',
                ] : null,
            ]
        ];
        
        Log::info("📤 Sending request to Node.js API...");
        Log::info("  URL: http://127.0.0.1:3000/execute-task");
        Log::info("  Task Type: {$this->task->task_type}");
        
        try {
            // Send POST request to Node.js execution server
            $response = Http::timeout(180)->post(
                'http://127.0.0.1:3000/execute-task',
                $payload
            );
            
            Log::info("📥 Response received from Node.js API");
            Log::info("  Status Code: {$response->status()}");
            
            if ($response->successful()) {
                $responseData = $response->json();
                Log::info("✅✅✅ TASK EXECUTED SUCCESSFULLY");
                Log::info("  Response: " . json_encode($responseData));
                
                $this->task->markAsCompleted();
                Log::info("✅ Task status changed to COMPLETED");
                
            } else {
                $errorBody = $response->body();
                $statusCode = $response->status();
                
                Log::error("❌ Task execution FAILED");
                Log::error("  Status Code: {$statusCode}");
                Log::error("  Error Response: {$errorBody}");
                
                $this->task->markAsFailed("API Error ({$statusCode}): {$errorBody}");
            }
            
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("❌ CONNECTION ERROR to Node.js API");
            Log::error("  Error: {$e->getMessage()}");
            Log::error("  Make sure Node.js server is running on http://127.0.0.1:3000");
            
            $this->task->markAsFailed('Connection error: Node.js server not reachable');
            throw $e; // Rethrow to trigger retry
            
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error("❌ REQUEST ERROR");
            Log::error("  Error: {$e->getMessage()}");
            
            $this->task->markAsFailed('Request error: ' . $e->getMessage());
            throw $e;
            
        } catch (\Exception $e) {
            Log::error("❌ UNEXPECTED ERROR");
            Log::error("  Error: {$e->getMessage()}");
            Log::error("  File: {$e->getFile()}");
            Log::error("  Line: {$e->getLine()}");
            
            $this->task->markAsFailed('Exception: ' . $e->getMessage());
            throw $e;
        }
        
        Log::info("══════════════════════════════════════");
        Log::info("✅ JOB COMPLETED - Task #{$this->task->id}");
        Log::info("══════════════════════════════════════\n");
    }
    
    /**
     * Handle job failure after all retries exhausted
     */
    public function failed(\Throwable $exception)
    {
        Log::error("══════════════════════════════════════");
        Log::error("❌❌❌ JOB PERMANENTLY FAILED");
        Log::error("  Task ID: {$this->task->id}");
        Log::error("  Reason: {$exception->getMessage()}");
        Log::error("══════════════════════════════════════");
        
        $this->task->markAsFailed('Job permanently failed: ' . $exception->getMessage());
    }
}