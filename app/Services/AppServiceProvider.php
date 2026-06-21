<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Notifikasi;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('*', function ($view) {

            if (!auth()->check()) {
                return;
            }

            $notifikasi = Notifikasi::where('user_id', auth()->id())
                ->latest()
                ->take(5)
                ->get();

            $unreadCount = Notifikasi::where('user_id', auth()->id())
                ->where('dibaca', false)
                ->count();

            $view->with([
                'notifikasi' => $notifikasi,
                'unreadCount' => $unreadCount,
            ]);
        });
    }
}
