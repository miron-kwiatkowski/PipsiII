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
        Schema::create('guesses', function (Blueprint $table) {
            $table->id();
            $table->integer('UserId');
            $table->integer('PuzzleId');
            $table->integer('Points');
            $table->integer('Time');
            $table->date('Date');
            $table->boolean('DidWin');
            $table->primary('ID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guesses');
    }
};
