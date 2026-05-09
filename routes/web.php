<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReleaseController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TrackHeartController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/releases/{slug}', ReleaseController::class)->name('releases.show');
Route::post('/tracks/{track}/heart', TrackHeartController::class)->name('tracks.heart');

Route::get('/subscribe/verify/{token}', [SubscriptionController::class, 'showVerify'])->name('subscribe.verify');
Route::post('/subscribe/verify/{token}', [SubscriptionController::class, 'verify'])->name('subscribe.verify.confirm');
Route::get('/subscribe/unsubscribe/{token}', [SubscriptionController::class, 'showUnsubscribe'])->name('subscribe.unsubscribe');
Route::post('/subscribe/unsubscribe/{token}', [SubscriptionController::class, 'unsubscribe'])->name('subscribe.unsubscribe.confirm');
