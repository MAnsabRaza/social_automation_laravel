<?php

use App\Http\Controllers\SocialAccountsController;

Route::post('/save-session', [SocialAccountsController::class, 'saveSession']);
Route::get('/get-session/{account_id}', [SocialAccountsController::class, 'getSession']);