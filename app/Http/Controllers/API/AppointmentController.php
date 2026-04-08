<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Notification;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $user  = $request->user();
        $query = Appointment::with(['farmer.user', 'sorter.user', 'sortingSession']);
        $perPage = (int) $request->integer('per_page', 15);
        $perPage = max(1, min($perPage, 200));

        if ($user->role === 'farmer') {
            $query->where('farmer_id', $user->farmer->id);
        } elseif ($user->role === 'sorter') {
            $query->where('sorter_id', $user->sorter->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->boolean('has_session')) {
            $query->whereHas('sortingSession');
        }

        return response()->json($query->latest()->paginate($perPage));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sorter_id'      => 'required|exists:sorters,id',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'scheduled_time' => 'required|date_format:H:i',
            'notes'          => 'nullable|string',
        ]);

        $user = $request->user();
        abort_if($user->role !== 'farmer', 403, 'Only farmers can book appointments.');

        $appointment = Appointment::create([
            'farmer_id'      => $user->farmer->id,
            'sorter_id'      => $request->sorter_id,
            'scheduled_date' => $request->scheduled_date,
            'scheduled_time' => $request->scheduled_time,
            'status'         => 'pending',
            'notes'          => $request->notes,
        ]);

        $sorterUserId = $appointment->sorter->user_id;
        Notification::create([
            'user_id'        => $sorterUserId,
            'appointment_id' => $appointment->id,
            'message'        => "New appointment booked by {$user->full_name} on {$appointment->scheduled_date} at {$appointment->scheduled_time}.",
            'is_read'        => false,
        ]);

        ActivityLogger::log(
            action:      'created',
            description: "{$user->full_name} booked appointment #{$appointment->id} on {$appointment->scheduled_date} at {$appointment->scheduled_time}.",
            modelType:   'Appointment',
            modelId:     $appointment->id,
        );

        return response()->json($appointment->load('farmer.user', 'sorter.user'), 201);
    }

    public function show(Appointment $appointment)
    {
        $this->authorizeAppointment($appointment);
        return response()->json(
            $appointment->load('farmer.user', 'sorter.user', 'sortingSession.sortingLogs')
        );
    }

    public function update(Request $request, Appointment $appointment)
    {
        $this->authorizeAppointment($appointment);
        abort_if($appointment->status !== 'pending', 422, 'Only pending appointments can be edited.');

        $request->validate([
            'scheduled_date' => 'sometimes|date|after_or_equal:today',
            'scheduled_time' => 'sometimes|date_format:H:i',
            'notes'          => 'nullable|string',
        ]);

        $old = $appointment->only('scheduled_date', 'scheduled_time', 'notes');
        $appointment->update($request->only('scheduled_date', 'scheduled_time', 'notes'));
        $new = $appointment->only('scheduled_date', 'scheduled_time', 'notes');

        ActivityLogger::log(
            action:      'updated',
            description: "{$request->user()->full_name} updated appointment #{$appointment->id}.",
            modelType:   'Appointment',
            modelId:     $appointment->id,
            changes:     ['before' => $old, 'after' => $new],
        );

        return response()->json($appointment->fresh()->load('farmer.user', 'sorter.user'));
    }

    public function destroy(Appointment $appointment)
    {
        $this->authorizeAppointment($appointment);
        abort_if($appointment->status === 'completed', 422, 'Completed appointments cannot be deleted.');

        ActivityLogger::log(
            action:      'deleted',
            description: request()->user()->full_name . " cancelled appointment #{$appointment->id} scheduled on {$appointment->scheduled_date}.",
            modelType:   'Appointment',
            modelId:     $appointment->id,
        );

        $appointment->delete();

        return response()->json(['message' => 'Appointment cancelled.']);
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $request->validate([
            'status' => 'required|in:confirmed,cancelled,completed',
        ]);

        $user = $request->user();
        abort_if($user->role === 'farmer', 403, 'Farmers cannot change appointment status.');

        $oldStatus = $appointment->status;
        $appointment->update(['status' => $request->status]);

        Notification::create([
            'user_id'        => $appointment->farmer->user_id,
            'appointment_id' => $appointment->id,
            'message'        => "Your appointment on {$appointment->scheduled_date} has been {$request->status}.",
            'is_read'        => false,
        ]);

        ActivityLogger::log(
            action:      'status_changed',
            description: "{$user->full_name} changed appointment #{$appointment->id} status from {$oldStatus} to {$request->status}.",
            modelType:   'Appointment',
            modelId:     $appointment->id,
            changes:     ['before' => ['status' => $oldStatus], 'after' => ['status' => $request->status]],
        );

        return response()->json($appointment->fresh()->load('farmer.user', 'sorter.user'));
    }

    private function authorizeAppointment(Appointment $appointment)
    {
        $user = request()->user();
        if ($user->role === 'farmer') {
            abort_if($appointment->farmer_id !== $user->farmer->id, 403);
        } elseif ($user->role === 'sorter') {
            abort_if($appointment->sorter_id !== $user->sorter->id, 403);
        }
    }
}
