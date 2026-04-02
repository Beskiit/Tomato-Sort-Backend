<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\SortingSession;
use Illuminate\Http\Request;

class SortingSessionController extends Controller
{
    public function index(Appointment $appointment)
    {
        return response()->json(
            $appointment->sortingSession()->with('sortingLogs')->get()
        );
    }

    public function store(Request $request, Appointment $appointment)
    {
        abort_if($appointment->status !== 'confirmed', 422, 'Appointment must be confirmed before starting a session.');
        abort_if($appointment->sortingSession()->exists(), 422, 'A session already exists for this appointment.');

        $request->validate([
            'raspberry_pi_id' => 'required|string|max:50',
        ]);

        $session = SortingSession::create([
            'appointment_id'  => $appointment->id,
            'started_at'      => now(),
            'ripe_count'      => 0,
            'unripe_count'    => 0,
            'rotten_count'    => 0,
            'raspberry_pi_id' => $request->raspberry_pi_id,
            'session_status'  => 'in_progress',
        ]);

        $appointment->update(['status' => 'completed']);

        return response()->json($session, 201);
    }

    public function show(SortingSession $session)
    {
        return response()->json($session->load('sortingLogs', 'appointment.farmer.user'));
    }

    public function complete(Request $request, SortingSession $session)
    {
        abort_if($session->session_status !== 'in_progress', 422, 'Session is not in progress.');

        $session->update([
            'ended_at'       => now(),
            'session_status' => 'completed',
            'ripe_count'     => $session->sortingLogs()->where('tomato_classification', 'ripe')->count(),
            'unripe_count'   => $session->sortingLogs()->where('tomato_classification', 'unripe')->count(),
            'rotten_count'   => $session->sortingLogs()->where('tomato_classification', 'rotten')->count(),
        ]);

        return response()->json($session->fresh()->load('sortingLogs'));
    }

    public function update(Request $request, SortingSession $session) {}
    public function destroy(SortingSession $session) {}
}
