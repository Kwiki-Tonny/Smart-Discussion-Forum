<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_terms', function (Blueprint $table) {
            $table->id();
            $table->string('term');
            $table->unsignedBigInteger('group_id');
            $table->string('category');
            $table->unsignedInteger('frequency')->default(1);
            $table->timestamps();

            $table->unique(['term', 'group_id']);
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_terms');
    }
};