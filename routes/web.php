<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\ScheduleController;

Route::view('/', 'welcome')->name('dashboard');
Route::get('/schools', [SchoolController::class, 'index'])->name('schools.index');
Route::post('/schools', [SchoolController::class, 'store'])->name('schools.store');
Route::get('/schools/{school}', [SchoolController::class, 'show'])->name('schools.show');
Route::view('/login', 'login')->name('login');
Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule');
Route::post('/schedule', [ScheduleController::class, 'store'])->name('schedule.store');
Route::delete('/schedule/{schedule}', [ScheduleController::class, 'destroy'])->name('schedule.destroy');
Route::get('/jadwal', [ScheduleController::class, 'list'])->name('schedule.list');
Route::get('/pic', [ScheduleController::class, 'pic'])->name('schedule.pic');
Route::patch('/schedule/{schedule}/complete', [ScheduleController::class, 'complete'])->name('schedule.complete');
Route::view('/settings', 'settings')->name('settings');



Route::post('/login', function () {
    return back()->with('status', 'Autentikasi belum diimplementasikan.');
})->name('login.attempt');
