<?php

namespace App\Http\Controllers;
use App\Models\Proxy;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class ProxyController extends Controller
{
    public function index()
    {
        $data['modules'] = ['setup/add-proxy.js'];
        return view('proxy/proxy', $data);
    }
    public function createProxy(Request $request)
    {
        $data = $request->all();
        $userId = Auth::id();
        if (isset($data['id'])) {
            $proxy = Proxy::find($data['id']);
            if ($proxy) {
                $proxy->current_date = $data['current_date'];
                $proxy->user_id = $userId;
                $proxy->proxy_type = $data['proxy_type'];
                $proxy->proxy_port = $data['proxy_port'];
                $proxy->proxy_host = $data['proxy_host'];
                $proxy->proxy_username = $data['proxy_username'];
                $proxy->proxy_password = $data['proxy_password'];
                $proxy->is_active = isset($data['is_active']) ? 1 : 0;
                $proxy->last_used = $data['last_used'];
                $proxy->save();

                return redirect()->route('proxy')->with('success', 'Proxy created successfully');
            } else {
                return redirect()->route('proxy')->with('error', 'Proxy not found');
            }
        }
        $proxy = new Proxy();
        $proxy->current_date = $data['current_date'];
        $proxy->user_id = $userId;
        $proxy->proxy_type = $data['proxy_type'];
        $proxy->proxy_port = $data['proxy_port'];
        $proxy->proxy_host = $data['proxy_host'];
        $proxy->proxy_username = $data['proxy_username'];
        $proxy->proxy_password = $data['proxy_password'];
        $proxy->is_active = isset($data['is_active']) ? 1 : 0;
        $proxy->last_used = $data['last_used'];
        $proxy->save();

        return redirect()->route('proxy')->with('success', 'Proxy created successfully');
    }
    public function getProxyData()
    {

        // if (Auth::user()->role === 'admin') {
        //     // Admin can see all data
        //     $proxy = Proxy::all();
        // } else {
        //     // Regular user can see only their own data
        //     $proxy = Proxy::where('user_id', Auth::id())->get();
        // }
        $proxy = Proxy::where('user_id', Auth::id())->get();
        return DataTables::of($proxy)->make(true);

    }
    public function deleteProxy($id)
    {
        $proxy = Proxy::find($id);
        if (!$proxy) {
            return redirect()->route('proxy')->with('error', 'Proxy not found');
        }

        // if (Auth::user()->role !== 'admin' && $proxy->user_id !== Auth::id()) {
        //     return response()->json(['success' => false, 'message' => 'Access denied']);
        // }

        $proxy->delete();
        return response()->json(['success' => true, 'message' => 'Proxy deleted successfully']);
    }
   public function fetchProxyData($id)
    {
        $proxy = Proxy::find($id);
        if (!$proxy) {
            return response()->json(['success' => false, 'message' => 'Proxy not found']);
        }
        return response()->json(['success' => true, 'data' => $proxy]);
    }
}
