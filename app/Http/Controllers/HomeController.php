<?php

namespace App\Http\Controllers;

use App\Models\SocialAccounts;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
         $data['modules'] = ['setup/add-home.js'];
        if (!Auth::check()) {
            return redirect()->route('showLogin')->with('error', 'Please login to continue.');
        }

        $userId = Auth::id();

        // Get account statistics
        $data['totalAccounts'] = SocialAccounts::where('user_id', $userId)->count();
        $data['loggedInAccounts'] = SocialAccounts::where('user_id', $userId)
            ->where('status', 'logged_in')
            ->count();
        $data['activeAccounts'] = SocialAccounts::where('user_id', $userId)
            ->where('status', 'active')
            ->count();
        $data['failedAccounts'] = SocialAccounts::where('user_id', $userId)
            ->where('status', 'login_failed')
            ->count();

        // Get task statistics
        $data['runningTasks'] = Task::where('user_id', $userId)
            ->where('status', 'running')
            ->count();
        $data['scheduledTasks'] = Task::where('user_id', $userId)
            ->where('status', 'pending')
            ->count();
        $data['completedTasks'] = Task::where('user_id', $userId)
            ->where('status', 'completed')
            ->count();
        $data['failedTasks'] = Task::where('user_id', $userId)
            ->where('status', 'failed')
            ->count();

        // Get accounts list with details
        $data['accounts'] = SocialAccounts::where('user_id', $userId)
            ->select('id', 'platform', 'account_username', 'account_email', 'status', 'last_login', 'warmup_level')
            ->orderBy('platform')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get recent tasks
        $data['recentTasks'] = Task::where('user_id', $userId)
            ->with('socialAccount:id,platform,account_username')
            ->select('id', 'account_id', 'task_type', 'status', 'scheduled_at', 'executed_at', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
            return view('home/home',$data);

       
    }

    public function getAccountsByPlatform(Request $request)
    {
        $platform = $request->get('platform');
        $userId = Auth::id();

        $query = SocialAccounts::where('user_id', $userId);

        if ($platform && $platform !== 'all') {
            $query->where('platform', $platform);
        }

        $accounts = $query->select('id', 'platform', 'account_username', 'account_email', 'status', 'last_login')
            ->orderBy('platform')
            ->get();

        return response()->json(['accounts' => $accounts]);
    }

    public function getTasksByStatus(Request $request)
    {
        $status = $request->get('status');
        $userId = Auth::id();

        $query = Task::where('user_id', $userId)->with('socialAccount:id,platform,account_username');

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $tasks = $query->select('id', 'account_id', 'task_type', 'status', 'scheduled_at', 'executed_at', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['tasks' => $tasks]);
    }
}