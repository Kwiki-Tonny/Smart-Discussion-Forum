<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('blacklist_logs', function (Blueprint $table) {
            // Add only if it doesn't exist
            if (!Schema::hasColumn('blacklist_logs', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            }
        });
    }

    public function down()
    {
        Schema::table('blacklist_logs', function (Blueprint $table) {
            $table->dropColumn('updated_at');
        });
    }
};