<?php

namespace App\Http\Controllers;

use App\Models\PostContent;
use App\Models\SocialAccounts;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http; // <-- used to call Node API
use Yajra\DataTables\Facades\DataTables;

class TaskController extends Controller
{
    public function index()
    {
        $data['post_contents'] = PostContent::all();
        $data['accounts'] = SocialAccounts::all();
        $data['modules'] = ['setup/add-task.js'];
        return view('task/task', $data);
    }

    public function createTask(Request $request)
    {
        $data = $request->all();
        $userId = Auth::id();

        if (isset($data['id'])) {
            $task = Task::find($data['id']);
            if ($task) {
                $task->update([
                    'current_date' => $data['current_date'] ?? $task->current_date,
                    'user_id' => $userId,
                    'account_id' => $data['account_id'],
                    'task_type' => $data['task_type'],
                    'target_url' => $data['target_url'],
                    'scheduled_at' => $data['scheduled_at'],
                    'post_content_id' => $data['post_content_id'],
                    'executed_at' => $data['executed_at'],
                ]);
            } else {
                return redirect()->route('task')->with('error', 'task not found');
            }
        } else {
            $task = Task::create([
                'current_date' => $data['current_date'],
                'user_id' => $userId,
                'account_id' => $data['account_id'],
                'task_type' => $data['task_type'],
                'target_url' => $data['target_url'],
                'scheduled_at' => $data['scheduled_at'],
                'post_content_id' => $data['post_content_id'],
                'executed_at' => $data['executed_at'],
            ]);
        }

        // Immediately trigger Node worker to run this task now (or you can schedule based on scheduled_at)
        // Gather payload to send to Node
        $account = SocialAccounts::find($task->account_id);
        $postContent = $task->postContent ? $task->postContent->toArray() : null;

        $payload = [
            'task' => $task->toArray(),
            'account' => $account ? $account->toArray() : null,
            'post_content' => $postContent,
        ];

        // Node server endpoint (adjust if different host/port)
        try {
            $nodeResp = Http::timeout(30)->post('http://127.0.0.1:3000/execute-task', $payload);
            // optionally store response / set executed_at when success
            if ($nodeResp->ok()) {
                $respJson = $nodeResp->json();
                if (!empty($respJson['success'])) {
                    $task->executed_at = now();
                    $task->save();
                }
            }
        } catch (\Exception $e) {
            // Log error but do not break task creation
            \Log::error("Node execute-task call failed: " . $e->getMessage());
        }

        return redirect()->route('task')->with('success', 'task created successfully');
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
