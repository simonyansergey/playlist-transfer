<?php

namespace App\Http\Resources\Models\PlaylistTransferItem;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlaylistTransferItemIndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'source_title' => $this->source_title,
            'source_video_id' => $this->source_video_id,
            'search_query' => $this->search_query,
            'matched_uri' => $this->matched_uri,
            'status' => $this->status,
            'error_message' => $this->error_message,
        ];
    }
}
