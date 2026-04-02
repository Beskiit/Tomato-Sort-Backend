<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('full_name', 100)->after('id');
            $table->enum('role', ['admin', 'sorter', 'farmer'])->after('email');
            $table->dropColumn('name');
            $table->renameColumn('password', 'password_hash');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['full_name', 'role']);
            $table->renameColumn('password_hash', 'password');
        });
    }
};