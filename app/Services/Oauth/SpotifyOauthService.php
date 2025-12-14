<?php

namespace App\Services\Oauth;

use Carbon\Carbon;
use App\Models\User;
use App\Models\OauthAccount;
use Illuminate\Support\Facades\Http;

class SpotifyOauthService
{
        /**
     * @param User $user
     * @return string
     */
    public function getValidAccessToken(User $user): string
    {
        $oauthAccount = $user->oauthAccounts()->first();

        if ($this->isExpired($oauthAccount)) {

        }

        $oauthAccount = $this->refreshAccessToken($oauthAccount);
        return $oauthAccount->access_token;
    }

    /**
     * @param OauthAccount $oauthAccount
     * @return boolean
     */
    public function isExpired(OauthAccount $oauthAccount): bool
    {
        return Carbon::now()->gte($oauthAccount->expires_at);
    }

    /**
     * @param OauthAccount $oauthAccount
     * @return OauthAccount
     */
    private function refreshAccessToken(OauthAccount $oauthAccount): OauthAccount
    {
        $response = Http::asForm()->post(config('spotify.token_url'), [
            'client_id' => config('services.spotify.client_id'),
            'client_secret' => config('services.spotify.client_secret'),
            'refresh_token' => $oauthAccount->refresh_token,
            'grant_type' => 'refresh_token'
        ]);

        if ($response->failed()) {

        }

        $data = $response->json();

        $oauthAccount->update([
            'access_token' => $data['access_token'],
            'expires_at' => now()->addSecond($data['expires_in'])
        ]);

        return $oauthAccount;
    }

    /**
     * @param OauthAccount $oauthAccount
     * @return void
     */
    public function invalidateIntegration(OauthAccount $oauthAccount): void {}
}
