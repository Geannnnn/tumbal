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
        // Tambahkan foreign key ke tabel pengusul
        Schema::table('pengusul', function (Blueprint $table) {
            $table->foreign('id_role_pengusul')->references('id_role_pengusul')->on('role_pengusul');
        });

        // Tambahkan foreign key ke tabel surat
        Schema::table('surat', function (Blueprint $table) {
            $table->foreign('id_jenis_surat')->references('id_jenis_surat')->on('jenis_surat')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('dibuat_oleh')->references('id_pengusul')->on('pengusul')->onDelete('restrict')->onUpdate('cascade');
        });

        // Tambahkan foreign key ke tabel pivot_pengusul_surat
        Schema::table('pivot_pengusul_surat', function (Blueprint $table) {
            $table->foreign('id_pengusul')->references('id_pengusul')->on('pengusul');
            $table->foreign('id_surat')->references('id_surat')->on('surat');
            $table->foreign('id_peran_keanggotaan')->references('id_peran_keanggotaan')->on('peran_anggota');
        });

        // Tambahkan foreign key ke tabel riwayat_status_surat
        Schema::table('riwayat_status_surat', function (Blueprint $table) {
            $table->foreign('id_status_surat')->references('id_status_surat')->on('status_surat')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('id_surat')->references('id_surat')->on('surat');
        });

        // Tambahkan foreign key ke tabel komentar_surat
        Schema::table('komentar_surat', function (Blueprint $table) {
            $table->foreign('id')->references('id')->on('riwayat_status_surat');
            $table->foreign('id_surat')->references('id_surat')->on('surat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus foreign key constraints dalam urutan terbalik
        Schema::table('riwayat_status_surat', function (Blueprint $table) {
            $table->dropForeign(['id_status_surat']);
            $table->dropForeign(['id_surat']);
        });

        Schema::table('pivot_pengusul_surat', function (Blueprint $table) {
            $table->dropForeign(['id_pengusul']);
            $table->dropForeign(['id_surat']);
            $table->dropForeign(['id_peran_keanggotaan']);
        });

        Schema::table('surat', function (Blueprint $table) {
            $table->dropForeign(['id_jenis_surat']);
            $table->dropForeign(['dibuat_oleh']);
        });

        Schema::table('pengusul', function (Blueprint $table) {
            $table->dropForeign(['id_role_pengusul']);
        });

        Schema::table('komentar_surat', function (Blueprint $table) {
            $table->dropForeign(['id']);
            $table->dropForeign(['id_surat']);
            $table->dropForeign(['id_user']);
        });
    }
};
