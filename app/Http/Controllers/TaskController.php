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
        $data = $request->all();
        $userId = Auth::id();
        $base64Image = null;
        if ($request->hasFile('media_urls')) {
            $file = $request->file('media_urls');
            $base64Image = "data:" . $file->getMimeType() . ";base64," . base64_encode(file_get_contents($file));
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
                $task->media_urls = $base64Image;
                $task->save();

                return redirect()->route('task')->with('success', 'task created successfully');
            } else {
                return redirect()->route('socialAccount')->with('error', 'task not found');
            }
        }
        $task = new Task();
        $task->current_date = $data['current_date'];
        $task->user_id = $userId;
        $task->account_id = $data['account_id'];
        $task->task_type = $data['task_type'];
        $task->target_url = $data['target_url'];
        $task->scheduled_at = $data['scheduled_at'];
        $task->executed_at = $data['executed_at'];
        $task->content = $data['content'];
        $task->hashtags = $data['hashtags'];
        $task->media_urls = $base64Image;
        $task->save();
        if ($task->task_type === 'post') {
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
