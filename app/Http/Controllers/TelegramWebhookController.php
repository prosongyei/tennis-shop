<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Setting;
use App\Services\PaymentService;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TelegramWebhookController extends Controller
{
    protected PaymentService $paymentService;
    protected TelegramService $telegramService;

    public function __construct(PaymentService $paymentService, TelegramService $telegramService)
    {
        $this->paymentService = $paymentService;
        $this->telegramService = $telegramService;
    }

    /**
     * Handle incoming Telegram Webhook updates & GET health/status
     */
    public function handle(Request $request)
    {
        $botToken = Setting::get('telegram_bot_token', config('services.telegram.bot_token', '8851308730:AAFIs5Dyu4exg6mXw0JLN1jbOuQyvgucrPc'));

        // If accessed via GET, check webhook status and return info
        if ($request->isMethod('get')) {
            try {
                $info = Http::get("https://api.telegram.org/bot{$botToken}/getWebhookInfo")->json();
                $savedChatId = Setting::get('telegram_chat_id', config('services.telegram.chat_id'));
                return response()->json([
                    'status' => 'ok',
                    'bot' => 'Confirmation_buddy (@TLS_Payment_bot)',
                    'webhook' => $info['result'] ?? $info,
                    'saved_admin_chat_id' => $savedChatId ?: 'Not registered yet (Send /start to @TLS_Payment_bot in Telegram to link your phone!)',
                ]);
            } catch (\Throwable $e) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
            }
        }

        $update = $request->all();
        Log::info('Telegram webhook received:', $update);

        // 1. Handle Inline Keyboard Button Click (callback_query)
        if (isset($update['callback_query'])) {
            $this->handleCallbackQuery($update['callback_query'], $botToken);
            return response()->json(['ok' => true]);
        }

        // 2. Handle Text Messages (/start, /pending, /help, etc.)
        if (isset($update['message'])) {
            $this->handleMessage($update['message'], $botToken);
            return response()->json(['ok' => true]);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Process interactive button clicks (e.g. Confirm Payment)
     */
    protected function handleCallbackQuery(array $callback, string $botToken): void
    {
        $callbackId = $callback['id'] ?? '';
        $data = $callback['data'] ?? '';
        $message = $callback['message'] ?? [];
        $chatId = $message['chat']['id'] ?? null;
        $messageId = $message['message_id'] ?? null;

        // Auto-save chat ID
        if ($chatId) {
            Setting::set('telegram_chat_id', (string) $chatId, 'telegram');
            Setting::set('telegram_enabled', '1', 'telegram');
        }

        // CONFIRM ORDER PAYMENT
        if (str_starts_with($data, 'confirm_')) {
            $orderNumber = substr($data, 8);
            $order = Order::where('order_number', $orderNumber)->first();

            if (!$order) {
                $this->answerCallback($botToken, $callbackId, "❌ Order #{$orderNumber} not found.", true);
                return;
            }

            if ($order->is_paid) {
                $this->answerCallback($botToken, $callbackId, "ℹ️ Order #{$orderNumber} is already confirmed as Paid.", false);
                return;
            }

            // Mark order as Paid & Confirmed
            $transactionId = 'TG-PAY-' . strtoupper(Str::random(8));
            $this->paymentService->markOrderAsPaid(
                $order,
                'khqr',
                $transactionId,
                'Verified and confirmed by Admin via Telegram Bot',
                null
            );

            // Pop up toast on admin's phone
            $this->answerCallback($botToken, $callbackId, "✅ Order #{$orderNumber} confirmed as PAID!", false);

            // Edit Telegram message to update status and remove action buttons
            $safeCustomerName = htmlspecialchars($order->customer_name, ENT_QUOTES, 'UTF-8');
            $safeCustomerPhone = htmlspecialchars($order->customer_phone, ENT_QUOTES, 'UTF-8');
            $newText = "✅ <b>PAYMENT CONFIRMED & APPROVED!</b>\n\n"
                . "<b>Order:</b> #{$order->order_number}\n"
                . "<b>Customer:</b> {$safeCustomerName} ({$safeCustomerPhone})\n"
                . "<b>Total Paid:</b> <b>$" . number_format($order->total_amount, 2) . "</b>\n"
                . "<b>Method:</b> " . strtoupper(str_replace('_', ' ', $order->payment_method)) . "\n"
                . "<b>Confirmed At:</b> " . now()->format('d M Y, h:i A') . "\n"
                . "<b>Status:</b> PAID & CONFIRMED\n\n"
                . "<i>Customer screen has been automatically updated to Confirmed.</i>";

            $updatedButtons = [
                'inline_keyboard' => [
                    [
                        ['text' => "📄 View Invoice", 'url' => $this->telegramService->getInvoiceUrl($order->order_number)],
                        ['text' => "⚙️ Admin Orders", 'url' => $this->telegramService->getAdminOrdersUrl()],
                    ]
                ]
            ];

            if (isset($message['photo'])) {
                // If it was a photo message
                $this->editMessageCaption($botToken, $chatId, $messageId, $newText, $updatedButtons);
            } else {
                // Regular text message
                $this->editMessageText($botToken, $chatId, $messageId, $newText, $updatedButtons);
            }

            return;
        }

        // REJECT PAYMENT
        if (str_starts_with($data, 'reject_')) {
            $orderNumber = substr($data, 7);
            $order = Order::where('order_number', $orderNumber)->first();

            if ($order) {
                $order->update([
                    'payment_status' => 'failed',
                ]);

                $this->answerCallback($botToken, $callbackId, "❌ Payment rejected for Order #{$orderNumber}.", false);

                $safeCustName = htmlspecialchars($order->customer_name, ENT_QUOTES, 'UTF-8');
                $rejectText = "❌ <b>PAYMENT REJECTED BY ADMIN</b>\n\n"
                    . "<b>Order:</b> #{$order->order_number}\n"
                    . "<b>Customer:</b> {$safeCustName}\n"
                    . "<b>Amount:</b> $" . number_format($order->total_amount, 2) . "\n"
                    . "<b>Status:</b> PAYMENT FAILED / REJECTED\n"
                    . "<b>Time:</b> " . now()->format('d M Y, h:i A');

                if (isset($message['photo'])) {
                    $this->editMessageCaption($botToken, $chatId, $messageId, $rejectText);
                } else {
                    $this->editMessageText($botToken, $chatId, $messageId, $rejectText);
                }
            }
            return;
        }

        $this->answerCallback($botToken, $callbackId, "Unknown command.");
    }

    /**
     * Process text commands (/start, /pending, etc.)
     */
    protected function handleMessage(array $msg, string $botToken): void
    {
        $chatId = $msg['chat']['id'] ?? null;
        $text = trim($msg['text'] ?? '');
        $senderName = $msg['from']['first_name'] ?? 'Admin';

        if (!$chatId) return;

        // Automatically store the chat ID so all order alerts go here
        Setting::set('telegram_chat_id', (string) $chatId, 'telegram');
        Setting::set('telegram_enabled', '1', 'telegram');

        if ($text === '/start') {
            $welcome = "👋 <b>Hello {$senderName}! Welcome to TosLengSey Store Confirmation Bot.</b>\n\n"
                . "✅ <b>Your Telegram is now connected!</b>\n"
                . "<b>Chat ID:</b> <code>{$chatId}</code>\n\n"
                . "📱 Whenever a customer places an order or pays via ABA KHQR, you will receive an alert here with a <b>[ ✅ Confirm Payment ]</b> button.\n\n"
                . "When you verify the money in your phone's banking app, simply tap the button and the customer's order will immediately be confirmed on the website!\n\n"
                . "<b>Useful Commands:</b>\n"
                . "• /pending - View orders waiting for confirmation\n"
                . "• /status - Check store connection";

            $this->sendDirectMessage($botToken, $chatId, $welcome);
            return;
        }

        if ($text === '/pending') {
            $pendingOrders = Order::where('payment_status', 'pending')
                ->where('order_status', '!=', 'cancelled')
                ->latest()
                ->take(5)
                ->get();

            if ($pendingOrders->isEmpty()) {
                $this->sendDirectMessage($botToken, $chatId, "🎉 <b>All caught up!</b>\nThere are currently no orders waiting for payment confirmation.");
                return;
            }

            $this->sendDirectMessage($botToken, $chatId, "📋 <b>Found {$pendingOrders->count()} order(s) awaiting payment confirmation:</b>");

            foreach ($pendingOrders as $order) {
                $msg = "🛒 <b>Order #{$order->order_number}</b>\n"
                    . "• Customer: {$order->customer_name} ({$order->customer_phone})\n"
                    . "• Total: <b>$" . number_format($order->total_amount, 2) . "</b>\n"
                    . "• Method: " . strtoupper(str_replace('_', ' ', $order->payment_method)) . "\n"
                    . "• Time: " . ($order->created_at ? $order->created_at->format('d M, h:i A') : 'N/A');

                $buttons = [
                    'inline_keyboard' => [
                        [
                            ['text' => "✅ Confirm Payment ($" . number_format($order->total_amount, 2) . ")", 'callback_data' => "confirm_{$order->order_number}"],
                            ['text' => "❌ Reject", 'callback_data' => "reject_{$order->order_number}"],
                        ]
                    ]
                ];

                $this->sendDirectMessage($botToken, $chatId, $msg, $buttons);
            }
            return;
        }

        if ($text === '/status') {
            $orderCount = Order::count();
            $paidCount = Order::where('payment_status', 'paid')->count();
            $this->sendDirectMessage(
                $botToken,
                $chatId,
                "📊 <b>Store Bot Status: ACTIVE</b>\n\n• Store: TosLengSey Badminton Flagship\n• Total Orders: {$orderCount}\n• Paid Orders: {$paidCount}\n• Connected Chat ID: <code>{$chatId}</code>"
            );
            return;
        }

        // Generic reply for any other message
        $this->sendDirectMessage(
            $botToken,
            $chatId,
            "ℹ️ Command received. Type /pending to view orders waiting for payment confirmation, or /status to check connection."
        );
    }

    protected function answerCallback(string $botToken, string $callbackId, string $text, bool $showAlert = false): void
    {
        try {
            Http::timeout(5)->post("https://api.telegram.org/bot{$botToken}/answerCallbackQuery", [
                'callback_query_id' => $callbackId,
                'text' => $text,
                'show_alert' => $showAlert,
            ]);
        } catch (\Throwable $e) {}
    }

    protected function editMessageText(string $botToken, $chatId, $messageId, string $text, ?array $replyMarkup = null): void
    {
        try {
            $payload = [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $text,
                'parse_mode' => 'HTML',
            ];
            if ($replyMarkup) {
                $payload['reply_markup'] = json_encode($replyMarkup);
            }
            Http::timeout(5)->post("https://api.telegram.org/bot{$botToken}/editMessageText", $payload);
        } catch (\Throwable $e) {}
    }

    protected function editMessageCaption(string $botToken, $chatId, $messageId, string $caption, ?array $replyMarkup = null): void
    {
        try {
            $payload = [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'caption' => $caption,
                'parse_mode' => 'HTML',
            ];
            if ($replyMarkup) {
                $payload['reply_markup'] = json_encode($replyMarkup);
            }
            Http::timeout(5)->post("https://api.telegram.org/bot{$botToken}/editMessageCaption", $payload);
        } catch (\Throwable $e) {}
    }

    protected function sendDirectMessage(string $botToken, $chatId, string $text, ?array $replyMarkup = null): void
    {
        try {
            $payload = [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
            ];
            if ($replyMarkup) {
                $payload['reply_markup'] = json_encode($replyMarkup);
            }
            Http::timeout(5)->post("https://api.telegram.org/bot{$botToken}/sendMessage", $payload);
        } catch (\Throwable $e) {}
    }
}
