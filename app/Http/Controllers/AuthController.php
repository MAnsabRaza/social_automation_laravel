<?php

namespace App\Http\Controllers;
use App\Models\Role;
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
    public function showSignUp()
    {
        return view('auth/signup');
    }

    public function create(Request $request)
    {
        $data = $request->all();

        // Check if email already exists
        if (User::where('email', $data['email'])->exists()) {
            return redirect()->back()->with('error', 'Email already exists! Please use a different email.');
        }

        // Find or create 'user' role
        $role = Role::firstOrCreate(
            ['name' => 'user'], // Check if role with name 'user' exists
            ['current_date' => now()] // If not, create it with current date
        );

        // Update existing user (if ID exists)
        if (isset($data['id'])) {
            $user = User::find($data['id']);
            if ($user) {
                $user->name = $data['name'];
                $user->email = $data['email'];
                if (!empty($data['password'])) {
                    $user->password = bcrypt($data['password']);
                }
                $user->role_id = $role->id; // Assign role
                $user->save();
                return response()->json(['success' => true, 'message' => 'User updated successfully']);
            } else {
                return response()->json(['success' => false, 'message' => 'User not found']);
            }
        }

        // Create new user
        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = bcrypt($data['password']);
        $user->role_id = $role->id; // Assign 'user' role
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
                'user_name' => $user->name,
                'email' => $user->email,
               'role_name' => $user->role ? $user->role->name : null
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
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('showLogin');
    }


    public function delete($id)
    {
        $user = User::find($id);
        if ($user) {
            $user->delete();
            return response()->json(['success' => true, 'message' => 'User deleted successfully']);
        } else {
            return response()->json(['success' => false, 'message' => 'User not found']);
        }
    }
    public function fetch($id)
    {
        $user = User::find($id);
        if ($user) {
            return response()->json(['success' => true, 'data' => $user]);
        }
        return response()->json(['success' => false, 'message' => 'User not found']);
    }
}
