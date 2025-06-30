<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateSpGetAllJenisSurat extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
            DROP PROCEDURE IF EXISTS sp_GetAllJenisSurat;
            CREATE PROCEDURE sp_GetAllJenisSurat()
            BEGIN
                SELECT 
                    id_jenis_surat,
                    jenis_surat,
                    created_at,
                    updated_at
                FROM jenis_surat
                ORDER BY jenis_surat ASC;
            END
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_GetAllJenisSurat;');
    }
} 