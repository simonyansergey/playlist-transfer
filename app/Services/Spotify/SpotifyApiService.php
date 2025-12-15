<?php

namespace App\Services\Spotify;

use App\Services\Oauth\SpotifyOauthService;

class SpotifyApiService
{
    public function __construct(
        private readonly SpotifyOauthService $spotifyOauthService
    ) {}
}
