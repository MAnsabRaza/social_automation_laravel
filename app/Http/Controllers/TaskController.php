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
        $data = $request->all();
        $userId = Auth::id();

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

        if (isset($data['id'])) {
            $task = Task::find($data['id']);
            if ($task) {
                $task->current_date = $data['current_date'];
                $task->user_id = $userId;
                $task->account_id = $data['account_id'];
                $task->task_type = $data['task_type'];
                $task->target_url = $data['target_url'] ?? null;
                $task->scheduled_at = $data['scheduled_at'];
                $task->executed_at = $data['executed_at'];
                $task->content = $data['content'] ?? null;
                $task->hashtags = $data['hashtags'] ?? null;
                $task->media_urls = $imagePath;
                $task->comment = $data['comment'] ?? null;
                $task->save();

                return redirect()->route('task')->with('success', 'task updated successfully');
            } else {
                return redirect()->route('socialAccount')->with('error', 'task not found');
            }
        }

        $task = new Task();
        $task->current_date = $data['current_date'];
        $task->user_id = $userId;
        $task->account_id = $data['account_id'];
        $task->task_type = $data['task_type'];
        $task->target_url = $data['target_url'] ?? null;
        $task->scheduled_at = $data['scheduled_at'];
        $task->executed_at = $data['executed_at'];
        $task->content = $data['content'] ?? null;
        $task->hashtags = $data['hashtags'] ?? null;
        $task->media_urls = $imagePath;
        $task->comment = $data['comment'] ?? null;
        
        // 🔥 Set status to 'running' for scroll/share tasks
        if (in_array($task->task_type, ['scroll', 'share'])) {
            $task->status = 'running';
        } else {
            $task->status = 'pending';
        }
        
        $task->save();

        if (in_array($task->task_type, ['like', 'post', 'follow', 'unfollow', 'comment', 'share', 'scroll'])) {
            $this->executeTask($task);
        }

        return redirect()->route('task')->with('success', 'task created successfully');
    }

    private function executeTask(Task $task)
    {
        $account = SocialAccounts::with('proxy')->find($task->account_id);

        if (!$account) {
            Log::error("Account not found for task {$task->id}");
            return;
        }

        $payload = [
            'task' => [
                'id' => $task->id,
                'task_type' => $task->task_type,
                'target_url' => $task->target_url,
                'content' => $task->content,
                'hashtags' => $task->hashtags,
                'media_urls' => $task->media_urls,
                'comment' => $task->comment,
                'likeChance' => 35,
                'commentChance' => 10,
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
            Http::timeout(120)->post(
                'http://127.0.0.1:3000/execute-task',
                $payload
            );

            $task->update(['executed_at' => now()]);
        } catch (\Exception $e) {
            Log::error("Failed to execute task {$task->id}: " . $e->getMessage());
        }
    }

    // 🔥 NEW: Stop Scroll Bot
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
            // Call Node.js API to stop the scroll bot
            $response = Http::timeout(30)->post('http://127.0.0.1:3000/stop-scroll', [
                'account_id' => $accountId
            ]);

            $result = $response->json();

            if ($result['success']) {
                // Update task status to 'completed'
                Task::where('account_id', $accountId)
                    ->whereIn('task_type', ['scroll', 'share'])
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

    // 🔥 NEW: Get Scroll Bot Status
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
            // Call Node.js API to get scroll bot status
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
        $task = Task::where('user_id', Auth::id())->get();
        return DataTables::of($task)->make(true);
    }

    public function deleteTask($id)
    {
        $task = Task::find($id);
        if (!$task) {
            return redirect()->route('task')->with('error', 'task not found');
        }
        $task->delete();
        return response()->json(['success' => true, 'message' => 'task deleted successfully']);
    }

    public function fetchTaskData($id)
    {
        $task = Task::find($id);
        if (!$task) {
            return response()->json(['success' => false, 'message' => 'task not found']);
        }
        return response()->json(['success' => true, 'data' => $task]);
    }
}