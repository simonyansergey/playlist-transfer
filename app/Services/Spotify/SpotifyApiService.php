<?php

namespace App\Services\Spotify;

use App\Models\User;
use App\Services\Oauth\SpotifyOauthService;

class SpotifyApiService
{
    public function __construct(
        private readonly SpotifyOauthService $spotifyOauthService
    ) {}

    /**
     * Get current spotify user
     *
     * @param User $user
     * @return string
     */
    public function getCurrentUser(User $user): string
    {
        return '';
    }

    /**
     * @param User $user
     * @param string $name
     * @param string|null $visibility
     * @return string
     */
    public function createPlaylist(User $user, string $name, ?string $visibility = 'public'): string
    {
        return '';
    }

    /**
     * Get best matching track or null
     *
     * @param User $user
     * @param string $searchQuery
     * @return string|null
     */
    public function searchTrack(User $user, string $searchQuery): ?string
    {
        return null;
    }

    /**
     * @param User $user
     * @param string $playlistId
     * @param array $trackList
     * @return string
     */
    public function addTracks(User $user, string $playlistId, array $trackList): string
    {
        return 'success';
    }
}
