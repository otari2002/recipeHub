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
        Schema::create('comments', function (Blueprint $table) {
            $table->id('idComment');
            $table->unsignedBigInteger('idRecipe');
            $table->unsignedBigInteger('idUser');
            $table->string('commentText', 254)->nullable();
            $table->timestamps();
            $table->foreign('idRecipe')->references('idRecipe')->on('recipes')->onDelete('cascade');
            $table->foreign('idUser')->references('idUser')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
