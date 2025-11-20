<?php

namespace App\Services;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;

class SocialLoginService
{
    protected $driver;

    public function __construct()
    {
        // Connect to ChromeDriver running on localhost:9515
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

    private function facebookLogin($account)
    {
        $this->driver->get('https://www.facebook.com/');
        sleep(3);

        $this->driver->findElement(WebDriverBy::id('email'))->sendKeys($account->account_username);
        $this->driver->findElement(WebDriverBy::id('pass'))->sendKeys($account->account_password);

        // Click Login
        $this->driver->findElement(WebDriverBy::name('login'))->click();
        sleep(5);

        // Check if CAPTCHA appears
        if (strpos($this->driver->getPageSource(), 'recaptcha') !== false) {

            // 1. Find site's reCAPTCHA key
            $iframe = $this->driver->findElement(
                WebDriverBy::cssSelector('iframe[src*="recaptcha"]')
            );
            $src = $iframe->getAttribute("src");

            preg_match('/k=([^&]+)/', $src, $matches);
            $siteKey = $matches[1];

            $pageUrl = $this->driver->getCurrentURL();

            // 2. Solve using 2Captcha
            $token = \App\Services\CaptchaSolver::solveRecaptchaV2($siteKey, $pageUrl);

            // 3. Inject token inside hidden field
            $this->driver->executeScript("
            document.getElementById('g-recaptcha-response').style.display = 'block';
            document.getElementById('g-recaptcha-response').value = '{$token}';
        ");

            // 4. Resubmit form
            $this->driver->executeScript("
            document.querySelector('button[name=\"login\"]').click();
        ");

            sleep(5);
        }
    }


    private function instagramLogin($account)
    {
        $this->driver->get('https://www.instagram.com/accounts/login/');
        sleep(3); // wait for page load
        $this->driver->findElement(WebDriverBy::name('username'))->sendKeys($account->account_username);
        $this->driver->findElement(WebDriverBy::name('password'))->sendKeys($account->account_password);
        $this->driver->findElement(WebDriverBy::xpath("//button[@type='submit']"))->click();

        sleep(5); // wait for login
    }

    public function quit()
    {
        $this->driver->quit();
    }
}
