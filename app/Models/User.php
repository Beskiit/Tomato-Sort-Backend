<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = ['full_name', 'email', 'password_hash', 'role'];
    protected $hidden   = ['password_hash'];

    public function farmer() { return $this->hasOne(Farmer::class); }
    public function sorter() { return $this->hasOne(Sorter::class); }
    public function notifications() { return $this->hasMany(Notification::class); }

    // Required by Sanctum — maps to password_hash column
    public function getAuthPassword() { return $this->password_hash; }
}