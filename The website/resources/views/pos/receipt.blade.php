<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>POS Receipt - #{{ $order->order_number }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 10px;
        }
        .receipt {
            max-width: 320px;
            margin: 0 auto;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .divider { border-top: 1px dashed #000; margin: 8px 0; }
        .double-divider { border-top: 2px dashed #000; margin: 8px 0; }
        .flex { display: flex; justify-content: space-between; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="receipt">
        <div class="no-print" style="margin-bottom: 15px; text-align: center;">
            <button onclick="window.print()" style="padding: 6px 12px; font-weight: bold; cursor: pointer;">Print Receipt</button>
            <button onclick="window.close()" style="padding: 6px 12px; margin-left: 8px; cursor: pointer;">Close</button>
        </div>

        <div class="text-center">
            <h2 style="margin: 0; font-size: 16px;">{{ $merchantName }}</h2>
            <p style="margin: 2px 0;">{{ $address }}</p>
            <p style="margin: 2px 0;">Tel: {{ $phone }}</p>
            <div class="divider"></div>
            <p class="bold" style="margin: 2px 0;">*** POS SALE RECEIPT ***</p>
        </div>

        <div class="divider"></div>

        <div class="flex">
            <span>Bill #: {{ $order->order_number }}</span>
            <span>Date: {{ $order->created_at->format('d/m/Y') }}</span>
        </div>
        <div class="flex">
            <span>Cashier: {{ $order->cashier?->name ?? 'Register 1' }}</span>
            <span>Time: {{ $order->created_at->format('H:i') }}</span>
        </div>
        <div>
            <span>Customer: {{ $order->customer_name }}</span>
        </div>

        <div class="double-divider"></div>

        <div class="flex bold">
            <span style="flex: 2;">Item</span>
            <span style="width: 35px; text-align: center;">Qty</span>
            <span style="width: 55px; text-align: right;">Price</span>
            <span style="width: 60px; text-align: right;">Total</span>
        </div>

        <div class="divider"></div>

        @foreach($order->items as $item)
            <div style="margin-bottom: 4px;">
                <div class="bold">{{ $item->product_name }}</div>
                @if($item->variant_name)
                    <div style="font-size: 10px;">- {{ $item->variant_name }}</div>
                @endif
                <div class="flex">
                    <span style="flex: 2;"></span>
                    <span style="width: 35px; text-align: center;">{{ $item->quantity }}</span>
                    <span style="width: 55px; text-align: right;">${{ number_format($item->unit_price, 2) }}</span>
                    <span style="width: 60px; text-align: right;">${{ number_format($item->subtotal, 2) }}</span>
                </div>
            </div>
        @endforeach

        <div class="double-divider"></div>

        <div class="flex">
            <span>Subtotal:</span>
            <span>${{ number_format($order->subtotal, 2) }}</span>
        </div>

        @if($order->discount_amount > 0)
            <div class="flex">
                <span>Discount:</span>
                <span>-${{ number_format($order->discount_amount, 2) }}</span>
            </div>
        @endif

        <div class="flex bold" style="font-size: 14px; margin-top: 4px;">
            <span>TOTAL (USD):</span>
            <span>${{ number_format($order->total_amount, 2) }}</span>
        </div>

        <div class="flex bold" style="font-size: 13px;">
            <span>TOTAL (KHR):</span>
            <span>{{ number_format($order->total_amount * $khrExchangeRate) }} R</span>
        </div>

        <div class="divider"></div>

        <div class="flex">
            <span>Payment Method:</span>
            <span class="bold uppercase">{{ str_replace('_', ' ', $order->payment_method) }}</span>
        </div>
        <div class="flex">
            <span>Status:</span>
            <span class="bold uppercase">{{ $order->payment_status }}</span>
        </div>
        @if($order->bakong_hash)
            <div style="font-size: 9px; word-break: break-all; margin-top: 2px;">
                Bakong Ref: {{ $order->bakong_hash }}
            </div>
        @endif

        <div class="double-divider"></div>

        <div class="text-center" style="margin-top: 10px;">
            <p style="margin: 2px 0;">THANK YOU FOR YOUR PURCHASE!</p>
            <p style="margin: 2px 0;">Please check goods before leaving counter.</p>
            <p style="font-size: 10px; margin-top: 6px;">Powered by Bakong KHQR & TosLengSey POS</p>
        </div>
    </div>
</body>
</html>
