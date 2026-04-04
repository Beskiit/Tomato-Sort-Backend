<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 100);         // e.g. "created", "updated", "deleted", "logged_in"
            $table->string('model_type', 100)->nullable(); // e.g. "Appointment", "User", "SortingSession"
            $table->unsignedBigInteger('model_id')->nullable(); // ID of the affected record
            $table->text('description');            // Human-readable description
            $table->json('changes')->nullable();    // Before/after values for updates
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('performed_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
