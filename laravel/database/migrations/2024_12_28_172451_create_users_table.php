<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('Email');
            $table->string('Name');
            $table->string('Password');
            $table->date('JoinDate');
            $table->integer('PfpNum');
            $table->integer('CurrentGame');
            $table->boolean('IsAdmin');
            $table->boolean('IsBanned');
            $table->string('_token');
            $table->string('Type');
            $table->primary('ID');
            $table->unique('_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
