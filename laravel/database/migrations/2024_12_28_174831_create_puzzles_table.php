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
        Schema::create('puzzles', function (Blueprint $table) {
            $table->id();
            $table->string('IMGSource');
            $table->integer('Xvalue');
            $table->integer('Yvalue');
            $table->string('IMGDesc');
            $table->integer('Difficulty');
            $table->primary('ID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('puzzles');
    }
};
