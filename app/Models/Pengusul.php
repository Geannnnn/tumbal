<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\RolePengusul;

class Pengusul extends Authenticatable
{
    protected $table = 'pengusul';
    protected $primaryKey = 'id_pengusul';

    protected $guard = 'pengusul';

    protected $fillable = [
        'nim', 
        'nip', 
        'nama', 
        'email', 
        'password', 
        'id_role_pengusul'
    ];

    protected $hidden = ['password'];

    public $timestamps = false;

    public function getAuthIdentifierName()
    {
        return $this->primaryKey;
    }

    public function role()
    {
        return $this->belongsTo(RolePengusul::class, 'id_role_pengusul', 'id_role_pengusul');
    }
    public function surat(){
        return $this->belongsToMany(Surat::class, 'pivot_pengusul_surat', 'id_pengusul', 'id_surat')
                    ->withPivot('id_peran_keanggotaan');
    }
    
}
