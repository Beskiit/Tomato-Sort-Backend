<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = ['farmer_id', 'sorter_id', 'scheduled_date', 'scheduled_time', 'status', 'notes'];

    public function farmer() { return $this->belongsTo(Farmer::class); }
    public function sorter() { return $this->belongsTo(Sorter::class); }
    public function sortingSession() { return $this->hasOne(SortingSession::class); }
    public function notifications() { return $this->hasMany(Notification::class); }
}