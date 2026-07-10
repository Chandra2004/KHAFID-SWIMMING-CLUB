<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// PASTIKAN import yang ini:
use Illuminate\Support\Facades\View; 
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // --- Workaround untuk InfinityFree ---
        // Mengubah lokasi direktori temp ke folder storage Laravel yang pasti bisa ditulis (writable)
        $tmpDir = storage_path('app/tmp');
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0775, true);
        }
        putenv('TMPDIR=' . $tmpDir);
        @ini_set('sys_temp_dir', $tmpDir);
        @ini_set('upload_tmp_dir', $tmpDir);
        // -------------------------------------

        $isLocal = in_array(request()->getHost(), ['127.0.0.1', 'localhost']) || str_contains(request()->getHost(), '.test');
        if (!$isLocal || str_starts_with(config('app.url'), 'https://')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
            $_SERVER['HTTPS'] = 'on';
        }
        
        View::composer('*', function ($view) {
            $user = Auth::user();
            
            $data = [
                'user' => $user,
                'totalUnreadNotifications' => 0,
                'unreadNotifications' => collect([])
            ];

            if ($user) {
                $data['totalUnreadNotifications'] = Notification::where('user_uid', $user->uid)
                    ->where('is_read', false)
                    ->count();
                $data['unreadNotifications'] = Notification::where('user_uid', $user->uid)
                    ->where('is_read', false)
                    ->latest()
                    ->take(5)
                    ->get();
            }

            $view->with($data);
        });
    }
}
