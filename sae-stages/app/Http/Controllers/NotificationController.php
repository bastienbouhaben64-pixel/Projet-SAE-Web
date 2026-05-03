<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->paginate(30);
        return view('notifications.index', compact('notifications'));
    }

    public function markRead(Notification $notification, Request $request)
    {
        abort_unless($notification->user_id === $request->user()->id, 403);
        $notification->update(['read_at' => now()]);
        return $notification->url ? redirect($notification->url) : back();
    }

    public function markAllRead(Request $request)
    {
        $request->user()->notifications()->whereNull('read_at')->update(['read_at' => now()]);
        return back()->with('status', 'Notifications marquées comme lues.');
    }
}
