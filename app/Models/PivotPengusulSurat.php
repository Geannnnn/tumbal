<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PivotPengusulSurat extends Model
{
    protected $table = 'pivot_pengusul_surat';
    public $timestamps = false;

    protected $fillable = [
        'id_surat',
        'id_pengusul',
        'id_peran_keanggotaan',
    ];
}
