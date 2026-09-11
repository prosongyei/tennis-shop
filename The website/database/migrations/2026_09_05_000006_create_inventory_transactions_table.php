<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->enum('type', ['purchase', 'sale', 'adjustment', 'return', 'cancel'])->index();
            $table->integer('quantity_change'); // can be positive (+stock) or negative (-stock)
            $table->integer('previous_stock');
            $table->integer('new_stock');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Admin or Cashier who initiated change
            $table->string('reason')->nullable();
            $table->string('reference_type')->nullable(); // e.g., 'order', 'manual_adjustment'
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action'); // e.g. 'created_product', 'updated_price', 'verified_payment', 'adjusted_stock'
            $table->string('entity_type')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('inventory_transactions');
    }
};
