<?php
namespace App\Http\Controllers;

use App\Models\SocialAccounts;
use App\Services\SocialLoginService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AutomationController extends Controller
{
    protected $socialService;

    public function __construct(SocialLoginService $socialService)
    {
        $this->socialService = $socialService;
    }

    public function autoLogin()
    {
        $accounts = SocialAccounts::where('user_id', Auth::id())->get();
        $results = [];

        foreach ($accounts as $account) {
            $success = $this->socialService->login($account);
            $results[] = [
                'platform' => $account->platform,
                'username' => $account->account_username,
                'logged_in' => $success
            ];
        }

        return response()->json($results);
    }
}
