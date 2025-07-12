<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
    public function markRead($id)
    {
        $user = Auth::guard('pengusul')->user()
            ?? Auth::guard('staff')->user()
            ?? Auth::guard('admin')->user()
            ?? Auth::guard('kepala_sub')->user()
            ?? Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $notif = method_exists($user, 'notifications') ? $user->notifications()->where('id', $id)->first() : null;
        if ($notif && is_null($notif->read_at)) {
            $notif->markAsRead();
        }
        return response()->json(['success' => true]);
    }
} 