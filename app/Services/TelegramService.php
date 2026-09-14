<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TelegramService
{
    /**
     * Get active Telegram bot token with auto-migration from legacy tokens
     */
    public function getBotToken(): string
    {
        $token = Setting::get('telegram_bot_token');
        if (empty($token) || str_starts_with($token, '8862288371')) {
            $token = '8851308730:AAFIs5Dyu4exg6mXw0JLN1jbOuQyvgucrPc';
            Setting::set('telegram_bot_token', $token, 'telegram');
        }
        return $token;
    }

    /**
     * Get active Telegram chat/group ID with fallback to primary group
     */
    public function getChatId(): string
    {
        $chatId = Setting::get('telegram_chat_id');
        if (empty($chatId) || $chatId == '6646751752') {
            $chatId = '-5475494678';
            Setting::set('telegram_chat_id', $chatId, 'telegram');
        }
        return (string) $chatId;
    }

    /**
     * Get public HTTPS base URL for Telegram buttons (Telegram forbids localhost)
     */
    public function getBaseUrl(): string
    {
        $appUrl = config('app.url');
        if (empty($appUrl) || str_contains($appUrl, 'localhost') || str_contains($appUrl, '127.0.0.1')) {
            return 'https://web-production-30153.up.railway.app';
        }
        return rtrim($appUrl, '/');
    }

    public function getInvoiceUrl(string $orderNumber): string
    {
        return $this->getBaseUrl() . "/orders/invoice/{$orderNumber}";
    }

    public function getAdminOrdersUrl(): string
    {
        return $this->getBaseUrl() . "/admin/orders";
    }

    /**
     * Send Markdown/HTML message to Telegram Bot with optional inline keyboard buttons
     */
    public function sendMessage(string $text, ?array $replyMarkup = null): bool
    {
        $enabled = Setting::get('telegram_enabled', config('services.telegram.enabled', true));
        $botToken = $this->getBotToken();
        $chatId = $this->getChatId();

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

            $response = Http::timeout(8)->post("https://api.telegram.org/bot{$botToken}/sendMessage", $payload);

            if (!$response->successful()) {
                Log::warning("Telegram sendMessage error: " . $response->body());
            }

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
        $botToken = $this->getBotToken();
        $chatId = $this->getChatId();

        if (!$enabled || empty($botToken) || empty($chatId)) {
            return false;
        }

        try {
            $fullPath = Storage::disk('public')->path($photoPath);
            if (!file_exists($fullPath)) {
                $fullPath = storage_path('app/public/' . $photoPath);
            }
            if (!file_exists($fullPath)) {
                $fullPath = public_path('storage/' . $photoPath);
            }

            if (!file_exists($fullPath)) {
                // If local file not accessible on server, fallback to public URL link
                $publicUrl = $this->getBaseUrl() . '/storage/' . ltrim($photoPath, '/');
                return $this->sendMessage("📸 <b>RECEIPT SLIP:</b> <a href='{$publicUrl}'>View Image</a>\n\n" . $caption, $replyMarkup);
            }

            $request = Http::timeout(12)->attach(
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

            if (!$response->successful()) {
                Log::warning("Telegram sendPhoto failed: " . $response->body() . ". Falling back to text message.");
                return $this->sendMessage($caption, $replyMarkup);
            }

            return true;
        } catch (\Exception $e) {
            Log::warning("Telegram sendPhoto exception: " . $e->getMessage() . ". Falling back to text message.");
            return $this->sendMessage($caption, $replyMarkup);
        }
    }

    /**
     * Alert for New Order placed with instant interactive Confirm button
     */
    public function sendNewOrderAlert(Order $order): bool
    {
        $order->loadMissing('items');

        $itemsList = '';
        foreach ($order->items as $item) {
            $safeProdName = htmlspecialchars($item->product_name, ENT_QUOTES, 'UTF-8');
            $safeVarName = $item->variant_name ? " (" . htmlspecialchars($item->variant_name, ENT_QUOTES, 'UTF-8') . ")" : "";
            $itemsList .= "• <b>{$safeProdName}</b>{$safeVarName} × {$item->quantity} ($" . number_format($item->subtotal, 2) . ")\n";
        }

        $safeCustomerName = htmlspecialchars($order->customer_name, ENT_QUOTES, 'UTF-8');
        $safeCustomerPhone = htmlspecialchars($order->customer_phone, ENT_QUOTES, 'UTF-8');
        $safeCity = htmlspecialchars($order->province_city ?? 'Phnom Penh', ENT_QUOTES, 'UTF-8');
        $safeAddress = htmlspecialchars($order->delivery_address ?? 'Store Pickup', ENT_QUOTES, 'UTF-8');
        $paymentMethod = strtoupper(str_replace('_', ' ', $order->payment_method));
        $paymentStatus = strtoupper($order->payment_status);

        $message = "🛒 <b>NEW ORDER PLACED!</b>\n\n"
            . "<b>Order:</b> #{$order->order_number}\n"
            . "<b>Source:</b> " . strtoupper($order->source) . "\n\n"
            . "<b>Customer:</b>\n"
            . "• Name: {$safeCustomerName}\n"
            . "• Phone: {$safeCustomerPhone}\n"
            . "• Delivery: " . ucfirst($order->delivery_method) . " ({$safeCity})\n"
            . "• Address: {$safeAddress}\n"
            . "\n<b>Items:</b>\n"
            . $itemsList . "\n"
            . "<b>Total Amount:</b> <b>$" . number_format($order->total_amount, 2) . "</b>\n"
            . "<b>Payment Method:</b> {$paymentMethod}\n"
            . "<b>Payment Status:</b> ⏳ <b>{$paymentStatus}</b>\n\n"
            . "<i>Check your mobile banking app. If money is received, tap 'Confirm Payment' below:</i>";

        $replyMarkup = [
            'inline_keyboard' => [
                [
                    ['text' => "✅ Confirm Payment ($" . number_format($order->total_amount, 2) . ")", 'callback_data' => "confirm_{$order->order_number}"],
                ],
                [
                    ['text' => "❌ Reject Order", 'callback_data' => "reject_{$order->order_number}"],
                    ['text' => "📄 View Invoice", 'url' => $this->getInvoiceUrl($order->order_number)],
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
        $safeCustomerName = htmlspecialchars($order->customer_name, ENT_QUOTES, 'UTF-8');
        $safeCustomerPhone = htmlspecialchars($order->customer_phone, ENT_QUOTES, 'UTF-8');
        $paymentMethod = strtoupper(str_replace('_', ' ', $order->payment_method));

        $caption = "📸 <b>PAYMENT RECEIPT SLIP UPLOADED!</b>\n\n"
            . "<b>Order:</b> #{$order->order_number}\n"
            . "<b>Customer:</b> {$safeCustomerName} ({$safeCustomerPhone})\n"
            . "<b>Amount Due:</b> <b>$" . number_format($order->total_amount, 2) . "</b>\n"
            . "<b>Method:</b> {$paymentMethod}\n"
            . "<b>Uploaded at:</b> " . now()->format('d M Y, h:i A') . "\n\n"
            . "<i>Verify the receipt screenshot against your banking app, then tap Confirm below:</i>";

        $replyMarkup = [
            'inline_keyboard' => [
                [
                    ['text' => "✅ Confirm Payment ($" . number_format($order->total_amount, 2) . ")", 'callback_data' => "confirm_{$order->order_number}"],
                ],
                [
                    ['text' => "❌ Reject Slip", 'callback_data' => "reject_{$order->order_number}"],
                    ['text' => "📄 Digital Invoice", 'url' => $this->getInvoiceUrl($order->order_number)],
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
        $safeCustomerName = htmlspecialchars($order->customer_name, ENT_QUOTES, 'UTF-8');
        $safeCustomerPhone = htmlspecialchars($order->customer_phone, ENT_QUOTES, 'UTF-8');

        $message = "✅ <b>ORDER PAYMENT CONFIRMED!</b>\n\n"
            . "<b>Order:</b> #{$order->order_number}\n"
            . "<b>Customer:</b> {$safeCustomerName} ({$safeCustomerPhone})\n"
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
        $variantText = $variant ? " - " . htmlspecialchars($variant->variant_name, ENT_QUOTES, 'UTF-8') : "";
        $prodName = htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8');

        $message = "{$statusText}\n\n"
            . "<b>Product:</b> {$prodName}{$variantText}\n"
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
        $safeCustomerName = htmlspecialchars($order->customer_name, ENT_QUOTES, 'UTF-8');
        $safeCustomerPhone = htmlspecialchars($order->customer_phone, ENT_QUOTES, 'UTF-8');
        $safeReason = htmlspecialchars($reason, ENT_QUOTES, 'UTF-8');

        $message = "❌ <b>ORDER CANCELLED</b>\n\n"
            . "<b>Order:</b> #{$order->order_number}\n"
            . "<b>Customer:</b> {$safeCustomerName} ({$safeCustomerPhone})\n"
            . "<b>Reason:</b> {$safeReason}\n"
            . "<b>Time:</b> " . now()->format('d M Y, h:i A');

        return $this->sendMessage($message);
    }
}
