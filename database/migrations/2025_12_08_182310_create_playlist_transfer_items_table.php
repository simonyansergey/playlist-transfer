<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('playlist_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('playlist_transfer_id')->constrained('playlist_transfers');
            $table->string('source_title');
            $table->string('source_video_id');
            $table->json('raw_data')->nullable();
            $table->string('search_query')->nullable();
            $table->string('matched_uri')->nullable();
            $table->string('status');
            $table->string('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('playlist_transfer_items');
    }
};
