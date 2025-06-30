<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateFunctionGetNamaPengusul extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
            DELIMITER $$
            
            CREATE FUNCTION GetNamaPengusul(p_id_pengusul INT)
            RETURNS VARCHAR(255)
            DETERMINISTIC
            BEGIN
                DECLARE nama_pengusul VARCHAR(255);
                
                SELECT nama INTO nama_pengusul 
                FROM pengusul
                WHERE id_pengusul = p_id_pengusul;
                
                RETURN COALESCE(nama_pengusul, "-");
            END $$
            
            DELIMITER ;
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP FUNCTION IF EXISTS GetNamaPengusul');
    }
} 