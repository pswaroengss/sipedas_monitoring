<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if ($validator->fails()) {
            return redirect('/login')
                ->withErrors($validator)
                ->withInput();
        }

        $user = DB::table('users')
            ->where('email', $request->input('email'))
            ->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return redirect('/login')
                ->withErrors(['email' => 'Invalid credentials'])
                ->withInput();
        }

        Auth::loginUsingId($user->id);

        session(['auth_user' => [
            'id' => $user->id,
            'name' => $user->name ?? $user->email,
            'email' => $user->email,
        ]]);

        return redirect('/dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('auth_user');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        Auth::logout();

        return redirect('/login');
    }
}
