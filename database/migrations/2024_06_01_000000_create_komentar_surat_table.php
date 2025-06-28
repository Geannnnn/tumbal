<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('komentar_surat', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_riwayat_status_surat');
            $table->unsignedBigInteger('id_surat');
            $table->unsignedBigInteger('id_user'); 
            $table->text('komentar');
            $table->timestamps();

            // $table->foreign('id_riwayat_status_surat')->references('id')->on('riwayat_status_surat')->onDelete('cascade');
            // $table->foreign('id_surat')->references('id_surat')->on('surat')->onDelete('cascade');
            // $table->foreign('id_user')->references('id')->on('users'); // sesuaikan dengan tabel user/staff anda
        });
    }
    public function down(): void {
        Schema::dropIfExists('komentar_surat');
    }
}; 