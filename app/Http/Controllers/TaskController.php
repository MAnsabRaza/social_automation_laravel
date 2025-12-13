<?php

namespace App\Http\Controllers;

use App\Models\PostContent;
use App\Models\SocialAccounts;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http; // <-- used to call Node API
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

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
        $userId = Auth::id();

        $request->validate([
            'account_id' => 'required|exists:social_accounts,id',
            'task_type' => 'required|in:post,comment,like,follow,unfollow,share,review',
            'scheduled_at' => 'nullable|date',

            // POST only
            'content' => 'required_if:task_type,post',
            'media_urls' => 'required_if:task_type,post',
        ]);

        $task = Task::updateOrCreate(
            ['id' => $request->id],
            [
                'current_date' => $request->current_date ?? now(),
                'user_id' => $userId,
                'account_id' => $request->account_id,
                'task_type' => $request->task_type,
                'target_url' => $request->target_url,
                'scheduled_at' => $request->scheduled_at ?? now(),
                'executed_at' => $request->executed_at,
                // 'content' => $request->content,
                'hashtags' => $request->hashtags,
                'media_urls' => $request->media_urls
                    ? json_encode($request->media_urls)
                    : null,
            ]
        );

        if ($task->task_type === 'post') {
            $this->executeTask($task);
        }

        return redirect()->route('task')->with('success', 'Task saved successfully');
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

        Http::timeout(120)->post(
            'http://127.0.0.1:3000/execute-task',
            $payload
        );

        $task->update(['executed_at' => now()]);
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
