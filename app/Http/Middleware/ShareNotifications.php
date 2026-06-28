<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class ShareNotifications
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            $unreadData = [
                'totalUnreadNotifications' => Notification::where('user_uid', $user->uid)
                    ->where('is_read', false)
                    ->count(),
                'unreadNotifications' => Notification::where('user_uid', $user->uid)
                    ->where('is_read', false)
                    ->latest()
                    ->take(5)
                    ->get(),
                'user' => $user
            ];

            View::share($unreadData);
        }

        return $next($request);
    }
}
