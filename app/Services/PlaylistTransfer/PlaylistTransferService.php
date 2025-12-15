<?php

namespace App\Services\PlaylistTransfer;

use App\Models\User;
use App\Services\Spotify\SpotifyApiService;
use App\Services\Youtube\YoutubeApiService;

class PlaylistTransferService
{
    public function __construct(
        private readonly YoutubeApiService $youtubeApiService,
        private readonly SpotifyApiService $spotifyApiService
    ) {}

    /**
     * @param User $user
     * @param string $playlistUrl
     * @param array $options
     * @return int
     */
    public function startTransfer(User $user, string $playlistUrl, array $options): int
    {
        return 0;
    }

    /**
     * @param int $transferId
     * @return array
     */
    public function executeTransfer(int $transferId): array
    {
        return [];
    }

    public function getTransfer(User $user, int $transferId): array
    {
        return [];
    }
}
