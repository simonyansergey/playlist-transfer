<?php

namespace App\Services\Youtube;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use App\Services\Oauth\GoogleOauthService;

class YoutubeApiService
{
    public function __construct(
        private readonly GoogleOauthService $googleOauthService
    ) {}

    /**
     * @param User $user
     * @param string $playlistUrl
     * @return array
     */
    public function getPlaylist(User $user, string $playlistUrl): array
    {
        $accessToken = $user->oauthAccounts()
            ->where('provider', 'google')
            ->first()
            ->access_token;

        $playlistId = $this->getPlaylistId($playlistUrl);

        $response = Http::withToken($accessToken)
            ->get(config('youtube.api_base') . '/playlists' . '?' . http_build_query([
                'id' => $playlistId,
                'part' => 'id,contentDetails,localizations,player,snippet,status'
            ]));

        $result = $response->json('items');

        return [
            'title' => $result['snippet']['title'],
            'channel_id' => $result['snippet']['channelId'],
            'total_items_count' => $result['contentDetails']['itemCount']
        ];
    }

    /**
     * @param User $user
     * @param string $playlistUrl
     * @return array
     */
    public function getPlaylistItems(User $user, string $playlistUrl): array
    {
        $accessToken = $user->oauthAccounts()
            ->where('provider', 'google')
            ->first()
            ->access_token;

        $playlistId = $this->getPlaylistId($playlistUrl);

        $response = Http::withToken($accessToken)
            ->get(config('youtube.api_base') . '/playlistItems' . '?' . http_build_query([
                'playlistId' => $playlistId,
                'part' => 'id,contentDetails,snippet,status'
            ]));

        $result = $response->json();

        $data = [
            'nextPageToken' => $result['nextPageToken'] ?? null,
            "prevPageToken" => $result['prevPageToken'] ?? null,
        ];

        // TODO handle pagination

        return [];
    }

    /**
     * @param string $url
     * @return string
     */
    private function getPlaylistId(string $url): string
    {
        parse_str(parse_url($url, PHP_URL_QUERY), $result);
        return $result['list'];
    }
}
