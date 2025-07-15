<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Pengusul;
use App\Models\Staff;
use App\Models\KepalaSub;

class RiwayatStatusSurat extends Model
{
    protected $table = 'riwayat_status_surat';
    protected $fillable = ['id_surat', 'id_status_surat', 'tanggal_rilis', 'keterangan', 'diubah_oleh', 'diubah_oleh_tipe'];
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

    public function diubahOleh()
    {
        // Dynamic relationship based on diubah_oleh_tipe
        switch ($this->diubah_oleh_tipe) {
            case 'pengusul':
                return $this->belongsTo(Pengusul::class, 'diubah_oleh', 'id_pengusul');
            case 'staff':
                return $this->belongsTo(Staff::class, 'diubah_oleh', 'id_staff');
            case 'kepala_sub':
                return $this->belongsTo(KepalaSub::class, 'diubah_oleh', 'id_kepala_sub');
            default:
                return null;
        }
    }

    /**
     * Get nama user yang mengubah status
     */
    public function getNamaDiubahOlehAttribute()
    {
        if (!$this->diubah_oleh || !$this->diubah_oleh_tipe) {
            return '-';
        }

        try {
            switch ($this->diubah_oleh_tipe) {
                case 'pengusul':
                    $pengusul = Pengusul::find($this->diubah_oleh);
                    return $pengusul ? $pengusul->nama : '-';
                case 'staff':
                    $staff = Staff::where('id_staff', $this->diubah_oleh)->first();
                    return $staff ? $staff->nama : '-';
                case 'kepala_sub':
                    $kepalaSub = KepalaSub::find($this->diubah_oleh);
                    return $kepalaSub ? $kepalaSub->nama : '-';
                default:
                    return '-';
            }
        } catch (\Exception $e) {
            return '-';
        }
    }
}
