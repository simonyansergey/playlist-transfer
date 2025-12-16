<?php

namespace App\Services\PlaylistTransfer;

use App\Models\User;
use App\Models\PlaylistTransfer;
use App\Models\PlaylistTransferItem;
use App\Services\Spotify\SpotifyApiService;
use App\Services\Youtube\YoutubeApiService;

class PlaylistTransferService
{
    public function __construct(
        private readonly YoutubeApiService $youtubeApiService,
        private readonly SpotifyApiService $spotifyApiService
    ) {
    }

    /**
     * Start a new playlist transfer
     * 
     * @param User $user The user initiating the transfer
     * @param string $playlistUrl YouTube playlist URL
     * @param array $options Transfer options (playlist_name, public, etc.)
     * @return int Transfer ID
     */
    public function startTransfer(User $user, string $playlistUrl, array $options): int
    {
        $playlistDetails = $this->youtubeApiService->getPlaylist($user, $playlistUrl);

        $transfer = PlaylistTransfer::create([
            'user_id' => $user->id,
            'source_provider' => 'youtube',
            'source_playlist_id' => $this->extractPlaylistId($playlistUrl),
            'target_provider' => 'spotify',
            'target_playlist_id' => $playlistDetails['title'],
            'status' => 'pending',
            'total_items' => $playlistDetails['total_items_count'] ?? 0,
            'matched_items' => 0,
            'failed_items' => 0,
        ]);

        return $transfer->id;
    }

    /**
     * Execute the playlist transfer process
     * 
     * @param int $transferId The transfer ID to execute
     * @return array Transfer execution results
     */
    public function executeTransfer(int $transferId): array
    {
        $transfer = PlaylistTransfer::findOrFail($transferId);
        $user = $transfer->user;

        try {
            $transfer->update([
                'status' => 'processing',
                'started_at' => now(),
            ]);

            $playlistItems = $this->youtubeApiService->getPlaylistItems(
                $user,
                "https://www.youtube.com/playlist?list=" . $transfer->source_playlist_id
            );

            $playlistName = $transfer->target_playlist_name
                ?? $playlistItems['title']
                ?? 'Transferred Playlist';

            $spotifyPlaylistId = $this->spotifyApiService->createPlaylist(
                $user,
                $playlistName,
                true
            );

            $transfer->update(['target_playlist_id' => $spotifyPlaylistId]);

            $matchedTracks = [];
            $matchedCount = 0;
            $failedCount = 0;

            foreach ($playlistItems as $item) {
                $sourceTitle = $item['snippet']['title'] ?? '';
                $sourceVideoId = $item['snippet']['resourceId']['videoId'] ?? '';

                $searchQuery = "track:{$sourceTitle}";

                try {
                    $spotifyTrackUri = $this->spotifyApiService->searchTrack($user, $searchQuery);

                    if ($spotifyTrackUri) {
                        $matchedTracks[] = $spotifyTrackUri;
                        $matchedCount++;

                        PlaylistTransferItem::create([
                            'playlist_transfer_id' => $transfer->id,
                            'source_title' => $sourceTitle,
                            'source_video_id' => $sourceVideoId,
                            'raw_data' => $item,
                            'search_query' => $searchQuery,
                            'matched_uri' => $spotifyTrackUri,
                            'status' => 'matched',
                        ]);
                    } else {
                        $failedCount++;

                        PlaylistTransferItem::create([
                            'playlist_transfer_id' => $transfer->id,
                            'source_title' => $sourceTitle,
                            'source_video_id' => $sourceVideoId,
                            'raw_data' => $item,
                            'search_query' => $searchQuery,
                            'status' => 'failed',
                            'error_message' => 'No matching track found on Spotify',
                        ]);
                    }
                } catch (\Exception $e) {
                    $failedCount++;

                    PlaylistTransferItem::create([
                        'playlist_transfer_id' => $transfer->id,
                        'source_title' => $sourceTitle,
                        'source_video_id' => $sourceVideoId,
                        'raw_data' => $item,
                        'search_query' => $searchQuery,
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                    ]);
                }
            }

            if (!empty($matchedTracks)) {
                $this->spotifyApiService->addTracks($user, $spotifyPlaylistId, $matchedTracks);
            }

            $transfer->update([
                'status' => 'completed',
                'matched_items' => $matchedCount,
                'failed_items' => $failedCount,
                'finished_at' => now(),
            ]);

            return [
                'success' => true,
                'transfer_id' => $transfer->id,
                'matched_items' => $matchedCount,
                'failed_items' => $failedCount,
                'spotify_playlist_id' => $spotifyPlaylistId,
            ];

        } catch (\Exception $e) {
            $transfer->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'finished_at' => now(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get transfer details
     * 
     * @param User $user The user requesting the transfer
     * @param int $transferId The transfer ID
     * @return array Transfer details
     */
    public function getTransfer(User $user, int $transferId): array
    {
        $transfer = PlaylistTransfer::where('user_id', $user->id)
            ->where('id', $transferId)
            ->with('playlistTransferItems')
            ->firstOrFail();

        return [
            'id' => $transfer->id,
            'status' => $transfer->status,
            'source_provider' => $transfer->source_provider,
            'source_playlist_id' => $transfer->source_playlist_id,
            'target_provider' => $transfer->target_provider,
            'target_playlist_id' => $transfer->target_playlist_id,
            'total_items' => $transfer->total_items,
            'matched_items' => $transfer->matched_items,
            'failed_items' => $transfer->failed_items,
            'error_message' => $transfer->error_message,
            'started_at' => $transfer->started_at,
            'finished_at' => $transfer->finished_at,
            'items' => $transfer->playlistTransferItems->map(function ($item) {
                return [
                    'source_title' => $item->source_title,
                    'source_video_id' => $item->source_video_id,
                    'search_query' => $item->search_query,
                    'matched_uri' => $item->matched_uri,
                    'status' => $item->status,
                    'error_message' => $item->error_message,
                ];
            }),
        ];
    }

    /**
     * Extract playlist ID from YouTube URL
     * 
     * @param string $url YouTube playlist URL
     * @return string Playlist ID
     */
    private function extractPlaylistId(string $url): string
    {
        parse_str(parse_url($url, PHP_URL_QUERY), $result);
        return $result['list'] ?? '';
    }
}
