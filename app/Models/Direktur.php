<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;

class Direktur extends Authenticatable implements CanResetPassword
{
    use CanResetPasswordTrait, Notifiable;
    protected $table = 'direktur';
    protected $primaryKey = 'id_direktur';
    protected $fillable = [
        'nama',
        'nip',
        'email',
        'password',
    ];
    protected $hidden = [
        'password', 'remember_token',
    ];
} 