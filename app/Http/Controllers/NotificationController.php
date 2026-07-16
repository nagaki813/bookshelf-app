<?php

namespace App\Http\Controllers;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->get();

        return view('notifications.index', compact('notifications'));
    }

    public function read(string $notification)
    {
        $notification = auth()->user()
            ->notifications()
            ->where('id', $notification)
            ->firstOrFail();

        $notification->markAsRead();

        return redirect()
            ->route('notifications.index')
            ->with('success', '通知を既読にしました。');
    }
}
