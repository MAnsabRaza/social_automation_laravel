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

    public function __construct($proxy = null)
    {
        if ($proxy) {
            $this->driver = $this->createDriverWithProxy($proxy);
        } else {
            $this->driver = RemoteWebDriver::create(
                'http://localhost:9515',
                DesiredCapabilities::chrome()
            );
        }
    }


    //Proxy
    private function createDriverWithProxy($proxy)
    {
        $chromeOptions = new \Facebook\WebDriver\Chrome\ChromeOptions();

        // Add proxy argument
        $proxyString = "{$proxy->proxy_host}:{$proxy->proxy_port}";
        $chromeOptions->addArguments([
            "--proxy-server=http://{$proxyString}"
        ]);

        // If proxy has username/password → use extension-based login
        if ($proxy->proxy_username && $proxy->proxy_password) {
            $this->addProxyAuthExtension(
                $chromeOptions,
                $proxy->proxy_host,
                $proxy->proxy_port,
                $proxy->proxy_username,
                $proxy->proxy_password
            );
        }

        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(
            \Facebook\WebDriver\Chrome\ChromeOptions::CAPABILITY,
            $chromeOptions
        );

        return RemoteWebDriver::create(
            'http://localhost:9515',
            $capabilities
        );
    }
    private function addProxyAuthExtension($chromeOptions, $host, $port, $username, $password)
    {
        $manifest_json = '
    {
        "version": "1.0.0",
        "manifest_version": 2,
        "name": "Chrome Proxy",
        "permissions": [
            "proxy",
            "tabs",
            "unlimitedStorage",
            "storage",
            "<all_urls>",
            "webRequest",
            "webRequestBlocking"
        ],
        "background": {
            "scripts": ["background.js"]
        }
    }';

        $background_js = '
    chrome.proxy.settings.set(
        {value: {"mode": "fixed_servers","rules": {"singleProxy": {"scheme": "http","host": "' . $host . '","port": ' . $port . '}}}, scope: "regular"},
        function() {}
    );

    chrome.webRequest.onAuthRequired.addListener(
        function(details) {
            return {authCredentials: {username: "' . $username . '", password: "' . $password . '"}};
        },
        {urls: ["<all_urls>"]},
        ["blocking"]
    );';

        $plugin_file = storage_path('app/proxy_auth_' . uniqid() . '.zip');

        $zip = new \ZipArchive();
        $zip->open($plugin_file, \ZipArchive::CREATE);
        $zip->addFromString('manifest.json', $manifest_json);
        $zip->addFromString('background.js', $background_js);
        $zip->close();

        $chromeOptions->addExtensions([$plugin_file]);
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

    // YOUTUBE / GOOGLE LOGIN
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

    // TIKTOK LOGIN (captcha removed)
    private function tiktokLogin($account)
    {
        try {
            $this->driver->get('https://www.tiktok.com/login');
            sleep(5);

            $this->driver->findElement(WebDriverBy::xpath("//div[contains(text(),'Use phone / email / username')]"))
                ->click();

            sleep(2);

            $this->driver->findElement(WebDriverBy::xpath("//div[contains(text(),'Email / Username')]"))
                ->click();

            sleep(2);

            $this->driver->findElement(WebDriverBy::xpath("//input[@type='text']"))
                ->sendKeys($account->account_username);

            $this->driver->findElement(WebDriverBy::xpath("//input[@type='password']"))
                ->sendKeys($account->account_password);

            $this->driver->findElement(WebDriverBy::xpath("//button[contains(text(),'Log in')]"))
                ->click();

            sleep(6);

            return true;

        } catch (\Exception $e) {
            throw new \Exception("❌ TikTok Login Error: " . $e->getMessage());
        }
    }

    public function quit()
    {
        $this->driver->quit();
    }


    /*
    |--------------------------------------------------------------------------
    | INSTAGRAM POSTING
    |--------------------------------------------------------------------------
    */
    public function postToInstagram($post)
    {
        try {
            $this->driver->get("https://www.instagram.com/create/select/");

            $wait = new \Facebook\WebDriver\WebDriverWait($this->driver, 15);

            $fileInput = $wait->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('input[type="file"]')
                )
            );

            $filePath = $this->saveBase64Image($post->media_urls);

            $fileInput->setFileDetector(new LocalFileDetector());
            $fileInput->sendKeys($filePath);

            $next1 = $wait->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::xpath("//button[contains(., 'Next')]")
                )
            );
            $next1->click();

            $next2 = $wait->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::xpath("//button[contains(., 'Next')]")
                )
            );
            $next2->click();

            $shareBtn = $this->findInstagramShareButton();
            if (!$shareBtn)
                throw new Exception("Share button not found.");

            $shareBtn->click();

            sleep(4);

            return true;

        } catch (Exception $e) {
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


    /*
    |--------------------------------------------------------------------------
    | FACEBOOK POSTING
    |--------------------------------------------------------------------------
    */
    public function postToFacebook($post)
    {
        try {
            $this->driver->get("https://www.facebook.com/creatorstudio");
            sleep(6);

            $wait = new \Facebook\WebDriver\WebDriverWait($this->driver, 25);

            $createPostBtn = $wait->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::xpath("//div[contains(@aria-label,'Create') or .//span[contains(text(),'Create')]]")
                )
            );
            $createPostBtn->click();
            sleep(3);

            $fbPostBtn = $wait->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::xpath(
                        "//span[contains(text(), 'Facebook Page') or contains(text(),'Post')]"
                    )
                )
            );
            $fbPostBtn->click();
            sleep(3);

            $textBox = $wait->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::xpath("//div[@role='textbox' and @contenteditable='true']")
                )
            );

            $textBox->sendKeys($post->content . "\n\n" . $post->hashtags);

            if (!empty($post->media_urls)) {

                $filePath = $this->saveBase64Image($post->media_urls);

                $fileInput = $wait->until(
                    WebDriverExpectedCondition::presenceOfElementLocated(
                        WebDriverBy::xpath("//input[@type='file']")
                    )
                );

                $fileInput->setFileDetector(new LocalFileDetector());
                $fileInput->sendKeys($filePath);

                sleep(5);
            }

            $publishBtn = $wait->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::xpath("//span[contains(text(), 'Publish') or contains(text(),'Share')]")
                )
            );

            $publishBtn->click();
            sleep(6);

            return true;

        } catch (\Exception $e) {
            $this->driver->takeScreenshot(storage_path("app/public/facebook_error_" . time() . ".png"));
            throw new \Exception("Facebook Post Failed: " . $e->getMessage());
        }
    }


    /*
    |--------------------------------------------------------------------------
    | LINKEDIN POSTING
    |--------------------------------------------------------------------------
    */
    public function postToLinkedIn($post)
    {
        try {
            $this->driver->get('https://www.linkedin.com/login');
            sleep(4);

            $wait = new \Facebook\WebDriver\WebDriverWait($this->driver, 10);

            $emailInput = $wait->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::xpath("//input[@id='username']")
                )
            );
            $emailInput->sendKeys('your_email@example.com');

            $passwordInput = $this->driver->findElement(WebDriverBy::id('password'));
            $passwordInput->sendKeys('your_password');

            $loginBtn = $wait->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::xpath("//button[@type='submit']")
                )
            );
            $loginBtn->click();

            sleep(6);

            $this->driver->get('https://www.linkedin.com/feed/');
            sleep(5);

            $startPostBox = $wait->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::xpath("//div[@role='textbox']")
                )
            );
            $startPostBox->click();
            sleep(3);

            $editor = $wait->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::xpath("//div[@role='textbox' and @aria-multiline='true']")
                )
            );

            $editor->sendKeys($post->content . "\n\n" . $post->hashtags);

            if (!empty($post->media_urls)) {

                $filePath = $this->saveBase64Image($post->media_urls);

                $uploadBtn = $wait->until(
                    WebDriverExpectedCondition::elementToBeClickable(
                        WebDriverBy::xpath("//button[contains(@aria-label, 'Photo')]")
                    )
                );
                $uploadBtn->click();
                sleep(2);

                $input = $wait->until(
                    WebDriverExpectedCondition::presenceOfElementLocated(
                        WebDriverBy::xpath("//input[@type='file']")
                    )
                );

                $input->setFileDetector(new LocalFileDetector());
                $input->sendKeys($filePath);

                sleep(5);
            }

            $postBtn = $wait->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::xpath("//button[contains(., 'Post') and not(@disabled)]")
                )
            );

            $postBtn->click();
            sleep(5);

            return true;

        } catch (\Exception $e) {
            $this->driver->takeScreenshot(storage_path("app/public/linkedin_error_" . time() . ".png"));
            throw new \Exception("LinkedIn Failed: " . $e->getMessage());
        }
    }

}
