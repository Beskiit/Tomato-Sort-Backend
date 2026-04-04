<?php
// ===== UserController.php =====
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Farmer;
use App\Models\Sorter;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('farmer', 'sorter')
            ->when($request->role, fn($q) => $q->where('role', $request->role))
            ->paginate(20);
        return response()->json($users);
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name'      => 'required|string|max:100',
            'email'          => 'required|email|unique:users,email',
            'password'       => 'required|string|min:8',
            'role'           => 'required|in:farmer,sorter,admin',
            'farm_name'      => 'required_if:role,farmer|string|max:150',
            'address'        => 'nullable|string',
            'location'       => 'required_if:role,sorter|string|max:200',
            'contact_number' => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'full_name'     => $request->full_name,
            'email'         => $request->email,
            'password_hash' => Hash::make($request->password),
            'role'          => $request->role,
        ]);

        if ($request->role === 'farmer') {
            Farmer::create(['user_id' => $user->id, 'farm_name' => $request->farm_name,
                'contact_number' => $request->contact_number, 'address' => $request->address]);
        } elseif ($request->role === 'sorter') {
            Sorter::create(['user_id' => $user->id, 'location' => $request->location,
                'contact_number' => $request->contact_number, 'is_available' => true]);
        }

        ActivityLogger::log(
            action:      'created',
            description: "Admin created new {$user->role} account for {$user->full_name} ({$user->email}).",
            modelType:   'User',
            modelId:     $user->id,
        );

        return response()->json($user->load('farmer', 'sorter'), 201);
    }

    public function show(User $user)
    {
        return response()->json($user->load('farmer', 'sorter'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'full_name' => 'sometimes|string|max:100',
            'email'     => "sometimes|email|unique:users,email,{$user->id}",
            'password'  => 'sometimes|string|min:8',
        ]);

        $old = $user->only('full_name', 'email');
        $user->update(array_filter([
            'full_name'     => $request->full_name,
            'email'         => $request->email,
            'password_hash' => $request->password ? Hash::make($request->password) : null,
        ]));
        $new = $user->only('full_name', 'email');

        ActivityLogger::log(
            action:      'updated',
            description: "Admin updated user account for {$user->full_name} ({$user->email}).",
            modelType:   'User',
            modelId:     $user->id,
            changes:     ['before' => $old, 'after' => $new],
        );

        return response()->json($user->fresh()->load('farmer', 'sorter'));
    }

    public function destroy(User $user)
    {
        ActivityLogger::log(
            action:      'deleted',
            description: "Admin deleted user account: {$user->full_name} ({$user->role}, {$user->email}).",
            modelType:   'User',
            modelId:     $user->id,
        );

        $user->delete();

        return response()->json(['message' => 'User deleted.']);
    }
}

// ===== SortingSessionController.php =====
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\SortingSession;
use App\Services\ActivityLogger;
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

        ActivityLogger::log(
            action:      'session_started',
            description: "{$request->user()->full_name} started sorting session #{$session->id} for appointment #{$appointment->id} using device {$request->raspberry_pi_id}.",
            modelType:   'SortingSession',
            modelId:     $session->id,
        );

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

        ActivityLogger::log(
            action:      'session_completed',
            description: "{$request->user()->full_name} completed sorting session #{$session->id}. Results: {$session->ripe_count} ripe, {$session->unripe_count} unripe, {$session->rotten_count} rotten.",
            modelType:   'SortingSession',
            modelId:     $session->id,
        );

        return response()->json($session->fresh()->load('sortingLogs'));
    }

    public function update(Request $request, SortingSession $session) {}
    public function destroy(SortingSession $session) {}
}
