<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\PlaylistTransfer;
use App\Jobs\ProcessPlaylistTransfer;
use App\Services\PlaylistTransfer\PlaylistTransferService;
use App\Services\Youtube\YoutubeApiService;
use App\Services\Spotify\SpotifyApiService;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PlaylistTransferJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_execute_transfer_dispatches_job()
    {
        Queue::fake();

        $user = User::factory()->create();
        $transfer = PlaylistTransfer::create([
            'user_id' => $user->id,
            'source_provider' => 'youtube',
            'source_playlist_id' => 'abc',
            'target_provider' => 'spotify',
            'target_playlist_id' => 'temp_id',
            'status' => 'pending',
            'total_items' => 10,
        ]);

        $youtubeMock = Mockery::mock(YoutubeApiService::class);
        $youtubeMock->shouldReceive('getPlaylistItems')
            ->andReturn([
                [
                    'snippet' => [
                        'title' => 'Test Song',
                        'resourceId' => ['videoId' => '123'],
                    ]
                ]
            ]);

        $spotifyMock = Mockery::mock(SpotifyApiService::class);
        $spotifyMock->shouldReceive('createPlaylist')->andReturn('spotify_playlist_id');
        $spotifyMock->shouldReceive('searchTrack')->andReturn('spotify:track:123');

        $service = new PlaylistTransferService($youtubeMock, $spotifyMock);

        // Mock auth user
        $this->actingAs($user);

        $result = $service->executeTransfer($transfer->id);

        $this->assertTrue($result['success']);

        // Verify Job Dispatched
        Queue::assertPushed(ProcessPlaylistTransfer::class, function ($job) use ($transfer) {
            return $job->transferId === $transfer->id;
        });

        // Verify Status is NOT completed yet
        $transfer->refresh();
        $this->assertNotEquals('completed', $transfer->status);
        $this->assertEquals('pending', $transfer->status);
    }
}
