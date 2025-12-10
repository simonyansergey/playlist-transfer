<?php

use App\Http\Controllers\Auth\Integrations\SpotifyAuthController;
use App\Http\Controllers\Auth\Integrations\YoutubeAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::controller(YoutubeAuthController::class)
    ->prefix('/youtube')
    ->middleware(['auth:sanctum'])
    ->group(static function (): void {
        Route::get('/redirect', 'redirect');
        Route::post('/callback', 'callback');
    });

Route::controller(SpotifyAuthController::class)
    ->prefix('/spotify')
    ->middleware(['auth:sanctum'])
    ->group(static function (): void {
        Route::get('/redirect', 'redirect');
        Route::post('/callback', 'callback');
    });
