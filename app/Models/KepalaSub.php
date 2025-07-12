<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;
use Illuminate\Notifications\Notifiable;

class KepalaSub extends Authenticatable implements CanResetPassword
{
    use CanResetPasswordTrait, Notifiable;
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
