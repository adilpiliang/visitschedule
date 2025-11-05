<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SchoolController;

Route::view('/', 'welcome')->name('dashboard');
Route::get('/schools', [SchoolController::class, 'index'])->name('schools.index');
Route::get('/schools/{school}', [SchoolController::class, 'show'])->name('schools.show');
Route::view('/login', 'login')->name('login');
Route::view('/schedule', 'schedule')->name('schedule');
Route::view('/settings', 'settings')->name('settings');



Route::post('/login', function () {
    return back()->with('status', 'Autentikasi belum diimplementasikan.');
})->name('login.attempt');
