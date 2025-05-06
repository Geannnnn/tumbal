<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Staff extends Authenticatable
{
    protected $table = 'staff';
    protected $guard = 'staff';
    protected $primaryKey = 'id_staff';

    public $timestamps = false;

    protected $fillable = [
        'nip', 
        'nama', 
        'email', 
        'password', 
        'role'];
        
    protected $hidden = ['password'];
    public function getAuthIdentifierName()
    {
        return 'nip';
    }
}
