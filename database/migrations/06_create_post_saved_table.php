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
        Schema::create('saved_recipes', function (Blueprint $table) {
            $table->unsignedBigInteger('idRecipe');
            $table->unsignedBigInteger('idUser');
            $table->timestamps();
            $table->primary(['idRecipe', 'idUser']);
            $table->foreign('idUser')->references('idUser')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_likes');
    }
};
