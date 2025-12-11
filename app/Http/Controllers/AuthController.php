<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $data = $request->only(['email', 'password']);

        if (Auth::attempt($data)) {
            $request->session()->regenerate();

            if (Auth::user()->role == 'SuperAdmin') {
                return redirect()->route('super.dashboard.index');
            }

            if (Auth::user()->role == 'Admin') {
                return redirect()->route('admin.dashboard.index');
            }

            if (Auth::user()->role == 'Member') {
                return redirect()->route('member.dashboard.index');
            }

        } else {
            return redirect()->route('home')->with('error', 'Invalid credentials');
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();

        return redirect()->route('home');
    }
}
