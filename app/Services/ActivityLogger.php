<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public static function log(
        string  $action,
        string  $description,
        ?string $modelType = null,
        ?int    $modelId   = null,
        ?array  $changes   = null,
    ): void {
        $request = app(Request::class);

        ActivityLog::create([
            'user_id'      => Auth::id(),
            'action'       => $action,
            'model_type'   => $modelType,
            'model_id'     => $modelId,
            'description'  => $description,
            'changes'      => $changes,
            'ip_address'   => $request->ip(),
            'performed_at' => now(),
        ]);
    }
}
