<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;

class Staff extends Authenticatable implements CanResetPassword
{
    use CanResetPasswordTrait;
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
