<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePengusul extends Model
{
    protected $table = 'role_pengusul'; 
    protected $primaryKey = 'id_role_pengusul'; 
    
    protected $fillable = ['role']; 
    
    // Relasi ke Pengusul
    public function pengusuls()
    {
        return $this->hasMany(Pengusul::class, 'id_role_pengusul');
    }
}
