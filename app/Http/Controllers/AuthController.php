<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {

            if(Auth::user()->on_boarding_required){
                return redirect()->route('on-boarding');
            }

            return redirect()->route('welcome');
        }

        return back()->withErrors(['email' => 'Invalid login details']);
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('welcome');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed'
        ]);

        DB::beginTransaction();

        try {

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password)
            ]);

            DB::commit();

            Auth::login($user);

            if ($user->on_boarding_required) {
                return redirect()->route('on-boarding');
            }

            return redirect()->route('dashboard');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->withErrors(['error' => 'Something went wrong. Please try again.']);
        }
    }
}
