<?php


use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProxyController;
use App\Http\Controllers\SocialAccountsController;
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
    //Social Account
    Route::get('/socialAccount', [SocialAccountsController::class, 'index'])->name('socialAccount');
    Route::post('/createSocialAccounts', [SocialAccountsController::class, 'createSocialAccounts'])->name('createSocialAccounts');
    Route::get('/getSocialAccountData', [SocialAccountsController::class, 'getSocialAccountData'])->name('getSocialAccountData');
    Route::delete('/deleteSocialAccount/{id}', [SocialAccountsController::class, 'deleteSocialAccount'])->name('deleteSocialAccount');
    Route::get('/fetchSocialAccountData/{id}', [SocialAccountsController::class,'fetchSocialAccountData'])->name('fetchSocialAccountData');
Route::post('/importCSV', [SocialAccountsController::class, 'importCSV'])
    ->name('importCSV');
    //Proxy
    Route::get('/proxy', [ProxyController::class, 'index'])->name('proxy');
    Route::post('/createProxy', [ProxyController::class, 'createProxy'])->name(name: 'createProxy');
    Route::get('/getProxyData', [ProxyController::class, 'getProxyData'])->name('getProxyData');
    Route::delete('/deleteProxy/{id}', [ProxyController::class, 'deleteProxy'])->name('deleteProxy');
    Route::get('/fetchProxyData/{id}', [ProxyController::class, 'fetchProxyData'])->name('fetchProxyData');

    //proflie
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('/saveUser', [ProfileController::class, 'saveUser'])->name('saveUser');
    //logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
