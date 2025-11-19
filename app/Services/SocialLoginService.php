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
        $this->driver->findElement(WebDriverBy::id('email'))->sendKeys($account->account_email);
        $this->driver->findElement(WebDriverBy::id('pass'))->sendKeys($account->account_password);
        $this->driver->findElement(WebDriverBy::name('login'))->click();

        // Wait 5 seconds for login to complete (or implement proper wait)
        sleep(5);
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
