<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'store_name' => Setting::get('store_name', 'TosLengSey Tennis & Badminton Pro Store'),
            'store_phone' => Setting::get('store_phone', '+855 96 785 5710'),
            'store_email' => Setting::get('store_email', 'contact@toslengsey.com'),
            'store_address' => Setting::get('store_address', '#128 St. 2004, Sen Sok, Phnom Penh, Cambodia'),
            'khr_exchange_rate' => Setting::get('khr_exchange_rate', '4100'),
            'bakong_account_name' => Setting::get('bakong_account_name', config('services.bakong.account_name', 'SORSONGYEI SOY')),
            'bakong_account_username' => Setting::get('bakong_account_username', config('services.bakong.account_username', '010921061@aba')),
            'bakong_phone_number' => Setting::get('bakong_phone_number', config('services.bakong.phone_number', '010921061')),
            'bakong_city' => Setting::get('bakong_city', config('services.bakong.city', 'Phnom Penh')),
            'bakong_access_token' => Setting::get('bakong_access_token', config('services.bakong.access_token', '')),
            'bakong_api_url' => Setting::get('bakong_api_url', config('services.bakong.api_url', 'https://api-bakong.nbc.gov.kh/v1/check_transaction_by_md5')),
            'telegram_enabled' => Setting::get('telegram_enabled', '1'),
            'telegram_confirm_bot_token' => Setting::get('telegram_confirm_bot_token', '8851308730:AAFIs5Dyu4exg6mXw0JLN1jbOuQyvgucrPc'),
            'telegram_confirm_chat_id' => Setting::get('telegram_confirm_chat_id', '6646751752'),
            'telegram_invoice_bot_token' => Setting::get('telegram_invoice_bot_token', '8862288371:AAGoz8XBLGz4eacOcIbprOWIn5eNiDAadrw'),
            'telegram_invoice_group_id' => Setting::get('telegram_invoice_group_id', '-5475494678'),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'store_phone' => ['nullable', 'string', 'max:50'],
            'store_email' => ['nullable', 'email', 'max:255'],
            'store_address' => ['nullable', 'string', 'max:500'],
            'khr_exchange_rate' => ['required', 'numeric', 'min:1000', 'max:10000'],
            'bakong_account_name' => ['required', 'string', 'max:100'],
            'bakong_account_username' => ['required', 'string', 'max:100'],
            'bakong_phone_number' => ['nullable', 'string', 'max:30'],
            'bakong_city' => ['required', 'string', 'max:50'],
            'bakong_access_token' => ['nullable', 'string'],
            'bakong_api_url' => ['required', 'url'],
            'telegram_enabled' => ['nullable'],
            'telegram_confirm_bot_token' => ['nullable', 'string'],
            'telegram_confirm_chat_id' => ['nullable', 'string'],
            'telegram_invoice_bot_token' => ['nullable', 'string'],
            'telegram_invoice_group_id' => ['nullable', 'string'],
        ]);

        $validated['telegram_enabled'] = $request->has('telegram_enabled') ? '1' : '0';

        foreach ($validated as $key => $val) {
            Setting::set($key, (string) ($val ?? ''));
        }

        return redirect()->route('admin.settings.index')->with('success', 'Store, Bakong Gateway & Telegram settings updated successfully!');
    }

    public function testBakong()
    {
        $token = Setting::get('bakong_access_token') ?: config('services.bakong.access_token');
        $rawUrl = Setting::get('bakong_api_url') ?: config('services.bakong.api_url', 'https://api-bakong.nbc.gov.kh/v1/check_transaction_by_md5');
        $cleanUrl = rtrim($rawUrl, '/');
        $endpoint = str_ends_with($cleanUrl, '/check_transaction_by_md5') ? $cleanUrl : "{$cleanUrl}/check_transaction_by_md5";
        $apiUrl = $endpoint;

        if (empty($token)) {
            return response()->json([
                'success' => false,
                'message' => 'No Bakong Access Token configured.',
            ]);
        }

        try {
            $response = Http::withToken($token)
                ->timeout(8)
                ->post($endpoint, [
                    'md5' => '00000000000000000000000000000000',
                ]);

            if ($response->status() === 200) {
                return response()->json([
                    'success' => true,
                    'status' => $response->status(),
                    'message' => 'Connected to Bakong API successfully! Token is valid.',
                    'endpoint' => $apiUrl,
                ]);
            }

            if ($response->status() === 401) {
                return response()->json([
                    'success' => false,
                    'status' => 401,
                    'message' => 'Unauthorized: Token is invalid or environment mismatch (SIT vs Production).',
                    'endpoint' => $apiUrl,
                ]);
            }

            return response()->json([
                'success' => false,
                'status' => $response->status(),
                'message' => 'Bakong API returned status: ' . $response->status(),
                'endpoint' => $apiUrl,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
                'endpoint' => $apiUrl,
            ]);
        }
    }

    public function testTelegramConfirm(TelegramService $telegramService)
    {
        $testMsg = "🧪 <b>TEST: PAYMENT CONFIRMATION BOT</b>\n\n"
            . "This is a test notification from TosLengSey Store Settings.\n"
            . "Your Payment Confirmation Bot is working properly!\n"
            . "Time: " . now()->format('d M Y, h:i A');

        $replyMarkup = [
            'inline_keyboard' => [
                [
                    ['text' => "✅ Test Button (OK)", 'callback_data' => "test_ping"],
                    ['text' => "⚙️ Admin Settings", 'url' => $telegramService->getBaseUrl() . '/admin/settings'],
                ]
            ]
        ];

        $sent = $telegramService->sendToConfirmationChat($testMsg, $replyMarkup);

        if ($sent) {
            return response()->json([
                'success' => true,
                'message' => "Test message sent successfully to Admin DM (" . $telegramService->getConfirmChatId() . ") via Confirmation Bot!",
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => "Failed to send to Admin DM (" . $telegramService->getConfirmChatId() . "). Check bot token or verify the admin has messaged the bot.",
        ]);
    }

    public function testTelegramInvoice(TelegramService $telegramService)
    {
        $groupId = $telegramService->getInvoiceGroupId();
        $invoiceBotToken = $telegramService->getInvoiceBotToken();
        $adminChatId = $telegramService->getConfirmChatId();

        $testInvoice = "🧾 <b>TEST: INVOICE & RECEIPT CHANNEL</b>\n"
            . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
            . "<b>TOSLENGSEY BADMINTON FLAGSHIP</b>\n"
            . "This is a test notification to verify that your Invoice Channel is operational!\n"
            . "<b>Channel:</b> Digital Invoices & Order Receipts\n"
            . "<b>Bot:</b> @TosLengSey_bot\n"
            . "<b>Status:</b> ✅ OPERATIONAL\n"
            . "<b>Time:</b> " . now()->format('d M Y, h:i A') . "\n"
            . "━━━━━━━━━━━━━━━━━━━━━━━━";

        $replyMarkup = [
            'inline_keyboard' => [
                [
                    ['text' => "🏸 Visit Store", 'url' => $telegramService->getBaseUrl() . '/shop'],
                ]
            ]
        ];

        // 1. Try sending directly to configured group first
        $sentToGroup = false;
        if (!empty($groupId)) {
            $sentToGroup = $telegramService->sendToInvoiceGroupDirect($invoiceBotToken, $groupId, $testInvoice, $replyMarkup);
        }

        if ($sentToGroup) {
            return response()->json([
                'success' => true,
                'message' => "Test tax invoice sent successfully to Telegram Group ({$groupId}) via @TosLengSey_bot!",
            ]);
        }

        // 2. If group is not accessible, deliver to Admin DM via Bot 2
        $sentToDm = $telegramService->sendToInvoiceGroupDirect(
            $invoiceBotToken,
            $adminChatId,
            "⚠️ <i>[Group {$groupId} is not reachable by bot yet — Delivered to your DM]</i>\n\n" . $testInvoice,
            $replyMarkup
        );

        if ($sentToDm) {
            return response()->json([
                'success' => true,
                'message' => "Delivered test invoice to your Telegram DM ({$adminChatId}) via @TosLengSey_bot! Note: To have invoices sent to a group, please add @TosLengSey_bot to your group and send /start in the group.",
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => "Failed to deliver test invoice. Please verify that @TosLengSey_bot is active.",
        ]);
    }
}
