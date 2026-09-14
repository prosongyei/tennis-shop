<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        // Force HTTPS when behind reverse proxy, in production, or on Railway
        if (app()->environment('production') || 
            request()->header('x-forwarded-proto') === 'https' || 
            (request()->server('HTTP_X_FORWARDED_PROTO') === 'https') ||
            str_contains(request()->getHttpHost(), 'railway.app') || 
            env('FORCE_HTTPS', false)) {
            URL::forceScheme('https');
        }

        // Standard MySQL UTF8MB4 compatibility for online database hosting
        Schema::defaultStringLength(191);

        // Ensure TosLengSey logo directory exists in public/images/
        $logoDir = public_path('images');
        if (!is_dir($logoDir)) {
            @mkdir($logoDir, 0755, true);
        }

        // Guarantee default operational accounts exist with password123
        try {
            if (!Cache::has('core_users_verified_v1')) {
                if (Schema::hasTable('users')) {
                    User::updateOrCreate(
                        ['email' => 'admin@badminton.com'],
                        [
                            'name' => 'Store Administrator',
                            'phone' => '+855 12 888 999',
                            'address' => 'St. 2004, Sen Sok',
                            'city' => 'Phnom Penh',
                            'password' => Hash::make('password123'),
                            'role' => 'admin',
                            'status' => 'active',
                        ]
                    );

                    User::updateOrCreate(
                        ['email' => 'cashier@badminton.com'],
                        [
                            'name' => 'Main Register Cashier',
                            'phone' => '+855 98 777 666',
                            'address' => 'Toul Kork',
                            'city' => 'Phnom Penh',
                            'password' => Hash::make('password123'),
                            'role' => 'cashier',
                            'status' => 'active',
                        ]
                    );

                    User::updateOrCreate(
                        ['email' => 'customer@badminton.com'],
                        [
                            'name' => 'Sophea Kim',
                            'phone' => '+855 77 123 456',
                            'address' => '#45, St. 310, BKK1',
                            'city' => 'Phnom Penh',
                            'password' => Hash::make('password123'),
                            'role' => 'customer',
                            'status' => 'active',
                        ]
                    );

                    Cache::put('core_users_verified_v1', true, now()->addDay());
                }
            }
        } catch (\Throwable $e) {
            // Silently ignore if DB connection isn't ready
        }

        // Share Wishlist count with all views
        view()->composer('*', function ($view) {
            $wishlist = session()->get('wishlist', []);
            $view->with('wishlistCount', is_array($wishlist) ? count($wishlist) : 0);
            $view->with('wishlistIds', is_array($wishlist) ? array_keys($wishlist) : []);
        });
    }
}
