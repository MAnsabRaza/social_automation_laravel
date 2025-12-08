<?php

namespace App\Http\Controllers;

use App\Models\PostContent;
use App\Models\SocialAccounts;
use App\Services\SocialLoginService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PostContentController extends Controller
{
    public function index()
    {
        $data['accounts'] = SocialAccounts::where('user_id', Auth::id())->get();
        $data['modules'] = ['setup/add-post-content.js'];
        return view('post-content/post-content', $data);
    }
    // public function createPostContent(Request $request)
    // {
    //     $data = $request->all();
    //     $userId = Auth::id();
    //     $base64Image = null;

    //     if ($request->hasFile('media_urls')) {
    //         $file = $request->file('media_urls');
    //         $base64Image = "data:" . $file->getMimeType() . ";base64," . base64_encode(file_get_contents($file));
    //     }
    //     if (isset($data['id'])) {
    //         $post_content = PostContent::find($data['id']);
    //         if ($post_content) {
    //             $post_content->current_date = $data['current_date'];
    //             $post_content->user_id = $userId;
    //             $post_content->account_id = $data['account_id'];
    //             $post_content->title = $data['title'];
    //             $post_content->content = $data['content'];
    //             if ($base64Image !== null) {
    //                 $post_content->media_urls = $base64Image;
    //             }
    //             $post_content->hashtags = $data['hashtags'];
    //             $post_content->save();

    //             return redirect()->route('post-content')->with('success', 'task created successfully');
    //         } else {
    //             return redirect()->route('post-content')->with('error', 'task not found');
    //         }
    //     }
    //     $post_content = new PostContent();
    //     $post_content->current_date = $data['current_date'];
    //     $post_content->user_id = $userId;
    //     $post_content->account_id = $data['account_id'];
    //     $post_content->title = $data['title'];
    //     $post_content->content = $data['content'];
    //     $post_content->media_urls = $base64Image;
    //     $post_content->hashtags = $data['hashtags'];
    //     $post_content->save();
    //     $account = SocialAccounts::find($post_content->account_id);

    //     $loginService = new SocialLoginService();
    //     $loginService->login($account);


    //     if ($account->platform == 'instagram') {
    //         $loginService->postToInstagram($post_content);
    //     }else if( $account->platform == 'facebook') {
    //         $loginService->postToFacebook($post_content);
    //     }

    //     return redirect()->route('post-content')->with('success', 'Post Content created successfully');
    // }

    public function createPostContent(Request $request)
    {
        $data = $request->all();
        $userId = Auth::id();
        $base64Image = null;

        if ($request->hasFile('media_urls')) {
            $file = $request->file('media_urls');
            $base64Image = "data:" . $file->getMimeType() . ";base64," . base64_encode(file_get_contents($file));
        }

        // If editing existing post
        if (isset($data['id'])) {
            $post_content = PostContent::find($data['id']);
            if (!$post_content) {
                return redirect()->route('post-content')->with('error', 'Post not found');
            }
        } else {
            $post_content = new PostContent();
        }

        $post_content->current_date = $data['current_date'] ?? now();
        $post_content->user_id = $userId;
        $post_content->account_id = $data['account_id'];
        $post_content->title = $data['title'];
        $post_content->content = $data['content'];
        $post_content->media_urls = $base64Image;
        $post_content->hashtags = $data['hashtags'] ?? '';
        $post_content->save();

        $account = SocialAccounts::with('proxy')->find($post_content->account_id);

        if (!$account) {
            return redirect()->route('post-content')->with('error', 'Account not found');
        }

        // Check if account has valid session
        $isSessionValid = false;

        if ($account->cookies && $account->session_data && $account->last_login) {
            try {
                // Ensure last_login is a Carbon instance
                $lastLogin = $account->last_login instanceof Carbon
                    ? $account->last_login
                    : Carbon::parse($account->last_login);

                // Check if logged in within last 24 hours
                $hoursAgo = $lastLogin->diffInHours(now());
                $isSessionValid = $hoursAgo < 24;

                Log::info("Session check for account {$account->id}: Last login {$hoursAgo} hours ago, Valid: " . ($isSessionValid ? 'Yes' : 'No'));
            } catch (\Exception $e) {
                Log::error("Error checking session validity: " . $e->getMessage());
                $isSessionValid = false;
            }
        }

        $proxy = $account->proxy;

        // Prepare post data
        $postData = [
            'platform' => $account->platform,
            'content' => $post_content->content,
            'image' => $base64Image,
            'hashtags' => $post_content->hashtags,
            'proxy_host' => $proxy->proxy_host ?? null,
            'proxy_port' => $proxy->proxy_port ?? null,
            'proxy_username' => $proxy->proxy_username ?? null,
            'proxy_password' => $proxy->proxy_password ?? null,
        ];

        // If session is valid, use saved cookies/session
        if ($isSessionValid) {
            $postData['cookies'] = $account->cookies;
            $postData['sessionData'] = $account->session_data;
            $postData['authToken'] = $account->auth_token;

            Log::info("✅ Using saved session for account: " . $account->id);
        } else {
            // Session expired or doesn't exist, use credentials
            $postData['username'] = $account->account_username;
            $postData['password'] = $account->account_password;

            Log::info("🔑 Session expired or not found, using credentials for account: " . $account->id);
        }

        try {
            $response = Http::timeout(120)->post('http://localhost:3000/post-social', $postData);
            $res = $response->json();

            if (isset($res['success']) && $res['success']) {
                // Update post status
                $post_content->status = 'published';
                $post_content->published_at = now();
                $post_content->save();

                // Update account activity
                $account->daily_actions_count = ($account->daily_actions_count ?? 0) + 1;
                $account->save();

                return redirect()->route('post-content')->with('success', 'Post created and published successfully');
            } else {
                $post_content->status = 'failed';
                $post_content->save();

                return redirect()->route('post-content')->with('error', 'Post saved but failed to publish: ' . ($res['message'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            Log::error("Error posting content: " . $e->getMessage());

            $post_content->status = 'failed';
            $post_content->save();

            return redirect()->route('post-content')->with('error', 'Post saved but failed to publish: ' . $e->getMessage());
        }
    }
    public function getPostContentData()
    {
        // if (Auth::user()->role === 'admin') {
        //     // Admin can see all data
        //     $proxy = Proxy::all();
        // } else {
        //     // Regular user can see only their own data
        //     $proxy = Proxy::where('user_id', Auth::id())->get();
        // }
        $task = PostContent::where('user_id', Auth::id())->get();
        return DataTables::of($task)->make(true);

    }
    public function deletePostContentData($id)
    {
        $task = PostContent::find($id);
        if (!$task) {
            return redirect()->route('post-content')->with('error', 'task not found');
        }

        // if (Auth::user()->role !== 'admin' && $proxy->user_id !== Auth::id()) {
        //     return response()->json(['success' => false, 'message' => 'Access denied']);
        // }

        $task->delete();
        return response()->json(['success' => true, 'message' => 'task deleted successfully']);
    }
    public function fetchPostContentData($id)
    {
        $task = PostContent::find($id);
        if (!$task) {
            return response()->json(['success' => false, 'message' => 'task not found']);
        }
        return response()->json(['success' => true, 'data' => $task]);
    }
}
