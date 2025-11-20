<?php

namespace App\Services;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Exception;

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
                return $this->facebookLogin($account);

            case 'instagram':
                return $this->instagramLogin($account);

            case 'twitter':
                return $this->twitterLogin($account);

            case 'youtube':
                return $this->youtubeLogin($account);

            case 'linkedin':
                return $this->linkedinLogin($account);

            default:
                throw new Exception("❌ Platform {$platform} not supported.");
        }
    }

    // FACEBOOK LOGIN
    private function facebookLogin($account)
    {
        $this->driver->get('https://www.facebook.com/login');
        sleep(3);

        $this->driver->findElement(WebDriverBy::id('email'))
            ->sendKeys($account->account_username);

        $this->driver->findElement(WebDriverBy::id('pass'))
            ->sendKeys($account->account_password);

        $this->driver->findElement(WebDriverBy::name('login'))->click();
        sleep(5);

        if (strpos($this->driver->getPageSource(), 'recaptcha') !== false) {
            $this->solveCaptcha();
        }

        return true;
    }

    // INSTAGRAM LOGIN
    
    private function instagramLogin($account)
    {
        $this->driver->get('https://www.instagram.com/accounts/login/');
        sleep(5);

        $this->driver->findElement(WebDriverBy::name('username'))
            ->sendKeys($account->account_username);

        $this->driver->findElement(WebDriverBy::name('password'))
            ->sendKeys($account->account_password);

        $this->driver->findElement(WebDriverBy::xpath("//button[@type='submit']"))
            ->click();

        sleep(5);
        return true;
    }

    // TWITTER LOGIN
    private function twitterLogin($account)
    {
        $this->driver->get('https://twitter.com/login');
        sleep(5);

        $this->driver->findElement(WebDriverBy::name('text'))
            ->sendKeys($account->account_username);

        $this->driver->findElement(WebDriverBy::xpath("//span[text()='Next']"))
            ->click();
        sleep(3);

        $this->driver->findElement(WebDriverBy::name('password'))
            ->sendKeys($account->account_password);

        $this->driver->findElement(WebDriverBy::xpath("//span[text()='Log in']"))
            ->click();

        sleep(5);
        return true;
    }

    // YOUTUBE LOGIN (GOOGLE LOGIN)
    private function youtubeLogin($account)
    {
        $this->driver->get('https://accounts.google.com/signin/v2/identifier');
        sleep(3);

        $this->driver->findElement(WebDriverBy::id('identifierId'))
            ->sendKeys($account->account_email);

        $this->driver->findElement(WebDriverBy::id('identifierNext'))->click();
        sleep(3);

        $this->driver->findElement(WebDriverBy::name('password'))
            ->sendKeys($account->account_password);

        $this->driver->findElement(WebDriverBy::id('passwordNext'))->click();
        sleep(5);

        return true;
    }

    // LINKEDIN LOGIN
    private function linkedinLogin($account)
    {
        $this->driver->get('https://www.linkedin.com/login');
        sleep(4);

        $this->driver->findElement(WebDriverBy::id('username'))
            ->sendKeys($account->account_email);

        $this->driver->findElement(WebDriverBy::id('password'))
            ->sendKeys($account->account_password);

        $this->driver->findElement(WebDriverBy::xpath("//button[@type='submit']"))
            ->click();

        sleep(5);
        return true;
    }
    //Tiktok Login
    private function tiktokLogin($account)
{
    // TikTok login page
    $this->driver->get('https://www.tiktok.com/login');
    sleep(5);

    try {
        $this->driver->findElement(WebDriverBy::xpath("//div[contains(text(),'Use phone / email / username')]"))
            ->click();

        sleep(2);

        $this->driver->findElement(WebDriverBy::xpath("//div[contains(text(),'Email / Username')]"))
            ->click();

        sleep(2);

        $this->driver->findElement(WebDriverBy::xpath("//input[@type='text']"))
            ->sendKeys($account->account_username);

        // Enter password
        $this->driver->findElement(WebDriverBy::xpath("//input[@type='password']"))
            ->sendKeys($account->account_password);

        // Click Login button
        $this->driver->findElement(WebDriverBy::xpath("//button[contains(text(),'Log in')]"))
            ->click();

        sleep(6);

        // Handle CAPTCHA if appears
        if (strpos($this->driver->getPageSource(), 'recaptcha') !== false) {
            $this->solveCaptcha();
        }

        return true;

    } catch (\Exception $e) {
        throw new \Exception("❌ TikTok Login Error: " . $e->getMessage());
    }
}



    // ---------------------------------------
    // CAPTCHA SOLVER
    // ---------------------------------------
    private function solveCaptcha()
    {
        try {
            $iframe = $this->driver->findElement(
                WebDriverBy::cssSelector('iframe[src*="recaptcha"]')
            );

            $src = $iframe->getAttribute("src");

            preg_match('/k=([^&]+)/', $src, $matches);
            $siteKey = $matches[1];

            $pageUrl = $this->driver->getCurrentURL();

            $token = \App\Services\CaptchaSolver::solveRecaptchaV2($siteKey, $pageUrl);

            $this->driver->executeScript("
                document.getElementById('g-recaptcha-response').value = '{$token}';
            ");

            $this->driver->executeScript("
                document.querySelector('button[name=\"login\"]').click();
            ");

        } catch (Exception $e) {
            throw new Exception("CAPTCHA Solve Failed: " . $e->getMessage());
        }
    }

    public function quit()
    {
        $this->driver->quit();
    }
}
