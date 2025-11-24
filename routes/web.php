<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\AuthController;

// Login routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');

// Protected Routes (hanya yang sudah login)
Route::middleware('auth')->group(function () {

    Route::view('/', 'schedule-list')->name('schedule-list');
    
    Route::get('/schools', [SchoolController::class, 'index'])->name('schools.index');
    Route::post('/schools', [SchoolController::class, 'store'])->name('schools.store');
    Route::get('/schools/{school}', [SchoolController::class, 'show'])->name('schools.show');
    Route::delete('/schools/{school}', [SchoolController::class, 'destroy'])->name('schools.destroy');

    Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule');
    Route::post('/schedule', [ScheduleController::class, 'store'])->name('schedule.store');
    Route::patch('/schedule/{schedule}', [ScheduleController::class, 'update'])->name('schedule.update');
    Route::delete('/schedule/{schedule}', [ScheduleController::class, 'destroy'])->name('schedule.destroy');
    Route::get('/jadwal', [ScheduleController::class, 'list'])->name('schedule.list');
    Route::get('/pic', [ScheduleController::class, 'pic'])->name('schedule.pic');
    Route::patch('/schedule/{schedule}/complete', [ScheduleController::class, 'complete'])->name('schedule.complete');

    Route::view('/settings', 'settings')->name('settings');

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
