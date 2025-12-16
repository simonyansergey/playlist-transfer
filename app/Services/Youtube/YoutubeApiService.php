<?php

namespace App\Services\Youtube;

use App\Models\User;
use App\Services\Oauth\GoogleOauthService;
use Illuminate\Support\Facades\Http;

class YoutubeApiService
{
    public function __construct(
        private readonly GoogleOauthService $googleOauthService
    ) {
    }

    public function getPlaylist(User $user, string $playlistUrl): array
    {
        $accessToken = $this->googleOauthService->getValidAccessToken($user);
        $playlistId = $this->getPlaylistId($playlistUrl);

        $response = Http::withToken($accessToken)
            ->get(config('youtube.api_base') . '/playlists' . '?' . http_build_query([
                'id' => $playlistId,
                'part' => 'id,contentDetails,localizations,player,snippet,status',
            ]));

        $items = $response->json('items');
        $result = $items[0] ?? [];

        return [
            'title' => $result['snippet']['title'] ?? '',
            'channel_id' => $result['snippet']['channelId'] ?? '',
            'total_items_count' => $result['contentDetails']['itemCount'] ?? 0,
        ];
    }

    public function getPlaylistItems(User $user, string $playlistUrl): array
    {
        $accessToken = $this->googleOauthService->getValidAccessToken($user);
        $playlistId = $this->getPlaylistId($playlistUrl);

        $allItems = [];
        $pageToken = null;

        do {
            $params = [
                'playlistId' => $playlistId,
                'part' => 'id,contentDetails,snippet,status',
                'maxResults' => 50,
            ];

            if ($pageToken) {
                $params['pageToken'] = $pageToken;
            }

            $response = Http::withToken($accessToken)
                ->get(config('youtube.api_base') . '/playlistItems' . '?' . http_build_query($params));

            $result = $response->json();

            $items = $result['items'] ?? [];
            $allItems = array_merge($allItems, $items);

            $pageToken = $result['nextPageToken'] ?? null;
        } while ($pageToken);

        return $allItems;
    }

    private function getPlaylistId(string $url): string
    {
        parse_str(parse_url($url, PHP_URL_QUERY), $result);

        if (!isset($result['list'])) {
            throw new \InvalidArgumentException('Invalid YouTube playlist URL');
        }

        return $result['list'];
    }
}
