<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Setting;
use App\Services\BakongKhqrService;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class BakongPaymentController extends Controller
{
    protected BakongKhqrService $khqrService;
    protected PaymentService $paymentService;

    public function __construct(BakongKhqrService $khqrService, PaymentService $paymentService)
    {
        $this->khqrService = $khqrService;
        $this->paymentService = $paymentService;
    }

    /**
     * Show KHQR payment checkout page
     */
    public function checkout(Request $request)
    {
        $orderNumber = $request->query('order') ?: $request->query('order_id');

        if ($orderNumber) {
            $order = Order::where('order_number', $orderNumber)->first();
        } else {
            $order = Order::latest()->first();
        }

        if (!$order) {
            return redirect()->route('shop.catalog')->with('warning', 'No active order found.');
        }

        if ($order->is_paid) {
            return redirect()->route('orders.show', $order->order_number)
                ->with('success', 'Order has already been paid and confirmed.');
        }

        $currency = strtoupper($request->query('currency', 'USD'));
        $khrExchangeRate = (float) (config('bakong.usd_khr_rate') ?: Setting::get('khr_exchange_rate', 4100));
        $amountKhr = round($order->total_amount * $khrExchangeRate);

        $qrUsd = $this->khqrService->generateDynamicKhqr((float) $order->total_amount, 'USD', $order->order_number);
        $qrKhr = $this->khqrService->generateDynamicKhqr($amountKhr, 'KHR', $order->order_number);

        $order->update([
            'khqr_string' => $qrUsd['khqr_string'],
            'khqr_md5' => $qrUsd['khqr_md5'],
            'khqr_expiration' => now()->addMinutes(15),
        ]);

        $khqrUsdPayload = $qrUsd['khqr_string'];
        $khqrKhrPayload = $qrKhr['khqr_string'];
        $merchantName = config('bakong.merchant_name', 'SORSONGYEI SOY');

        return view('checkout.khqr', compact('order', 'merchantName', 'khrExchangeRate', 'amountKhr', 'currency', 'khqrUsdPayload', 'khqrKhrPayload'));
    }

    /**
     * On-the-fly QR regeneration endpoint when toggling USD/KHR
     */
    public function generateQr(Request $request)
    {
        $request->validate([
            'amount' => ['nullable', 'numeric'],
            'currency' => ['required', 'string', 'in:USD,KHR,usd,khr'],
            'bill_number' => ['nullable', 'string'],
        ]);

        $currency = strtoupper($request->input('currency'));
        $billNumber = $request->input('bill_number', 'ORD-WEB-' . time());
        $khrRate = (float) (config('bakong.usd_khr_rate') ?: 4100);

        $order = Order::where('order_number', $billNumber)->first();
        $totalUsd = $order ? (float) $order->total_amount : (float) $request->input('amount', 25.00);

        $amount = ($currency === 'KHR') ? round($totalUsd * $khrRate) : $totalUsd;

        $qrData = $this->khqrService->generateDynamicKhqr($amount, $currency, $billNumber);

        if ($order) {
            $order->update([
                'khqr_string' => $qrData['khqr_string'],
                'khqr_md5' => $qrData['khqr_md5'],
            ]);
        }

        return response()->json([
            'success' => true,
            'currency' => $currency,
            'amount' => $amount,
            'amount_formatted' => ($currency === 'KHR') ? number_format($amount) . ' ៛' : '$' . number_format($amount, 2),
            'account_number' => $qrData['account_number'],
            'khqr_string' => $qrData['khqr_string'],
            'khqr_md5' => $qrData['khqr_md5'],
        ]);
    }

    /**
     * Check transaction status with NBC Bakong API
     */
    public function checkPayment(Request $request)
    {
        $md5 = $request->input('md5');
        $billNumber = $request->input('bill_number') ?: $request->input('order_number');

        if (empty($md5) && !empty($billNumber)) {
            $order = Order::where('order_number', $billNumber)->first();
            $md5 = $order ? $order->khqr_md5 : null;
        }

        if (empty($md5)) {
            return response()->json(['paid' => false, 'message' => 'No MD5 hash provided.'], 400);
        }

        $order = Order::where('khqr_md5', $md5)->first();
        if ($order && $order->is_paid) {
            return response()->json([
                'paid' => true,
                'message' => 'Payment already verified.',
                'order_number' => $order->order_number,
                'redirect_url' => route('orders.show', $order->order_number),
            ]);
        }

        $result = $this->khqrService->checkTransactionByMd5($md5);

        if (!empty($result['paid'])) {
            if ($order) {
                $this->paymentService->markOrderAsPaid($order, 'khqr', $result['hash'] ?? null, 'Verified via Bakong Open API');
            }
            return response()->json([
                'paid' => true,
                'message' => $result['message'] ?? 'Payment confirmed successfully.',
                'order_number' => $order ? $order->order_number : null,
                'redirect_url' => $order ? route('orders.show', $order->order_number) : null,
            ]);
        }

        return response()->json([
            'paid' => false,
            'message' => $result['message'] ?? 'Payment pending.',
        ]);
    }
}
