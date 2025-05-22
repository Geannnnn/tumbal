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

            $table->foreign('id_status_surat')->references('id_status_surat')->on('status_surat')->onDelete('cascade');
            $table->foreign('id_surat')->references('id_surat')->on('surat')->onDelete('cascade');
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
