<?php

namespace App\Services\PlaylistTransfer;

use App\Services\Spotify\SpotifyApiService;
use App\Services\Youtube\YoutubeApiService;

class PlaylistTransferService
{
    public function __construct(
        private readonly YoutubeApiService $youtubeApiService,
        private readonly SpotifyApiService $spotifyApiService
    ) {}
}
