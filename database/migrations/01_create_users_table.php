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
        Schema::create('users', function (Blueprint $table) {
            $table->id('idUser');
            $table->uuid('uuid', 15)->unique();
            $table->string('fullName', 100)->nullable();
            $table->string('username', 50)->unique()->nullable();
            $table->string('email', 150)->unique()->nullable();
            $table->enum('provider', ['Google', 'Apple', 'Facebook'])->nullable();
            $table->string('password', 100)->nullable();
            $table->boolean('email_verified')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
