<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BakongKhqrService
{
    /**
     * Generate dynamic Bakong KHQR with ABA Bank Tag 29 and Tag 40 P2P tags
     */
    public function generateDynamicKhqr(float $amount, string $currency = 'USD', string $billNumber = 'ORD-001'): array
    {
        $currency = strtoupper($currency);
        $merchantName = config('bakong.merchant_name', 'SORSONGYEI SOY');
        $city = config('bakong.merchant_city', 'Phnom Penh');

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

        // Tag 62: Order Number Reference & Terminal
        $refSubtag = $this->formatTlv('01', (string) $billNumber);
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

        return [
            'success' => true,
            'khqr_string' => $khqrString,
            'khqr_md5' => $md5,
            'amount' => $amount,
            'currency' => $currency,
            'merchant_name' => $merchantName,
            'account_number' => ($currency === 'KHR') ? '010 921 065 (KHR)' : '010 921 061 (USD)',
            'crc' => $crc,
        ];
    }

    /**
     * Check transaction status with NBC Bakong Open API
     */
    public function checkTransactionByMd5(string $md5): array
    {
        if (config('bakong.simulation_mode', false)) {
            return [
                'paid' => true,
                'message' => 'Simulated test payment verified.',
                'hash' => 'SIM_' . strtoupper(bin2hex(random_bytes(16))),
            ];
        }

        $token = config('bakong.api_token') ?: config('services.bakong.access_token');
        $rawUrl = config('bakong.api_url', 'https://api-bakong.nbc.gov.kh/v1/check_transaction_by_md5');
        $cleanUrl = rtrim($rawUrl, '/');
        $endpoint = str_ends_with($cleanUrl, '/check_transaction_by_md5') ? $cleanUrl : "{$cleanUrl}/check_transaction_by_md5";

        if (empty($token)) {
            return [
                'paid' => false,
                'message' => 'Bakong API token not configured.',
            ];
        }

        // Throttle outbound requests per MD5: at most once every 5 seconds
        $cacheKey = 'bakong_khqr_throttle_' . substr($md5, 0, 16);
        if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
            return [
                'paid' => false,
                'message' => 'Awaiting payment confirmation...',
            ];
        }
        \Illuminate\Support\Facades\Cache::put($cacheKey, true, 5);

        try {
            $response = Http::withToken($token)
                ->timeout(2.5)
                ->post($endpoint, [
                    'md5' => $md5,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['responseCode']) && $data['responseCode'] === 0 && !empty($data['data']['hash'])) {
                    return [
                        'paid' => true,
                        'message' => 'Payment received successfully.',
                        'hash' => $data['data']['hash'],
                    ];
                }
            }

            return [
                'paid' => false,
                'message' => 'Payment pending or not yet detected.',
            ];
        } catch (\Throwable $e) {
            Log::warning("Bakong check failed: " . $e->getMessage());
            return [
                'paid' => false,
                'message' => 'Error querying Bakong API: ' . $e->getMessage(),
            ];
        }
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
     * Calculate CRC16-CCITT (0xFFFF, Poly 0x1021)
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
