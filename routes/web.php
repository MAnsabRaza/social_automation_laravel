<?php


use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
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
    Route::get('/social-account', [SocialAccountsController::class, 'index'])->name('social-account');

    //Proxy
    Route::get('/proxy', [ProxyController::class, 'index'])->name('proxy');
    Route::post('/createProxy', [ProxyController::class, 'createProxy'])->name(name: 'createProxy');
    Route::get('/getProxyData', [ProxyController::class, 'getProxyData'])->name('getProxyData');
    Route::delete('/deleteProxy/{id}', [ProxyController::class, 'deleteProxy'])->name('deleteProxy');
    Route::get('/fetchProxyData/{id}', [ProxyController::class, 'fetchProxyData'])->name('fetchProxyData');
    //logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
