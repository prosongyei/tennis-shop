<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed System Users
        User::updateOrCreate(['email' => 'admin@badminton.com'], [
            'name' => 'Store Administrator',
            'phone' => '+855 12 888 999',
            'address' => 'St. 2004, Sen Sok',
            'city' => 'Phnom Penh',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        User::updateOrCreate(['email' => 'cashier@badminton.com'], [
            'name' => 'Main Register Cashier',
            'phone' => '+855 98 777 666',
            'address' => 'Toul Kork',
            'city' => 'Phnom Penh',
            'password' => Hash::make('password123'),
            'role' => 'cashier',
            'status' => 'active',
        ]);

        User::updateOrCreate(['email' => 'customer@badminton.com'], [
            'name' => 'Sophea Kim',
            'phone' => '+855 77 123 456',
            'address' => '#45, St. 310, BKK1',
            'city' => 'Phnom Penh',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'status' => 'active',
        ]);

        // 2. Seed Categories
        $categories = [
            ['name' => 'Badminton Rackets', 'slug' => 'badminton-rackets', 'icon' => 'zap', 'description' => 'Professional & intermediate attack and control rackets.'],
            ['name' => 'Badminton Shoes', 'slug' => 'badminton-shoes', 'icon' => 'shield', 'description' => 'Court shoes with Power Cushion grip and lateral stability.'],
            ['name' => 'Shuttlecocks', 'slug' => 'shuttlecocks', 'icon' => 'feather', 'description' => 'BWF approved tournament goose feather and nylon shuttlecocks.'],
            ['name' => 'Strings & Tension', 'slug' => 'strings-tension', 'icon' => 'activity', 'description' => 'High-repulsion and durability strings with electronic stringing service.'],
            ['name' => 'Bags & Backpacks', 'slug' => 'bags-backpacks', 'icon' => 'package', 'description' => 'Thermo-guard multi-racket bags and court tournament backpacks.'],
            ['name' => 'Grips & Accessories', 'slug' => 'grips-accessories', 'icon' => 'tag', 'description' => 'Tacky overgrips, towel grips, wristbands, and stencil ink.'],
        ];

        $catModels = [];
        foreach ($categories as $c) {
            $catModels[$c['slug']] = Category::updateOrCreate(['slug' => $c['slug']], $c);
        }

        // 3. Seed Brands
        $brands = [
            ['name' => 'Yonex', 'slug' => 'yonex', 'description' => 'World #1 badminton equipment manufacturer.'],
            ['name' => 'Victor', 'slug' => 'victor', 'description' => 'Premium performance gear trusted by world champions.'],
            ['name' => 'Li-Ning', 'slug' => 'li-ning', 'description' => 'Innovative materials and lightning speed attack frames.'],
            ['name' => 'Mizuno', 'slug' => 'mizuno', 'description' => 'Exceptional Japanese craftsmanship court shoes and rackets.'],
            ['name' => 'Ashaway', 'slug' => 'ashaway', 'description' => 'Industry leaders in high-tension MicroPower strings.'],
        ];

        $brandModels = [];
        foreach ($brands as $b) {
            $brandModels[$b['slug']] = Brand::updateOrCreate(['slug' => $b['slug']], $b);
        }

        // 4. Seed Products
        $products = [
            [
                'name' => 'Yonex Astrox 100ZZ (Kurenai)',
                'slug' => 'yonex-astrox-100zz-kurenai',
                'sku' => 'YON-AX100ZZ-KR',
                'category_id' => $catModels['badminton-rackets']->id,
                'brand_id' => $brandModels['yonex']->id,
                'description' => 'The ultimate head-heavy attack racquet used by Olympic & World Champion Viktor Axelsen. Features the Hyper Slim Shaft and Namd revolutionary graphite for hyper-steep power smashes.',
                'price' => 245.00,
                'discount_price' => 229.00,
                'cost_price' => 170.00,
                'stock_quantity' => 18,
                'min_stock_level' => 3,
                'image' => 'https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?w=800&auto=format&fit=crop&q=80',
                'status' => 'active',
                'is_featured' => true,
                'specifications' => [
                    'flex' => 'Extra Stiff',
                    'frame' => 'HM Graphite / Namd / Tungsten / Black Micro Core',
                    'balance' => 'Head Heavy (305mm)',
                    'string_tension' => '3U: 21-29 lbs, 4U: 20-28 lbs',
                    'weight_grip' => '3U (Avg. 88g) G5 / 4U (Avg. 83g) G5',
                ],
                'variants' => [
                    ['variant_name' => '4U / G5 (83g)', 'sku' => 'YON-AX100ZZ-4UG5', 'price' => 229.00, 'stock' => 12],
                    ['variant_name' => '3U / G5 (88g Heavy)', 'sku' => 'YON-AX100ZZ-3UG5', 'price' => 229.00, 'stock' => 6],
                ],
            ],
            [
                'name' => 'Yonex Astrox 88D Pro (Gen 3 Silver/Black)',
                'slug' => 'yonex-astrox-88d-pro-gen3',
                'sku' => 'YON-AX88DP-G3',
                'category_id' => $catModels['badminton-rackets']->id,
                'brand_id' => $brandModels['yonex']->id,
                'description' => 'Designed specifically for rear-court doubles players who demand devastating smash angles and rapid rotation recovery.',
                'price' => 239.00,
                'discount_price' => 219.00,
                'cost_price' => 165.00,
                'stock_quantity' => 14,
                'min_stock_level' => 3,
                'image' => 'https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?w=800&auto=format&fit=crop&q=80',
                'status' => 'active',
                'is_featured' => true,
                'specifications' => [
                    'flex' => 'Stiff',
                    'frame' => 'HM Graphite / CFR / Tungsten',
                    'balance' => 'Head Heavy',
                    'string_tension' => '20-28 lbs',
                    'weight_grip' => '4U (Avg. 83g) G5',
                ],
                'variants' => [
                    ['variant_name' => '4U / G5', 'sku' => 'YON-AX88DP-4UG5', 'price' => 219.00, 'stock' => 14],
                ],
            ],
            [
                'name' => 'Victor Thruster Ryuga II Pro',
                'slug' => 'victor-thruster-ryuga-ii-pro',
                'sku' => 'VIC-TK-RYUGA2',
                'category_id' => $catModels['badminton-rackets']->id,
                'brand_id' => $brandModels['victor']->id,
                'description' => 'The beast of attacking racquets equipped with WES 2.0 (Whippy Enhancement System) and FREE CORE synthetic handle for sharper rebound.',
                'price' => 225.00,
                'discount_price' => 205.00,
                'cost_price' => 150.00,
                'stock_quantity' => 10,
                'min_stock_level' => 2,
                'image' => 'https://images.unsplash.com/photo-1521537634581-0dced2fed2a8?w=800&auto=format&fit=crop&q=80',
                'status' => 'active',
                'is_featured' => true,
                'specifications' => [
                    'flex' => 'Stiff',
                    'frame' => 'High Resilience Modulus Graphite + HARD CORED',
                    'balance' => 'Heavy Head',
                    'string_tension' => '3U <= 32 lbs, 4U <= 31 lbs',
                    'weight_grip' => '4U / G5',
                ],
                'variants' => [
                    ['variant_name' => '4U / G5', 'sku' => 'VIC-RYUGA2-4U', 'price' => 205.00, 'stock' => 10],
                ],
            ],
            [
                'name' => 'Yonex Nanoflare 1000Z (Lightning Yellow)',
                'slug' => 'yonex-nanoflare-1000z',
                'sku' => 'YON-NF1000Z',
                'category_id' => $catModels['badminton-rackets']->id,
                'brand_id' => $brandModels['yonex']->id,
                'description' => 'World record smash racquet (565 km/h) engineered for lightning fast headlight swings with the sonic flare system and ultra PE fiber.',
                'price' => 235.00,
                'discount_price' => 215.00,
                'cost_price' => 160.00,
                'stock_quantity' => 8,
                'min_stock_level' => 2,
                'image' => 'https://images.unsplash.com/photo-1534158914592-062992fbe900?w=800&auto=format&fit=crop&q=80',
                'status' => 'active',
                'is_featured' => true,
                'specifications' => [
                    'flex' => 'Extra Stiff',
                    'frame' => 'HM Graphite / NANOMETRIC DR / M40X',
                    'balance' => 'Head Light',
                    'string_tension' => '20-28 lbs',
                    'weight_grip' => '4U (83g) G5',
                ],
                'variants' => [
                    ['variant_name' => '4U / G5', 'sku' => 'YON-NF1000Z-4U', 'price' => 215.00, 'stock' => 8],
                ],
            ],
            [
                'name' => 'Li-Ning Halbertec 9000 (Olympic Edition)',
                'slug' => 'li-ning-halbertec-9000',
                'sku' => 'LN-HALB-9000',
                'category_id' => $catModels['badminton-rackets']->id,
                'brand_id' => $brandModels['li-ning']->id,
                'description' => 'Wielded by Olympic Champion Yuta Watanabe. 6.6mm hard flexible shaft with Acc-Rif Tech for surgical drop shots and pinpoint clears.',
                'price' => 230.00,
                'discount_price' => null,
                'cost_price' => 165.00,
                'stock_quantity' => 7,
                'min_stock_level' => 2,
                'image' => 'https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?w=800&auto=format&fit=crop&q=80',
                'status' => 'active',
                'is_featured' => false,
                'specifications' => [
                    'flex' => 'Medium-Stiff',
                    'frame' => 'Med High Carbon Graphite + T1100',
                    'balance' => 'Even Balance (295mm)',
                    'string_tension' => 'Up to 30 lbs',
                    'weight_grip' => '4U / G5',
                ],
                'variants' => [],
            ],
            [
                'name' => 'Yonex Power Cushion 65Z3 Court Shoes (White/Red)',
                'slug' => 'yonex-power-cushion-65z3',
                'sku' => 'YON-SHB-65Z3',
                'category_id' => $catModels['badminton-shoes']->id,
                'brand_id' => $brandModels['yonex']->id,
                'description' => 'The all-time preferred court shoe of world tour players. Power Cushion+ absorbs shock then reverses the impact energy for smooth footwork transition.',
                'price' => 149.00,
                'discount_price' => 135.00,
                'cost_price' => 95.00,
                'stock_quantity' => 25,
                'min_stock_level' => 5,
                'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=800&auto=format&fit=crop&q=80',
                'status' => 'active',
                'is_featured' => true,
                'specifications' => [
                    'upper' => 'Synthetic Leather / Double Russel Mesh',
                    'midsole' => 'Synthetic Resin + Power Cushion Plus',
                    'outsole' => 'Radial Blade Non-Marking Rubber',
                ],
                'variants' => [
                    ['variant_name' => 'Size US 8.5 (EU 41)', 'sku' => 'YON-65Z3-41', 'price' => 135.00, 'stock' => 8],
                    ['variant_name' => 'Size US 9.5 (EU 42.5)', 'sku' => 'YON-65Z3-425', 'price' => 135.00, 'stock' => 10],
                    ['variant_name' => 'Size US 10.5 (EU 44)', 'sku' => 'YON-65Z3-44', 'price' => 135.00, 'stock' => 7],
                ],
            ],
            [
                'name' => 'Victor P9200III Professional Court Shoes',
                'slug' => 'victor-p9200iii-shoes',
                'sku' => 'VIC-SH-P9200',
                'category_id' => $catModels['badminton-shoes']->id,
                'brand_id' => $brandModels['victor']->id,
                'description' => 'Renowned for world-class heel support, HYPEREVA midsole cushioning, and carbon-fiber torsion plate for extreme tournament lunges.',
                'price' => 139.00,
                'discount_price' => 125.00,
                'cost_price' => 85.00,
                'stock_quantity' => 16,
                'min_stock_level' => 4,
                'image' => 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=800&auto=format&fit=crop&q=80',
                'status' => 'active',
                'is_featured' => false,
                'specifications' => [
                    'upper' => 'Microfiber PU Leather + V-Tough',
                    'midsole' => 'HYPEREVA + Solid EVA + Carbon Sheet',
                    'outsole' => 'VSR Rubber',
                ],
                'variants' => [
                    ['variant_name' => 'Size EU 41', 'sku' => 'VIC-P9200-41', 'price' => 125.00, 'stock' => 6],
                    ['variant_name' => 'Size EU 42', 'sku' => 'VIC-P9200-42', 'price' => 125.00, 'stock' => 6],
                    ['variant_name' => 'Size EU 43', 'sku' => 'VIC-P9200-43', 'price' => 125.00, 'stock' => 4],
                ],
            ],
            [
                'name' => 'Yonex Aerosensa 50 (AS-50) Shuttlecocks (Dozen)',
                'slug' => 'yonex-aerosensa-50-shuttlecocks',
                'sku' => 'YON-AS50-TUBE',
                'category_id' => $catModels['shuttlecocks']->id,
                'brand_id' => $brandModels['yonex']->id,
                'description' => 'The official tournament shuttlecock of the Olympic Games and BWF Super Series. Crafted strictly from premium Grade A goose feathers and 100% natural solid cork.',
                'price' => 44.00,
                'discount_price' => 41.50,
                'cost_price' => 32.00,
                'stock_quantity' => 60,
                'min_stock_level' => 10,
                'image' => 'https://images.unsplash.com/photo-1613918108466-292b78a8ef95?w=800&auto=format&fit=crop&q=80',
                'status' => 'active',
                'is_featured' => true,
                'specifications' => [
                    'feather' => 'Special Grade A Goose Feather',
                    'flight' => 'A+ Consistent International Standard',
                    'speed' => 'Speed 77 (Phnom Penh climate)',
                    'quantity' => '12 Shuttlecocks per Tube',
                ],
                'variants' => [],
            ],
            [
                'name' => 'Yonex BG80 Power Badminton String (0.68mm)',
                'slug' => 'yonex-bg80-power-string',
                'sku' => 'YON-BG80P-SET',
                'category_id' => $catModels['strings-tension']->id,
                'brand_id' => $brandModels['yonex']->id,
                'description' => 'The combination of YONEX original high-intensity nylon multifilament and high-modulus Vectran provides a solid feel and powerful smash for hard hitters.',
                'price' => 12.50,
                'discount_price' => 11.00,
                'cost_price' => 7.00,
                'stock_quantity' => 120,
                'min_stock_level' => 20,
                'image' => 'https://images.unsplash.com/photo-1613918108466-292b78a8ef95?w=800&auto=format&fit=crop&q=80',
                'status' => 'active',
                'is_featured' => false,
                'specifications' => [
                    'gauge' => '0.68mm / 22 GA',
                    'length' => '10m (33ft)',
                    'feeling' => 'Hard Feeling, Maximum Power',
                ],
                'variants' => [
                    ['variant_name' => 'White', 'sku' => 'YON-BG80P-WH', 'price' => 11.00, 'stock' => 70],
                    ['variant_name' => 'Neon Orange', 'sku' => 'YON-BG80P-OR', 'price' => 11.00, 'stock' => 50],
                ],
            ],
            [
                'name' => 'Yonex Pro 9-Racket Tournament Bag (Cobalt Blue)',
                'slug' => 'yonex-pro-9-racket-bag',
                'sku' => 'YON-BAG92229',
                'category_id' => $catModels['bags-backpacks']->id,
                'brand_id' => $brandModels['yonex']->id,
                'description' => 'Tour-level thermo-guard lining protects delicate string tensions against heat. Separate ventilated shoe pocket and dual padded ergonomic backpack straps.',
                'price' => 120.00,
                'discount_price' => 105.00,
                'cost_price' => 70.00,
                'stock_quantity' => 12,
                'min_stock_level' => 3,
                'image' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=800&auto=format&fit=crop&q=80',
                'status' => 'active',
                'is_featured' => true,
                'specifications' => [
                    'capacity' => '9 Racquets + Shoes + Apparel',
                    'dimensions' => '78 x 38 x 33 cm',
                    'material' => 'Polyester 80%, PU 20% + Thermo Guard',
                ],
                'variants' => [],
            ],
            [
                'name' => 'Yonex Super Grap Overgrip AC102EX (Pack of 3)',
                'slug' => 'yonex-super-grap-ac102ex-3pack',
                'sku' => 'YON-AC102EX-3P',
                'category_id' => $catModels['grips-accessories']->id,
                'brand_id' => $brandModels['yonex']->id,
                'description' => 'Since launch in 1987, YONEX Super Grap has sold enough grips to wrap around the earth 4 times! Unbeatable absorbency and tacky control.',
                'price' => 7.00,
                'discount_price' => 6.00,
                'cost_price' => 3.50,
                'stock_quantity' => 150,
                'min_stock_level' => 30,
                'image' => 'https://images.unsplash.com/photo-1613918108466-292b78a8ef95?w=800&auto=format&fit=crop&q=80',
                'status' => 'active',
                'is_featured' => false,
                'specifications' => [
                    'width' => '25mm',
                    'length' => '1,200mm',
                    'thickness' => '0.6mm',
                    'material' => 'Polyurethane (Tacky feel)',
                ],
                'variants' => [
                    ['variant_name' => 'Yellow', 'sku' => 'AC102-YL', 'price' => 6.00, 'stock' => 50],
                    ['variant_name' => 'Black', 'sku' => 'AC102-BK', 'price' => 6.00, 'stock' => 50],
                    ['variant_name' => 'White', 'sku' => 'AC102-WH', 'price' => 6.00, 'stock' => 50],
                ],
            ],
        ];

        foreach ($products as $pData) {
            $variants = $pData['variants'] ?? [];
            unset($pData['variants']);

            $product = Product::updateOrCreate(['sku' => $pData['sku']], $pData);

            foreach ($variants as $v) {
                ProductVariant::updateOrCreate(
                    ['sku' => $v['sku']],
                    [
                        'product_id' => $product->id,
                        'variant_name' => $v['variant_name'],
                        'price_override' => $v['price'] ?? null,
                        'stock_quantity' => $v['stock'],
                    ]
                );
            }
        }

        // 5. Seed Store Settings
        $settings = [
            'store_name' => 'TosLengSey Tennis & Badminton Pro Store',
            'store_phone' => '+855 96 785 5710',
            'store_email' => 'contact@toslengsey.com',
            'store_address' => '#128 St. 2004, Sen Sok, Phnom Penh, Cambodia',
            'khr_exchange_rate' => '4100',
            'bakong_account_name' => env('BAKONG_ACCOUNT_NAME', env('BAKONG_MERCHANT_NAME', 'SORSONGYEI SOY')),
            'bakong_account_username' => env('BAKONG_ACCOUNT_USERNAME', env('BAKONG_ACCOUNT_ID', '010921061@aba')),
            'bakong_account_id_usd' => env('BAKONG_ACCOUNT_ID_USD', '010921061@aba'),
            'bakong_account_id_khr' => env('BAKONG_ACCOUNT_ID_KHR', '010921065@aba'),
            'bakong_phone_number' => env('BAKONG_PHONE_NUMBER', '010921061'),
            'bakong_city' => env('BAKONG_CITY', env('BAKONG_MERCHANT_CITY', 'Phnom Penh')),
            'bakong_access_token' => env('BAKONG_ACCESS_TOKEN', env('BAKONG_API_TOKEN', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJkYXRhIjp7ImlkIjoiM2VmM2ExOWQ1NDJhNDRjMiJ9LCJpYXQiOjE3ODg5MzA5MjIsImV4cCI6MTc5NjcwNjkyMn0.-pkrvjCi8wSX8A0zLtdPGtmdztgjW_lyDGzL4tA5NR8')),
            'bakong_api_url' => env('BAKONG_API_URL', 'https://api-bakong.nbc.gov.kh/v1/check_transaction_by_md5'),
            'currency_primary' => 'USD',
        ];

        foreach ($settings as $key => $val) {
            Setting::set($key, $val);
        }

        // 6. Seed Discount Coupons
        Discount::updateOrCreate(['code' => 'SMASH10'], [
            'name' => 'Grand Opening 10% Off',
            'type' => 'percentage',
            'value' => 10.00,
            'min_spend' => 30.00,
            'is_active' => true,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonths(6),
        ]);

        Discount::updateOrCreate(['code' => 'WELCOME5'], [
            'name' => 'Welcome Gift $5 Off',
            'type' => 'fixed',
            'value' => 5.00,
            'min_spend' => 50.00,
            'is_active' => true,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonths(6),
        ]);
    }
}
