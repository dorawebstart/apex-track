<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CabinetController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TrackSessionController;
use Illuminate\Support\Facades\Route;

// Публічні сторінки (гість)
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/sessions', [TrackSessionController::class, 'index'])->name('sessions.index');
Route::get('/sessions/suggest', [TrackSessionController::class, 'suggest'])->name('sessions.suggest');
Route::get('/sessions/{trackSession}', [TrackSessionController::class, 'show'])->name('sessions.show');

// Реєстрація та вхід — лише для неавторизованих
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
});

// Авторизований користувач
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/cabinet', [CabinetController::class, 'index'])->name('cabinet');

    Route::get('/sessions/{trackSession}/book', [BookingController::class, 'start'])->name('bookings.start');
    Route::post('/sessions/{trackSession}/book', [BookingController::class, 'store'])->name('bookings.store');
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');
});
