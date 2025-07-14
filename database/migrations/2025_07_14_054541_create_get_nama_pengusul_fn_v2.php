<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateGetNamaPengusulFnV2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            DB::unprepared("
                CREATE FUNCTION GetNamaPengusul(p_id_pengusul INT)
                RETURNS VARCHAR(255)
                DETERMINISTIC
                RETURN (
                    SELECT COALESCE(nama, '-') 
                    FROM pengusul 
                    WHERE id_pengusul = p_id_pengusul
                    LIMIT 1
                );
            ");
        } catch (\Exception $e) {
            // Fungsi sudah ada atau error lain, tidak hentikan migrasi
            Log::warning('Gagal membuat FUNCTION GetNamaPengusul: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        try {
            DB::unprepared('DROP FUNCTION IF EXISTS GetNamaPengusul');
        } catch (\Exception $e) {
            Log::warning('Gagal menghapus FUNCTION GetNamaPengusul: ' . $e->getMessage());
        }
    }
}