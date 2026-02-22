<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReleaseController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/releases/{slug}', ReleaseController::class)->name('releases.show');
