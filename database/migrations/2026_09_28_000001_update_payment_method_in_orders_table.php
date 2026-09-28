<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `orders` MODIFY COLUMN `payment_method` VARCHAR(50) NOT NULL DEFAULT 'khqr'");
        } else {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('payment_method', 50)->default('khqr')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `orders` MODIFY COLUMN `payment_method` ENUM('khqr', 'bank_transfer', 'cod', 'cash_store', 'cash_on_delivery', 'credit_card') NOT NULL DEFAULT 'khqr'");
        } else {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('payment_method', 50)->default('khqr')->change();
            });
        }
    }
};
