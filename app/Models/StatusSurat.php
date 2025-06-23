<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusSurat extends Model
{
    protected $table = 'status_surat';
    protected $fillable = ['status_surat'];
    protected $primaryKey = 'id_status_surat';
    public $timestamps = false;

    public function riwayatStatus()
    {
        return $this->hasMany(RiwayatStatusSurat::class, 'id_status_surat');
    }
    
}
