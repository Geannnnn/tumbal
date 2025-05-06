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
        Schema::table('surat', function (Blueprint $table) {
            $table->integer('dibuat_oleh')->after('id_jenis_surat');
            $table->foreign('dibuat_oleh')->references('id_pengusul')->on('pengusul');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat', function (Blueprint $table) {
            $table->dropForeign(['dibuat_oleh']);
            $table->dropColumn('dibuat_oleh');
            
        });
    }
};
