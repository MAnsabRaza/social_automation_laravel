<?php

namespace App\Http\Controllers;

use App\Models\PostContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class PostContentController extends Controller
{
    public function index()
    {
        $data['modules'] = ['setup/add-post-content.js'];
        return view('post-content/post-content', $data);
    }
    public function createPostContent(Request $request)
    {
        $data = $request->all();
        $userId = Auth::id();
        if (isset($data['id'])) {
            $post_content = PostContent::find($data['id']);
            if ($post_content) {
                $post_content->current_date = $data['current_date'];
                $post_content->user_id = $userId;
                $post_content->title = $data['title'];
                $post_content->content = $data['content'];
                $post_content->media_urls = $data['media_urls'];
                $post_content->hashtags = $data['hashtags'];
                $post_content->category = $data['category'];
                $post_content->save();

                return redirect()->route('post-content')->with('success', 'task created successfully');
            } else {
                return redirect()->route('post-content')->with('error', 'task not found');
            }
        }
        $post_content = new PostContent();
        $post_content->current_date = $data['current_date'];
        $post_content->user_id = $userId;
        $post_content->title = $data['title'];
        $post_content->content = $data['content'];
        $post_content->media_urls = $data['media_urls'];
        $post_content->hashtags = $data['hashtags'];
        $post_content->category = $data['category'];
        $post_content->save();

        return redirect()->route('post-content')->with('success', 'Post Content created successfully');
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
