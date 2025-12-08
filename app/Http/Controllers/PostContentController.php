<?php

namespace App\Http\Controllers;

use App\Models\PostContent;
use App\Models\SocialAccounts;
use App\Services\SocialLoginService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Yajra\DataTables\Facades\DataTables;

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

        $post_content->current_date = $data['current_date'];
        $post_content->user_id = $userId;
        $post_content->account_id = $data['account_id'];
        $post_content->title = $data['title'];
        $post_content->content = $data['content'];
        $post_content->media_urls = $base64Image;
        $post_content->hashtags = $data['hashtags'];
        $post_content->save();

        $account = SocialAccounts::with('proxy')->find($post_content->account_id);

        // Call Node.js API to login and post
        $proxy = $account->proxy;

        $response = Http::post('http://localhost:3000/post-social', [
            'username' => $account->account_username,
            'password' => $account->account_password,
            'platform' => $account->platform,
            'proxy_host' => $proxy->proxy_host ?? null,
            'proxy_port' => $proxy->proxy_port ?? null,
            'proxy_username' => $proxy->proxy_username ?? null,
            'proxy_password' => $proxy->proxy_password ?? null,
            'content' => $post_content->content,
            'image' => $base64Image,
            'hashtags' => $post_content->hashtags,
        ]);

        $res = $response->json();

        if ($res['success']) {
            return redirect()->route('post-content')->with('success', 'Post created and published successfully');
        } else {
            return redirect()->route('post-content')->with('error', 'Post saved but failed to publish: ' . $res['message']);
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
