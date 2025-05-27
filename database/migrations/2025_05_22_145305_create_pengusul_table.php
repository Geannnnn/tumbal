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
        Schema::create('pengusul', function (Blueprint $table) {
            $table->id('id_pengusul');
            $table->string('nama');
            $table->char('nim', 20)->unique()->nullable();
            $table->char('nip', 20)->unique()->nullable();
            $table->string('password');
            $table->unsignedBigInteger('id_role_pengusul');
            $table->string('email')->unique();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengusul');
    }
};
