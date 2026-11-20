<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\TrackSessionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/sessions', [TrackSessionController::class, 'index'])->name('sessions.index');
Route::get('/sessions/{trackSession}', [TrackSessionController::class, 'show'])->name('sessions.show');
