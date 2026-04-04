<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'description',
        'changes',
        'ip_address',
        'performed_at',
    ];

    protected $casts = [
        'changes'      => 'array',
        'performed_at' => 'datetime',
    ];

    const CREATED_AT = 'performed_at';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
