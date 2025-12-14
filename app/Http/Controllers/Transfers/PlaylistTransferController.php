<?php

namespace App\Http\Controllers\Transfers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Transfers\TransferStoreRequest;

class PlaylistTransferController extends Controller
{
    public function store(TransferStoreRequest $request)
    {
        $data = $request->validated();
    }
}
