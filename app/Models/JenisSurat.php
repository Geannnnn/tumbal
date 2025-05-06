<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisSurat extends Model
{
    protected $table = 'jenis_surat';
    protected $primaryKey = 'id_jenis_surat';
    public $timestamps = 'false';

    // public function jenissurat(){
    //     return $this->belongsTo(JenisSurat::class,'id_jenis_surat','id_jenis_surat');
    // }
    
}
