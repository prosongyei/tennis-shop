<?php

return [
    'bakong' => [
        'account_name' => env('BAKONG_ACCOUNT_NAME', env('BAKONG_MERCHANT_NAME', 'SORSONGYEI SOY')),
        'account_username' => env('BAKONG_ACCOUNT_USERNAME', env('BAKONG_ACCOUNT_ID', '010921061@aba')),
        'account_id_usd' => env('BAKONG_ACCOUNT_ID_USD', '010921061@aba'),
        'account_id_khr' => env('BAKONG_ACCOUNT_ID_KHR', '010921065@aba'),
        'phone_number' => env('BAKONG_PHONE_NUMBER', '010921061'),
        'city' => env('BAKONG_CITY', env('BAKONG_MERCHANT_CITY', 'Phnom Penh')),
        'access_token' => env('BAKONG_ACCESS_TOKEN', env('BAKONG_API_TOKEN')),
        'api_token' => env('BAKONG_API_TOKEN', env('BAKONG_ACCESS_TOKEN')),
        'api_url' => env('BAKONG_API_URL', 'https://api-bakong.nbc.gov.kh/v1/check_transaction_by_md5'),
        'prod_url' => env('BAKONG_PROD_BASE_API_URL', 'https://api-bakong.nbc.gov.kh/v1'),
        'dev_url' => env('BAKONG_DEV_BASE_API_URL', 'https://sit-api-bakong.nbc.gov.kh/v1'),
        'currency' => env('BAKONG_CURRENCY', 'USD'),
        'usd_khr_rate' => env('BAKONG_USD_KHR_RATE', 4100),
        'simulation_mode' => (bool) env('BAKONG_SIMULATION_MODE', false),
    ],

    'telegram' => [
        'enabled' => env('TELEGRAM_ENABLED', true),
        'bot_token' => env('TELEGRAM_BOT_TOKEN', '8851308730:AAFIs5Dyu4exg6mXw0JLN1jbOuQyvgucrPc'),
        'chat_id' => env('TELEGRAM_CHAT_ID'),
    ],

    'google_sheet' => [
        'enabled' => env('GOOGLE_SHEET_ENABLED', false),
        'credentials_path' => env('GOOGLE_SHEET_CREDENTIALS_PATH', storage_path('app/google/service-account.json')),
        'spreadsheet_id' => env('GOOGLE_SHEET_ID'),
        'sheet_name' => env('GOOGLE_SHEET_TAB_NAME', 'Sheet1'),
    ],
];
