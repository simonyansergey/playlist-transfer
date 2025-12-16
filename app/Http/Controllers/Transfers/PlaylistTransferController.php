<?php

namespace App\Http\Controllers\Transfers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transfers\TransferStoreRequest;
use App\Models\PlaylistTransfer;
use App\Services\PlaylistTransfer\PlaylistTransferService;

class PlaylistTransferController extends Controller
{
    public function __construct(
        private readonly PlaylistTransferService $playlistTransferService
    ) {
    }

    /**
     * Store a new playlist transfer
     */
    public function store(TransferStoreRequest $request)
    {
        $data = $request->validated();
        $user = $request->user('sanctum');

        // Start the transfer
        $transferId = $this->playlistTransferService->startTransfer(
            $user,
            $data['playlist_url'],
            [
                'playlist_name' => $data['playlist_name'] ?? null,
                'public' => $data['public'] ?? true,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Playlist transfer initiated successfully',
            'transfer_id' => $transferId,
        ], 201);
    }

    /**
     * Get transfer details
     */
    public function show(int $transferId)
    {
        $user = request()->user();

        $transfer = $this->playlistTransferService->getTransfer($user, $transferId);

        return response()->json([
            'success' => true,
            'transfer' => $transfer,
        ]);
    }

    /**
     * Execute a playlist transfer
     */
    public function execute(PlaylistTransfer $transfer)
    {
        $this->authorize('execute', $transfer);
        $result = $this->playlistTransferService->executeTransfer($transfer->id);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Playlist transfer completed successfully',
                'data' => $result,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Playlist transfer failed',
            'error' => $result['error'],
        ], 500);
    }
}
