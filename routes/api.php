<?php

use App\Models\OauthAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\Integrations\SpotifyAuthController;
use App\Http\Controllers\Auth\Integrations\YoutubeAuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('/v1')
    ->group(static function (): void {
        Route::controller(YoutubeAuthController::class)
            ->prefix('/youtube')
            ->group(static function (): void {
                Route::get('/redirect', 'redirect');
                Route::get('/callback', 'callback');
            });

        Route::controller(SpotifyAuthController::class)
            ->prefix('/spotify')
            ->group(static function (): void {
                Route::get('/redirect', 'redirect');
                Route::get('/callback', 'callback');
            });
    });
