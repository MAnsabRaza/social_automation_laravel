<?php

namespace App\Http\Controllers;

use App\Models\Proxy;
use App\Models\SocialAccounts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class SocialAccountsController extends Controller
{
    public function index()
    {
        $data['proxy']=Proxy::where('is_active',1)->get();
        $data['modules'] = ['setup/add-social-account.js'];
        return view('social-account/social-account', $data);
    }
    public function createSocialAccounts(Request $request)
    {
        $data = $request->all();
        $userId = Auth::id();
        if (isset($data['id'])) {
            $socialAccount = SocialAccounts::find($data['id']);
            if ($socialAccount) {
                $socialAccount->current_date = $data['current_date'];
                $socialAccount->user_id = $userId;
                $socialAccount->platform = $data['platform'];
                $socialAccount->account_username = $data['account_username'];
                $socialAccount->account_email = $data['account_email'];
                $socialAccount->account_password = $data['account_password'];
                $socialAccount->account_phone = $data['account_phone'];
                $socialAccount->cookies = $data['cookies'];
                $socialAccount->auth_token = $data['auth_token'];
                $socialAccount->session_data = $data['session_data'];
                $socialAccount->proxy_id = $data['proxy_id'];
                $socialAccount->status = $data['status'];
                $socialAccount->last_login = $data['last_login'];
                $socialAccount->warmup_level = $data['warmup_level'];
                $socialAccount->daily_actions_count = $data['daily_actions_count'];
                $socialAccount->save();

                return redirect()->route('socialAccount')->with('success', 'Social Account created successfully');
            } else {
                return redirect()->route('socialAccount')->with('error', 'Social Account not found');
            }
        }
        $socialAccount = new SocialAccounts();
        $socialAccount->current_date = $data['current_date'];
        $socialAccount->user_id = $userId;
        $socialAccount->platform = $data['platform'];
        $socialAccount->account_username = $data['account_username'];
        $socialAccount->account_email = $data['account_email'];
        $socialAccount->account_password = $data['account_password'];
        $socialAccount->account_phone = $data['account_phone'];
        $socialAccount->cookies = $data['cookies'];
        $socialAccount->auth_token = $data['auth_token'];
        $socialAccount->session_data = $data['session_data'];
        $socialAccount->proxy_id = $data['proxy_id'];
        $socialAccount->status = $data['status'];
        $socialAccount->last_login = $data['last_login'];
        $socialAccount->warmup_level = $data['warmup_level'];
        $socialAccount->daily_actions_count = $data['daily_actions_count'];
        $socialAccount->save();
        $socialAccount->save();

        return redirect()->route('socialAccount')->with('success', 'Social Account created successfully');
    }
    public function getSocialAccountData()
    {

        // if (Auth::user()->role === 'admin') {
        //     // Admin can see all data
        //     $proxy = Proxy::all();
        // } else {
        //     // Regular user can see only their own data
        //     $proxy = Proxy::where('user_id', Auth::id())->get();
        // }
        $socialAccount = SocialAccounts::where('user_id', Auth::id())->get();
        return DataTables::of($socialAccount)->make(true);

    }
    public function deleteSocialAccount($id)
    {
        $socialAccount = SocialAccounts::find($id);
        if (!$socialAccount) {
            return redirect()->route('socialAccount')->with('error', 'Social Account not found');
        }

        // if (Auth::user()->role !== 'admin' && $proxy->user_id !== Auth::id()) {
        //     return response()->json(['success' => false, 'message' => 'Access denied']);
        // }

        $socialAccount->delete();
        return response()->json(['success' => true, 'message' => 'Proxy deleted successfully']);
    }
    public function fetchSocialAccountData($id)
    {
        $socialAccount = SocialAccounts::find($id);
        if (!$socialAccount) {
            return response()->json(['success' => false, 'message' => 'Social Account not found']);
        }
        return response()->json(['success' => true, 'data' => $socialAccount]);
    }
}
