<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = ['user_id', 'appointment_id', 'message', 'is_read', 'sent_at'];
    protected $casts    = ['is_read' => 'boolean', 'sent_at' => 'datetime'];

    const CREATED_AT = 'sent_at';

    public function user() { return $this->belongsTo(User::class); }
    public function appointment() { return $this->belongsTo(Appointment::class); }
}