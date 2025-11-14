<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AccountGroupController extends Controller
{
    public function index()
    {
        $data['modules'] = ['setup/add-account-management.js'];
        return view('account-management/account-management', $data);
    }
}
