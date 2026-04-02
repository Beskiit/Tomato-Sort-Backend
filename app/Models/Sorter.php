<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sorter extends Model
{
    protected $fillable = ['user_id', 'location', 'contact_number', 'is_available'];
    protected $casts    = ['is_available' => 'boolean'];

    public function user() { return $this->belongsTo(User::class); }
    public function appointments() { return $this->hasMany(Appointment::class); }
}