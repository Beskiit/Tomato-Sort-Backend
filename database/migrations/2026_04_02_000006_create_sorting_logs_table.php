<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sorting_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('sorting_sessions')->cascadeOnDelete();
            $table->timestamp('logged_at')->useCurrent();
            $table->enum('tomato_classification', ['ripe', 'unripe', 'rotten']);
            $table->string('image_path', 255)->nullable();
            $table->float('ai_confidence')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sorting_logs');
    }
};
