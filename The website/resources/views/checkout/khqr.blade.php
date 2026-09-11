@extends('layouts.app')

@section('title', 'ABA KHQR Payment - Order #' . $order->order_number)

@section('content')
<div class="max-w-xl mx-auto px-4 py-8 sm:py-12">
    <!-- KHQR Stand Container -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-2xl relative">

        <!-- Body Content -->
        <div class="p-6 sm:p-8 space-y-6">

            <!-- Currency Selection Tabs (USD vs KHR) -->
            <div>
                <div class="flex items-center justify-between text-xs text-slate-400 mb-2">
                    <span class="font-semibold uppercase tracking-wider text-[11px]">Select Payment Currency</span>
                    <span class="text-[11px] font-mono text-slate-400">Rate: 1 USD = {{ number_format($khrExchangeRate) }} ៛</span>
                </div>
                <div class="grid grid-cols-2 gap-3 p-1 rounded-2xl bg-slate-950 border border-slate-800">
                    <button type="button" onclick="changeCurrency('USD')" id="btn-curr-usd"
                        class="curr-tab-btn py-2.5 px-4 rounded-xl font-bold text-xs flex items-center justify-center gap-2 transition bg-gradient-to-r from-sky-500 to-blue-600 text-white shadow-md">
                        <span>Pay in USD</span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] bg-white/20 font-mono">${{ number_format($order->total_amount, 2) }}</span>
                    </button>
                    <button type="button" onclick="changeCurrency('KHR')" id="btn-curr-khr"
                        class="curr-tab-btn py-2.5 px-4 rounded-xl font-bold text-xs flex items-center justify-center gap-2 transition text-slate-400 hover:text-white hover:bg-slate-900">
                        <span>Pay in KHR</span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] bg-slate-800 font-mono">{{ number_format($amountKhr) }} ៛</span>
                    </button>
                </div>
            </div>

            <!-- Total Amount Due Highlight -->
            <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 text-center relative overflow-hidden">
                <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Amount To Pay</span>
                <div id="display-amount" class="font-display font-black text-4xl text-sky-400 my-1 font-mono tracking-tight transition-all duration-200">
                    ${{ number_format($order->total_amount, 2) }}
                </div>
                <div id="display-sub-amount" class="text-xs text-slate-400 font-mono transition-all duration-200">
                    ≈ {{ number_format($amountKhr) }} KHR
                </div>
            </div>

            <!-- Dynamic ABA KHQR Frame -->
            <div class="relative mx-auto max-w-[280px] p-5 rounded-3xl bg-white text-slate-950 shadow-2xl border-4 border-[#E1232A] flex flex-col items-center justify-center">
                <div class="relative w-full aspect-square flex items-center justify-center">
                    <canvas id="khqr-canvas" class="w-full aspect-square transition-opacity duration-150 rounded-xl"></canvas>

                    <!-- Center Website Logo Inside QR (Zero-Space Full Fit) -->
                    <div class="absolute inset-0 m-auto w-12 h-12 rounded-2xl bg-white shadow-md border-2 border-white flex items-center justify-center pointer-events-none p-0 overflow-hidden">
                        <img src="{{ asset('images/logo_tight.png') }}" alt="Store Logo" class="w-full h-full object-cover">
                    </div>
                </div>

                <!-- Verified Account Identification (SORSONGYEI SOY) -->
                <div class="mt-3 text-center pt-2 border-t border-slate-100 w-full">
                    <span class="text-base font-black tracking-wide text-slate-900 uppercase block font-display">
                        SORSONGYEI SOY
                    </span>
                </div>
            </div>

            <!-- Real-Time Polling Status & 15-Minute Countdown -->
            <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-between text-xs">
                <div class="flex items-center gap-3">
                    <div id="polling-spinner" class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></div>
                    <span id="polling-status-text" class="text-slate-300 font-medium">Awaiting payment via ABA Mobile...</span>
                </div>
                <div class="font-mono text-sky-400 font-bold flex items-center gap-1.5 bg-sky-950/40 border border-sky-800/40 px-2 py-1 rounded-lg">
                    <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                    <span id="countdown-timer">15:00</span>
                </div>
            </div>

            <!-- Supported Banking Apps Badges -->
            <div class="text-center pt-1">
                <p class="text-[11px] text-slate-400 mb-2 font-medium">Supported Mobile Banking Apps:</p>
                <div class="flex flex-wrap items-center justify-center gap-1.5 text-[10px] font-bold text-slate-300">
                    <span class="px-2 py-1 rounded-lg bg-[#003B64]/60 border border-[#003B64] text-sky-200">ABA Mobile</span>
                    <span class="px-2 py-1 rounded-lg bg-slate-800 border border-slate-700">ACLEDA mobile</span>
                    <span class="px-2 py-1 rounded-lg bg-slate-800 border border-slate-700">Wing Bank</span>
                    <span class="px-2 py-1 rounded-lg bg-slate-800 border border-slate-700">Sathapana</span>
                    <span class="px-2 py-1 rounded-lg bg-slate-800 border border-slate-700">Canadia</span>
                    <span class="px-2 py-1 rounded-lg bg-slate-800 border border-slate-700">Bakong App</span>
                </div>
            </div>

            <!-- Manual Slip Upload Option (Accordion) -->
            <div class="pt-2 border-t border-slate-800">
                <details class="text-xs group">
                    <summary class="cursor-pointer text-slate-400 hover:text-slate-200 font-semibold flex items-center justify-between py-2">
                        <span>Transferred manually? Upload receipt screenshot</span>
                        <i data-lucide="chevron-down" class="w-4 h-4 group-open:rotate-180 transition"></i>
                    </summary>
                    <form action="{{ route('payment.upload-proof', $order->order_number) }}" method="POST" enctype="multipart/form-data" class="pt-3 space-y-3">
                        @csrf
                        <input type="file" name="proof_image" accept="image/*" required class="w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-white hover:file:bg-slate-700">
                        <button type="submit" class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs transition">
                            Submit Slip Screenshot
                        </button>
                    </form>
                </details>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let currentCurrency = 'USD';
    let khqrUsdPayload = @json($khqrUsdPayload ?? $order->khqr_string ?? '');
    let khqrKhrPayload = @json($khqrKhrPayload ?? '');
    let orderNumber = @json($order->order_number);
    let totalUsd = @json((float)$order->total_amount);
    let exchangeRate = @json((float)$khrExchangeRate);
    let totalKhr = Math.round(totalUsd * exchangeRate);

    // 1. Render QRious Canvas
    const qrCanvas = document.getElementById('khqr-canvas');
    let qr = new QRious({
        element: qrCanvas,
        value: khqrUsdPayload,
        size: 260,
        level: 'H'
    });

    // 2. Instant Zero-Delay Currency Switcher
    function changeCurrency(currency) {
        if (currentCurrency === currency) return;
        currentCurrency = currency;

        const btnUsd = document.getElementById('btn-curr-usd');
        const btnKhr = document.getElementById('btn-curr-khr');
        const amountDisplay = document.getElementById('display-amount');
        const subAmountDisplay = document.getElementById('display-sub-amount');

        // Instant UI toggle
        if (currency === 'USD') {
            btnUsd.className = "curr-tab-btn py-2.5 px-4 rounded-xl font-bold text-xs flex items-center justify-center gap-2 transition bg-gradient-to-r from-sky-500 to-blue-600 text-white shadow-md";
            btnKhr.className = "curr-tab-btn py-2.5 px-4 rounded-xl font-bold text-xs flex items-center justify-center gap-2 transition text-slate-400 hover:text-white hover:bg-slate-900";
            amountDisplay.innerText = "$" + totalUsd.toFixed(2);
            subAmountDisplay.innerText = "≈ " + totalKhr.toLocaleString() + " KHR";
        } else {
            btnKhr.className = "curr-tab-btn py-2.5 px-4 rounded-xl font-bold text-xs flex items-center justify-center gap-2 transition bg-gradient-to-r from-sky-500 to-blue-600 text-white shadow-md";
            btnUsd.className = "curr-tab-btn py-2.5 px-4 rounded-xl font-bold text-xs flex items-center justify-center gap-2 transition text-slate-400 hover:text-white hover:bg-slate-900";
            amountDisplay.innerText = totalKhr.toLocaleString() + " ៛";
            subAmountDisplay.innerText = "≈ $" + totalUsd.toFixed(2) + " USD";
        }

        // Smooth subtle 100ms fade transition without ugly black corner glitches
        qrCanvas.style.opacity = '0.2';
        setTimeout(() => {
            const targetPayload = (currency === 'KHR') ? (khqrKhrPayload || khqrUsdPayload) : khqrUsdPayload;
            qr.set({ value: targetPayload });
            qrCanvas.style.opacity = '1';
        }, 100);

        // Notify server in background to keep active order MD5 in sync
        fetch('{{ route("payment.khqr.currency", $order->order_number) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ currency: currency })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.khqr_string) {
                if (currency === 'KHR') {
                    khqrKhrPayload = data.khqr_string;
                } else {
                    khqrUsdPayload = data.khqr_string;
                }
            }
        })
        .catch(() => {});
    }

    // 3. 15-Minute Countdown Timer
    let durationSeconds = 15 * 60;
    const timerDisplay = document.getElementById('countdown-timer');

    const timerInterval = setInterval(() => {
        durationSeconds--;
        if (durationSeconds <= 0) {
            clearInterval(timerInterval);
            timerDisplay.innerText = "00:00 (Expired)";
            document.getElementById('polling-status-text').innerText = "QR Code expired. Please reorder.";
            return;
        }
        const m = Math.floor(durationSeconds / 60).toString().padStart(2, '0');
        const s = (durationSeconds % 60).toString().padStart(2, '0');
        timerDisplay.innerText = `${m}:${s}`;
    }, 1000);

    // 4. Automated Polling Every 2 Seconds (Fast & Non-blocking)
    let isPolling = false;
    const pollInterval = setInterval(() => {
        if (isPolling) return;
        isPolling = true;

        fetch('{{ route("payment.khqr.status", $order->order_number) }}')
            .then(res => res.json())
            .then(data => {
                if (data.paid) {
                    clearInterval(pollInterval);
                    clearInterval(timerInterval);
                    document.getElementById('polling-status-text').innerText = "Payment Verified via ABA! Redirecting...";
                    document.getElementById('polling-status-text').className = "text-emerald-400 font-bold";
                    setTimeout(() => {
                        window.location.href = data.redirect_url || '{{ route("orders.show", $order->order_number) }}';
                    }, 800);
                }
            })
            .catch(() => {})
            .finally(() => {
                isPolling = false;
            });
    }, 2000);
</script>
@endsection
