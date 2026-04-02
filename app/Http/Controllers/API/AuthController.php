<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Farmer;
use App\Models\Sorter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'full_name'      => 'required|string|max:100',
            'email'          => 'required|email|unique:users,email',
            'password'       => 'required|string|min:8|confirmed',
            'role'           => 'required|in:farmer,sorter',
            // Farmer fields
            'farm_name'      => 'required_if:role,farmer|string|max:150',
            'address'        => 'nullable|string',
            // Sorter fields
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
            Farmer::create([
                'user_id'        => $user->id,
                'farm_name'      => $request->farm_name,
                'contact_number' => $request->contact_number,
                'address'        => $request->address,
            ]);
        } elseif ($request->role === 'sorter') {
            Sorter::create([
                'user_id'        => $user->id,
                'location'       => $request->location,
                'contact_number' => $request->contact_number,
                'is_available'   => true,
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Account created successfully.',
            'token'   => $token,
            'user'    => $user->load('farmer', 'sorter'),
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password_hash)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $user->load('farmer', 'sorter'),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user()->load('farmer', 'sorter'));
    }
}
