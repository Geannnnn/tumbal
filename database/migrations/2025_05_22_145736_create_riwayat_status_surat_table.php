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
        Schema::create('riwayat_status_surat', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_status_surat');
            $table->unsignedBigInteger('id_surat');
            $table->timestamp('tanggal_rilis')->useCurrent();
            $table->string('keterangan');
            $table->unsignedBigInteger('diubah_oleh')->nullable();
            $table->string('diubah_oleh_tipe')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_status_surat');
    }
};
