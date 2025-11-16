<?php

namespace App\Jobs;

use App\Models\Task;
use App\Services\BrowserService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunTaskJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    protected $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function handle()
    {
        $account = $this->task->socialAccount;
        $service = app(BrowserService::class);

        try {
            if ($this->task->task_type === 'login') {
                $service->loginInstagram($account);
            }

            // Extend: post, comment, follow etc.
            $this->task->status = 'completed';
        } catch (\Exception $e) {
            $this->task->status = 'failed';
            $this->task->error_message = $e->getMessage();
        }

        $this->task->executed_at = now();
        $this->task->save();
    }
}
