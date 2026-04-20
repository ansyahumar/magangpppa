<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property string $role  
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'unit',   
        'no_id',

    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
   
public function isVerifikator() {
    return $this->role === 'verifikator';
}

public function getRoleNameAttribute()
{
    return [
        'p1' => 'Pemimpin',
        'p2' => 'Karodatin',
        'admin' => 'Administrator',
        'verifikator' => 'Verifikator',
        'user' => 'User',
        'kordinator' => 'Kordinator',
    ][$this->role] ?? $this->role;
}
}
