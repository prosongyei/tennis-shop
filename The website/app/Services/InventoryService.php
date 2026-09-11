<?php

namespace App\Services;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\ProductVariant;
use Exception;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Deduct inventory for a product / variant
     */
    public function deductStock(Product $product, ?ProductVariant $variant, int $quantity, string $reason = 'Customer Order', ?string $refType = 'order', ?int $refId = null): InventoryTransaction
    {
        if ($quantity <= 0) {
            throw new Exception("Invalid quantity for stock deduction.");
        }

        return DB::transaction(function () use ($product, $variant, $quantity, $reason, $refType, $refId) {
            if ($variant) {
                // Deduct from variant
                if ($variant->stock_quantity < $quantity) {
                    throw new Exception("Insufficient stock for {$product->name} ({$variant->variant_name}). Available: {$variant->stock_quantity}");
                }

                $previousStock = $variant->stock_quantity;
                $newStock = $previousStock - $quantity;

                $variant->update(['stock_quantity' => $newStock]);
                $product->decrement('stock_quantity', $quantity);

                $transaction = InventoryTransaction::create([
                    'product_id' => $product->id,
                    'product_variant_id' => $variant->id,
                    'type' => 'sale',
                    'quantity_change' => -$quantity,
                    'previous_stock' => $previousStock,
                    'new_stock' => $newStock,
                    'user_id' => auth()->id(),
                    'reason' => $reason,
                    'reference_type' => $refType,
                    'reference_id' => $refId,
                ]);

                // Check low stock alert
                if ($newStock <= $product->min_stock_level) {
                    app(TelegramService::class)->sendLowStockAlert($product, $variant, $newStock);
                }

                return $transaction;
            } else {
                // Deduct from main product
                if ($product->stock_quantity < $quantity) {
                    throw new Exception("Insufficient stock for {$product->name}. Available: {$product->stock_quantity}");
                }

                $previousStock = $product->stock_quantity;
                $newStock = $previousStock - $quantity;

                $product->update(['stock_quantity' => $newStock]);

                $transaction = InventoryTransaction::create([
                    'product_id' => $product->id,
                    'product_variant_id' => null,
                    'type' => 'sale',
                    'quantity_change' => -$quantity,
                    'previous_stock' => $previousStock,
                    'new_stock' => $newStock,
                    'user_id' => auth()->id(),
                    'reason' => $reason,
                    'reference_type' => $refType,
                    'reference_id' => $refId,
                ]);

                if ($newStock <= $product->min_stock_level) {
                    app(TelegramService::class)->sendLowStockAlert($product, null, $newStock);
                }

                return $transaction;
            }
        });
    }

    /**
     * Restore stock upon order cancellation or return
     */
    public function restoreStock(Product $product, ?ProductVariant $variant, int $quantity, string $reason = 'Order Cancelled', ?string $refType = 'order', ?int $refId = null): InventoryTransaction
    {
        return DB::transaction(function () use ($product, $variant, $quantity, $reason, $refType, $refId) {
            if ($variant) {
                $previousStock = $variant->stock_quantity;
                $newStock = $previousStock + $quantity;

                $variant->update(['stock_quantity' => $newStock]);
                $product->increment('stock_quantity', $quantity);

                return InventoryTransaction::create([
                    'product_id' => $product->id,
                    'product_variant_id' => $variant->id,
                    'type' => 'cancel',
                    'quantity_change' => $quantity,
                    'previous_stock' => $previousStock,
                    'new_stock' => $newStock,
                    'user_id' => auth()->id(),
                    'reason' => $reason,
                    'reference_type' => $refType,
                    'reference_id' => $refId,
                ]);
            } else {
                $previousStock = $product->stock_quantity;
                $newStock = $previousStock + $quantity;

                $product->update(['stock_quantity' => $newStock]);

                return InventoryTransaction::create([
                    'product_id' => $product->id,
                    'product_variant_id' => null,
                    'type' => 'cancel',
                    'quantity_change' => $quantity,
                    'previous_stock' => $previousStock,
                    'new_stock' => $newStock,
                    'user_id' => auth()->id(),
                    'reason' => $reason,
                    'reference_type' => $refType,
                    'reference_id' => $refId,
                ]);
            }
        });
    }

    /**
     * Manual stock adjustment by Admin
     */
    public function adjustStock(Product $product, ?ProductVariant $variant, int $quantityChange, string $reason): InventoryTransaction
    {
        return DB::transaction(function () use ($product, $variant, $quantityChange, $reason) {
            if ($variant) {
                $previousStock = $variant->stock_quantity;
                $newStock = $previousStock + $quantityChange;

                if ($newStock < 0) {
                    throw new Exception("Stock adjustment cannot result in negative inventory (Calculated: {$newStock}).");
                }

                $variant->update(['stock_quantity' => $newStock]);
                $product->increment('stock_quantity', $quantityChange);

                return InventoryTransaction::create([
                    'product_id' => $product->id,
                    'product_variant_id' => $variant->id,
                    'type' => 'adjustment',
                    'quantity_change' => $quantityChange,
                    'previous_stock' => $previousStock,
                    'new_stock' => $newStock,
                    'user_id' => auth()->id(),
                    'reason' => $reason,
                    'reference_type' => 'manual_adjustment',
                ]);
            } else {
                $previousStock = $product->stock_quantity;
                $newStock = $previousStock + $quantityChange;

                if ($newStock < 0) {
                    throw new Exception("Stock adjustment cannot result in negative inventory (Calculated: {$newStock}).");
                }

                $product->update(['stock_quantity' => $newStock]);

                return InventoryTransaction::create([
                    'product_id' => $product->id,
                    'product_variant_id' => null,
                    'type' => 'adjustment',
                    'quantity_change' => $quantityChange,
                    'previous_stock' => $previousStock,
                    'new_stock' => $newStock,
                    'user_id' => auth()->id(),
                    'reason' => $reason,
                    'reference_type' => 'manual_adjustment',
                ]);
            }
        });
    }
}
