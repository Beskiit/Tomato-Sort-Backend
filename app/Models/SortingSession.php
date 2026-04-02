<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SortingSession extends Model
{
    protected $fillable = [
        'appointment_id', 'started_at', 'ended_at',
        'ripe_count', 'unripe_count', 'rotten_count',
        'raspberry_pi_id', 'session_status'
    ];
    protected $casts = ['started_at' => 'datetime', 'ended_at' => 'datetime'];

    public function appointment() { return $this->belongsTo(Appointment::class); }
    public function sortingLogs() { return $this->hasMany(SortingLog::class, 'session_id'); }
}