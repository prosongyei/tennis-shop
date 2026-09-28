<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique()->index(); // e.g. ORD-20260905-10025
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Nullable for walk-in POS or guest
            $table->foreignId('cashier_id')->nullable()->constrained('users')->nullOnDelete(); // Set if processed at POS
            
            // Customer Info
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();
            $table->text('delivery_address')->nullable();
            $table->string('province_city')->default('Phnom Penh');
            $table->text('customer_note')->nullable();
            $table->enum('delivery_method', ['delivery', 'pickup'])->default('delivery');

            // Amounts
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('delivery_fee', 10, 2)->default(0.00);
            $table->decimal('total_amount', 10, 2);

            // Payment Details
            $table->string('payment_method', 50)->default('khqr');
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'cancelled', 'refunded'])->default('pending');
            $table->text('khqr_string')->nullable();
            $table->string('khqr_md5')->nullable()->index();
            $table->timestamp('khqr_expiration')->nullable();
            $table->string('payment_proof_image')->nullable();
            $table->string('bakong_hash')->nullable();
            $table->timestamp('paid_at')->nullable();

            // Order Status
            $table->enum('order_status', [
                'pending',
                'confirmed',
                'processing',
                'ready_pickup',
                'shipped',
                'delivered',
                'completed',
                'cancelled',
                'rejected',
                'refunded'
            ])->default('pending')->index();

            $table->enum('source', ['web', 'pos'])->default('web');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('product_name');
            $table->string('variant_name')->nullable();
            $table->string('sku')->nullable();
            $table->decimal('unit_price', 10, 2); // Saves historical price at time of purchase!
            $table->integer('quantity');
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('payment_method');
            $table->string('transaction_id')->nullable();
            $table->string('bakong_hash')->nullable();
            $table->string('proof_image')->nullable();
            $table->enum('status', ['pending', 'verified', 'failed', 'refunded'])->default('pending');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
