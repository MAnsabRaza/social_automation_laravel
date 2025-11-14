<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth/login');
    }
    public function showSignUp(){
        return view('auth/signup');
    }
    public function create(Request $request)
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
                return response()->json(['success'=>true,'message'=>'User updated successfully']);
            }else{
                return response()->json(['success'=>false,'message'=>'User not found']);
            }
        }
        $user=new User();
        $user->name=$data['name'];
        $user->email=$data['email'];
        $user->password=bcrypt($data['password']);
        $user->save();
       return redirect()->route('showLogin')->with('success', 'Account created successfully! Please login.');
    }
   public function checkLogin(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $user = User::where('email', $credentials['email'])->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            Auth::login($user, true);

        session([
            'user_name'=>$user->name,
            'email'=>$user->email
        ]);

            return response()->json([
                'success' => true,
                'message' => 'Login Successful',
                'redirect' => route('home')
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }
    }
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->flush();
        return redirect()->route('showLogin');
    }

       
    public function delete($id){
        $user=User::find($id);
        if($user){
            $user->delete();
            return response()->json(['success'=>true,'message'=>'User deleted successfully']);
        }
        else{
            return response()->json(['success'=>false,'message'=>'User not found']);
        }
    }
    public function fetch($id){
        $user=User::find($id);
        if($user){
            return response()->json(['success'=>true,'data'=>$user]);
        }
        return response()->json(['success'=>false,'message'=>'User not found']);
    }
}
