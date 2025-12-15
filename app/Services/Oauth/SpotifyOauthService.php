<?php

namespace App\Services\Oauth;

use App\Models\OauthAccount;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Http;

class SpotifyOauthService
{
    public function getValidAccessToken(User $user): string
    {
        $oauthAccount = $user->oauthAccounts()
            ->where('provider', 'spotify')
            ->first();

        if ($this->isExpired($oauthAccount)) {
            $oauthAccount = $this->refreshAccessToken($oauthAccount);
        }

        return $oauthAccount->access_token;
    }

    public function isExpired(OauthAccount $oauthAccount): bool
    {
        return Carbon::now()->gte($oauthAccount->expires_at);
    }

    private function refreshAccessToken(OauthAccount $oauthAccount): OauthAccount
    {
        $response = Http::asForm()->post(config('spotify.token_url'), [
            'client_id' => config('services.spotify.client_id'),
            'client_secret' => config('services.spotify.client_secret'),
            'refresh_token' => $oauthAccount->refresh_token,
            'grant_type' => 'refresh_token',
        ]);

        if ($response->failed()) {
            throw new Exception('Failed to refresh access token');
        }

        $data = $response->json();

        $oauthAccount->update([
            'access_token' => $data['access_token'],
            'expires_at' => now()->addSeconds($data['expires_in']),
        ]);

        return $oauthAccount;
    }

    public function invalidateIntegration(OauthAccount $oauthAccount): void {}
}
