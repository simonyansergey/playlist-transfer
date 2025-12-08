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
        Schema::create('playlist_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('source_provider');
            $table->string('source_playlist_id');
            $table->string('target_provider');
            $table->string('target_playlist_id');
            $table->string('status');
            $table->integer('total_items')->default(0);
            $table->integer('matched_items')->default(0);
            $table->integer('failed_items')->default(0);
            $table->string('error_message')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('finished_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('playlist_transfers');
    }
};
