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

        // Guarantee default operational accounts exist with user password
        try {
            if (!Cache::has('core_users_verified_v3')) {
                if (Schema::hasTable('users')) {
                    User::updateOrCreate(
                        ['email' => 'admin@badminton.com'],
                        [
                            'name' => 'Store Administrator',
                            'phone' => '+855 12 888 999',
                            'address' => 'St. 2004, Sen Sok',
                            'city' => 'Phnom Penh',
                            'password' => Hash::make('MyTeamMy099'),
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
                            'password' => Hash::make('MyTeamMy099'),
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
                }

                // Guarantee default categories exist so dropdowns are never empty
                if (Schema::hasTable('categories') && \App\Models\Category::count() === 0) {
                    $defaultCategories = [
                        ['name' => 'Badminton Rackets', 'slug' => 'badminton-rackets', 'icon' => 'zap', 'description' => 'Professional & intermediate attack and control racquets.'],
                        ['name' => 'Badminton Shoes', 'slug' => 'badminton-shoes', 'icon' => 'footprints', 'description' => 'Court shoes with Power Cushion grip.'],
                        ['name' => 'Shuttlecocks', 'slug' => 'shuttlecocks', 'icon' => 'feather', 'description' => 'BWF approved tournament goose feather and nylon shuttlecocks.'],
                        ['name' => 'Strings & Tension', 'slug' => 'strings-tension', 'icon' => 'activity', 'description' => 'High-repulsion and durability strings.'],
                        ['name' => 'Bags & Backpacks', 'slug' => 'bags-backpacks', 'icon' => 'package', 'description' => 'Thermo-guard multi-racket bags.'],
                        ['name' => 'Grips & Accessories', 'slug' => 'grips-accessories', 'icon' => 'tag', 'description' => 'Tacky overgrips, towel grips, and accessories.'],
                        ['name' => 'Tennis & Court Gear', 'slug' => 'tennis-court-gear', 'icon' => 'award', 'description' => 'Tennis racquets, balls, and accessories.'],
                    ];
                    foreach ($defaultCategories as $c) {
                        \App\Models\Category::updateOrCreate(['slug' => $c['slug']], $c);
                    }
                }

                // Guarantee default brands exist so dropdowns are never empty
                if (Schema::hasTable('brands') && \App\Models\Brand::count() === 0) {
                    $defaultBrands = [
                        ['name' => 'Yonex', 'slug' => 'yonex', 'description' => 'World #1 equipment manufacturer.'],
                        ['name' => 'Victor', 'slug' => 'victor', 'description' => 'Premium performance gear trusted by world champions.'],
                        ['name' => 'Li-Ning', 'slug' => 'li-ning', 'description' => 'Innovative materials and lightning speed attack frames.'],
                        ['name' => 'Mizuno', 'slug' => 'mizuno', 'description' => 'Exceptional Japanese craftsmanship court shoes and rackets.'],
                        ['name' => 'Ashaway', 'slug' => 'ashaway', 'description' => 'Industry leaders in high-tension strings.'],
                        ['name' => 'Wilson', 'slug' => 'wilson', 'description' => 'World-class tennis and badminton equipment.'],
                        ['name' => 'Babolat', 'slug' => 'babolat', 'description' => 'High performance tournament racquets.'],
                        ['name' => 'Head', 'slug' => 'head', 'description' => 'Precision tennis racquets and court gear.'],
                    ];
                    foreach ($defaultBrands as $b) {
                        \App\Models\Brand::updateOrCreate(['slug' => $b['slug']], $b);
                    }
                }

                // Guarantee Telegram bot settings exist
                if (Schema::hasTable('settings')) {
                    \App\Models\Setting::updateOrCreate(
                        ['key' => 'telegram_bot_token'],
                        ['value' => '8851308730:AAFIs5Dyu4exg6mXw0JLN1jbOuQyvgucrPc', 'group' => 'telegram', 'description' => 'Confirmation Buddy Bot Token']
                    );
                    \App\Models\Setting::updateOrCreate(
                        ['key' => 'telegram_enabled'],
                        ['value' => '1', 'group' => 'telegram', 'description' => 'Enable Telegram Order & Payment Alerts']
                    );
                }

                Cache::put('core_users_verified_v3', true, now()->addDay());
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
