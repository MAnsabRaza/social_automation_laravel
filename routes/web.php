<?php


use App\Http\Controllers\AuthController;
use App\Http\Controllers\AutomationController;
use App\Http\Controllers\CaptchaSettingsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostContentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProxyController;
use App\Http\Controllers\SocialAccountsController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;
//Login
Route::get('/', [AuthController::class, 'showLogin'])->name('showLogin');
//SignUp
Route::get('/signup', [AuthController::class, 'showSignUp'])->name('showSignUp');
//Create User
Route::post('/createUser', [AuthController::class, 'create'])->name('createUser');
//Delete User
Route::delete('/delete/{id}', [AuthController::class, 'delete']);
//Fetch User
Route::get('/fetch/{id}', [AuthController::class, 'fetch']);

//check login
Route::post('/checkLogin', [AuthController::class, 'checkLogin'])->name('checkLogin');

//Home
Route::middleware('auth')->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
     Route::get('/getAccountsByPlatform', [HomeController::class, 'getAccountsByPlatform'])->name('getAccountsByPlatform');
    Route::get('/getTasksByStatus', [HomeController::class, 'getTasksByStatus'])->name('getTasksByStatus');
    
    //Social Account
    Route::get('/socialAccount', [SocialAccountsController::class, 'index'])->name('socialAccount');
    Route::post('/createSocialAccounts', [SocialAccountsController::class, 'createSocialAccounts'])->name('createSocialAccounts');
    Route::get('/getSocialAccountData', [SocialAccountsController::class, 'getSocialAccountData'])->name('getSocialAccountData');
    Route::delete('/deleteSocialAccount/{id}', [SocialAccountsController::class, 'deleteSocialAccount'])->name('deleteSocialAccount');
    Route::get('/fetchSocialAccountData/{id}', [SocialAccountsController::class, 'fetchSocialAccountData'])->name('fetchSocialAccountData');
    Route::post('/importCSV', [SocialAccountsController::class, 'importCSV'])
        ->name('importCSV');

    Route::post('/startAccount/{id}', [SocialAccountsController::class, 'startAccount'])->name('startAccount');
    Route::get('/runAccount/{id}', [SocialAccountsController::class, 'runAccount'])->name('runAccount');
    Route::post('/stopAccount/{id}', [SocialAccountsController::class, 'stopAccount'])->name('stopAccount');
     Route::get('/checkAccountStatus/{id}', [SocialAccountsController::class, 'checkAccountStatus'])
        ->name('checkAccountStatus');
    //stopAccount
    Route::get('/proxy', [ProxyController::class, 'index'])->name('proxy');
    Route::post('/createProxy', [ProxyController::class, 'createProxy'])->name(name: 'createProxy');
    Route::get('/getProxyData', [ProxyController::class, 'getProxyData'])->name('getProxyData');
    Route::delete('/deleteProxy/{id}', [ProxyController::class, 'deleteProxy'])->name('deleteProxy');
    Route::get('/fetchProxyData/{id}', [ProxyController::class, 'fetchProxyData'])->name('fetchProxyData');
    //Captcha Setting
    Route::get('/captchaSetting', [CaptchaSettingsController::class, 'index'])->name('captchaSetting');
    Route::post('/createCaptcha', [CaptchaSettingsController::class, 'createCaptcha'])->name('createCaptcha');
    Route::get('/getCaptchaSettingData', [CaptchaSettingsController::class, 'getCaptchaSettingData'])->name('getCaptchaSettingData');
    Route::delete('/deleteCaptchaSettingData/{id}', [CaptchaSettingsController::class, 'deleteCaptchaSettingData'])->name('deleteCaptchaSettingData');
    Route::get('/fetchCaptchaSettingData/{id}', [CaptchaSettingsController::class, 'fetchCaptchaSettingData'])->name('fetchCaptchaSettingData');
    //Task
    Route::get('/task', [TaskController::class, 'index'])->name('task');
    Route::post('/createTask', [TaskController::class, 'createTask'])->name('createTask');
    Route::get('/fetchTaskData/{id}', [TaskController::class, 'fetchTaskData'])->name('fetchTaskData');
    Route::get('/getTaskData', [TaskController::class, 'getTaskData'])->name('getTaskData');
    Route::delete('/deleteTask/{id}', [TaskController::class, 'deleteTask'])->name('deleteTask');

    //post-content
    Route::get('/post-content', [PostContentController::class, 'index'])->name('post-content');
    Route::post('/createPostContent', [PostContentController::class, 'createPostContent'])->name('createPostContent');
    Route::get('/getPostContentData', [PostContentController::class, 'getPostContentData'])->name('getPostContentData');
    Route::get('/fetchPostContentData/{id}', [PostContentController::class, 'fetchPostContentData'])->name('fetchPostContentData');
    Route::delete('/deletePostContentData/{id}', [PostContentController::class, 'deletePostContentData'])->name('deletePostContentData');

    //Automation COntroller
    Route::get('/auto-login', [AutomationController::class, 'autoLogin'])->name('auto-login');
    //proflie
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('/saveUser', [ProfileController::class, 'saveUser'])->name('saveUser');
    //logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});


