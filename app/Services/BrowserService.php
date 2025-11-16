<?php

namespace App\Services;

use App\Models\CaptchaSettings;
use App\Models\SocialAccounts;
use HeadlessChromium\BrowserFactory;
use App\Models\SocialAccount;
use Illuminate\Support\Facades\Http;

class BrowserService
{
    public function browser($proxy = null)
    {
        $browserFactory = new BrowserFactory('chrome');
        $options = ['headless' => true];

        if ($proxy) {
            $options['proxyServer'] = $proxy->proxy_host . ':' . $proxy->proxy_port;
        }

        return $browserFactory->createBrowser($options);
    }

    public function openPage($url, $account)
    {
        $proxy = $account->proxy;
        $cookies = $account->cookies ? json_decode($account->cookies, true) : null;

        $browser = $this->browser($proxy);
        $page = $browser->createPage();

        if ($cookies) {
            $page->setCookies($cookies);
        }

        $page->navigate($url)->waitForNavigation();
        return $page;
    }

    public function loginInstagram(SocialAccounts $account)
    {
        $page = $this->openPage('https://www.instagram.com/accounts/login/', $account);

        sleep(3);

        $page->type('input[name="username"]', $account->account_username);
        $page->type('input[name="password"]', $account->account_password);
        $page->click('button[type="submit"]');

        sleep(5);

        $cookies = $page->getCookies();
        $account->cookies = json_encode($cookies);
        $account->status = 'active';
        $account->last_login = now();
        $account->save();

        return "LOGIN SUCCESS";
    }

    public function solveCaptcha($sitekey, $url, $user_id)
    {
        $captcha = CaptchaSettings::where('user_id', $user_id)
            ->where('status', true)->first();

        if (!$captcha) {
            return null;
        }

        $apiKey = $captcha->api_key;

        $response = Http::post('http://2captcha.com/in.php', [
            'key' => $apiKey,
            'method' => 'userrecaptcha',
            'googlekey' => $sitekey,
            'pageurl' => $url,
        ]);

        $taskId = explode('|', $response)[1];
        sleep(20);

        $result = Http::get("http://2captcha.com/res.php?key={$apiKey}&action=get&id={$taskId}");
        return explode('|', $result)[1];
    }
}
