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
     * Bot 1: Payment Confirmation Bot Token (@TLS_Payment_bot)
     */
    public function getConfirmBotToken(): string
    {
        $token = Setting::get('telegram_confirm_bot_token');
        if (empty($token)) {
            $token = Setting::get('telegram_bot_token', config('services.telegram.bot_token', '8851308730:AAFIs5Dyu4exg6mXw0JLN1jbOuQyvgucrPc'));
        }
        return (string) $token;
    }

    /**
     * Bot 1: Store Admin / Cashier Chat ID for Payment Confirmation (Admin DM)
     */
    public function getConfirmChatId(): string
    {
        $chatId = Setting::get('telegram_confirm_chat_id');
        if (empty($chatId)) {
            $chatId = '6646751752'; // SoySorSongyei Admin DM
        }
        return (string) $chatId;
    }

    /**
     * Bot 2: Store Invoice Bot Token (@TosLengSey_bot)
     */
    public function getInvoiceBotToken(): string
    {
        $token = Setting::get('telegram_invoice_bot_token');
        if (empty($token)) {
            $token = '8862288371:AAGoz8XBLGz4eacOcIbprOWIn5eNiDAadrw'; // @TosLengSey_bot
        }
        return (string) $token;
    }

    /**
     * Bot 2 / Group: Digital Invoices & Receipts Group ID
     */
    public function getInvoiceGroupId(): string
    {
        $groupId = Setting::get('telegram_invoice_group_id');
        if (empty($groupId)) {
            $groupId = '-5475494678'; // TLS_Payment Group
        }
        return (string) $groupId;
    }

    /**
     * Backward-compatible alias for bot token
     */
    public function getBotToken(): string
    {
        return $this->getConfirmBotToken();
    }

    /**
     * Backward-compatible alias for chat ID
     */
    public function getChatId(): string
    {
        return $this->getConfirmChatId();
    }

    /**
     * Public HTTPS base URL for Telegram buttons (Telegram forbids localhost)
     */
    public function getBaseUrl(): string
    {
        if (!empty(env('RAILWAY_PUBLIC_DOMAIN'))) {
            return 'https://' . rtrim(env('RAILWAY_PUBLIC_DOMAIN'), '/');
        }
        $appUrl = config('app.url');
        if (!empty($appUrl) && !str_contains($appUrl, 'localhost') && !str_contains($appUrl, '127.0.0.1')) {
            return rtrim($appUrl, '/');
        }
        return 'https://toslengsey.up.railway.app';
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
     * Master dispatch when a new order is placed:
     * 1. Send payment confirmation request with [Confirm] buttons to Bot 1 (Admin DM)
     * 2. Send complete digital tax invoice & item breakdown to Bot 2 (Invoice Group)
     */
    public function sendNewOrderNotifications(Order $order): void
    {
        $order->loadMissing(['items.product', 'items.variant']);

        // 1. Alert Store Admin to confirm payment
        $this->sendPaymentConfirmationAlert($order);

        // 2. Post full digital invoice to the Invoice Group
        $this->sendInvoiceToGroup($order);
    }

    /**
     * Backward-compatible method called by existing order flows
     */
    public function sendNewOrderAlert(Order $order): bool
    {
        $this->sendNewOrderNotifications($order);
        return true;
    }

    /**
     * BOT 1: Send payment confirmation request with interactive buttons to Admin DM
     */
    public function sendPaymentConfirmationAlert(Order $order): bool
    {
        $order->loadMissing(['items.product', 'items.variant']);

        $safeCustomerName = htmlspecialchars($order->customer_name, ENT_QUOTES, 'UTF-8');
        $safeCustomerPhone = htmlspecialchars($order->customer_phone, ENT_QUOTES, 'UTF-8');
        $safeCity = htmlspecialchars($order->province_city ?? 'Phnom Penh', ENT_QUOTES, 'UTF-8');
        $safeAddress = htmlspecialchars($order->delivery_address ?? 'Store Pickup', ENT_QUOTES, 'UTF-8');
        $paymentMethod = strtoupper(str_replace('_', ' ', $order->payment_method));
        $paymentStatus = strtoupper($order->payment_status);

        $itemsList = '';
        foreach ($order->items as $item) {
            $safeProdName = htmlspecialchars($item->product_name, ENT_QUOTES, 'UTF-8');
            $safeVarName = $item->variant_name ? " (" . htmlspecialchars($item->variant_name, ENT_QUOTES, 'UTF-8') . ")" : "";
            $itemsList .= "• {$safeProdName}{$safeVarName} × {$item->quantity} ($" . number_format($item->subtotal, 2) . ")\n";
        }

        $khrRate = (float) Setting::get('khr_exchange_rate', 4100);
        $khrTotal = number_format(round($order->total_amount * $khrRate));

        $message = "🛒 <b>NEW ORDER AWAITING PAYMENT CONFIRMATION</b>\n\n"
            . "<b>Order:</b> #{$order->order_number}\n"
            . "<b>Source:</b> " . strtoupper($order->source) . "\n\n"
            . "👤 <b>Customer Details:</b>\n"
            . "• Name: <b>{$safeCustomerName}</b>\n"
            . "• Phone: <code>{$safeCustomerPhone}</code>\n"
            . "• Delivery: " . ucfirst($order->delivery_method) . " ({$safeCity})\n"
            . "• Address: {$safeAddress}\n"
            . (!empty($order->customer_note) ? "• Note: " . htmlspecialchars($order->customer_note, ENT_QUOTES, 'UTF-8') . "\n" : "")
            . "\n📦 <b>Order Items:</b>\n"
            . $itemsList . "\n"
            . "💰 <b>Total Amount Due:</b> <b>$" . number_format($order->total_amount, 2) . "</b> (≈ {$khrTotal} KHR)\n"
            . "💳 <b>Payment Method:</b> {$paymentMethod}\n"
            . "⏳ <b>Payment Status:</b> <b>{$paymentStatus}</b>\n\n"
            . "<i>Check your mobile banking app. If payment is received, tap 'Confirm Payment' below:</i>";

        $replyMarkup = [
            'inline_keyboard' => [
                [
                    ['text' => "✅ Confirm Payment ($" . number_format($order->total_amount, 2) . ")", 'callback_data' => "confirm_{$order->order_number}"],
                ],
                [
                    ['text' => "❌ Reject Order", 'callback_data' => "reject_{$order->order_number}"],
                    ['text' => "📄 Digital Invoice", 'url' => $this->getInvoiceUrl($order->order_number)],
                ]
            ]
        ];

        return $this->sendToConfirmationChat($message, $replyMarkup);
    }

    /**
     * BOT 2 / GROUP: Send complete itemized Tax Invoice & Receipt to the Invoice Group
     */
    public function sendInvoiceToGroup(Order $order): bool
    {
        $order->loadMissing(['items.product', 'items.variant']);

        $safeCustomerName = htmlspecialchars($order->customer_name, ENT_QUOTES, 'UTF-8');
        $safeCustomerPhone = htmlspecialchars($order->customer_phone, ENT_QUOTES, 'UTF-8');
        $safeCity = htmlspecialchars($order->province_city ?? 'Phnom Penh', ENT_QUOTES, 'UTF-8');
        $safeAddress = htmlspecialchars($order->delivery_address ?? 'Store Pickup', ENT_QUOTES, 'UTF-8');
        $paymentMethod = strtoupper(str_replace('_', ' ', $order->payment_method));
        $paymentStatus = $order->is_paid ? "✅ PAID & CONFIRMED" : "⏳ PENDING PAYMENT";

        $khrRate = (float) Setting::get('khr_exchange_rate', 4100);
        $khrTotal = number_format(round($order->total_amount * $khrRate));

        $itemsList = '';
        foreach ($order->items as $idx => $item) {
            $num = $idx + 1;
            $safeProdName = htmlspecialchars($item->product_name, ENT_QUOTES, 'UTF-8');
            $safeVarName = $item->variant_name ? " [" . htmlspecialchars($item->variant_name, ENT_QUOTES, 'UTF-8') . "]" : "";
            $itemsList .= "{$num}. <b>{$safeProdName}</b>{$safeVarName}\n"
                . "    {$item->quantity} × $" . number_format($item->unit_price, 2) . " = <b>$" . number_format($item->subtotal, 2) . "</b>\n";
        }

        $dateStr = $order->created_at ? $order->created_at->format('d M Y, h:i A') : now()->format('d M Y, h:i A');

        $message = "🧾 <b>TOSLENGSEY BADMINTON FLAGSHIP</b>\n"
            . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
            . "📋 <b>TAX INVOICE & ORDER RECEIPT</b>\n\n"
            . "<b>Invoice #:</b> #<code>{$order->order_number}</code>\n"
            . "<b>Date:</b> {$dateStr}\n"
            . "<b>Status:</b> <b>{$paymentStatus}</b>\n\n"
            . "👤 <b>Customer Details:</b>\n"
            . "• Name: <b>{$safeCustomerName}</b>\n"
            . "• Phone: <code>{$safeCustomerPhone}</code>\n"
            . "• Delivery: " . ucfirst($order->delivery_method) . " ({$safeCity})\n"
            . "• Destination: {$safeAddress}\n"
            . "\n📦 <b>Purchased Items:</b>\n"
            . $itemsList . "\n"
            . "💵 <b>Financial Breakdown:</b>\n"
            . "• Subtotal: $" . number_format($order->subtotal, 2) . "\n"
            . "• Delivery Fee: $" . number_format($order->delivery_fee, 2) . "\n"
            . ($order->discount_amount > 0 ? "• Discount: -$" . number_format($order->discount_amount, 2) . "\n" : "")
            . "• <b>GRAND TOTAL:</b> <b>$" . number_format($order->total_amount, 2) . "</b> (≈ {$khrTotal} KHR)\n"
            . "• <b>Payment Method:</b> {$paymentMethod}\n"
            . "━━━━━━━━━━━━━━━━━━━━━━━━";

        $replyMarkup = [
            'inline_keyboard' => [
                [
                    ['text' => "📄 Open Digital Tax Invoice", 'url' => $this->getInvoiceUrl($order->order_number)],
                ]
            ]
        ];

        return $this->sendToInvoiceGroup($message, $replyMarkup);
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
            . "<b>Customer:</b> {$safeCustomerName} (<code>{$safeCustomerPhone}</code>)\n"
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

        // 1. Send receipt photo to Bot 1 (Admin DM)
        if (!empty($order->payment_proof_image)) {
            $this->sendPhotoToConfirmationChat($order->payment_proof_image, $caption, $replyMarkup);
        } else {
            $this->sendToConfirmationChat($caption, $replyMarkup);
        }

        // 2. Post notice in the Invoice Group
        $groupNotice = "📸 <b>PAYMENT SLIP SUBMITTED</b>\n\n"
            . "Customer <b>{$safeCustomerName}</b> has uploaded a bank transfer receipt for <b>Invoice #{$order->order_number}</b> ($" . number_format($order->total_amount, 2) . ").\n"
            . "Store manager is currently verifying the transfer.";

        $groupButtons = [
            'inline_keyboard' => [
                [
                    ['text' => "📄 View Invoice", 'url' => $this->getInvoiceUrl($order->order_number)],
                ]
            ]
        ];

        $this->sendToInvoiceGroup($groupNotice, $groupButtons);

        return true;
    }

    /**
     * Alert for Payment Confirmed: Dispatches notice to Invoice Group & Confirmation Bot
     */
    public function sendPaymentConfirmedNotice(Order $order): bool
    {
        $safeCustomerName = htmlspecialchars($order->customer_name, ENT_QUOTES, 'UTF-8');
        $safeCustomerPhone = htmlspecialchars($order->customer_phone, ENT_QUOTES, 'UTF-8');

        $message = "✅ <b>INVOICE PAYMENT CONFIRMED & VERIFIED!</b>\n\n"
            . "<b>Invoice #:</b> #{$order->order_number}\n"
            . "<b>Customer:</b> {$safeCustomerName} (<code>{$safeCustomerPhone}</code>)\n"
            . "<b>Total Paid:</b> <b>$" . number_format($order->total_amount, 2) . "</b>\n"
            . "<b>Method:</b> " . strtoupper(str_replace('_', ' ', $order->payment_method)) . "\n"
            . "<b>Status:</b> PAID & CONFIRMED\n"
            . "<b>Time:</b> " . now()->format('d M Y, h:i A') . "\n\n"
            . "<i>The order has been approved and moved to fulfillment processing.</i>";

        $buttons = [
            'inline_keyboard' => [
                [
                    ['text' => "📄 View Paid Invoice", 'url' => $this->getInvoiceUrl($order->order_number)],
                ]
            ]
        ];

        // Send to Invoice Group
        $this->sendToInvoiceGroup($message, $buttons);

        return true;
    }

    /**
     * Backward-compatible alias for payment confirmed alert
     */
    public function sendPaymentConfirmedAlert(Order $order): bool
    {
        return $this->sendPaymentConfirmedNotice($order);
    }

    /**
     * Low stock alert
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

        return $this->sendToConfirmationChat($message);
    }

    /**
     * Order cancelled alert
     */
    public function sendOrderCancelledAlert(Order $order, string $reason = 'Cancelled by user'): bool
    {
        $safeCustomerName = htmlspecialchars($order->customer_name, ENT_QUOTES, 'UTF-8');
        $safeCustomerPhone = htmlspecialchars($order->customer_phone, ENT_QUOTES, 'UTF-8');
        $safeReason = htmlspecialchars($reason, ENT_QUOTES, 'UTF-8');

        $message = "❌ <b>ORDER / INVOICE CANCELLED</b>\n\n"
            . "<b>Order:</b> #{$order->order_number}\n"
            . "<b>Customer:</b> {$safeCustomerName} (<code>{$safeCustomerPhone}</code>)\n"
            . "<b>Reason:</b> {$safeReason}\n"
            . "<b>Time:</b> " . now()->format('d M Y, h:i A');

        $this->sendToConfirmationChat($message);
        $this->sendToInvoiceGroup($message);

        return true;
    }

    /**
     * Low-level helper to send to Confirmation Chat (Bot 1 -> Admin DM)
     */
    public function sendToConfirmationChat(string $text, ?array $replyMarkup = null): bool
    {
        $enabled = Setting::get('telegram_enabled', config('services.telegram.enabled', true));
        $botToken = $this->getConfirmBotToken();
        $chatId = $this->getConfirmChatId();

        if (!$enabled || empty($botToken) || empty($chatId)) {
            Log::info("Confirmation bot notification skipped. Token or chat_id empty.");
            return false;
        }

        return $this->executeSendMessage($botToken, $chatId, $text, $replyMarkup);
    }

    /**
     * Low-level helper to send photo to Confirmation Chat (Bot 1 -> Admin DM)
     */
    public function sendPhotoToConfirmationChat(string $photoPath, string $caption, ?array $replyMarkup = null): bool
    {
        $enabled = Setting::get('telegram_enabled', config('services.telegram.enabled', true));
        $botToken = $this->getConfirmBotToken();
        $chatId = $this->getConfirmChatId();

        if (!$enabled || empty($botToken) || empty($chatId)) {
            return false;
        }

        return $this->executeSendPhoto($botToken, $chatId, $photoPath, $caption, $replyMarkup);
    }

    /**
     * Low-level helper to send to Invoice Group (Bot 2 -> Group, with Admin DM fallback)
     */
    public function sendToInvoiceGroup(string $text, ?array $replyMarkup = null): bool
    {
        $enabled = Setting::get('telegram_enabled', config('services.telegram.enabled', true));
        $groupId = $this->getInvoiceGroupId();

        if (!$enabled) {
            return false;
        }

        $invoiceBotToken = $this->getInvoiceBotToken();

        // 1. Try sending with Bot 2 (@TosLengSey_bot) to configured group
        if (!empty($groupId) && !empty($invoiceBotToken)) {
            $sent = $this->executeSendMessage($invoiceBotToken, $groupId, $text, $replyMarkup);
            if ($sent) {
                return true;
            }
            Log::info("Bot 2 failed to send to group {$groupId}. Trying Bot 1.");
        }

        // 2. Try sending with Bot 1 (@TLS_Payment_bot) to group
        $confirmBotToken = $this->getConfirmBotToken();
        if (!empty($groupId) && !empty($confirmBotToken) && $confirmBotToken !== $invoiceBotToken) {
            $sent = $this->executeSendMessage($confirmBotToken, $groupId, $text, $replyMarkup);
            if ($sent) {
                return true;
            }
        }

        // 3. Fallback safeguard: If group is unreachable (chat not found / not added yet),
        // deliver directly to Admin DM (6646751752) using Bot 2 so no invoice is ever lost!
        $adminChatId = $this->getConfirmChatId();
        if (!empty($adminChatId) && !empty($invoiceBotToken)) {
            Log::info("Group {$groupId} unreachable. Delivering invoice to Admin DM {$adminChatId} via Bot 2.");
            $fallbackNotice = "⚠️ <i>[Invoice Group not reachable yet — Delivered to your DM via @TosLengSey_bot]</i>\n\n" . $text;
            return $this->executeSendMessage($invoiceBotToken, $adminChatId, $fallbackNotice, $replyMarkup);
        }

        return false;
    }

    /**
     * Public direct sender helper for invoice group messages (uses Invoice Bot by default)
     */
    public function sendToInvoiceGroupDirect(string $chatId, string $text, ?string $botToken = null, ?array $replyMarkup = null): bool
    {
        $token = !empty($botToken) ? $botToken : $this->getInvoiceBotToken();
        return $this->executeSendMessage($token, $chatId, $text, $replyMarkup);
    }

    /**
     * Backward-compatible sendMessage alias
     */
    public function sendMessage(string $text, ?array $replyMarkup = null): bool
    {
        return $this->sendToConfirmationChat($text, $replyMarkup);
    }

    /**
     * Internal HTTP executor for Telegram sendMessage
     */
    protected function executeSendMessage(string $botToken, string $chatId, string $text, ?array $replyMarkup = null): bool
    {
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
                Log::warning("Telegram sendMessage error ({$chatId}): " . $response->body());
            }

            return $response->successful();
        } catch (\Exception $e) {
            Log::warning("Telegram sendMessage exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Internal HTTP executor for Telegram sendPhoto
     */
    protected function executeSendPhoto(string $botToken, string $chatId, string $photoPath, string $caption, ?array $replyMarkup = null): bool
    {
        try {
            $fullPath = Storage::disk('public')->path($photoPath);
            if (!file_exists($fullPath)) {
                $fullPath = storage_path('app/public/' . $photoPath);
            }
            if (!file_exists($fullPath)) {
                $fullPath = public_path('storage/' . $photoPath);
            }

            if (!file_exists($fullPath)) {
                $publicUrl = $this->getBaseUrl() . '/storage/' . ltrim($photoPath, '/');
                return $this->executeSendMessage($botToken, $chatId, "📸 <b>RECEIPT SLIP:</b> <a href='{$publicUrl}'>View Image</a>\n\n" . $caption, $replyMarkup);
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
                return $this->executeSendMessage($botToken, $chatId, $caption, $replyMarkup);
            }

            return true;
        } catch (\Exception $e) {
            Log::warning("Telegram sendPhoto exception: " . $e->getMessage() . ". Falling back to text message.");
            return $this->executeSendMessage($botToken, $chatId, $caption, $replyMarkup);
        }
    }
}
