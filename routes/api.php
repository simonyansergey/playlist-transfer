<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Transfers\PlaylistTransferController;
use App\Http\Controllers\Auth\Integrations\SpotifyAuthController;
use App\Http\Controllers\Auth\Integrations\YoutubeAuthController;
use App\Models\OauthAccount;
use App\Models\User;

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

        Route::controller(PlaylistTransferController::class)
            ->prefix('/transfers')
            ->group(static function (): void {
                Route::post('/youtube-to-spotify', 'store');
            });

        // Route::get('/{user}/{playlistId}/test', function (User $user, string $playlistId) {
        //     $accessToken = $user->oauthAccounts()
        //         ->where('provider', 'google')
        //         ->first()
        //         ->access_token;

        //     $response = Http::withToken($accessToken)
        //         ->get(config('youtube.api_base') . '/playlists' . '?' . http_build_query([
        //             'id' => $playlistId,
        //             'part' => 'id,contentDetails,localizations,player,snippet,status'
        //         ]));
        //     dd($response->json('items'));
        // });

        // Route::post('/{oauthAccount}/extend', function (OauthAccount $oauthAccount) {
        //     $response = Http::asForm()->post(config('youtube.token_url'), [
        //         'client_id' => config('services.google.client_id'),
        //         'client_secret' => config('services.google.client_secret'),
        //         'refresh_token' => $oauthAccount->refresh_token,
        //         'grant_type' => 'refresh_token'
        //     ]);

        //     if ($response->failed()) {

        //     }

        //     $data = $response->json();

        //     $oauthAccount->update([
        //         'access_token' => $data['access_token'],
        //         'expires_at' => now()->addSecond($data['expires_in'])
        //     ]);
        // });
    });
