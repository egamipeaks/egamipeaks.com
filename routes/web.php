<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReleaseController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TrackHeartController;
use App\Http\Controllers\TrackPlayController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/releases/{slug}', ReleaseController::class)->name('releases.show');
Route::post('/tracks/{track}/heart', TrackHeartController::class)->name('tracks.heart');
Route::post('/tracks/{track}/play', TrackPlayController::class)->name('tracks.play');

Route::get('/subscribe/verify/{token}', [SubscriptionController::class, 'showVerify'])->name('subscribe.verify');
Route::post('/subscribe/verify/{token}', [SubscriptionController::class, 'verify'])->name('subscribe.verify.confirm');
Route::get('/subscribe/unsubscribe/{token}', [SubscriptionController::class, 'showUnsubscribe'])->name('subscribe.unsubscribe');
Route::post('/subscribe/unsubscribe/{token}', [SubscriptionController::class, 'unsubscribe'])->name('subscribe.unsubscribe.confirm');
