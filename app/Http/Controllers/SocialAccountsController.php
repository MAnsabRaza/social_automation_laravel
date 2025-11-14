<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SocialAccountsController extends Controller
{
    public function index(){
         $data['modules'] = ['setup/add-social-account.js'];
        return view('social-account/social-account',$data);
    }
}
