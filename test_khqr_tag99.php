<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$order = App\Models\Order::first();
$paymentService = app(App\Services\PaymentService::class);
$result = $paymentService->generateKhqr($order, 46.50, 'USD');

echo "NEW_KHQR:" . $result['khqr_string'] . "\n";
