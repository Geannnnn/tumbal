<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusSurat extends Model
{
    protected $table = 'status_surat';
    protected $fillable = ['status_surat'];

    public function riwayatStatus()
    {
        return $this->hasMany(RiwayatStatusSurat::class, 'id_status_surat');
    }
}
