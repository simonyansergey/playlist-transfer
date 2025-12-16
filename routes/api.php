<?php

use App\Http\Controllers\Auth\Integrations\SpotifyAuthController;
use App\Http\Controllers\Auth\Integrations\YoutubeAuthController;
use App\Http\Controllers\Transfers\PlaylistTransferController;
use App\Models\OauthAccount;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

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
            ->middleware(['auth:sanctum'])
            ->group(static function (): void {
                Route::get('/redirect', 'redirect');
                Route::get('/callback', 'callback');
            });

        Route::controller(PlaylistTransferController::class)
            ->prefix('/transfers')
            ->middleware(['auth:sanctum'])
            ->group(static function (): void {
                Route::post('/youtube-to-spotify', 'store');
                Route::get('/{transfer}', 'show');
                Route::post('/{transfer}/execute', 'execute');
            });

        // Route::post('/{user}/{playlistId}/test', function (User $user, string $playlistId) {
        //     $accessToken = $user->oauthAccounts()
        //         ->where('provider', 'spotify')
        //         ->value('access_token');
    
        //     $response = Http::withToken($accessToken)
        //         ->post(config('spotify.api_base') . '/playlists/' . $playlistId . '/tracks', [
        //             'uris' => ['spotify:track:4nClbtvjF76kDFFecauY1J']
        //         ]);
    
        //     $data = $response->json();
    
        //     dd($data['snapshot_id']);
    
        //     return response()->json(
        //         ['id' => $data['tracks']['items'][0]['album']['id']]
        //     );
        // });
    
        // Route::post('/{oauthAccount}/extend', function (OauthAccount $oauthAccount) {
        //     $response = Http::asForm()->post(config('spotify.token_url'), [
        //         'client_id' => config('services.spotify.client_id'),
        //         'client_secret' => config('services.spotify.client_secret'),
        //         'refresh_token' => $oauthAccount->refresh_token,
        //         'grant_type' => 'refresh_token'
        //     ]);
    
        //     if ($response->failed()) {
        //         throw new Exception('Failed to refresh access token');
        //     }
    
        //     $data = $response->json();
    
        //     $oauthAccount->update([
        //         'access_token' => $data['access_token'],
        //         'expires_at' => now()->addSeconds($data['expires_in'])
        //     ]);
        // });
    });
