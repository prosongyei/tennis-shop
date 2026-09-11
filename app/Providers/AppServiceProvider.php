<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
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
        // Standard MySQL UTF8MB4 compatibility for online database hosting
        Schema::defaultStringLength(191);

        // Ensure TosLengSey logo directory exists in public/images/
        $logoDir = public_path('images');
        if (!is_dir($logoDir)) {
            @mkdir($logoDir, 0755, true);
        }

        // Fix database category icons so shoes never use shield
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('categories')) {
                \Illuminate\Support\Facades\DB::table('categories')
                    ->where('slug', 'badminton-shoes')
                    ->where('icon', 'shield')
                    ->update(['icon' => 'footprints']);
            }
        } catch (\Throwable $e) {
            // Silently ignore if DB not ready
        }

        // Share Wishlist count with all views
        view()->composer('*', function ($view) {
            $wishlist = session()->get('wishlist', []);
            $view->with('wishlistCount', is_array($wishlist) ? count($wishlist) : 0);
            $view->with('wishlistIds', is_array($wishlist) ? array_keys($wishlist) : []);
        });
    }
}
