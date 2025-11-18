<?php
// app/Services/SocialLoginService.php

namespace App\Services;

use App\Models\SocialAccounts;
use Exception;
use Illuminate\Support\Facades\Log;

class SocialLoginService
{
    public function login(SocialAccounts $account)
    {
        Log::info("Starting auto-login for account: {$account->account_username}");
        
        try {
            $success = false;
            
            if ($account->platform === 'facebook') {
                $success = $this->loginFacebookWithApi($account);
            } else {
                // For other platforms, simulate success for now
                $success = $this->simulateLogin($account);
            }

            if ($success) {
                $account->last_login = now();
                $account->status = 'active';
                $account->daily_actions_count = 0;
                $account->warmup_level = ($account->warmup_level ?? 0) + 1;
                
                // Generate mock session data
                $this->generateMockSessionData($account);
                
                Log::info("Login successful for: {$account->account_username}");
            } else {
                $account->status = 'failed';
                Log::warning("Login failed for: {$account->account_username}");
            }

            $account->save();
            return $success;

        } catch (Exception $e) {
            Log::error('Auto login failed: ' . $e->getMessage());
            $account->status = 'error';
            $account->save();
            return false;
        }
    }

    private function loginFacebookWithApi(SocialAccounts $account)
    {
        try {
            // Using Facebook Graph API to verify credentials
            $appId = config('services.facebook.app_id');
            $appSecret = config('services.facebook.app_secret');
            
            if (!$appId || !$appSecret) {
                Log::warning('Facebook app credentials not configured');
                return $this->simulateLogin($account);
            }

            // Note: This is a simplified approach. Actual Facebook login requires OAuth flow.
            // For now, we'll simulate successful login and generate mock data.
            return $this->simulateLogin($account);

        } catch (Exception $e) {
            Log::error('Facebook API login error: ' . $e->getMessage());
            return $this->simulateLogin($account);
        }
    }

    private function simulateLogin(SocialAccounts $account)
    {
        try {
            // Simulate a successful login for testing
            // In production, you would implement actual login logic
            
            Log::info("Simulating login for: {$account->account_username}");
            
            // Simulate network delay
            sleep(2);
            
            // Return true 80% of the time for testing
            return rand(0, 100) < 80;
            
        } catch (Exception $e) {
            Log::error('Simulated login error: ' . $e->getMessage());
            return false;
        }
    }

    private function generateMockSessionData(SocialAccounts $account)
    {
        try {
            // Generate mock cookies
            $mockCookies = [
                [
                    'name' => 'session_id',
                    'value' => 'mock_session_' . md5($account->account_username . time()),
                    'domain' => '.facebook.com',
                    'path' => '/',
                    'expiry' => time() + 3600,
                    'secure' => true,
                    'httpOnly' => true
                ]
            ];

            // Generate mock local storage
            $mockLocalStorage = [
                'user_preferences' => '{}',
                'last_login' => now()->toISOString(),
                'account_id' => $account->id
            ];

            // Generate mock auth token
            $mockAuthToken = $account->platform . '_' . md5($account->account_username . time() . 'secret_salt');

            $account->cookies = json_encode($mockCookies);
            $account->session_data = json_encode($mockLocalStorage);
            $account->auth_token = $mockAuthToken;

            Log::info("Generated mock session data for: {$account->account_username}");

        } catch (Exception $e) {
            Log::error('Failed to generate mock session data: ' . $e->getMessage());
        }
    }
}