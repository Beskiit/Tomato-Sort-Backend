<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SortingSession;
use App\Models\SortingLog;
use Illuminate\Http\Request;

class SortingLogController extends Controller
{
    public function index(SortingSession $session)
    {
        return response()->json(
            $session->sortingLogs()->latest('logged_at')->paginate(50)
        );
    }

    public function store(Request $request, SortingSession $session)
    {
        abort_if($session->session_status !== 'in_progress', 422, 'Cannot log to a completed or failed session.');

        $request->validate([
            'tomato_classification' => 'required|in:ripe,unripe,rotten',
            'image_path'            => 'nullable|string|max:255',
            'ai_confidence'         => 'nullable|numeric|min:0|max:1',
        ]);

        $log = SortingLog::create([
            'session_id'            => $session->id,
            'logged_at'             => now(),
            'tomato_classification' => $request->tomato_classification,
            'image_path'            => $request->image_path,
            'ai_confidence'         => $request->ai_confidence,
        ]);

        // Update session counts in real time
        $session->increment("{$request->tomato_classification}_count");

        return response()->json($log, 201);
    }

    public function show(SortingLog $log)
    {
        return response()->json($log);
    }
}
