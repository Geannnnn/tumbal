<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KomentarSurat extends Model
{
    protected $table = 'komentar_surat';
    protected $fillable = ['id_riwayat_status_surat', 'id_surat', 'id_user', 'komentar'];

    public function riwayatStatus()
    {
        return $this->belongsTo(RiwayatStatusSurat::class, 'id_riwayat_status_surat');
    }
    public function surat()
    {
        return $this->belongsTo(Surat::class, 'id_surat');
    }
   
} 