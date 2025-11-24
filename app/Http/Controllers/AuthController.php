<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function showLogin()
    {
        // Jika sudah login, arahkan ke halaman utama
        if (Session::has('user')) {
            return redirect()->route('schedule');
        }

        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        // Hardcoded akun
        $users = [
            'yayasan' => 'yayasanboash',
            'marketing' => 'marketingpdbi',
        ];

        $username = $credentials['username'];
        $password = $credentials['password'];

        if (! (isset($users[$username]) && $users[$username] === $password)) {
            return back()
                ->withErrors(['loginError' => 'Username atau password salah.'])
                ->withInput(['username' => $username]);
        }

        // Simpan info user di session
        $request->session()->regenerate();

        $userData = [
            'username' => $username,
            'role' => $username, // role sederhana sesuai username
        ];

        $request->session()->put('user', $userData);

        // Remember me: simpan cookie terenkripsi agar login bertahan
        if ($request->boolean('remember')) {
            Cookie::queue('remember_user', encrypt($userData), 60 * 24 * 30); // 30 hari
        } else {
            Cookie::queue(Cookie::forget('remember_user'));
        }

        return redirect()->intended(route('schedule'));
    }

    public function logout(Request $request)
    {
        Cookie::queue(Cookie::forget('remember_user'));
        $request->session()->forget('user');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
