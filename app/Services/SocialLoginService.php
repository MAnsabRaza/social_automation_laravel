<?php

namespace App\Services;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Illuminate\Support\Facades\Http;
use App\Models\CaptchaSettings;
use Illuminate\Support\Facades\Auth;

class SocialLoginService
{
    protected $driver;

    public function __construct()
    {
        $this->driver = RemoteWebDriver::create(
            'http://localhost:9515',
            DesiredCapabilities::chrome()
        );
    }

    public function login($account)
    {
        $platform = strtolower($account->platform);

        switch ($platform) {
            case 'facebook':
                $this->facebookLogin($account);
                break;
            case 'instagram':
                $this->instagramLogin($account);
                break;
            default:
                throw new \Exception("Platform {$platform} not supported yet");
        }
    }

    /**
     * Solve reCAPTCHA v2 using 2Captcha
     */
    private function solveRecaptchaV2($siteKey, $url, $apiKey)
    {
        $response = Http::asForm()->post("http://2captcha.com/in.php", [
            'key' => $apiKey,
            'method' => 'userrecaptcha',
            'googlekey' => $siteKey,
            'pageurl' => $url,
            'json' => 1,
        ]);

        if (!isset($response['request'])) {
            throw new \Exception("2Captcha request failed: " . json_encode($response));
        }

        $requestId = $response['request'];
        $timeout = 120; // max 2 minutes
        $elapsed = 0;

        while ($elapsed < $timeout) {
            sleep(5);
            $elapsed += 5;

            $result = Http::get("http://2captcha.com/res.php", [
                'key' => $apiKey,
                'action' => 'get',
                'id' => $requestId,
                'json' => 1
            ]);

            if ($result['status'] == 1) {
                return $result['request']; // captcha token
            }
        }

        throw new \Exception("CAPTCHA solving timed out");
    }

    /**
     * Check if reCAPTCHA is present
     */
    private function isRecaptchaPresent()
    {
        try {
            $this->driver->findElement(WebDriverBy::cssSelector('iframe[src*="recaptcha"]'));
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Facebook login with CAPTCHA handling
     */
    private function facebookLogin($account)
    {
        $driver = $this->driver;

        $driver->get('https://www.facebook.com/');
        $driver->findElement(WebDriverBy::id('email'))->sendKeys($account->account_username);
        $driver->findElement(WebDriverBy::id('pass'))->sendKeys($account->account_password);
        $driver->findElement(WebDriverBy::name('login'))->click();

        sleep(5);

        // Handle CAPTCHA if present
        if ($this->isRecaptchaPresent()) {

            // Switch to frame and get sitekey
            $iframe = $driver->findElement(WebDriverBy::cssSelector('iframe[src*="recaptcha"]'));
            $driver->switchTo()->frame($iframe);

            $siteKey = $driver->findElement(WebDriverBy::cssSelector('.g-recaptcha'))->getAttribute('data-sitekey');
            $driver->switchTo()->defaultContent();

            $captchaSettings = CaptchaSettings::where('user_id', Auth::id())->where('status', 1)->first();
            if (!$captchaSettings) {
                throw new \Exception("No active CAPTCHA API key found");
            }

            $token = $this->solveRecaptchaV2($siteKey, $driver->getCurrentURL(), $captchaSettings->api_key);

            // Inject token dynamically (works on Facebook/Instagram)
            $script = "
                var textarea = document.createElement('textarea');
                textarea.id = 'g-recaptcha-response';
                textarea.style.display = 'none';
                textarea.value = '$token';
                document.body.appendChild(textarea);
            ";
            $driver->executeScript($script);

            // Submit login again
            $driver->findElement(WebDriverBy::name('login'))->click();
            sleep(5);
        }
    }

    /**
     * Instagram login with optional CAPTCHA
     */
    private function instagramLogin($account)
    {
        $driver = $this->driver;

        $driver->get('https://www.instagram.com/accounts/login/');
        sleep(3);

        $driver->findElement(WebDriverBy::name('username'))->sendKeys($account->account_username);
        $driver->findElement(WebDriverBy::name('password'))->sendKeys($account->account_password);
        $driver->findElement(WebDriverBy::xpath("//button[@type='submit']"))->click();

        sleep(5);

        // Instagram reCAPTCHA (if any) handled same as Facebook
        if ($this->isRecaptchaPresent()) {

            $iframe = $driver->findElement(WebDriverBy::cssSelector('iframe[src*="recaptcha"]'));
            $driver->switchTo()->frame($iframe);
            $siteKey = $driver->findElement(WebDriverBy::cssSelector('.g-recaptcha'))->getAttribute('data-sitekey');
            $driver->switchTo()->defaultContent();

            $captchaSettings = CaptchaSettings::where('user_id', Auth::id())->where('status', 1)->first();
            if (!$captchaSettings) {
                throw new \Exception("No active CAPTCHA API key found");
            }

            $token = $this->solveRecaptchaV2($siteKey, $driver->getCurrentURL(), $captchaSettings->api_key);

            $script = "
                var textarea = document.createElement('textarea');
                textarea.id = 'g-recaptcha-response';
                textarea.style.display = 'none';
                textarea.value = '$token';
                document.body.appendChild(textarea);
            ";
            $driver->executeScript($script);

            // Submit login again
            $driver->findElement(WebDriverBy::xpath("//button[@type='submit']"))->click();
            sleep(5);
        }
    }

    public function quit()
    {
        $this->driver->quit();
    }
}
