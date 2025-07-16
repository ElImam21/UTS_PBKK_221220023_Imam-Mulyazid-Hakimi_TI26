<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public $timestamps = false;

    protected $primaryKey = 'user_id'; // <- Tambahkan ini
    public $incrementing = false;      // <- Karena ULID bukan auto increment
    protected $keyType = 'string';     // <- Karena ULID berupa string

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'password',
        'membership_date',
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

    public function getAuthIdentifierName()
    {
        return 'user_id';
    }

    public function getKeyName()
    {
        return 'user_id';
    }

    public function detail()
    {
        return $this->hasOne(UserDetail::class, 'user_id', 'user_id');        
    }
}
