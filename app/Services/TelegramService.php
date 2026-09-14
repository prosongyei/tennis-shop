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
     * Send Markdown/HTML message to Telegram Bot with optional inline keyboard buttons
     */
    public function sendMessage(string $text, ?array $replyMarkup = null): bool
    {
        $enabled = Setting::get('telegram_enabled', config('services.telegram.enabled', true));
        $botToken = Setting::get('telegram_bot_token', config('services.telegram.bot_token', '8851308730:AAFIs5Dyu4exg6mXw0JLN1jbOuQyvgucrPc'));
        $chatId = Setting::get('telegram_chat_id', config('services.telegram.chat_id'));

        if (!$enabled || empty($botToken) || empty($chatId)) {
            Log::info("Telegram notification skipped (disabled or missing chat_id). Text:\n" . $text);
            return false;
        }

        try {
            $payload = [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ];

            if ($replyMarkup) {
                $payload['reply_markup'] = json_encode($replyMarkup);
            }

            $response = Http::timeout(6)->post("https://api.telegram.org/bot{$botToken}/sendMessage", $payload);

            return $response->successful();
        } catch (\Exception $e) {
            Log::warning("Telegram message failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send Photo with Caption and inline keyboard buttons
     */
    public function sendPhoto(string $photoPath, string $caption, ?array $replyMarkup = null): bool
    {
        $enabled = Setting::get('telegram_enabled', config('services.telegram.enabled', true));
        $botToken = Setting::get('telegram_bot_token', config('services.telegram.bot_token', '8851308730:AAFIs5Dyu4exg6mXw0JLN1jbOuQyvgucrPc'));
        $chatId = Setting::get('telegram_chat_id', config('services.telegram.chat_id'));

        if (!$enabled || empty($botToken) || empty($chatId)) {
            return false;
        }

        try {
            $fullPath = storage_path('app/public/' . $photoPath);
            if (!file_exists($fullPath)) {
                $fullPath = public_path('storage/' . $photoPath);
            }

            if (!file_exists($fullPath)) {
                // If local file not accessible, fallback to sending message with URL
                $publicUrl = asset('storage/' . $photoPath);
                return $this->sendMessage("📸 <b>RECEIPT SLIP:</b> <a href='{$publicUrl}'>View Image</a>\n\n" . $caption, $replyMarkup);
            }

            $request = Http::timeout(10)->attach(
                'photo',
                file_get_contents($fullPath),
                basename($fullPath)
            );

            $payload = [
                'chat_id' => $chatId,
                'caption' => $caption,
                'parse_mode' => 'HTML',
            ];

            if ($replyMarkup) {
                $payload['reply_markup'] = json_encode($replyMarkup);
            }

            $response = $request->post("https://api.telegram.org/bot{$botToken}/sendPhoto", $payload);

            return $response->successful();
        } catch (\Exception $e) {
            Log::warning("Telegram sendPhoto failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Alert for New Order placed with instant interactive Confirm button
     */
    public function sendNewOrderAlert(Order $order): bool
    {
        $itemsList = '';
        foreach ($order->items as $item) {
            $variantText = $item->variant_name ? " ({$item->variant_name})" : "";
            $itemsList .= "• <b>{$item->product_name}</b>{$variantText} × {$item->quantity} ($" . number_format($item->subtotal, 2) . ")\n";
        }

        $paymentMethod = strtoupper(str_replace('_', ' ', $order->payment_method));
        $paymentStatus = strtoupper($order->payment_status);
        $orderStatus = strtoupper($order->order_status);
        $time = $order->created_at ? $order->created_at->format('d M Y, h:i A') : now()->format('d M Y, h:i A');

        $message = "🛒 <b>NEW CUSTOMER ORDER RECEIVED!</b>\n\n"
            . "<b>Order:</b> #{$order->order_number}\n"
            . "<b>Source:</b> " . strtoupper($order->source) . "\n\n"
            . "<b>Customer:</b>\n"
            . "• Name: {$order->customer_name}\n"
            . "• Phone: {$order->customer_phone}\n"
            . "• Delivery: " . ucfirst($order->delivery_method) . " (" . ($order->province_city ?? 'Phnom Penh') . ")\n"
            . ($order->delivery_address ? "• Address: {$order->delivery_address}\n" : "")
            . "\n<b>Items:</b>\n"
            . $itemsList . "\n"
            . "<b>Total Amount:</b> <b>$" . number_format($order->total_amount, 2) . "</b>\n"
            . "<b>Payment Method:</b> {$paymentMethod}\n"
            . "<b>Payment Status:</b> ⏳ <b>{$paymentStatus}</b>\n\n"
            . "<i>Check your mobile banking app. If payment is received, tap 'Confirm Payment' below!</i>";

        $replyMarkup = [
            'inline_keyboard' => [
                [
                    ['text' => "✅ Confirm Payment ($" . number_format($order->total_amount, 2) . ")", 'callback_data' => "confirm_{$order->order_number}"],
                ],
                [
                    ['text' => "❌ Reject / Cancel", 'callback_data' => "reject_{$order->order_number}"],
                    ['text' => "📄 View Invoice", 'url' => url("/orders/invoice/{$order->order_number}")],
                ]
            ]
        ];

        return $this->sendMessage($message, $replyMarkup);
    }

    /**
     * Alert when customer uploads a bank transfer slip screenshot
     */
    public function sendSlipUploadedAlert(Order $order): bool
    {
        $caption = "📸 <b>PAYMENT RECEIPT SLIP UPLOADED!</b>\n\n"
            . "<b>Order:</b> #{$order->order_number}\n"
            . "<b>Customer:</b> {$order->customer_name} ({$order->customer_phone})\n"
            . "<b>Amount Due:</b> <b>$" . number_format($order->total_amount, 2) . "</b>\n"
            . "<b>Method:</b> " . strtoupper(str_replace('_', ' ', $order->payment_method)) . "\n"
            . "<b>Uploaded at:</b> " . now()->format('d M Y, h:i A') . "\n\n"
            . "<i>Verify the receipt screenshot above against your banking app, then confirm:</i>";

        $replyMarkup = [
            'inline_keyboard' => [
                [
                    ['text' => "✅ Confirm Payment ($" . number_format($order->total_amount, 2) . ")", 'callback_data' => "confirm_{$order->order_number}"],
                ],
                [
                    ['text' => "❌ Reject Slip", 'callback_data' => "reject_{$order->order_number}"],
                    ['text' => "📄 Digital Invoice", 'url' => url("/orders/invoice/{$order->order_number}")],
                ]
            ]
        ];

        if (!empty($order->payment_proof_image)) {
            return $this->sendPhoto($order->payment_proof_image, $caption, $replyMarkup);
        }

        return $this->sendMessage($caption, $replyMarkup);
    }

    /**
     * Alert for Payment Confirmed
     */
    public function sendPaymentConfirmedAlert(Order $order): bool
    {
        $message = "✅ <b>ORDER PAYMENT CONFIRMED!</b>\n\n"
            . "<b>Order:</b> #{$order->order_number}\n"
            . "<b>Customer:</b> {$order->customer_name} ({$order->customer_phone})\n"
            . "<b>Amount:</b> $" . number_format($order->total_amount, 2) . "\n"
            . "<b>Method:</b> " . strtoupper(str_replace('_', ' ', $order->payment_method)) . "\n"
            . "<b>Status:</b> PAID & CONFIRMED\n"
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
