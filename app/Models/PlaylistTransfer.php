<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlaylistTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'source_provider',
        'source_playlist_id',
        'target_provider',
        'target_playlist_id',
        'status',
        'total_items',
        'matched_items',
        'failed_items',
        'error_message',
        'started_at',
        'finished_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function playlistTransferItems(): HasMany
    {
        return $this->hasMany(PlaylistTransferItem::class);
    }
}
