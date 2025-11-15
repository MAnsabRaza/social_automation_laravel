<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index(){
         $user = Auth::user();
        return view('setting/profile/profile',compact('user'));
    }
     public function saveUser(Request $request)
    {
        $data = $request->all();
        if(User::where('email', $data['email'])->exists()){
            return redirect()->back()->with('error', 'Email already exists! Please use a different email.');
        }
        //login for existing user
        if(isset($data['id'])){
            $user=User::find($data['id']);
            if($user){
                $user->name=$data['name'];
                $user->email=$data['email'];
                if(!empty($data['password'])){
                    $user->password=bcrypt($data['password']);
                }
                $user->save();
                return redirect()->route('profile')->with('success', 'User Update successfully! Please login.');
            }else{
                return redirect()->route('profile')->with(['success'=>false,'message'=>'User not found']);
            }
        }
        $user=new User();
        $user->name=$data['name'];
        $user->email=$data['email'];
        $user->password=bcrypt($data['password']);
        $user->save();
       return redirect()->route('profile')->with('success', 'User created successfully! Please login.');
    }
}
