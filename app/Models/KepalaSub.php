<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;

class KepalaSub extends Authenticatable
{
    protected $table = 'kepala_sub';
    protected $primaryKey = 'id_kepala_sub';

    protected $fillable = [
        'nama',
        'nip',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
