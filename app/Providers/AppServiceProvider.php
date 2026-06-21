<?php

namespace App\Providers;

use App\Models\Notifikasi;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        View::composer('*', function ($view) {
            if (! auth()->check()) {
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
