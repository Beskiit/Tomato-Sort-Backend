<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sorting_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->cascadeOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('ripe_count')->default(0);
            $table->unsignedInteger('unripe_count')->default(0);
            $table->unsignedInteger('rotten_count')->default(0);
            $table->string('raspberry_pi_id', 50)->nullable();
            $table->enum('session_status', ['in_progress', 'completed', 'failed'])->default('in_progress');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sorting_sessions');
    }
};
