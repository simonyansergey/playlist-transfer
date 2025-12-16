<?php

namespace App\Services\Oauth;

use App\Models\OauthAccount;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Exception;

class GoogleOauthService
{
    public function getValidAccessToken(User $user): string
    {
        $oauthAccount = $user->oauthAccounts()->first();

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
        $response = Http::asForm()->post(config('youtube.token_url'), [
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'refresh_token' => $oauthAccount->refresh_token,
            'grant_type' => 'refresh_token',
        ]);

        if ($response->failed()) {
            throw new Exception('Response failed with the following error message');
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
