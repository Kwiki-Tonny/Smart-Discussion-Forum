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
            $table->string('category');
            $table->foreignId('group_id')->constrained()->onDelete('cascade');
            $table->integer('frequency')->default(0);
            $table->timestamps();
            
            $table->unique(['term', 'group_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_terms');
    }
};