<?php

namespace App\Services\Spotify;

use App\Models\User;
use App\Services\Oauth\SpotifyOauthService;
use Illuminate\Support\Facades\Http;

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
        return $user->oauthAccounts()->where('provider', 'spotify')->value('provider_user_id');
    }

    /**
     * @param User $user
     * @param string $name
     * @param string|null $visibility
     * @return string
     */
    public function createPlaylist(User $user, string $name, bool $public = true): string
    {
        // TODO this method i incomplete
        $response = Http::asForm()
            ->post(config('spotify.api_base') . '/users/' . $this->getCurrentUser($user) . '/playlists', [
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'name' => $name,
                'public' => $public,
                'description' => 'Created using Playlist transfer app. Enjoy!',
            ]);

        $data = $response->json();

        return $data['id'];
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
        // TODO this method i incomplete
        $response = Http::withToken($this->spotifyOauthService->getValidAccessToken($user))
            ->get(config('spotify.api_base') . '/search' . '?' . http_build_query([
                'q' => $searchQuery,
                'type' => 'track'
            ]));

        $data = $response->json();

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
        // TODO this method i incomplete
        $response = Http::asForm()
            ->post(config('spotify.api_base') . '/playlists/' . $playlistId . '/tracks', [
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'playlist_id' => $playlistId
            ]);

        $data = $response->json();

        return 'success';
    }
}
