<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = [];
        if ($request->user()->hasRole(['admin', 'owner'])) {
            $notifications = DatabaseNotification::latest()->paginate(20)->through(function ($n) {
                return [
                    'id' => $n->id,
                    'type' => $n->data['type'] ?? 'System Activity',
                    'message' => $n->data['message'] ?? 'Activity occurred',
                    'action_url' => $n->data['action_url'] ?? '#',
                    'time' => $n->created_at->diffForHumans(),
                    'read_at' => $n->read_at,
                ];
            });
        } else {
            $notifications = $request->user()->notifications()->paginate(20)->through(function ($n) {
                return [
                    'id' => $n->id,
                    'type' => $n->data['type'] ?? 'Notification',
                    'message' => $n->data['message'] ?? 'New notification',
                    'action_url' => $n->data['action_url'] ?? '#',
                    'time' => $n->created_at->diffForHumans(),
                    'read_at' => $n->read_at,
                ];
            });
        }

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
        ]);
    }

    public function markAsRead(Request $request, $id)
    {
        $notification = $request->user()->notifications()->where('id', $id)->first();
        if ($notification) {
            $notification->markAsRead();
        }
        return back();
    }
    
    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        return back();
    }
}
