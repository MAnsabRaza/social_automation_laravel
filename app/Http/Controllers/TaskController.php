<?php

namespace App\Http\Controllers;

use App\Models\PostContent;
use Illuminate\Support\Str;
use App\Models\SocialAccounts;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class TaskController extends Controller
{
    public function index()
    {
        $data['accounts'] = SocialAccounts::all();
        $data['modules'] = ['setup/add-task.js'];
        return view('task/task', $data);
    }

      public function createTask(Request $request)
    {
        $data   = $request->all();
        $userId = Auth::id();

        /* ---------------- Image Upload ---------------- */

        $imagePath = null;
        if ($request->hasFile('media_urls')) {
            $file = $request->file('media_urls');
            $fileName = 'task_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('images/tasks');

            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $fileName);
            $imagePath = 'images/tasks/' . $fileName;
        }

        /* =====================================================
           UPDATE TASK
        ===================================================== */

        if (!empty($data['id'])) {

            $task = Task::find($data['id']);

            if (!$task) {
                return redirect()->route('task')->with('error', 'Task not found');
            }

            $task->current_date = $data['current_date'];
            $task->user_id      = $userId;
            $task->account_id   = $data['account_id'];
            $task->task_type    = $data['task_type'];
            $task->target_url   = $data['target_url'] ?? null;

            // ✅ TIME FIX (IMPORTANT)
            $task->scheduled_at = Carbon::parse(
                $data['scheduled_at'],
                config('app.timezone')
            )->startOfMinute();

            $task->executed_at = Carbon::parse(
                $data['executed_at'],
                config('app.timezone')
            )->startOfMinute();

            $task->content    = $data['content'] ?? null;
            $task->hashtags   = $data['hashtags'] ?? null;
            $task->media_urls = $imagePath ?? $task->media_urls;
            $task->comment    = $data['comment'] ?? null;

            // ✅ STATUS LOGIC
            if (in_array($task->task_type, ['scroll'])) {
                $task->status = 'running';
            } else {
                $task->status = $task->isDue() ? 'running' : 'pending';
            }

            $task->save();

            if ($task->status === 'running') {
                $this->executeTask($task);
            }

            return redirect()->route('task')->with('success', 'Task updated successfully');
        }

        /* =====================================================
           CREATE TASK
        ===================================================== */

        $task = new Task();

        $task->current_date = $data['current_date'];
        $task->user_id      = $userId;
        $task->account_id   = $data['account_id'];
        $task->task_type    = $data['task_type'];
        $task->target_url   = $data['target_url'] ?? null;

        // ✅ TIME FIX (IMPORTANT)
        $task->scheduled_at = Carbon::parse(
            $data['scheduled_at'],
            config('app.timezone')
        )->startOfMinute();

        $task->executed_at = Carbon::parse(
            $data['executed_at'],
            config('app.timezone')
        )->startOfMinute();

        $task->content    = $data['content'] ?? null;
        $task->hashtags   = $data['hashtags'] ?? null;
        $task->media_urls = $imagePath;
        $task->comment    = $data['comment'] ?? null;

        // ✅ STATUS LOGIC
        if (in_array($task->task_type, ['scroll'])) {
            $task->status = 'running';
        } else {
            $task->status = $task->isDue() ? 'running' : 'pending';
        }

        $task->save();

        Log::info('Task created', [
            'task_id'      => $task->id,
            'status'       => $task->status,
            'executed_at'  => $task->executed_at,
            'scheduled_at'=> $task->scheduled_at,
        ]);

        if ($task->status === 'running') {
            $this->executeTask($task);
        }

        return redirect()->route('task')->with('success', 'Task created successfully');
    }

    /* =====================================================
       EXECUTION LOGIC
    ===================================================== */

    private function executeTask(Task $task)
    {
        Log::info("Executing task {$task->id}");

        $account = SocialAccounts::with('proxy')->find($task->account_id);

        if (!$account) {
            $task->markAsFailed('Account not found');
            return;
        }

        $payload = [
            'task' => [
                'id' => $task->id,
                'task_type' => $task->task_type,
                'target_url' => $task->target_url,
                'content' => $task->content,
                'hashtags' => $task->hashtags,
                'media_urls' => $task->media_urls ? asset($task->media_urls) : null,
                'comment' => $task->comment,
            ],
            'account' => [
                'id' => $account->id,
                'platform' => $account->platform,
                'session_data' => $account->session_data,
                'proxy' => $account->proxy,
            ],
        ];

        try {
            Http::timeout(120)->post('http://127.0.0.1:3000/execute-task', $payload);
        } catch (\Exception $e) {
            $task->markAsFailed($e->getMessage());
        }
    }

    public function executeTaskFromScheduler(Task $task)
    {
        $this->executeTask($task);
    }

    // Stop Scroll Bot
    public function stopScroll(Request $request)
    {
        $accountId = $request->input('account_id');

        if (!$accountId) {
            return response()->json([
                'success' => false,
                'message' => 'account_id is required'
            ], 400);
        }

        try {
            $response = Http::timeout(30)->post('http://127.0.0.1:3000/stop-scroll', [
                'account_id' => $accountId
            ]);

            $result = $response->json();

            if ($result['success'] ?? false) {
                Task::where('account_id', $accountId)
                    ->whereIn('task_type', ['scroll'])
                    ->where('status', 'running')
                    ->update(['status' => 'completed']);

                return response()->json([
                    'success' => true,
                    'message' => 'Scroll bot stopped successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Failed to stop scroll bot'
            ]);
        } catch (\Exception $e) {
            Log::error("Error stopping scroll bot: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    // Get Scroll Bot Status
    public function scrollStatus(Request $request)
    {
        $accountId = $request->input('account_id');

        if (!$accountId) {
            return response()->json([
                'success' => false,
                'message' => 'account_id is required'
            ], 400);
        }

        try {
            $response = Http::timeout(10)->post('http://127.0.0.1:3000/scroll-status', [
                'account_id' => $accountId
            ]);

            $result = $response->json();

            return response()->json([
                'success' => true,
                'isRunning' => $result['isRunning'] ?? false,
                'stats' => $result['stats'] ?? null
            ]);
        } catch (\Exception $e) {
            Log::error("Error getting scroll status: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'isRunning' => false,
                'stats' => null
            ]);
        }
    }

    public function getTaskData()
    {
        $tasks = Task::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
        return DataTables::of($tasks)->make(true);
    }

    public function deleteTask($id)
    {
        $task = Task::find($id);
        if (!$task) {
            return response()->json(['success' => false, 'message' => 'Task not found']);
        }
        
        if ($task->media_urls && File::exists(public_path($task->media_urls))) {
            File::delete(public_path($task->media_urls));
        }
        
        $task->delete();
        return response()->json(['success' => true, 'message' => 'Task deleted successfully']);
    }

    public function fetchTaskData($id)
    {
        $task = Task::find($id);
        if (!$task) {
            return response()->json(['success' => false, 'message' => 'Task not found']);
        }
        
        if ($task->media_urls) {
            $task->media_urls_full = asset($task->media_urls);
        }
        
        return response()->json(['success' => true, 'data' => $task]);
    }
}