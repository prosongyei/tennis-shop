<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    /**
     * Send Markdown/HTML message to Telegram Bot
     */
    public function sendMessage(string $text): bool
    {
        $enabled = Setting::get('telegram_enabled', config('services.telegram.enabled', false));
        $botToken = Setting::get('telegram_bot_token', config('services.telegram.bot_token'));
        $chatId = Setting::get('telegram_chat_id', config('services.telegram.chat_id'));

        if (!$enabled || empty($botToken) || empty($chatId)) {
            Log::info("Telegram notification skipped (disabled or missing credentials):\n" . $text);
            return false;
        }

        try {
            $response = Http::timeout(5)->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::warning("Telegram message failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Alert for New Order placed
     */
    public function sendNewOrderAlert(Order $order): bool
    {
        $itemsList = '';
        foreach ($order->items as $item) {
            $variantText = $item->variant_name ? " ({$item->variant_name})" : "";
            $itemsList .= "• <b>{$item->product_name}</b>{$variantText} × {$item->quantity} ($" . number_format($item->subtotal, 2) . ")\n";
        }

        $paymentMethod = strtoupper($order->payment_method);
        $paymentStatus = strtoupper($order->payment_status);
        $orderStatus = strtoupper($order->order_status);
        $time = $order->created_at ? $order->created_at->format('d M Y, h:i A') : now()->format('d M Y, h:i A');

        $message = "🛒 <b>NEW ORDER RECEIVED!</b>\n\n"
            . "<b>Order:</b> #{$order->order_number}\n"
            . "<b>Source:</b> " . strtoupper($order->source) . "\n\n"
            . "<b>Customer:</b>\n"
            . "• Name: {$order->customer_name}\n"
            . "• Phone: {$order->customer_phone}\n"
            . "• Delivery: " . ucfirst($order->delivery_method) . " (" . ($order->province_city ?? 'Phnom Penh') . ")\n\n"
            . "<b>Items:</b>\n"
            . $itemsList . "\n"
            . "<b>Total:</b> $" . number_format($order->total_amount, 2) . "\n"
            . "<b>Payment:</b> {$paymentMethod} (<b>{$paymentStatus}</b>)\n"
            . "<b>Order Status:</b> {$orderStatus}\n\n"
            . "<b>Time:</b> {$time}";

        return $this->sendMessage($message);
    }

    /**
     * Alert for Payment Confirmed
     */
    public function sendPaymentConfirmedAlert(Order $order): bool
    {
        $message = "✅ <b>PAYMENT CONFIRMED!</b>\n\n"
            . "<b>Order:</b> #{$order->order_number}\n"
            . "<b>Customer:</b> {$order->customer_name} ({$order->customer_phone})\n"
            . "<b>Amount:</b> $" . number_format($order->total_amount, 2) . "\n"
            . "<b>Method:</b> " . strtoupper($order->payment_method) . "\n"
            . "<b>Time:</b> " . now()->format('d M Y, h:i A');

        return $this->sendMessage($message);
    }

    /**
     * Alert for Low Stock / Out of Stock
     */
    public function sendLowStockAlert(Product $product, ?ProductVariant $variant, int $currentStock): bool
    {
        $statusText = $currentStock <= 0 ? "🚨 <b>OUT OF STOCK ALERT!</b>" : "⚠️ <b>LOW STOCK WARNING!</b>";
        $variantText = $variant ? " - " . $variant->variant_name : "";

        $message = "{$statusText}\n\n"
            . "<b>Product:</b> {$product->name}{$variantText}\n"
            . "<b>SKU:</b> " . ($variant ? $variant->sku : $product->sku) . "\n"
            . "<b>Current Stock:</b> {$currentStock} items\n"
            . "<b>Threshold:</b> {$product->min_stock_level} items\n\n"
            . "<i>Please restock this item soon.</i>";

        return $this->sendMessage($message);
    }

    /**
     * Alert for Order Cancelled
     */
    public function sendOrderCancelledAlert(Order $order, string $reason = 'Cancelled by user'): bool
    {
        $message = "❌ <b>ORDER CANCELLED</b>\n\n"
            . "<b>Order:</b> #{$order->order_number}\n"
            . "<b>Customer:</b> {$order->customer_name} ({$order->customer_phone})\n"
            . "<b>Reason:</b> {$reason}\n"
            . "<b>Time:</b> " . now()->format('d M Y, h:i A');

        return $this->sendMessage($message);
    }
}
