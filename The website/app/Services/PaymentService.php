<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * Generate Bakong KHQR payload and MD5 for an order
     * Migrated from Khor QR / Integrating-Bakong-KHQR
     */
    public function generateKhqr(Order $order, float $amount, string $currency = 'USD'): array
    {
        $currency = strtoupper($currency);
        $merchantName = config('services.bakong.account_name', 'SORSONGYEI SOY');
        $city = config('services.bakong.city', 'Phnom Penh');

        // Select exact ABA Account
        $accNumber = ($currency === 'KHR') ? '010921065' : '010921061';
        $currencyCode = ($currency === 'KHR') ? '116' : '840';

        $formattedAmount = ($currency === 'KHR')
            ? number_format($amount, 0, '.', '')
            : number_format($amount, 2, '.', '');

        // Tag 00 & Tag 01 (Dynamic QR)
        $tag00 = $this->formatTlv('00', '01');
        $tag01 = $this->formatTlv('01', '12');

        // Tag 29: Official ABA Bank Merchant Identification
        $subTag00 = $this->formatTlv('00', 'abaakhppxxx@abaa');
        $subTag01 = $this->formatTlv('01', $accNumber);
        $subTag02 = $this->formatTlv('02', 'ABA Bank');
        $tag29 = $this->formatTlv('29', $subTag00 . $subTag01 . $subTag02);

        // Tag 40: ABA Proprietary Dual-Currency P2P Tag (Enables direct ABA Mobile App Recognition)
        $abaTag00 = $this->formatTlv('00', 'abaP2P');
        $abaTag01 = $this->formatTlv('01', 'C86129338506');
        $abaTag02 = $this->formatTlv('02', '010921065'); // KHR Account
        $abaTag03 = $this->formatTlv('03', '010921061'); // USD Account
        $abaTag04 = $this->formatTlv('04', 'Dual');
        $tag40 = $this->formatTlv('40', $abaTag00 . $abaTag01 . $abaTag02 . $abaTag03 . $abaTag04);

        $tag52 = $this->formatTlv('52', '0000');
        $tag53 = $this->formatTlv('53', $currencyCode);
        $tag54 = $this->formatTlv('54', $formattedAmount);
        $tag58 = $this->formatTlv('58', 'KH');
        $tag59 = $this->formatTlv('59', strtoupper(substr($merchantName, 0, 25)));
        $tag60 = $this->formatTlv('60', strtoupper(substr($city, 0, 15)));

        // Tag 62: Order Number Reference
        $refSubtag = $this->formatTlv('01', (string) $order->order_number);
        $terminalSubtag = $this->formatTlv('07', 'WEB');
        $tag62 = $this->formatTlv('62', $refSubtag . $terminalSubtag);

        // Tag 99: Dynamic Expiration (15 Mins)
        $nowMs = (string) round(microtime(true) * 1000);
        $expireMs = (string) (round(microtime(true) * 1000) + (15 * 60 * 1000));
        $tag99 = $this->formatTlv('99', $this->formatTlv('00', $nowMs) . $this->formatTlv('01', $expireMs));

        // Assemble Payload & Calculate CRC-16
        $rawPayload = $tag00 . $tag01 . $tag29 . $tag40 . $tag52 . $tag53 . $tag54 . $tag58 . $tag59 . $tag60 . $tag62 . $tag99 . '6304';
        $crc = $this->calculateCrc16($rawPayload);
        $khqrString = $rawPayload . $crc;
        $md5 = md5($khqrString);
        $expiration = now()->addMinutes(15);

        $order->update([
            'khqr_string' => $khqrString,
            'khqr_md5' => $md5,
            'khqr_expiration' => $expiration,
        ]);

        return [
            'success' => true,
            'khqr_string' => $khqrString,
            'khqr_md5' => $md5,
            'amount' => $amount,
            'currency' => $currency,
            'merchant_name' => $merchantName,
            'expiration' => $expiration->toIso8601String(),
        ];
    }

    /**
     * Check payment status via Bakong API or verify by MD5
     * Matches logic from backend/controller/checkpayment.controller.js
     */
    public function checkBakongPayment(Order $order): array
    {
        if ($order->is_paid) {
            return [
                'paid' => true,
                'message' => 'Payment already confirmed',
                'order' => $order,
            ];
        }

        // Optional simulation mode for development/testing
        if (config('services.bakong.simulation_mode', false)) {
            $simHash = 'SIM_' . strtoupper(bin2hex(random_bytes(16)));
            $this->markOrderAsPaid($order, 'khqr', $simHash, 'Simulated Payment in Test Mode');
            return [
                'paid' => true,
                'message' => 'Simulated test payment approved!',
                'hash' => $simHash,
            ];
        }

        $token = Setting::get('bakong_access_token') ?: config('services.bakong.access_token') ?: config('services.bakong.api_token');
        $primaryUrl = Setting::get('bakong_api_url') ?: config('services.bakong.api_url', 'https://api-bakong.nbc.gov.kh/v1');
        $devUrl = config('services.bakong.dev_url', 'https://sit-api-bakong.nbc.gov.kh/v1');
        $prodUrl = config('services.bakong.prod_url', 'https://api-bakong.nbc.gov.kh/v1');

        $endpoints = array_unique(array_filter([$primaryUrl, $prodUrl, $devUrl]));

        if (!empty($token) && !empty($order->khqr_md5)) {
            foreach ($endpoints as $apiUrl) {
                // Ensure endpoint does not duplicate /check_transaction_by_md5
                $cleanUrl = rtrim($apiUrl, '/');
                $endpoint = str_ends_with($cleanUrl, '/check_transaction_by_md5')
                    ? $cleanUrl
                    : "{$cleanUrl}/check_transaction_by_md5";

                try {
                    $response = Http::withToken($token)
                        ->timeout(8)
                        ->post($endpoint, [
                            'md5' => $order->khqr_md5,
                        ]);

                    if ($response->successful()) {
                        $data = $response->json();
                        if (isset($data['responseCode']) && $data['responseCode'] === 0 && !empty($data['data']['hash'])) {
                            $this->markOrderAsPaid($order, 'khqr', $data['data']['hash'], 'Auto-verified via Bakong API');
                            return [
                                'paid' => true,
                                'message' => 'Payment confirmed via Bakong!',
                                'hash' => $data['data']['hash'],
                            ];
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("Bakong API check failed on {$endpoint}: " . $e->getMessage());
                }
            }
        }

        return [
            'paid' => false,
            'message' => 'Payment pending or not yet detected.',
        ];
    }

    /**
     * Mark order as paid and record Payment transaction
     */
    public function markOrderAsPaid(Order $order, string $method, ?string $transactionId = null, ?string $note = null, ?int $verifiedById = null): Payment
    {
        $order->update([
            'payment_status' => 'paid',
            'order_status' => ($order->order_status === 'pending') ? 'confirmed' : $order->order_status,
            'bakong_hash' => $transactionId,
            'paid_at' => now(),
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'amount' => $order->total_amount,
            'currency' => 'USD',
            'payment_method' => $method,
            'transaction_id' => $transactionId,
            'bakong_hash' => $transactionId,
            'status' => 'verified',
            'verified_by' => $verifiedById ?? Auth::id(),
            'verified_at' => now(),
            'note' => $note ?? 'Payment recorded successfully',
        ]);

        // Send Telegram notification if enabled
        app(TelegramService::class)->sendPaymentConfirmedAlert($order);

        // Log to Google Sheet if enabled
        app(GoogleSheetService::class)->appendOrder($order);

        return $payment;
    }

    /**
     * Format TLV (Tag-Length-Value)
     */
    private function formatTlv(string $tag, string $value): string
    {
        $length = str_pad(strlen($value), 2, '0', STR_PAD_LEFT);
        return $tag . $length . $value;
    }

    /**
     * Calculate CRC16-CCITT (0xFFFF, Poly 0x1021) as required by EMVCo / Bakong KHQR
     */
    private function calculateCrc16(string $data): string
    {
        $crc = 0xFFFF;
        for ($i = 0; $i < strlen($data); $i++) {
            $crc ^= (ord($data[$i]) << 8);
            for ($j = 0; $j < 8; $j++) {
                if ($crc & 0x8000) {
                    $crc = (($crc << 1) ^ 0x1021) & 0xFFFF;
                } else {
                    $crc = ($crc << 1) & 0xFFFF;
                }
            }
        }
        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }
}
