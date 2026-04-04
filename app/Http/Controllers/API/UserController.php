<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Farmer;
use App\Models\Sorter;
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
            'farm_name'      => 'nullable|required_if:role,farmer|string|max:150',
            'address'        => 'nullable|string',
            'location'       => 'nullable|required_if:role,sorter|string|max:200',
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

        return response()->json($user->load('farmer', 'sorter'), 201);
    }

    public function show(User $user)
    {
        return response()->json($user->load('farmer', 'sorter'));
    }

    public function sorters()
    {
        $sorters = Sorter::with('user')
        ->where('is_available', true)
        ->get();
        return response()->json($sorters);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'full_name' => 'sometimes|string|max:100',
            'email'     => "sometimes|email|unique:users,email,{$user->id}",
            'password'  => 'sometimes|string|min:8',
        ]);

        $user->update(array_filter([
            'full_name'     => $request->full_name,
            'email'         => $request->email,
            'password_hash' => $request->password ? Hash::make($request->password) : null,
        ]));

        return response()->json($user->fresh()->load('farmer', 'sorter'));
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'User deleted.']);
    }
}
