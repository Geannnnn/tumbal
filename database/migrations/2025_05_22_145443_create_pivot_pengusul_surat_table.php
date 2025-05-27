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
        Schema::create('pivot_pengusul_surat', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_pengusul');
            $table->unsignedBigInteger('id_surat');
            $table->unsignedBigInteger('id_peran_keanggotaan');

         });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pivot_pengusul_surat');
    }
};
