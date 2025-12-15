<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaylistTransferItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'playlist_transfer_id',
        'source_title',
        'source_video_id',
        'raw_data',
        'search_query',
        'matched_uri',
        'status',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'raw_data' => 'array',
        ];
    }

    public function playlistTransfer(): BelongsTo
    {
        return $this->belongsTo(PlaylistTransfer::class);
    }
}
