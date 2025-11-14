<?php


use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
//Login
Route::get('/',[AuthController::class,'showLogin'])->name('showLogin');
//SignUp
Route::get('/signup',[AuthController::class,'showSignUp'])->name('showSignUp');
//Create User
Route::post('/createUser',[AuthController::class,'create'])->name('createUser');
//Delete User
Route::delete('/delete/{id}',[AuthController::class,'delete']);
//Fetch User
Route::get('/fetch/{id}',[AuthController::class,'fetch']);

//check login
Route::post('/checkLogin',[AuthController::class,'checkLogin'])->name('checkLogin');
//Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
//Home
Route::middleware('auth')->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
});
