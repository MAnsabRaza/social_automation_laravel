<?php

namespace App\Services;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\LocalFileDetector;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Exception;

class SocialLoginService
{
    // -------------------------------------------------
    // SINGLETON WEBDRIVER INSTANCE (Only 1 browser)
    // -------------------------------------------------
    protected static $driver = null;

    public function __construct()
    {
        if (self::$driver === null) {

            $options = new ChromeOptions();
            $options->addArguments([
                '--start-maximized',
                '--disable-notifications',
                '--disable-infobars',
                '--disable-popup-blocking'
            ]);

            $cap = DesiredCapabilities::chrome();
            $cap->setCapability(ChromeOptions::CAPABILITY, $options);

            // Create driver only once
            self::$driver = RemoteWebDriver::create(
                'http://localhost:9515',
                $cap
            );
        }
    }

    public function getDriver()
    {
        return self::$driver;
    }

    public function quit()
    {
        if (self::$driver) {
            self::$driver->quit();
            self::$driver = null;
        }
    }

    // -------------------------------------------------
    // UNIVERSAL LOGIN ENTRY POINT (ALL PLATFORMS)
    // -------------------------------------------------
    public function login($account)
    {
        $driver = $this->getDriver();
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

            case 'tiktok':
                return $this->tiktokLogin($account);

            default:
                throw new Exception("Platform not supported: {$platform}");
        }
    }

    // -------------------------------------------------
    // FACEBOOK LOGIN
    // -------------------------------------------------
    private function facebookLogin($account)
    {
        $driver = $this->getDriver();

        $driver->get('https://www.facebook.com/login');
        sleep(3);

        $driver->findElement(WebDriverBy::id('email'))
            ->sendKeys($account->account_username);

        $driver->findElement(WebDriverBy::id('pass'))
            ->sendKeys($account->account_password);

        $driver->findElement(WebDriverBy::name('login'))->click();

        sleep(6);

        return true;
    }


    // -------------------------------------------------
    // INSTAGRAM LOGIN
    // -------------------------------------------------
    private function instagramLogin($account)
    {
        $driver = $this->getDriver();

        $driver->get('https://www.instagram.com/accounts/login/');
        sleep(5);

        $driver->findElement(WebDriverBy::name('username'))
            ->sendKeys($account->account_username);

        $driver->findElement(WebDriverBy::name('password'))
            ->sendKeys($account->account_password);

        $driver->findElement(WebDriverBy::xpath("//button[@type='submit']"))
            ->click();

        sleep(8);

        return true;
    }


    // -------------------------------------------------
    // TWITTER LOGIN
    // -------------------------------------------------
    private function twitterLogin($account)
    {
        $driver = $this->getDriver();

        $driver->get('https://twitter.com/login');
        sleep(5);

        $driver->findElement(WebDriverBy::name('text'))
            ->sendKeys($account->account_username);

        $driver->findElement(WebDriverBy::xpath("//span[text()='Next']"))
            ->click();
        sleep(3);

        $driver->findElement(WebDriverBy::name('password'))
            ->sendKeys($account->account_password);

        $driver->findElement(WebDriverBy::xpath("//span[text()='Log in']"))
            ->click();

        sleep(6);
        return true;
    }

    // -------------------------------------------------
    // YOUTUBE / GOOGLE LOGIN
    // -------------------------------------------------
    private function youtubeLogin($account)
    {
        $driver = $this->getDriver();

        $driver->get('https://accounts.google.com/signin/v2/identifier');
        sleep(3);

        $driver->findElement(WebDriverBy::id('identifierId'))
            ->sendKeys($account->account_email);

        $driver->findElement(WebDriverBy::id('identifierNext'))->click();

        sleep(3);

        $driver->findElement(WebDriverBy::name('password'))
            ->sendKeys($account->account_password);

        $driver->findElement(WebDriverBy::id('passwordNext'))->click();

        sleep(6);

        return true;
    }

    // -------------------------------------------------
    // LINKEDIN LOGIN
    // -------------------------------------------------
    private function linkedinLogin($account)
    {
        $driver = $this->getDriver();

        $driver->get('https://www.linkedin.com/login');
        sleep(4);

        $driver->findElement(WebDriverBy::id('username'))
            ->sendKeys($account->account_email);

        $driver->findElement(WebDriverBy::id('password'))
            ->sendKeys($account->account_password);

        $driver->findElement(WebDriverBy::xpath("//button[@type='submit']"))
            ->click();

        sleep(6);
        return true;
    }

    // -------------------------------------------------
    // TIKTOK LOGIN
    // -------------------------------------------------
    private function tiktokLogin($account)
    {
        $driver = $this->getDriver();

        $driver->get('https://www.tiktok.com/login');
        sleep(5);

        $driver->findElement(WebDriverBy::xpath("//div[contains(text(),'Use phone / email / username')]"))
            ->click();
        sleep(2);

        $driver->findElement(WebDriverBy::xpath("//div[contains(text(),'Email / Username')]"))
            ->click();
        sleep(2);

        $driver->findElement(WebDriverBy::xpath("//input[@type='text']"))
            ->sendKeys($account->account_username);

        $driver->findElement(WebDriverBy::xpath("//input[@type='password']"))
            ->sendKeys($account->account_password);

        $driver->findElement(WebDriverBy::xpath("//button[contains(text(),'Log in')]"))
            ->click();

        sleep(6);
        return true;
    }


    // -------------------------------------------------
    // INSTAGRAM LOGOUT (SAME BROWSER TAB)
    // -------------------------------------------------
    public function instagramLogout()
    {
        $driver = $this->getDriver();

        try {
            $driver->get('https://www.instagram.com/');
            sleep(5);

            $profile = $driver->findElement(
                WebDriverBy::xpath("//img[contains(@alt,'profile')]")
            );
            $profile->click();
            sleep(3);

            $settings = $driver->findElement(
                WebDriverBy::xpath("//span[contains(text(),'Settings')]")
            );
            $settings->click();
            sleep(3);

            $driver->executeScript("window.scrollTo(0, document.body.scrollHeight);");
            sleep(2);

            $logout = $driver->findElement(
                WebDriverBy::xpath("//div[contains(text(),'Log out') or contains(text(),'Log Out')]")
            );
            $logout->click();

            sleep(4);
            return true;

        } catch (Exception $e) {
            throw new Exception("Instagram Logout Failed: " . $e->getMessage());
        }
    }

    // -------------------------------------------------
    // INSTAGRAM POST
    // -------------------------------------------------
    public function postToInstagram($post)
    {
        $driver = $this->getDriver();

        try {
            $driver->get("https://www.instagram.com/create/select/");
            sleep(5);

            $wait = new \Facebook\WebDriver\WebDriverWait($driver, 20);

            $fileInput = $wait->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector("input[type='file']")
                )
            );

            $fileInput->setFileDetector(new LocalFileDetector());

            $filePath = $this->saveBase64Image($post->media_urls);
            $fileInput->sendKeys($filePath);

            sleep(5);

            // Click Next
            $wait->until(WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::xpath("//button[contains(., 'Next')]")
            ))->click();

            sleep(3);

            // Next again
            $wait->until(WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::xpath("//button[contains(., 'Next')]")
            ))->click();

            sleep(3);

            // Caption
            $captionArea = $driver->findElement(WebDriverBy::xpath("//textarea"));
            $captionArea->sendKeys($post->caption);

            sleep(2);

            // Share publish
            $wait->until(WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::xpath("//button[contains(., 'Share') or contains(., 'Publish')]")
            ))->click();

            sleep(6);
            return true;

        } catch (Exception $e) {
            $driver->takeScreenshot(storage_path("app/public/instagram_error.png"));
            throw new Exception("Instagram Posting Failed: " . $e->getMessage());
        }
    }

    // -------------------------------------------------
    // SAVE BASE64 IMAGE TO TEMP FILE
    // -------------------------------------------------
    private function saveBase64Image($base64)
    {
        $data = explode(",", $base64);
        $img = base64_decode(end($data));

        $path = storage_path("app/public/ig_post_" . time() . ".jpg");
        file_put_contents($path, $img);

        return $path;
    }
}
