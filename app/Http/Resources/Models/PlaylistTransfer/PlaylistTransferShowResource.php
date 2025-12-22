<?php

namespace App\Http\Resources\Models\PlaylistTransfer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Models\PlaylistTransferItem\PlaylistTransferItemIndexResource;

class PlaylistTransferShowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'source_provider' => $this->source_provider,
            'source_playlist_id' => $this->source_playlist_id,
            'target_provider' => $this->target_provider,
            'target_playlist_id' => $this->target_playlist_id,
            'total_items' => $this->total_items,
            'matched_items' => $this->matched_items,
            'failed_items' => $this->failed_items,
            'error_message' => $this->error_message,
            'started_at' => $this->started_at,
            'finished_at' => $this->finished_at,
            'items' => PlaylistTransferItemIndexResource::collection($this->playlistTransferItems),
        ];
    }
}
