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

            $table->foreign('id_pengusul')->references('id_pengusul')->on('pengusul')->onDelete('cascade');
            $table->foreign('id_surat')->references('id_surat')->on('surat')->onDelete('cascade');
            $table->foreign('id_peran_keanggotaan')->references('id_peran_keanggotaan')->on('peran_anggota')->onDelete('cascade');
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
