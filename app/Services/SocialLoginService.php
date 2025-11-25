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

    //post create Instagram
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

    // POST TO LINKEDIN (2025 Updated Working Code)
    public function postToLinkedIn($post)
    {
        try {
            // === STEP 0: LOGIN (ZAROORI - warna feed pe elements nahi) ===
            $this->driver->get('https://www.linkedin.com/login');
            sleep(rand(3, 5)); // Random for human-like

            $wait = new \Facebook\WebDriver\WebDriverWait($this->driver, 10); // Reduced timeout

            // Email
            $emailInput = $wait->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::xpath("//input[@id='username' or @name='session_key']")
                )
            );
            $emailInput->sendKeys('your_email@example.com'); // Apna email daalo

            // Password
            $passwordInput = $this->driver->findElement(
                WebDriverBy::xpath("//input[@id='password' or @name='session_password']")
            );
            $passwordInput->sendKeys('your_password'); // Apna password daalo

            // Login button
            $loginBtn = $wait->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::xpath("//button[@type='submit' and contains(., 'Sign in')]")
                )
            );
            $loginBtn->click();
            sleep(rand(5, 8)); // Wait for redirect (manual CAPTCHA if needed)

            // === STEP 1: FEED ===
            $this->driver->get('https://www.linkedin.com/feed/');
            sleep(rand(4, 6));

            // === STEP 2: START POST - Direct Textbox (2025 UI: No button, textbox triggers modal) ===
            $startSelectors = [
                "//div[@contenteditable='true' and contains(@data-placeholder, 'What do you want to talk about?')]",
                "//div[@role='textbox' and contains(@class, 'share-box')]",
                "//div[contains(@class, 'ql-editor') and contains(@data-placeholder, 'Start a post')]",
                "//div[contains(., 'Start a post') and @contenteditable='true']"
            ];

            $startPostBox = null;
            foreach ($startSelectors as $selector) {
                try {
                    $startPostBox = $wait->until(
                        WebDriverExpectedCondition::elementToBeClickable(
                            WebDriverBy::xpath($selector)
                        )
                    );
                    if ($startPostBox)
                        break;
                } catch (\Exception $e) {
                    // Try next
                }
            }

            if (!$startPostBox) {
                $this->driver->takeScreenshot(storage_path("app/public/linkedin_error_start_" . time() . ".png"));
                throw new \Exception("Start Post textbox not found. Check login/screenshot.");
            }

            $startPostBox->click();
            sleep(rand(2, 4));

            // === STEP 3: EDITOR ===
            $editorSelectors = [
                "//div[@contenteditable='true' and contains(@class, 'ql-editor')]",
                "//div[@role='textbox' and @aria-multiline='true']",
                "//div[contains(@class, 'mentions-texteditor__contenteditable')]",
                "//div[@data-testid='artdeco-editor']"
            ];

            $editor = null;
            foreach ($editorSelectors as $selector) {
                try {
                    $editor = $wait->until(
                        WebDriverExpectedCondition::presenceOfElementLocated(
                            WebDriverBy::xpath($selector)
                        )
                    );
                    if ($editor)
                        break;
                } catch (\Exception $e) {
                    // Next
                }
            }

            if (!$editor) {
                $this->driver->takeScreenshot(storage_path("app/public/linkedin_error_editor_" . time() . ".png"));
                throw new \Exception("Editor not found.");
            }

            $editor->clear();
            $contentText = $post->content . "\n\n" . $post->hashtags;
            $editor->sendKeys($contentText);
            sleep(rand(1, 2));

            // === STEP 4: IMAGE (Optional) ===
            if (!empty($post->media_urls)) {
                $filePath = $this->saveBase64Image($post->media_urls);
                if (file_exists($filePath)) {
                    $uploadSelectors = [
                        "//button[contains(@aria-label, 'Add photo') or contains(@aria-label, 'Add image')]",
                        "//button[.//span[contains(text(), 'Photo')]]",
                        "//button[@data-testid='upload-media-button']"
                    ];

                    $uploadBtn = null;
                    foreach ($uploadSelectors as $selector) {
                        try {
                            $uploadBtn = $wait->until(
                                WebDriverExpectedCondition::elementToBeClickable(
                                    WebDriverBy::xpath($selector)
                                )
                            );
                            if ($uploadBtn)
                                break;
                        } catch (\Exception $e) {
                            // Next
                        }
                    }

                    if ($uploadBtn) {
                        $uploadBtn->click();
                        sleep(rand(2, 3));
                    }

                    $input = $wait->until(
                        WebDriverExpectedCondition::presenceOfElementLocated(
                            WebDriverBy::xpath("//input[@type='file' and @accept='image/*']")
                        )
                    );
                    $input->setFileDetector(new LocalFileDetector());
                    $input->sendKeys($filePath);
                    sleep(rand(4, 6));
                }
            }

            // === STEP 5: POST BUTTON ===
            $postSelectors = [
                "//button[normalize-space(.)='Post' and not(@disabled)]",
                "//button[contains(@aria-label, 'Post') and @data-testid='share-submit-button']"
            ];

            $postBtn = null;
            foreach ($postSelectors as $selector) {
                try {
                    $postBtn = $wait->until(
                        WebDriverExpectedCondition::elementToBeClickable(
                            WebDriverBy::xpath($selector)
                        )
                    );
                    if ($postBtn)
                        break;
                } catch (\Exception $e) {
                    // Next
                }
            }

            if (!$postBtn) {
                $this->driver->takeScreenshot(storage_path("app/public/linkedin_error_post_" . time() . ".png"));
                throw new \Exception("Post button not found.");
            }

            $postBtn->click();
            sleep(rand(5, 7));

            return true;

        } catch (\Exception $e) {
            $this->driver->takeScreenshot(storage_path("app/public/linkedin_error_full_" . time() . ".png"));
            throw new \Exception("LinkedIn Failed: " . $e->getMessage() . " - Check screenshot.");
        }
    }
    //Post in facebook
    public function postToFacebook($post)
{
    try {

        $this->driver->get("https://www.facebook.com/creatorstudio");
        sleep(6);

        $wait = new \Facebook\WebDriver\WebDriverWait($this->driver, 20);

        // 1️⃣ CLICK CREATE POST BUTTON
        $createPostBtn = $wait->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::xpath("//span[contains(text(), 'Create Post') or contains(text(),'Create new')]")
            )
        );
        $createPostBtn->click();
        sleep(3);

        // 2️⃣ SELECT "Facebook Page Post"
        $fbPostBtn = $wait->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::xpath("//span[contains(text(), 'Facebook Page') or contains(text(), 'Post')]")
            )
        );
        $fbPostBtn->click();
        sleep(3);

        // 3️⃣ FILL TEXT CONTENT
        $textBox = $wait->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::xpath("//div[@contenteditable='true']")
            )
        );

        $textBox->sendKeys($post->content . "\n\n" . $post->hashtags);
        sleep(2);

        // 4️⃣ UPLOAD IMAGE (if available)
        if (!empty($post->media_urls)) {

            $filePath = $this->saveBase64Image($post->media_urls);

            $fileInput = $wait->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::xpath("//input[@type='file']")
                )
            );

            $fileInput->setFileDetector(new LocalFileDetector());
            $fileInput->sendKeys($filePath);

            sleep(5); // wait until image preview loads
        }

        // 5️⃣ CLICK PUBLISH BUTTON
        $publishBtn = $wait->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::xpath("//span[contains(text(), 'Publish')]")
            )
        );

        $publishBtn->click();

        sleep(6); // wait to finish

        return true;

    } catch (\Exception $e) {

        $this->driver->takeScreenshot(storage_path("app/public/facebook_error_" . time() . ".png"));
        throw new \Exception("Facebook Post Failed: " . $e->getMessage());
    }
}


}