<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\PlaylistTransfer;
use App\Models\PlaylistTransferItem;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\Spotify\SpotifyApiService;
use App\Services\Youtube\YoutubeApiService;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessPlaylistTransfer implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $transferId,
        public int $userId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(SpotifyApiService $spotifyApiService, YoutubeApiService $youtubeApiService): void
    {
        $transfer = PlaylistTransfer::findOrFail($this->transferId);
        $user = User::findOrFail($this->userId);

        $transfer->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);

        $playlistItems = $youtubeApiService->getPlaylistItems(
            user: $user,
            playlistUrl: "https://www.youtube.com/playlist?list=" . $transfer->source_playlist_id
        );

        $playlistName = $transfer->target_playlist_id ?? 'Transferred Playlist';

        $spotifyPlaylistId = $spotifyApiService->createPlaylist(
            user: $user,
            name: $playlistName,
            public: true
        );

        $transfer->update(['target_playlist_id' => $spotifyPlaylistId]);

        $matchedTracks = [];
        $matchedCount = 0;
        $failedCount = 0;

        foreach ($playlistItems as $item) {
            $sourceTitle = $item['snippet']['title'] ?? '';
            $sourceVideoId = $item['snippet']['resourceId']['videoId'] ?? '';

            $cleanedTrackTitle = $this->cleanYoutubeTitle($sourceTitle);

            $searchQuery = $cleanedTrackTitle;

            if (str_contains($cleanedTrackTitle, ' - ')) {
                $parts = explode(' - ', $cleanedTrackTitle, 2);
                $artist = trim($parts[0]);
                $track = trim($parts[1]);
                $searchQuery = "artist:{$artist} track:{$track}";
            }

            try {
                $spotifyTrackUri = $spotifyApiService->searchTrack(
                    user: $user,
                    searchQuery: $searchQuery
                );

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

        try {
            $spotifyApiService->addTracks(
                user: $user,
                playlistId: $spotifyPlaylistId,
                trackList: $matchedTracks
            );

            $transfer->update([
                'status' => 'completed',
                'finished_at' => now(),
                'matched_items' => $matchedCount,
                'failed_items' => $failedCount,
            ]);
        } catch (\Exception $e) {
            $transfer->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'finished_at' => now(),
            ]);

            throw $e;
        }
    }


    /**
     * @param string $title
     * @return string
     */
    private function cleanYoutubeTitle(string $title): string
    {
        $title = str_replace('//', '-', $title);

        $patterns = [
            '/\s*[\(\[](?:official|video|audio|lyrics?|hd|4k|hq|live|remix|cover|mv|music\s*video|official\s*video|official\s*audio|demo|promo|from).*?[\)\]]/i' => '',
            '/\s*[\(\[]ft\.?.*?[\)\]]/i' => '',
            '/\s*[\(\[]feat\.?.*?[\)\]]/i' => '',
            '/\s*[\|\-]\s*(?:official|video|audio|lyrics?|hd|4k|hq|live|promo).*?$/i' => '',
            '/\s*[\-]\s*.*?\s*Cover\s*$/i' => '',
            '/(?:\s+(?:official|video|audio|lyrics?|hd|4k|hq|live|remix|cover|mv|music\s*video|acoustic|version|promo))+$/i' => '',
        ];

        $cleaned = preg_replace(array_keys($patterns), array_values($patterns), $title);
        return trim(preg_replace('/\s+/', ' ', $cleaned));
    }
}
