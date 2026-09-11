<?php

namespace App\Services;

use App\Models\Order;
use Google\Client as GoogleClient;
use Google\Service\Sheets as GoogleSheets;
use Google\Service\Sheets\ValueRange;
use Illuminate\Support\Facades\Log;

class GoogleSheetService
{
    /**
     * Append a new row to the configured Google Sheet for this order.
     */
    public function appendOrder(Order $order): bool
    {
        $enabled = (bool) config('services.google_sheet.enabled');
        $spreadsheetId = config('services.google_sheet.spreadsheet_id');
        $credentialsPath = config('services.google_sheet.credentials_path');
        $sheetName = config('services.google_sheet.sheet_name', 'Sheet1');

        if (!$enabled || empty($spreadsheetId) || empty($credentialsPath) || !file_exists($credentialsPath)) {
            Log::info('Google Sheet logging skipped (disabled or missing config) for order ' . $order->order_number);
            return false;
        }

        try {
            $client = new GoogleClient();
            $client->setAuthConfig($credentialsPath);
            $client->addScope(GoogleSheets::SPREADSHEETS);

            $service = new GoogleSheets($client);

            $row = [[
                $order->order_number,
                $order->customer_name,
                $order->customer_phone,
                $order->customer_email,
                number_format((float) $order->total_amount, 2, '.', ''),
                strtoupper($order->payment_method),
                strtoupper($order->payment_status),
                strtoupper($order->order_status),
                $order->created_at ? $order->created_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
            ]];

            $body = new ValueRange(['values' => $row]);

            $service->spreadsheets_values->append(
                $spreadsheetId,
                "{$sheetName}!A:I",
                $body,
                ['valueInputOption' => 'USER_ENTERED']
            );

            return true;
        } catch (\Exception $e) {
            Log::warning('Google Sheet append failed for order ' . $order->order_number . ': ' . $e->getMessage());
            return false;
        }
    }
}
