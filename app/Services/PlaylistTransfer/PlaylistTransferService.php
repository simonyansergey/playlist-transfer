<?php

namespace App\Services\PlaylistTransfer;

use App\Models\User;
use App\Models\PlaylistTransfer;
use App\Models\PlaylistTransferItem;
use App\Jobs\ProcessPlaylistTransfer;
use App\Services\Spotify\SpotifyApiService;
use App\Services\Youtube\YoutubeApiService;
use App\Http\Resources\Models\PlaylistTransfer\PlaylistTransferShowResource;

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
        $playlistDetails = $this->youtubeApiService->getPlaylist(
            user: $user,
            playlistUrl: $playlistUrl
        );

        $transfer = PlaylistTransfer::create([
            'user_id' => $user->id,
            'source_provider' => 'youtube',
            'source_playlist_id' => $this->extractPlaylistId($playlistUrl),
            'target_provider' => 'spotify',
            'target_playlist_id' => $options['playlist_name'] ?? $playlistDetails['title'],
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

        try {
            ProcessPlaylistTransfer::dispatch(
                $transfer->id,
                auth()->user()->id
            );

            return [
                'success' => true,
                'transfer_id' => $transfer->id,
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
     * @return PlaylistTransferShowResource Transfer details
     */
    public function getTransfer(User $user, int $transferId): PlaylistTransferShowResource
    {
        $transfer = PlaylistTransfer::where('user_id', $user->id)
            ->where('id', $transferId)
            ->with('playlistTransferItems')
            ->firstOrFail();

        return new PlaylistTransferShowResource($transfer);
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
