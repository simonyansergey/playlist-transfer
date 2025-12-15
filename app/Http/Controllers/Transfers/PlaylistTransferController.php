<?php

namespace App\Http\Controllers\Transfers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transfers\TransferStoreRequest;
use App\Services\PlaylistTransfer\PlaylistTransferService;

class PlaylistTransferController extends Controller
{
    public function __construct(
        private readonly PlaylistTransferService $playlistTransferService
    ) {}

    public function store(TransferStoreRequest $request)
    {
        $data = $request->validated();
    }
}
