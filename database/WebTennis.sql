-- =====================================================================
-- Database: `WebTennis`
-- Compatible with: MySQL 5.7+, MySQL 8.0+, MariaDB 10.3+, phpMyAdmin, cPanel
-- Generated for: TosLengSey Badminton Web Store & POS System
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `WebTennis` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `WebTennis`;

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+07:00";

-- --------------------------------------------------------
-- Table structure for `migrations`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_09_05_000001_create_categories_table', 1),
(5, '2026_09_05_000002_create_brands_table', 1),
(6, '2026_09_05_000003_create_products_and_variants_table', 1),
(7, '2026_09_05_000004_create_carts_and_items_table', 1),
(8, '2026_09_05_000005_create_orders_and_payments_table', 1),
(9, '2026_09_05_000006_create_inventory_transactions_table', 1),
(10, '2026_09_05_000007_create_discounts_reviews_settings_table', 1);

-- --------------------------------------------------------
-- Table structure for `users`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `phone` varchar(30) NOT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT 'Phnom Penh',
  `password` varchar(255) NOT NULL,
  `role` enum('admin','cashier','customer') NOT NULL DEFAULT 'customer',
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_phone_index` (`phone`),
  KEY `users_role_status_index` (`role`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `address`, `city`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Store Administrator', 'admin@badminton.com', '85512888999', 'St. 2004, Sen Sok', 'Phnom Penh', '$2y$12$4mI65yP62/o4t32sO77ZleL/E3D4bX1ZpQfT3a16h3CvhU9V8/jC.', 'admin', 'active', NOW(), NOW()),
(2, 'Main Register Cashier', 'cashier@badminton.com', '85598777666', 'Toul Kork', 'Phnom Penh', '$2y$12$4mI65yP62/o4t32sO77ZleL/E3D4bX1ZpQfT3a16h3CvhU9V8/jC.', 'cashier', 'active', NOW(), NOW()),
(3, 'Sophea Kim', 'customer@badminton.com', '85577123456', '#45, St. 310, BKK1', 'Phnom Penh', '$2y$12$4mI65yP62/o4t32sO77ZleL/E3D4bX1ZpQfT3a16h3CvhU9V8/jC.', 'customer', 'active', NOW(), NOW());

-- --------------------------------------------------------
-- Table structure for `password_reset_tokens`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `sessions`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `categories`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `slug` varchar(160) NOT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `description`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Badminton Racquets', 'badminton-rackets', 'crosshair', 'Professional offensive, speed, and all-round badminton rackets.', 1, 1, NOW(), NOW()),
(2, 'Shuttlecocks', 'shuttlecocks', 'feather', 'Tournament grade goose feather and durable synthetic nylon shuttles.', 1, 2, NOW(), NOW()),
(3, 'Court Shoes', 'court-shoes', 'footprints', 'Non-marking gum rubber court shoes with dynamic shock absorption.', 1, 3, NOW(), NOW()),
(4, 'Bags & Backpacks', 'bags-backpacks', 'briefcase', 'Thermal tournament racket bags, duffels, and everyday court backpacks.', 1, 4, NOW(), NOW()),
(5, 'Strings & Grips', 'strings-grips', 'activity', 'High-repulsion strings, synthetic overgrips, towel grips, and cushions.', 1, 5, NOW(), NOW()),
(6, 'Apparel & Accessories', 'apparel-accessories', 'shirt', 'Breathable quick-dry tournament shirts, shorts, wristbands, and towels.', 1, 6, NOW(), NOW());

-- --------------------------------------------------------
-- Table structure for `brands`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `brands`;
CREATE TABLE `brands` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `slug` varchar(160) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `brands_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `brands` (`id`, `name`, `slug`, `logo`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Yonex', 'yonex', NULL, 'The global premier brand in badminton engineering and world championships.', 1, NOW(), NOW()),
(2, 'Victor', 'victor', NULL, 'High performance Taiwanese racquets, speed gear, and national team sponsor.', 1, NOW(), NOW()),
(3, 'Li-Ning', 'li-ning', NULL, 'Innovative materials and lightning speed attack frames.', 1, NOW(), NOW()),
(4, 'Mizuno', 'mizuno', NULL, 'Exceptional Japanese craftsmanship court shoes and rackets.', 1, NOW(), NOW()),
(5, 'Ashaway', 'ashaway', NULL, 'Industry leaders in high-tension MicroPower strings.', 1, NOW(), NOW());

-- --------------------------------------------------------
-- Table structure for `products`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `brand_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `sku` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount_price` decimal(10,2) DEFAULT NULL,
  `cost_price` decimal(10,2) DEFAULT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `min_stock_level` int(11) NOT NULL DEFAULT 5,
  `image` varchar(255) DEFAULT NULL,
  `specifications` json DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('active','draft','discontinued') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_slug_unique` (`slug`),
  UNIQUE KEY `products_sku_unique` (`sku`),
  KEY `products_category_id_foreign` (`category_id`),
  KEY `products_brand_id_foreign` (`brand_id`),
  CONSTRAINT `products_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `products` (`id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `description`, `price`, `discount_price`, `cost_price`, `stock_quantity`, `min_stock_level`, `image`, `specifications`, `is_featured`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Yonex Astrox 100ZZ (Kurenai)', 'yonex-astrox-100zz-kurenai', 'YON-AX100ZZ-KR', 'The ultimate head-heavy attack racquet used by Olympic & World Champion Viktor Axelsen. Features the Hyper Slim Shaft and Namd revolutionary graphite for hyper-steep power smashes.', 245.00, 229.00, 170.00, 18, 3, 'https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?w=800&auto=format&fit=crop&q=80', '{\"flex\": \"Extra Stiff\", \"frame\": \"HM Graphite / Namd / Tungsten / Black Micro Core\", \"balance\": \"Head Heavy (305mm)\", \"weight_grip\": \"3U (Avg. 88g) G5 / 4U (Avg. 83g) G5\", \"string_tension\": \"3U: 21-29 lbs, 4U: 20-28 lbs\"}', 1, 'active', NOW(), NOW()),
(2, 1, 1, 'Yonex Astrox 88D Pro (Gen 3 Silver/Black)', 'yonex-astrox-88d-pro-gen3', 'YON-AX88DP-G3', 'Designed specifically for rear-court doubles players who demand devastating smash angles and rapid rotation recovery.', 239.00, 219.00, 165.00, 14, 3, 'https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?w=800&auto=format&fit=crop&q=80', '{\"flex\": \"Stiff\", \"frame\": \"HM Graphite / CFR / Tungsten\", \"balance\": \"Head Heavy\", \"weight_grip\": \"4U (Avg. 83g) G5\", \"string_tension\": \"20-28 lbs\"}', 1, 'active', NOW(), NOW()),
(3, 1, 2, 'Victor Thruster Ryuga II Pro', 'victor-thruster-ryuga-ii-pro', 'VIC-TK-RYUGA2', 'The beast of attacking racquets equipped with WES 2.0 (Whippy Enhancement System) and FREE CORE synthetic handle for sharper rebound.', 225.00, 205.00, 150.00, 10, 2, 'https://images.unsplash.com/photo-1521537634581-0dced2fed2a8?w=800&auto=format&fit=crop&q=80', '{\"flex\": \"Stiff\", \"frame\": \"High Resilience Modulus Graphite + HARD CORED\", \"balance\": \"Heavy Head\", \"weight_grip\": \"4U / G5\", \"string_tension\": \"3U <= 32 lbs, 4U <= 31 lbs\"}', 1, 'active', NOW(), NOW()),
(4, 1, 1, 'Yonex Nanoflare 1000Z (Lightning Yellow)', 'yonex-nanoflare-1000z', 'YON-NF1000Z', 'World record smash racquet (565 km/h) engineered for lightning fast headlight swings with the sonic flare system and ultra PE fiber.', 235.00, 215.00, 160.00, 8, 2, 'https://images.unsplash.com/photo-1534158914592-062992fbe900?w=800&auto=format&fit=crop&q=80', '{\"flex\": \"Extra Stiff\", \"frame\": \"HM Graphite / NANOMETRIC DR / M40X\", \"balance\": \"Head Light\", \"weight_grip\": \"4U (83g) G5\", \"string_tension\": \"20-28 lbs\"}', 1, 'active', NOW(), NOW()),
(5, 1, 3, 'Li-Ning Halbertec 9000 (Olympic Edition)', 'li-ning-halbertec-9000', 'LN-HALB-9000', 'Wielded by Olympic Champion Yuta Watanabe. 6.6mm hard flexible shaft with Acc-Rif Tech for surgical drop shots and pinpoint clears.', 230.00, NULL, 165.00, 7, 2, 'https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?w=800&auto=format&fit=crop&q=80', '{\"flex\": \"Medium-Stiff\", \"frame\": \"Med Carbon Fiber + T1100\", \"balance\": \"Even Balance (295mm)\", \"weight_grip\": \"4U (84g) G5\", \"string_tension\": \"Up to 30 lbs\"}', 0, 'active', NOW(), NOW()),
(6, 2, 1, 'Yonex Aerosensa 50 (AS-50) Shuttlecocks', 'yonex-as-50-shuttlecocks', 'YON-AS50-DOZ', 'Official BWF Grade 1 tournament shuttlecocks made from specially selected premium goose feathers with 100% solid cork base.', 38.50, 36.00, 28.00, 45, 10, 'https://images.unsplash.com/photo-1613918108466-292b78a8ef95?w=800&auto=format&fit=crop&q=80', '{\"speed\": \"Speed 77 (Slow/Medium)\", \"material\": \"Premium Goose Feather\", \"quantity\": \"12 shuttles per tube (1 Dozen)\"}', 1, 'active', NOW(), NOW()),
(7, 2, 1, 'Yonex Mavis 350 Synthetic Nylon Shuttles', 'yonex-mavis-350-yellow', 'YON-M350-YL', 'Precision manufactured wing rib design nylon shuttles with genuine cork base. Lasts 4-5 times longer than regular feather shuttles.', 14.50, NULL, 9.50, 80, 15, 'https://images.unsplash.com/photo-1613918108466-292b78a8ef95?w=800&auto=format&fit=crop&q=80', '{\"speed\": \"Medium (Blue Band)\", \"color\": \"High-Visibility Yellow\", \"package\": \"6 Shuttles / Tube\"}', 0, 'active', NOW(), NOW()),
(8, 3, 1, 'Yonex Power Cushion 65 Z3 Men', 'yonex-power-cushion-65-z3', 'YON-SHB65Z3-WH', 'The gold standard in all-round badminton footwear. Power Cushion+ technology converts impact shock into propulsion power for explosive push-offs.', 140.00, 128.00, 90.00, 22, 5, 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=800&auto=format&fit=crop&q=80', '{\"upper\": \"Synthetic Fiber + Double Raschel Mesh\", \"midsole\": \"Synthetic Resin + Power Graphite Sheet\", \"outsole\": \"Radial Blade Non-Marking Rubber\"}', 1, 'active', NOW(), NOW()),
(9, 4, 1, 'Yonex Pro Tournament 9-Racquet Bag (Deep Blue)', 'yonex-pro-bag-9-racquet', 'YON-BAG92229-BL', 'Heavy-duty tournament thermal bag with dedicated thermo-guard racquet compartment, ventilated shoe vault, and ergonomic backpack straps.', 115.00, 99.00, 68.00, 12, 3, 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=800&auto=format&fit=crop&q=80', '{\"capacity\": \"9 Racquets + Shoes + Gear\", \"dimensions\": \"78 x 38 x 34 cm\", \"features\": \"Thermal guard, Shoe pocket, Dual padded straps\"}', 1, 'active', NOW(), NOW()),
(10, 5, 1, 'Yonex BG80 Power Badminton String (0.68mm)', 'yonex-bg80-power-string', 'YON-BG80P-068', 'High-modulus Vectran fibers wound around a strong multifilament core. Delivers crisp repulsion feel and explosive smashing power.', 11.50, 10.50, 6.00, 60, 15, 'https://images.unsplash.com/photo-1587280501635-68a0e82cd5ff?w=800&auto=format&fit=crop&q=80', '{\"gauge\": \"0.68mm / 22 GA\", \"length\": \"10m (33 ft) single set\", \"feel\": \"Hard hitting sensation\"}', 0, 'active', NOW(), NOW());

-- --------------------------------------------------------
-- Table structure for `product_variants`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `product_variants`;
CREATE TABLE `product_variants` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `variant_name` varchar(100) NOT NULL,
  `sku` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_variants_sku_unique` (`sku`),
  KEY `product_variants_product_id_foreign` (`product_id`),
  CONSTRAINT `product_variants_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `product_variants` (`id`, `product_id`, `variant_name`, `sku`, `price`, `stock`, `created_at`, `updated_at`) VALUES
(1, 1, '4U / G5 (83g)', 'YON-AX100ZZ-4UG5', 229.00, 12, NOW(), NOW()),
(2, 1, '3U / G5 (88g Heavy)', 'YON-AX100ZZ-3UG5', 229.00, 6, NOW(), NOW()),
(3, 2, '4U / G5', 'YON-AX88DP-4UG5', 219.00, 14, NOW(), NOW()),
(4, 3, '4U / G5', 'VIC-RYUGA2-4U', 205.00, 10, NOW(), NOW()),
(5, 4, '4U / G5', 'YON-NF1000Z-4U', 215.00, 8, NOW(), NOW()),
(6, 8, 'US 8.5 (EU 41)', 'SHB65Z3-41', 128.00, 6, NOW(), NOW()),
(7, 8, 'US 9.5 (EU 42.5)', 'SHB65Z3-425', 128.00, 8, NOW(), NOW()),
(8, 8, 'US 10.5 (EU 44)', 'SHB65Z3-44', 128.00, 5, NOW(), NOW());

-- --------------------------------------------------------
-- Table structure for `orders`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_number` varchar(50) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `cashier_id` bigint(20) UNSIGNED DEFAULT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_phone` varchar(30) NOT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `delivery_address` text DEFAULT NULL,
  `province_city` varchar(100) DEFAULT 'Phnom Penh',
  `customer_note` text DEFAULT NULL,
  `delivery_method` enum('delivery','pickup') NOT NULL DEFAULT 'delivery',
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('khqr','cash_delivery','cash_store','bank_transfer') NOT NULL,
  `payment_status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `order_status` enum('pending','confirmed','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `source` enum('web','pos') NOT NULL DEFAULT 'web',
  `khqr_string` text DEFAULT NULL,
  `khqr_md5` varchar(64) DEFAULT NULL,
  `khqr_expiration` timestamp NULL DEFAULT NULL,
  `bakong_hash` varchar(128) DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_order_number_unique` (`order_number`),
  KEY `orders_khqr_md5_index` (`khqr_md5`),
  KEY `orders_payment_status_index` (`payment_status`),
  KEY `orders_order_status_index` (`order_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `order_items`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `product_variant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `variant_name` varchar(100) DEFAULT NULL,
  `sku` varchar(100) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_foreign` (`order_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `payments`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `payment_method` varchar(50) NOT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `bakong_hash` varchar(128) DEFAULT NULL,
  `status` enum('pending','verified','failed','refunded') NOT NULL DEFAULT 'pending',
  `verified_by` bigint(20) UNSIGNED DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_order_id_foreign` (`order_id`),
  CONSTRAINT `payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `carts`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `carts`;
CREATE TABLE `carts` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `carts_user_id_index` (`user_id`),
  KEY `carts_session_id_index` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `cart_items`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `cart_items`;
CREATE TABLE `cart_items` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cart_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `product_variant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cart_items_cart_id_foreign` (`cart_id`),
  CONSTRAINT `cart_items_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `discounts`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `discounts`;
CREATE TABLE `discounts` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `name` varchar(150) NOT NULL,
  `type` enum('percentage','fixed') NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `min_spend` decimal(10,2) NOT NULL DEFAULT 0.00,
  `max_discount` decimal(10,2) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `starts_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `discounts_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `discounts` (`id`, `code`, `name`, `type`, `value`, `min_spend`, `is_active`, `starts_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'SMASH10', 'Grand Opening 10% Off', 'percentage', 10.00, 30.00, 1, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), NOW(), NOW()),
(2, 'WELCOME5', 'Welcome Gift $5 Off', 'fixed', 5.00, 50.00, 1, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), NOW(), NOW());

-- --------------------------------------------------------
-- Table structure for `settings`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `group` varchar(50) NOT NULL DEFAULT 'general',
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings` (`key`, `value`, `group`, `description`, `created_at`, `updated_at`) VALUES
('store_name', 'SMASH BADMINTON PRO STORE', 'general', 'Store display name', NOW(), NOW()),
('store_phone', '+855 12 888 999', 'general', 'Contact phone number', NOW(), NOW()),
('store_email', 'contact@smashbadminton.com', 'general', 'Customer service email', NOW(), NOW()),
('store_address', '#128 St. 2004, Sen Sok, Phnom Penh, Cambodia', 'general', 'Physical store location', NOW(), NOW()),
('khr_exchange_rate', '4100', 'general', 'USD to KHR exchange conversion rate', NOW(), NOW()),
('bakong_account_name', 'SORSONGYEI SOY', 'bakong', 'Registered merchant account title in Bakong', NOW(), NOW()),
('bakong_account_username', '010921061@aba', 'bakong', 'Registered Bakong account ID (e.g. 010921061@aba)', NOW(), NOW()),
('bakong_city', 'Phnom Penh', 'bakong', 'Merchant city for KHQR payload', NOW(), NOW()),
('currency_primary', 'USD', 'general', 'Base pricing currency', NOW(), NOW());

SET FOREIGN_KEY_CHECKS=1;
COMMIT;
