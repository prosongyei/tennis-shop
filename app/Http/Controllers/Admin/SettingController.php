<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
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
        ]);

        foreach ($validated as $key => $val) {
            Setting::set($key, (string) ($val ?? ''));
        }

        return redirect()->route('admin.settings.index')->with('success', 'Store & Bakong Bank settings updated successfully!');
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
}
