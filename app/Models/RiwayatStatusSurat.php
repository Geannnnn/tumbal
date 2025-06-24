<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatStatusSurat extends Model
{
    protected $table = 'riwayat_status_surat';
    protected $fillable = ['id_surat', 'id_status_surat', 'tanggal_rilis'];
    public $timestamps = false;

    public function surat()
    {
        return $this->belongsTo(Surat::class, 'id_surat');
    }

    public function statusSurat()
    {
        return $this->belongsTo(StatusSurat::class, 'id_status_surat', 'id_status_surat');
    }

    public function komentarSurat()
    {
        return $this->hasMany(KomentarSurat::class, 'id_riwayat_status_surat');
    }
}
