<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateSpGetAllJenisSuratV2 extends Migration
{
    public function up()
    {
        try {
            DB::unprepared('
                DROP PROCEDURE IF EXISTS sp_GetAllJenisSurat;
                CREATE PROCEDURE sp_GetAllJenisSurat()
                BEGIN
                    SELECT 
                        id_jenis_surat,
                        jenis_surat
                    FROM jenis_surat
                    ORDER BY jenis_surat ASC;
                END
            ');
        } catch (\Exception $e) {
            Log::warning("Gagal membuat stored procedure sp_GetAllJenisSurat: " . $e->getMessage());
        }
    }

    public function down()
    {
        try {
            DB::unprepared('DROP PROCEDURE IF EXISTS sp_GetAllJenisSurat;');
        } catch (\Exception $e) {
            Log::warning("Gagal menghapus sp_GetAllJenisSurat: " . $e->getMessage());
        }
    }
}
