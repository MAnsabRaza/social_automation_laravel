<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Task;
use App\Jobs\ExecuteTaskJob;

/*
|--------------------------------------------------------------------------
| Console Commands
|--------------------------------------------------------------------------
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Task Scheduler
|--------------------------------------------------------------------------
| Ye scheduler har minute check karega ke kaunse tasks execute hone chahiye
| Multiple tasks ko handle karega simultaneously
*/

Schedule::call(function () {
    // Pending tasks jo execute hone ke liye ready hain
    Task::where('status', 'pending')
        ->whereNotNull('executed_at')
        ->where('executed_at', '<=', now())
        ->chunkById(50, function ($tasks) {
            foreach ($tasks as $task) {
                // Pehle status queued karein taake duplicate execution na ho
                $task->update(['status' => 'queued']);
                
                // Job ko queue mein dispatch karein
                ExecuteTaskJob::dispatch($task);
            }
        });
})->everyMinute()->name('execute-scheduled-tasks')->withoutOverlapping();