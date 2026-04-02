<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            return $this->adminDashboard();
        } elseif ($user->role === 'farmer') {
            return $this->farmerDashboard($user);
        } else {
            return $this->sorterDashboard($user);
        }
    }

    private function adminDashboard()
    {
        return response()->json([
            'total_appointments'   => Appointment::count(),
            'pending_appointments' => Appointment::where('status', 'pending')->count(),
            'confirmed_appointments' => Appointment::where('status', 'confirmed')->count(),
            'completed_appointments' => Appointment::where('status', 'completed')->count(),
            'total_users'          => User::where('role', '!=', 'admin')->count(),
            'total_farmers'        => User::where('role', 'farmer')->count(),
            'total_sorters'        => User::where('role', 'sorter')->count(),
            'recent_appointments'  => Appointment::with('farmer.user', 'sorter.user')
                ->latest()->take(5)->get(),
        ]);
    }

    private function farmerDashboard(User $user)
    {
        $farmerId = $user->farmer->id;
        return response()->json([
            'total_appointments'     => Appointment::where('farmer_id', $farmerId)->count(),
            'pending_appointments'   => Appointment::where('farmer_id', $farmerId)->where('status', 'pending')->count(),
            'confirmed_appointments' => Appointment::where('farmer_id', $farmerId)->where('status', 'confirmed')->count(),
            'completed_appointments' => Appointment::where('farmer_id', $farmerId)->where('status', 'completed')->count(),
            'recent_appointments'    => Appointment::where('farmer_id', $farmerId)
                ->with('sorter.user', 'sortingSession')
                ->latest()->take(5)->get(),
        ]);
    }

    private function sorterDashboard(User $user)
    {
        $sorterId = $user->sorter->id;
        return response()->json([
            'total_appointments'     => Appointment::where('sorter_id', $sorterId)->count(),
            'pending_appointments'   => Appointment::where('sorter_id', $sorterId)->where('status', 'pending')->count(),
            'confirmed_appointments' => Appointment::where('sorter_id', $sorterId)->where('status', 'confirmed')->count(),
            'completed_appointments' => Appointment::where('sorter_id', $sorterId)->where('status', 'completed')->count(),
            'recent_appointments'    => Appointment::where('sorter_id', $sorterId)
                ->with('farmer.user', 'sortingSession')
                ->latest()->take(5)->get(),
        ]);
    }
}
