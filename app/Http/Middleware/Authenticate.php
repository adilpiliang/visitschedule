<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class Authenticate
{
    /**
        * Pastikan user sudah login sebelum melanjutkan.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('user')) {
            // Pulihkan dari cookie remember jika ada
            $remember = Cookie::get('remember_user');
            if ($remember) {
                try {
                    $userData = decrypt($remember);
                    if (is_array($userData) && isset($userData['username'])) {
                        Session::put('user', $userData);
                    }
                } catch (\Throwable $e) {
                    Cookie::queue(Cookie::forget('remember_user'));
                }
            }
        }

        if (! $request->session()->has('user')) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
