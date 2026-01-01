<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use Illuminate\Support\Facades\Log;

class ProcessScheduledTasks extends Command
{
    protected $signature = 'tasks:process-scheduled';
    protected $description = 'Execute scheduled tasks';

    public function handle()
    {
        $this->info('Scheduler running at: ' . now());

        $tasks = Task::dueTasks()->get();

        if ($tasks->isEmpty()) {
            $this->info('No tasks are due.');
            return 0;
        }

        foreach ($tasks as $task) {
            try {
                $this->info("Executing Task ID: {$task->id}");
                $task->markAsRunning();

                app(\App\Http\Controllers\TaskController::class)
                    ->executeTaskFromScheduler($task);

            } catch (\Exception $e) {
                Log::error($e->getMessage());
                $task->markAsFailed($e->getMessage());
            }
        }

        return 0;
    }
}
