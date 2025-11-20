<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CaptchaSolver
{
    public static function solveRecaptchaV2($siteKey, $pageUrl)
    {
        $apiKey = env('CAPTCHA_API_KEY');

        // Step 1: Send CAPTCHA to 2Captcha
        $request = Http::get("http://2captcha.com/in.php", [
            'key' => $apiKey,
            'method' => 'userrecaptcha',
            'googlekey' => $siteKey,
            'pageurl' => $pageUrl,
            'json' => 1
        ]);

        $captchaId = $request->json()['request'];

        // Step 2: Wait until solved
        for ($i = 0; $i < 20; $i++) {
            sleep(5);

            $result = Http::get("http://2captcha.com/res.php", [
                'key' => $apiKey,
                'action' => 'get',
                'id' => $captchaId,
                'json' => 1
            ]);

            if ($result->json()['status'] == 1) {
                return $result->json()['request']; // TOKEN
            }
        }

        throw new \Exception("Captcha solving failed");
    }
}
