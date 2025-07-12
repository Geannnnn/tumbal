<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Surat extends Model
{
    protected $table = 'surat';
    protected $primaryKey = 'id_surat';
    public $timestamps = 'false';
    protected $fillable = ['judul_surat', 'tanggal_pengajuan', 'id_jenis_surat', 'deskripsi','dibuat_oleh','lampiran','is_draft','tujuan_surat'];
    

    public function riwayatStatus(){
        return $this->hasMany(RiwayatStatusSurat::class, 'id_surat');
    }
    public function dibuatOleh(){
        return $this->belongsTo(Pengusul::class, 'dibuat_oleh', 'id_pengusul');
    }
    

    public function jenisSurat(){
        return $this->belongsTo(JenisSurat::class, 'id_jenis_surat', 'id_jenis_surat');
    }
    public function pengusul(){
        return $this->belongsToMany(Pengusul::class, 'pivot_pengusul_surat', 'id_surat', 'id_pengusul')
                    ->withPivot('id_peran_keanggotaan');
    }
    public function statusTerakhir()
    {
        return $this->hasOne(RiwayatStatusSurat::class, 'id_surat')
        ->latestOfMany('tanggal_rilis');
    }
    public function komentarSurat()
    {
        return $this->hasMany(KomentarSurat::class, 'id_surat');    
    }
}

