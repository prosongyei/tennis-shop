<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Setting;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Show the dynamic Bakong KHQR payment screen
     */
    public function showKhqr(string $orderNumber)
    {
        $order = Order::with('items')->where('order_number', $orderNumber)->firstOrFail();

        // If order already paid, redirect straight to receipt
        if ($order->is_paid) {
            return redirect()->route('orders.show', $order->order_number)
                ->with('success', 'This order has already been paid and confirmed.');
        }

        // Generate KHQR if not already generated
        if (empty($order->khqr_string)) {
            $this->paymentService->generateKhqr($order, (float) $order->total_amount, 'USD');
            $order->refresh();
        }

        $merchantName = Setting::get('bakong_account_name', config('services.bakong.account_name', 'SORSONGYEI SOY'));
        $khrExchangeRate = (float) Setting::get('khr_exchange_rate', 4100);
        $amountKhr = round($order->total_amount * $khrExchangeRate);

        $qrUsd = $this->paymentService->generateKhqr($order, (float) $order->total_amount, 'USD');
        $qrKhr = $this->paymentService->generateKhqr($order, $amountKhr, 'KHR');

        $order->update([
            'khqr_string' => $qrUsd['khqr_string'],
            'khqr_md5' => $qrUsd['khqr_md5'],
            'khqr_expiration' => now()->addMinutes(15),
        ]);

        $khqrUsdPayload = $qrUsd['khqr_string'];
        $khqrKhrPayload = $qrKhr['khqr_string'];

        return view('checkout.khqr', compact('order', 'merchantName', 'khrExchangeRate', 'amountKhr', 'khqrUsdPayload', 'khqrKhrPayload'));
    }

    /**
     * Switch payment currency between USD and KHR dynamically
     */
    public function switchCurrency(Request $request, string $orderNumber)
    {
        $currency = strtoupper($request->input('currency', 'USD'));
        if (!in_array($currency, ['USD', 'KHR'])) {
            $currency = 'USD';
        }

        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        $khrExchangeRate = (float) Setting::get('khr_exchange_rate', 4100);

        $amount = ($currency === 'KHR')
            ? round($order->total_amount * $khrExchangeRate)
            : (float) $order->total_amount;

        $result = $this->paymentService->generateKhqr($order, $amount, $currency);

        return response()->json([
            'success' => true,
            'currency' => $currency,
            'amount' => $amount,
            'amount_formatted' => ($currency === 'KHR') ? number_format($amount) . ' ៛' : '$' . number_format($amount, 2),
            'account_number' => ($currency === 'KHR') ? '010 921 065 (KHR)' : '010 921 061 (USD)',
            'khqr_string' => $result['khqr_string'],
            'khqr_md5' => $result['khqr_md5'],
        ]);
    }

    /**
     * Polling endpoint to check if Bakong payment has been received
     */
    public function checkStatus(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->first();

        if (!$order) {
            return response()->json(['success' => false, 'paid' => false, 'message' => 'Order not found.'], 404);
        }

        if ($order->is_paid) {
            return response()->json([
                'success' => true,
                'paid' => true,
                'order_number' => $order->order_number,
                'redirect_url' => route('orders.show', $order->order_number),
                'message' => 'Payment verified successfully!',
            ]);
        }

        // Query Bakong API using PaymentService if configured
        $result = $this->paymentService->checkBakongPayment($order);

        if ($result['paid']) {
            return response()->json([
                'success' => true,
                'paid' => true,
                'order_number' => $order->order_number,
                'redirect_url' => route('orders.show', $order->order_number),
                'message' => 'Payment verified via Bakong!',
            ]);
        }

        return response()->json([
            'success' => true,
            'paid' => false,
            'order_number' => $order->order_number,
            'message' => 'Awaiting payment confirmation...',
        ]);
    }

    /**
     * Simulate Bakong App payment for effortless live demo & testing
     */
    public function simulateBakongPay(Request $request, string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        if ($order->is_paid) {
            return response()->json([
                'success' => true,
                'paid' => true,
                'redirect_url' => route('orders.show', $order->order_number),
                'message' => 'Payment was already confirmed.',
            ]);
        }

        $mockBakongHash = 'BK-' . strtoupper(Str::random(12)) . '-' . time();
        $this->paymentService->markOrderAsPaid(
            $order,
            'khqr',
            $mockBakongHash,
            'Verified via Bakong KHQR Simulation Mode',
            Auth::id()
        );

        return response()->json([
            'success' => true,
            'paid' => true,
            'hash' => $mockBakongHash,
            'redirect_url' => route('orders.show', $order->order_number),
            'message' => 'Bakong KHQR payment simulated successfully! Order confirmed.',
        ]);
    }

    /**
     * Customer uploads receipt screenshot: forward to Telegram bot for admin confirmation
     */
    public function uploadProof(Request $request, string $orderNumber)
    {
        $request->validate([
            'proof_image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        if ($request->hasFile('proof_image')) {
            $path = $request->file('proof_image')->store('payment_proofs', 'public');
            $order->update([
                'payment_proof_image' => $path,
                'payment_status' => 'pending',
            ]);

            // Notify Store Admin on Telegram with the receipt photo & [Confirm] button
            try {
                app(\App\Services\TelegramService::class)->sendSlipUploadedAlert($order);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Telegram slip alert failed: " . $e->getMessage());
            }
        }

        return redirect()->route('orders.show', $order->order_number)
            ->with('success', "Payment receipt uploaded! Our store manager has received your slip on Telegram and will confirm your order shortly.");
    }

    /**
     * Customer manual confirmation removed - only authorized staff / Telegram bot can confirm
     */
    public function confirmPayment(Request $request, string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        // If staff / admin user is logged in, allow confirmation
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if ($user && ($user->isAdmin() || $user->isCashier())) {
            $transactionId = 'STAFF-' . strtoupper(Str::random(8));
            $this->paymentService->markOrderAsPaid(
                $order,
                'khqr',
                $transactionId,
                'Confirmed by staff ' . $user->name,
                $user->id
            );

            return redirect()->route('orders.show', $order->order_number)
                ->with('success', "Payment confirmed by staff! Order #{$order->order_number} is approved.");
        }

        // Notify admin on Telegram that customer is waiting
        try {
            app(\App\Services\TelegramService::class)->sendNewOrderAlert($order);
        } catch (\Throwable $e) {}

        return redirect()->route('orders.show', $order->order_number)
            ->with('info', "A notification has been sent to our store manager on Telegram to verify your payment.");
    }
}
