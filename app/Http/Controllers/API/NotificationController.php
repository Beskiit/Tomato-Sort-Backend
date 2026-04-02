<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = Notification::where('user_id', $request->user()->id)
            ->with('appointment')
            ->latest('sent_at')
            ->paginate(20);

        return response()->json($notifications);
    }

    public function markRead(Request $request, Notification $notification)
    {
        abort_if($notification->user_id !== $request->user()->id, 403);
        $notification->update(['is_read' => true]);
        return response()->json($notification);
    }

    public function markAllRead(Request $request)
    {
        Notification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);
        return response()->json(['message' => 'All notifications marked as read.']);
    }
}
