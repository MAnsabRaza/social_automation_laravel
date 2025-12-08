<?php

namespace App\Http\Controllers;

use App\Models\Proxy;
use App\Models\SocialAccounts;
use App\Services\SocialLoginService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Http;

class SocialAccountsController extends Controller
{
    public function index()
    {
        $data['proxy'] = Proxy::where('is_active', 1)->get();
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
                $socialAccount->last_login = now();
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
        $socialAccount->last_login = now();
        $socialAccount->warmup_level = $data['warmup_level'];
        $socialAccount->daily_actions_count = $data['daily_actions_count'];
        $socialAccount->save();
        return redirect()->route('socialAccount')->with('success', 'Social Account created successfully');
    }
    public function getSocialAccountData(Request $request)
    {
        $query = SocialAccounts::where('user_id', Auth::id());

        // Filter by platform if provided
        if ($request->platform) {
            $query->where('platform', $request->platform);
        }

        return DataTables::of($query)->make(true);
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
    private function convertDate($date)
    {
        if (!$date)
            return null;

        // Convert any date format into Y-m-d
        $timestamp = strtotime($date);

        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d', $timestamp);
    }

    public function importCSV(Request $request)
    {
        $file = $request->file('csv_file');
        $handle = fopen($file, 'r');
        $userId = Auth::id();
        $row = 0;
        while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {

            if ($row == 0) {
                $row++;
                continue;
            }

            SocialAccounts::create([
                'current_date' => $this->convertDate($data[0]),
                'user_id' => $userId,
                'platform' => $data[1],
                'account_username' => $data[2],
                'account_email' => $data[3],
                'account_password' => $data[4],
                'account_phone' => $data[5],
                'cookies' => $data[6],
                'auth_token' => $data[7],
                'session_data' => $data[8],
                'proxy_id' => $data[9],
                'status' => $data[10],
                'last_login' => $this->convertDate($data[11]),
                'warmup_level' => $data[12],
                'daily_actions_count' => $data[13]
            ]);
            $row++;
        }
        fclose($handle);

        return redirect()->back()->with('success', 'CSV Imported Successfully');
    }

    public function runAccount($id)
    {
        $account = SocialAccounts::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        $platforms = [
            'facebook' => 'https://www.facebook.com',
            'instagram' => 'https://www.instagram.com',
            'twitter' => 'https://twitter.com',
            'youtube' => 'https://www.youtube.com',
            'tiktok' => 'https://www.tiktok.com',
        ];

        $url = $platforms[$account->platform] ?? 'https://google.com';

        // Decode saved cookies (JSON format)
        $cookies = $account->cookies ? json_decode($account->cookies, true) : [];

        return view('social-account/social-account-run', compact('account', 'url', 'cookies'));
    }

    public function startAccount($id)
    {
        $account = SocialAccounts::with('proxy')->findOrFail($id);
        $account->status = 'inprogress';
        $account->save();

        $proxy = $account->proxy;

        $response = Http::timeout(120)->post('http://localhost:3000/login-social', [
            'username' => $account->account_username,
            'password' => $account->account_password,
            'platform' => $account->platform,
            'account_id' => $account->id,
            'proxy_host' => $proxy->proxy_host ?? null,
            'proxy_port' => $proxy->proxy_port ?? null,
            'proxy_username' => $proxy->proxy_username ?? null,
            'proxy_password' => $proxy->proxy_password ?? null,
        ]);

        $result = $response->json();

        if (isset($result['success']) && $result['success']) {
            // Save cookies, auth token, and session data
            $account->cookies = $result['cookies'] ?? null;
            $account->auth_token = $result['authToken'] ?? null;
            $account->session_data = $result['sessionData'] ?? null;
            $account->last_login = now();
            $account->status = 'active';
            $account->save();

            return response()->json([
                'success' => true,
                'message' => 'Login successful and session saved'
            ]);
        } else {
            $account->status = 'failed';
            $account->save();

            return response()->json([
                'success' => false,
                'message' => $result['error'] ?? 'Login failed'
            ]);
        }
    }

    public function checkAccountStatus($id)
    {
        $account = SocialAccounts::findOrFail($id);
        
        $isLoggedIn = $account->cookies && 
                     $account->last_login && 
                     $account->last_login->diffInHours(now()) < 24;

        return response()->json([
            'success' => true,
            'is_logged_in' => $isLoggedIn,
            'last_login' => $account->last_login,
            'status' => $account->status
        ]);
    }



    // public function startAccount($id)
    // {
    //     $account = SocialAccounts::findOrFail($id);

    //     $account->status = 'inprogress';
    //     $account->save();

    //     $loginService = new SocialLoginService();

    //     try {
    //         $loginService->login($account);
    //     } catch (\Exception $e) {
    //         $account->status = 'error';
    //         $account->save();
    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->getMessage()
    //         ]);
    //     }
    //     $account->status = 'inprogress';
    //     $account->save();

    //     return response()->json(['success' => true]);
    // }
    public function stopAccount($id)
    {
        $account = SocialAccounts::findOrFail($id);

        // If you are running ChromeDriver via server-side service, terminate it here
        // For now, just update status
        $account->last_login = Carbon::now();
        $account->status = 'complete';
        $account->save();

        return response()->json(['success' => true, 'message' => 'Account stopped']);
    }

}
