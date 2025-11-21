<?php

namespace App\Services;

use Facebook\WebDriver\Remote\LocalFileDetector;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Exception;
use Facebook\WebDriver\WebDriverExpectedCondition;

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
    private function solveCaptcha()
    {
        try {
            // Wait until iframe appears (max 15s)
            $iframe = null;
            for ($i = 0; $i < 15; $i++) {
                try {
                    $iframe = $this->driver->findElement(WebDriverBy::cssSelector('iframe[src*="recaptcha"]'));
                    if ($iframe)
                        break;
                } catch (\Exception $e) {
                    sleep(1);
                }
            }

            if (!$iframe) {
                throw new \Exception("No reCAPTCHA iframe found");
            }

            $src = $iframe->getAttribute("src");
            preg_match('/k=([^&]+)/', $src, $matches);
            $siteKey = $matches[1];
            $pageUrl = $this->driver->getCurrentURL();

            // Solve via CapSolver
            $token = CaptchaSolver::solveRecaptchaV2($siteKey, $pageUrl);

            // Inject token into textarea
            $this->driver->executeScript("
            var textarea = document.querySelector('textarea#g-recaptcha-response');
            if(textarea) {
                textarea.style.display='block';
                textarea.value = '{$token}';
            }
        ");

            // Trigger the official callback
            $this->driver->executeScript("
            if (window.grecaptcha && window.grecaptcha.getResponse) {
                var cb = document.querySelector('textarea#g-recaptcha-response');
                if(cb) {
                    var event = new Event('change');
                    cb.dispatchEvent(event);
                }
            }
        ");

            sleep(2);

            // Click login button
            $loginButton = $this->driver->findElement(
                WebDriverBy::xpath("//button[contains(@name,'login') or contains(@type,'submit')]")
            );
            $loginButton->click();

            sleep(5);
        } catch (\Exception $e) {
            throw new \Exception("CAPTCHA Solve Failed: " . $e->getMessage());
        }
    }

    public function quit()
    {
        $this->driver->quit();
    }

    //post create
    public function postToInstagram($post)
    {
        try {
            // STEP 1: Open Instagram Upload Page
            $this->driver->get("https://www.instagram.com/create/select/");

            // Wait for the file input to appear
            $wait = new \Facebook\WebDriver\WebDriverWait($this->driver, 15);
            $fileInput = $wait->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('input[type="file"]')
                )
            );

            // STEP 2: Upload Image
            $filePath = $this->saveBase64Image($post->media_urls);
            $fileInput->setFileDetector(new LocalFileDetector());
            $fileInput->sendKeys($filePath);

            // STEP 3: Click FIRST NEXT button
            $next1 = $wait->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::xpath("//button[contains(., 'Next')]")
                )
            );
            $next1->click();

            // STEP 4: Click SECOND NEXT button
            $next2 = $wait->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::xpath("//button[contains(., 'Next')]")
                )
            );
            $next2->click();

          
            // STEP 6: Click Share button
            $shareBtn = $this->findInstagramShareButton();
            if (!$shareBtn) {
                throw new Exception("Share button not found! IG UI may have changed.");
            }
            $shareBtn->click();

            // Wait for a few seconds to ensure post is uploaded
            sleep(5);

            return true;

        } catch (Exception $e) {

            // Save screenshot for debugging
            $this->driver->takeScreenshot(storage_path("app/public/instagram_error.png"));
            throw new Exception("Instagram Posting Failed: " . $e->getMessage());
        }
    }

    private function findInstagramShareButton()
    {
        try {
            $wait = new \Facebook\WebDriver\WebDriverWait($this->driver, 10);
            return $wait->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::xpath("//button[contains(., 'Share') or contains(., 'Publish')]")
                )
            );
        } catch (Exception $e) {
            return null;
        }
    }

    private function saveBase64Image($base64)
    {
        $fileData = explode(',', $base64);
        $imageData = base64_decode($fileData[1]);

        $filePath = storage_path('app/public/temp_' . time() . '.jpg');
        file_put_contents($filePath, $imageData);

        return $filePath;
    }
}