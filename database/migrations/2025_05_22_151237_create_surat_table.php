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
        Schema::create('surat', function (Blueprint $table) {
            $table->id('id_surat');
            $table->string('nomor_surat')->nullable();
            $table->string('judul_surat');
            $table->date('tanggal_pengajuan');
            $table->date('tanggal_surat_dibuat')->nullable();
            $table->unsignedBigInteger('id_jenis_surat')->nullable();
            $table->unsignedBigInteger('dibuat_oleh')->nullable();
            $table->string('deskripsi', 300)->nullable();
            $table->string('tujuan_surat')->nullable();
            $table->boolean('is_draft')->default(0);
            $table->string('lampiran')->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat');
    }
};
