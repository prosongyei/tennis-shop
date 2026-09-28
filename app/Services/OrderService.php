<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    protected InventoryService $inventoryService;
    protected PaymentService $paymentService;
    protected TelegramService $telegramService;
    protected GoogleSheetService $googleSheetService;

    public function __construct(
        InventoryService $inventoryService,
        PaymentService $paymentService,
        TelegramService $telegramService,
        GoogleSheetService $googleSheetService
    ) {
        $this->inventoryService = $inventoryService;
        $this->paymentService = $paymentService;
        $this->telegramService = $telegramService;
        $this->googleSheetService = $googleSheetService;
    }

    /**
     * Create order from web customer cart
     */
    public function createOrderFromCart(Cart $cart, array $customerData, string $paymentMethod, float $deliveryFee = 0.00, ?float $discountAmount = 0.00): Order
    {
        if ($cart->items->isEmpty()) {
            throw new Exception("Shopping cart is empty.");
        }

        $order = DB::transaction(function () use ($cart, $customerData, $paymentMethod, $deliveryFee, $discountAmount) {
            $subtotal = 0.00;

            // 1. Pre-validate stock for all items
            foreach ($cart->items as $cartItem) {
                $product = $cartItem->product;
                $variant = $cartItem->variant;

                if (!$product || $product->status !== 'active') {
                    throw new Exception("One of the products in your cart is no longer available.");
                }

                $availableStock = $variant ? $variant->stock_quantity : $product->stock_quantity;
                if ($availableStock < $cartItem->quantity) {
                    $itemDesc = $variant ? "{$product->name} ({$variant->variant_name})" : $product->name;
                    throw new Exception("Insufficient stock for {$itemDesc}. Requested: {$cartItem->quantity}, Available: {$availableStock}");
                }

                $subtotal += ($cartItem->unit_price * $cartItem->quantity);
            }

            $finalTotal = max(0.00, ($subtotal - $discountAmount) + $deliveryFee);
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(5));

            // 2. Create the Order
            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => Auth::id(),
                'cashier_id' => null,
                'customer_name' => $customerData['name'],
                'customer_phone' => $customerData['phone'],
                'customer_email' => $customerData['email'] ?? null,
                'delivery_address' => $customerData['address'] ?? 'Store Pickup',
                'province_city' => $customerData['province_city'] ?? 'Phnom Penh',
                'customer_note' => $customerData['note'] ?? null,
                'delivery_method' => $customerData['delivery_method'] ?? 'delivery',
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'delivery_fee' => $deliveryFee,
                'total_amount' => $finalTotal,
                'payment_method' => $paymentMethod,
                'payment_status' => 'pending',
                'order_status' => 'pending',
                'source' => 'web',
            ]);

            // 3. Create OrderItems and Deduct Inventory atomically
            foreach ($cart->items as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'product_variant_id' => $cartItem->product_variant_id,
                    'product_name' => $cartItem->product->name,
                    'variant_name' => $cartItem->variant ? $cartItem->variant->variant_name : null,
                    'sku' => $cartItem->variant ? $cartItem->variant->sku : $cartItem->product->sku,
                    'unit_price' => $cartItem->unit_price, // Saved purchase price at purchase time!
                    'quantity' => $cartItem->quantity,
                    'subtotal' => $cartItem->subtotal,
                ]);

                // Reduce inventory atomically
                $this->inventoryService->deductStock(
                    $cartItem->product,
                    $cartItem->variant,
                    $cartItem->quantity,
                    "Web Order #{$order->order_number}",
                    'order',
                    $order->id
                );
            }

            // 4. Generate KHQR if selected
            if ($paymentMethod === 'khqr') {
                $this->paymentService->generateKhqr($order, $finalTotal, 'USD');
            }

            // 5. Clear customer cart
            $cart->items()->delete();

            return $order;
        });

        // Send Telegram & Google Sheet notifications asynchronously / outside DB transaction
        try {
            $freshOrder = $order->fresh(['items']);
            $this->telegramService->sendNewOrderAlert($freshOrder);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Telegram order alert failed: " . $e->getMessage());
        }

        try {
            $this->googleSheetService->appendOrder($order);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Google Sheet append failed: " . $e->getMessage());
        }

        return $order;
    }

    /**
     * Create POS sale from Cashier register
     */
    public function createPosSale(array $items, array $saleData, string $paymentMethod, float $discount = 0.00): Order
    {
        return DB::transaction(function () use ($items, $saleData, $paymentMethod, $discount) {
            $subtotal = 0.00;

            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $variant = !empty($item['variant_id']) ? ProductVariant::find($item['variant_id']) : null;
                $quantity = (int) $item['quantity'];

                $availableStock = $variant ? $variant->stock_quantity : $product->stock_quantity;
                if ($availableStock < $quantity) {
                    $itemDesc = $variant ? "{$product->name} ({$variant->variant_name})" : $product->name;
                    throw new Exception("Insufficient stock for {$itemDesc}. Available: {$availableStock}");
                }

                $price = $variant ? $variant->effective_price : $product->effective_price;
                $subtotal += ($price * $quantity);
            }

            $totalAmount = max(0.00, $subtotal - $discount);
            $orderNumber = 'POS-' . date('Ymd') . '-' . strtoupper(Str::random(5));

            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $saleData['customer_id'] ?? null,
                'cashier_id' => Auth::id(),
                'customer_name' => $saleData['customer_name'] ?? 'Walk-in Customer',
                'customer_phone' => $saleData['customer_phone'] ?? 'N/A',
                'customer_email' => null,
                'delivery_address' => 'Store POS Sale',
                'province_city' => 'Phnom Penh',
                'delivery_method' => 'pickup',
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'delivery_fee' => 0.00,
                'total_amount' => $totalAmount,
                'payment_method' => $paymentMethod,
                'payment_status' => ($paymentMethod === 'cash_store') ? 'paid' : 'pending',
                'paid_at' => ($paymentMethod === 'cash_store') ? now() : null,
                'order_status' => ($paymentMethod === 'cash_store') ? 'completed' : 'confirmed',
                'source' => 'pos',
            ]);

            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $variant = !empty($item['variant_id']) ? ProductVariant::find($item['variant_id']) : null;
                $quantity = (int) $item['quantity'];
                $price = $variant ? $variant->effective_price : $product->effective_price;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_variant_id' => $variant ? $variant->id : null,
                    'product_name' => $product->name,
                    'variant_name' => $variant ? $variant->variant_name : null,
                    'sku' => $variant ? $variant->sku : $product->sku,
                    'unit_price' => $price,
                    'quantity' => $quantity,
                    'subtotal' => $price * $quantity,
                ]);

                $this->inventoryService->deductStock(
                    $product,
                    $variant,
                    $quantity,
                    "Cashier POS Sale #{$order->order_number}",
                    'pos_sale',
                    $order->id
                );
            }

            if ($paymentMethod === 'khqr') {
                $this->paymentService->generateKhqr($order, $totalAmount, 'USD');
            } elseif ($paymentMethod === 'cash_store') {
                $this->paymentService->markOrderAsPaid($order, 'cash_store', 'CASH-' . time(), 'Cash payment at register', Auth::id());
            }

            return $order;
        });

        // Send Telegram invoice to group for POS sale
        try {
            $freshPosOrder = $order->fresh(['items.product', 'items.variant']);
            if ($paymentMethod === 'khqr') {
                $this->telegramService->sendNewOrderNotifications($freshPosOrder);
            } else {
                $this->telegramService->sendInvoiceToGroup($freshPosOrder);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("POS Telegram notification failed: " . $e->getMessage());
        }

        return $order;
    }

    /**
     * Cancel order and restore stock
     */
    public function cancelOrder(Order $order, string $reason = 'Customer cancellation'): void
    {
        if (!$order->can_be_cancelled) {
            throw new Exception("This order cannot be cancelled in its current status ({$order->order_status}).");
        }

        DB::transaction(function () use ($order, $reason) {
            $order->update([
                'order_status' => 'cancelled',
                'payment_status' => ($order->payment_status === 'paid') ? 'refunded' : 'cancelled',
            ]);

            foreach ($order->items as $item) {
                if ($item->product) {
                    $this->inventoryService->restoreStock(
                        $item->product,
                        $item->variant,
                        $item->quantity,
                        "Restored from Cancelled Order #{$order->order_number}: {$reason}",
                        'order_cancellation',
                        $order->id
                    );
                }
            }
        });

        try {
            $this->telegramService->sendOrderCancelledAlert($order, $reason);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Telegram order cancelled alert failed: " . $e->getMessage());
        }
    }
}
