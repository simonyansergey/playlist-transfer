<?php

namespace App\Services\Spotify;

use App\Models\User;
use App\Services\Oauth\SpotifyOauthService;
use Illuminate\Support\Facades\Http;

class SpotifyApiService
{
    public function __construct(
        private readonly SpotifyOauthService $spotifyOauthService
    ) {
    }

    /**
     * Get current spotify user id
     */
    public function getCurrentUserId(User $user): string
    {
        return $user->oauthAccounts()->where('provider', 'spotify')->value('provider_user_id');
    }

    public function createPlaylist(User $user, string $name, bool $public = true): string
    {
        $token = $this->spotifyOauthService->getValidAccessToken($user);
        $url = config('spotify.api_base') . '/users/' . $this->getCurrentUserId($user) . '/playlists';

        $response = Http::withToken($token)
            ->post($url, [
                'public' => $public,
                'name' => $name,
                'description' => 'Created using Playlist transfer app. Enjoy!',
            ]);

        $data = $response->json();

        return $data['id'];
    }

    /**
     * Get best matching track URI or null
     */
    public function searchTrack(User $user, string $searchQuery): ?string
    {
        $accessToken = $this->spotifyOauthService->getValidAccessToken($user);
        $response = Http::withToken($accessToken)
            ->get(config('spotify.api_base') . '/search' . '?' . http_build_query([
                'q' => $searchQuery,
                'type' => 'track',
                'limit' => 1,
            ]));

        $data = $response->json();

        return $data['tracks']['items'][0]['uri'] ?? null;
    }

    public function addTracks(User $user, string $playlistId, array $trackList): string
    {
        $token = $this->spotifyOauthService->getValidAccessToken($user);
        $response = Http::timeout(100)
            ->withToken($token)
            ->post(config('spotify.api_base') . '/playlists/' . $playlistId . '/tracks', [
                'uris' => $trackList,
            ]);

        $data = $response->json();

        return $data['snapshot_id'] ?? '';
    }
}
