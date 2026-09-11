<?php

return [
    'account_id' => env('BAKONG_ACCOUNT_ID_USD', '010921061@aba'),
    'account_id_usd' => env('BAKONG_ACCOUNT_ID_USD', '010921061@aba'),
    'account_id_khr' => env('BAKONG_ACCOUNT_ID_KHR', '010921065@aba'),
    'merchant_name' => env('BAKONG_MERCHANT_NAME', 'SORSONGYEI SOY'),
    'merchant_city' => env('BAKONG_MERCHANT_CITY', 'Phnom Penh'),
    'currency' => env('BAKONG_CURRENCY', 'USD'),
    'usd_khr_rate' => env('BAKONG_USD_KHR_RATE', 4100),
    'api_url' => env('BAKONG_API_URL', 'https://api-bakong.nbc.gov.kh/v1/check_transaction_by_md5'),
    'api_token' => env('BAKONG_API_TOKEN', env('BAKONG_ACCESS_TOKEN', '')),
    'simulation_mode' => (bool) env('BAKONG_SIMULATION_MODE', false),
];
