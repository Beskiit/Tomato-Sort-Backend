<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $user  = $request->user();
        $query = ActivityLog::with('user')->latest('performed_at');

        // ── Role-based restriction ────────────────────────────────────────────
        // Farmers and sorters can only see their own activity
        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id)
                  ->whereNotIn('action', ['login_failed']);
        }

        // ── Filters (admin only gets full filter options) ─────────────────────
        if ($user->role === 'admin') {
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }
            if ($request->filled('model')) {
                $query->where('model_type', $request->model);
            }
            if ($request->filled('from')) {
                $query->whereDate('performed_at', '>=', $request->from);
            }
            if ($request->filled('to')) {
                $query->whereDate('performed_at', '<=', $request->to);
            }
        }

        // ── Shared filters ────────────────────────────────────────────────────
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('search')) {
            $query->where('description', 'like', "%{$request->search}%");
        }

        return response()->json($query->paginate(20));
    }

    public function show(Request $request, ActivityLog $activityLog)
    {
        $user = $request->user();

        // Non-admins can only view their own log entries
        if ($user->role !== 'admin') {
            abort_if($activityLog->user_id !== $user->id, 403, 'Access denied.');
        }

        return response()->json($activityLog->load('user'));
    }
}