<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SortingLog extends Model
{
    protected $fillable = ['session_id', 'logged_at', 'tomato_classification', 'image_path', 'ai_confidence'];
    protected $casts    = ['logged_at' => 'datetime', 'ai_confidence' => 'float'];

    public function session() { return $this->belongsTo(SortingSession::class); }
}