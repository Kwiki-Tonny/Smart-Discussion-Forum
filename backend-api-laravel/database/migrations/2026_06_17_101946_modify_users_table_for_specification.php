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
        Schema::table('users', function (Blueprint $table) {
            // Appends the custom role, compliance status, and telemetry tracking fields
            $table->enum('role', ['admin', 'lecturer', 'student'])->default('student')->after('email');
            $table->enum('status', ['active', 'warned_once', 'warned_twice', 'blacklisted'])->default('active')->after('role');
            $table->timestamp('last_communicated_at')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Safely drops only the custom additions if rolled back
            $table->dropColumn(['role', 'status', 'last_communicated_at']);
        });
    }
};
