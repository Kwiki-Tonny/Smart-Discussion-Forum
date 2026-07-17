<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('status', ['active', 'pending', 'warned_once', 'warned_twice', 'blacklisted'])
                  ->default('pending')
                  ->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('status', ['active', 'warned_once', 'warned_twice', 'blacklisted'])
                  ->default('active')
                  ->change();
        });
    }
};