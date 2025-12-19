<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\PlaylistTransfer;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\Spotify\SpotifyApiService;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessPlaylistTransfer implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $transferId,
        public int $userId,
        public array $matchedTracks,
        public string $spotifyPlaylistId
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(SpotifyApiService $spotifyApiService): void
    {
        $transfer = PlaylistTransfer::findOrFail($this->transferId);
        $user = User::findOrFail($this->userId);

        try {
            $spotifyApiService->addTracks(
                user: $user,
                playlistId: $this->spotifyPlaylistId,
                trackList: $this->matchedTracks
            );

            $transfer->update([
                'status' => 'completed',
                'finished_at' => now(),
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
}
