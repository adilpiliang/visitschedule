<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('dashboard');
Route::view('/school', 'school')->name('school');
Route::view('/login', 'login')->name('login');
Route::view('/schedule', 'schedule')->name('schedule');
Route::view('/settings', 'settings')->name('settings');



Route::post('/login', function () {
    return back()->with('status', 'Autentikasi belum diimplementasikan.');
})->name('login.attempt');
