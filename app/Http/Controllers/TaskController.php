<?php

namespace App\Http\Controllers;

use App\Jobs\RunTaskJob;
use App\Models\SocialAccounts;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class TaskController extends Controller
{
    public function dispatchTask($taskId)
    {
        $task = Task::findOrFail($taskId);
        RunTaskJob::dispatch($task);

        return response()->json([
            'message' => 'Task dispatched to queue successfully!'
        ]);
    }
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
        if (isset($data['id'])) {
            $task = Task::find($data['id']);
            if ($task) {
                $task->current_date = $data['current_date'];
                $task->user_id = $userId;
                $task->account_id = $data['account_id'];
                $task->task_type = $data['task_type'];
                $task->task_content = $data['task_content'];
                $task->target_url = $data['target_url'];
                $task->scheduled_at = $data['scheduled_at'];
                $task->status = $data['status'];
                $task->priority = $data['priority'];
                $task->retry_count = $data['retry_count'];
                $task->error_message = $data['error_message'];

                $task->executed_at = $data['executed_at'];
                $task->save();

                return redirect()->route('task')->with('success', 'task created successfully');
            } else {
                return redirect()->route('task')->with('error', 'task not found');
            }
        }
        $task = new Task();
        $task->current_date = $data['current_date'];
        $task->user_id = $userId;
        $task->account_id = $data['account_id'];
        $task->task_type = $data['task_type'];
        $task->task_content = $data['task_content'];
        $task->target_url = $data['target_url'];
        $task->scheduled_at = $data['scheduled_at'];
        $task->status = $data['status'];
        $task->priority = $data['priority'];
        $task->retry_count = $data['retry_count'];
        $task->error_message = $data['error_message'];

        $task->executed_at = $data['executed_at'];
        $task->save();

        return redirect()->route('task')->with('success', 'task created successfully');
    }
    public function getTaskData()
    {
        // if (Auth::user()->role === 'admin') {
        //     // Admin can see all data
        //     $proxy = Proxy::all();
        // } else {
        //     // Regular user can see only their own data
        //     $proxy = Proxy::where('user_id', Auth::id())->get();
        // }
        $task = Task::where('user_id', Auth::id())->get();
        return DataTables::of($task)->make(true);

    }
    public function deleteTask($id)
    {
        $task = Task::find($id);
        if (!$task) {
            return redirect()->route('task')->with('error', 'task not found');
        }

        // if (Auth::user()->role !== 'admin' && $proxy->user_id !== Auth::id()) {
        //     return response()->json(['success' => false, 'message' => 'Access denied']);
        // }

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
