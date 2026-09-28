@extends('layouts.app')

@section('title', 'Checkout - TosLengSey Badminton Store')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8">
        <h1 class="font-display font-black text-3xl sm:text-4xl text-white">Secure Checkout</h1>
        <p class="text-xs sm:text-sm text-slate-400 mt-1">Fill in your delivery details and choose your preferred payment option.</p>
    </div>

    <form action="{{ route('checkout.process') }}" method="POST" id="checkout-form">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
            <!-- Left 7 Columns: Delivery & Payment Details -->
            <div class="lg:col-span-7 space-y-8">
                <!-- 1. Contact & Customer Details -->
                <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-5">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-800">
                        <div class="w-8 h-8 rounded-xl bg-sky-600 text-white font-black text-sm flex items-center justify-center">1</div>
                        <h2 class="font-display font-bold text-xl text-white">Delivery Information</h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="customer_name" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Recipient Name *</label>
                            <input type="text" id="customer_name" name="customer_name" value="{{ old('customer_name', $user?->name) }}" required class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:border-sky-400 transition" placeholder="e.g. Sokha Chan">
                            @error('customer_name') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="customer_phone" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Phone Number (Digits Only) *</label>
                            <input type="tel" 
                                   inputmode="numeric" 
                                   pattern="[0-9]*" 
                                   id="customer_phone" 
                                   name="customer_phone" 
                                   value="{{ old('customer_phone', preg_replace('/[^0-9]/', '', $user?->phone ?? '')) }}" 
                                   required 
                                   maxlength="15"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                   onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                                   onpaste="setTimeout(() => { this.value = this.value.replace(/[^0-9]/g, ''); }, 0)"
                                   class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:border-sky-400 transition" 
                                   placeholder="012345678">
                            <p class="text-[11px] text-slate-400 mt-1">Numbers only (e.g. 012345678 or 855967855710)</p>
                            @error('customer_phone') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="customer_email" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Email Address (Optional Receipt)</label>
                        <input type="email" id="customer_email" name="customer_email" value="{{ old('customer_email', $user?->email) }}" class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:border-sky-400 transition" placeholder="you@example.com">
                    </div>

                    <!-- Delivery Method Switch -->
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Delivery Method</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" name="delivery_method" value="delivery" checked onchange="updateDelivery(this.value)" class="peer sr-only">
                                <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 peer-checked:border-sky-400 peer-checked:bg-sky-500/10 transition">
                                    <div class="flex items-center gap-2 mb-1">
                                        <i data-lucide="truck" class="w-4 h-4 text-sky-400"></i>
                                        <span class="text-xs font-bold text-white">Direct Delivery</span>
                                    </div>
                                    <p class="text-[11px] text-slate-400">Phnom Penh & Express Provinces</p>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" name="delivery_method" value="pickup" onchange="updateDelivery(this.value)" class="peer sr-only">
                                <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 peer-checked:border-sky-400 peer-checked:bg-sky-500/10 transition">
                                    <div class="flex items-center gap-2 mb-1">
                                        <i data-lucide="store" class="w-4 h-4 text-sky-400"></i>
                                        <span class="text-xs font-bold text-white">Store Pickup (Free)</span>
                                    </div>
                                    <p class="text-[11px] text-slate-400">St. 2004, Sen Sok Store</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Province / City Dropdown -->
                    <div id="address-container" class="space-y-4">
                        <div>
                            <label for="province_city" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Province / City *</label>
                            <select id="province_city" name="province_city" onchange="updateProvinceFee(this.value)" class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:border-sky-400 transition">
                                @foreach($provinces as $key => $prov)
                                    <option value="{{ $key }}" data-fee="{{ $prov['fee'] }}">{{ $prov['label'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="delivery_address" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Full Street Address *</label>
                            <textarea id="delivery_address" name="delivery_address" rows="2" required class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:border-sky-400 transition" placeholder="House #, Street number, Sangkat/Khan, Landmark...">{{ old('delivery_address', $user?->address) }}</textarea>
                            @error('delivery_address') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="customer_note" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Order / Stringing Note (Optional)</label>
                        <input type="text" id="customer_note" name="customer_note" value="{{ old('customer_note') }}" class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:border-sky-400 transition" placeholder="e.g. String at 26 lbs with Yonex BG80, please call before delivery">
                    </div>
                </div>

                <!-- 2. Payment Method -->
                <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-5">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-800">
                        <div class="w-8 h-8 rounded-xl bg-red-500 text-white font-black text-sm flex items-center justify-center">2</div>
                        <h2 class="font-display font-bold text-xl text-white">Payment Method</h2>
                    </div>

<style>
    /* Payment Method Selection Styling */
    input[name="payment_method"]:checked + .payment-card {
        transition: all 0.2s ease-in-out;
    }
    input[name="payment_method"][value="khqr"]:checked + .payment-card {
        border-color: #ef4444 !important;
        background-color: rgba(69, 10, 10, 0.25) !important;
    }
    input[name="payment_method"][value="khqr"]:checked + .payment-card .payment-radio-ring {
        border-color: #ef4444 !important;
        background-color: #ef4444 !important;
    }
    input[name="payment_method"]:not([value="khqr"]):checked + .payment-card {
        border-color: #0ea5e9 !important;
        background-color: rgba(12, 74, 110, 0.2) !important;
    }
    input[name="payment_method"]:not([value="khqr"]):checked + .payment-card .payment-radio-ring {
        border-color: #0ea5e9 !important;
        background-color: #0ea5e9 !important;
    }
    input[name="payment_method"]:checked + .payment-card .payment-radio-dot {
        opacity: 1 !important;
        transform: scale(1) !important;
        background-color: #ffffff !important;
    }
    input[name="payment_method"]:not(:checked) + .payment-card {
        border-color: #1e293b !important;
        background-color: #020617 !important;
    }
    input[name="payment_method"]:not(:checked) + .payment-card .payment-radio-ring {
        border-color: #334155 !important;
        background-color: transparent !important;
    }
    input[name="payment_method"]:not(:checked) + .payment-card .payment-radio-dot {
        opacity: 0 !important;
        transform: scale(0) !important;
    }
</style>

                    <div class="space-y-3">
                        <!-- Option 1: Bakong KHQR -->
                        <label class="block cursor-pointer">
                            <input type="radio" name="payment_method" value="khqr" checked onchange="togglePaymentFields('khqr')" class="peer sr-only">
                            <div class="payment-card p-5 rounded-2xl bg-slate-950 border-2 border-slate-800 transition flex items-center justify-between gap-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-xl bg-red-600 text-white font-black text-base flex items-center justify-center shadow-lg shadow-red-600/30 shrink-0">
                                        KHQR
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-bold text-white">Bakong KHQR Universal Scan</span>
                                            <span class="bg-red-500/20 text-red-400 text-[10px] font-bold px-2 py-0.5 rounded-full">Recommended</span>
                                        </div>
                                        <p class="text-xs text-slate-400 mt-0.5">Pay with ABA Mobile, Acleda, Wing, Sathapana, Canadia & 40+ banks.</p>
                                    </div>
                                </div>
                                <div class="payment-radio-ring w-5 h-5 rounded-full border-2 border-slate-700 flex items-center justify-center transition-all shrink-0">
                                    <div class="payment-radio-dot w-2 h-2 rounded-full bg-white transition-all transform scale-0 opacity-0"></div>
                                </div>
                            </div>
                        </label>

                        <!-- Option 2: Credit / Debit Card (Visa, Mastercard, JCB) -->
                        <label class="block cursor-pointer">
                            <input type="radio" name="payment_method" value="credit_card" onchange="togglePaymentFields('credit_card')" class="peer sr-only">
                            <div class="payment-card p-5 rounded-2xl bg-slate-950 border-2 border-slate-800 transition flex items-center justify-between gap-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-600 to-sky-600 text-white font-black text-xl flex items-center justify-center shadow-lg shadow-sky-600/20 shrink-0">
                                        <i data-lucide="credit-card" class="w-6 h-6"></i>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-bold text-white">Credit / Debit Card</span>
                                            <div class="flex items-center gap-1">
                                                <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-slate-800 text-sky-400 border border-slate-700">VISA</span>
                                                <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-slate-800 text-amber-400 border border-slate-700">MC</span>
                                                <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-slate-800 text-emerald-400 border border-slate-700">JCB</span>
                                            </div>
                                        </div>
                                        <p class="text-xs text-slate-400 mt-0.5">Instant checkout with Visa, MasterCard, or JCB card. Zero card surcharge.</p>
                                    </div>
                                </div>
                                <div class="payment-radio-ring w-5 h-5 rounded-full border-2 border-slate-700 flex items-center justify-center transition-all shrink-0">
                                    <div class="payment-radio-dot w-2 h-2 rounded-full bg-white transition-all transform scale-0 opacity-0"></div>
                                </div>
                            </div>
                        </label>

                        <!-- Collapsible Credit Card Details Container -->
                        <div id="credit-card-form" class="hidden p-5 rounded-2xl bg-slate-950/90 border border-sky-500/30 space-y-4 transition-all">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                                <div class="flex items-center gap-2 text-xs font-bold text-sky-400">
                                    <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                                    <span>256-Bit SSL Encrypted Card Payment</span>
                                </div>
                                <span class="text-[10px] text-slate-500 font-mono">Bank-Grade Secure</span>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Name on Card *</label>
                                <input type="text" name="cardholder_name" id="cardholder_name" placeholder="e.g. Sokha Chan" class="w-full bg-slate-900 text-white text-sm border border-slate-800 rounded-xl px-4 py-2.5 focus:outline-none focus:border-sky-400">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Card Number *</label>
                                <div class="relative">
                                    <input type="text" name="card_number" id="card_number" maxlength="19" oninput="formatCardNumber(this)" placeholder="4000 1234 5678 9010" class="w-full bg-slate-900 text-white text-sm border border-slate-800 rounded-xl px-4 py-2.5 pl-11 font-mono tracking-wider focus:outline-none focus:border-sky-400">
                                    <i data-lucide="credit-card" class="w-4 h-4 text-slate-500 absolute left-3.5 top-3"></i>
                                    <span id="card-type-badge" class="absolute right-3.5 top-2.5 text-[10px] font-bold uppercase tracking-wider text-sky-400 font-mono"></span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Expires (MM/YY) *</label>
                                    <input type="text" name="card_expiry" id="card_expiry" maxlength="5" oninput="formatCardExpiry(this)" placeholder="MM/YY" class="w-full bg-slate-900 text-white text-sm border border-slate-800 rounded-xl px-4 py-2.5 font-mono focus:outline-none focus:border-sky-400 text-center">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">CVV / CVC *</label>
                                    <input type="password" name="card_cvv" id="card_cvv" maxlength="4" placeholder="•••" class="w-full bg-slate-900 text-white text-sm border border-slate-800 rounded-xl px-4 py-2.5 font-mono focus:outline-none focus:border-sky-400 text-center">
                                </div>
                            </div>
                        </div>

                        <!-- Option 3: Cash on Delivery -->
                        <label class="block cursor-pointer">
                            <input type="radio" name="payment_method" value="cash_on_delivery" onchange="togglePaymentFields('cash_on_delivery')" class="peer sr-only">
                            <div class="payment-card p-5 rounded-2xl bg-slate-950 border-2 border-slate-800 transition flex items-center justify-between gap-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-xl bg-slate-800 text-sky-400 font-black text-xl flex items-center justify-center shrink-0">
                                        💵
                                    </div>
                                    <div>
                                        <span class="text-sm font-bold text-white">Cash on Delivery (COD)</span>
                                        <p class="text-xs text-slate-400 mt-0.5">Pay in cash directly to the delivery rider upon inspecting package.</p>
                                    </div>
                                </div>
                                <div class="payment-radio-ring w-5 h-5 rounded-full border-2 border-slate-700 flex items-center justify-center transition-all shrink-0">
                                    <div class="payment-radio-dot w-2 h-2 rounded-full bg-white transition-all transform scale-0 opacity-0"></div>
                                </div>
                            </div>
                        </label>
                    </div>

                </div>
            </div>

            <!-- Right 5 Columns: Order Summary Card -->
            <div class="lg:col-span-5 bg-slate-900/60 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 sticky top-28">
                <h3 class="font-display font-bold text-xl text-white">Order Summary</h3>

                <!-- Cart Items Snapshot -->
                <div class="max-h-64 overflow-y-auto divide-y divide-slate-800 pr-2">
                    @foreach($cart->items as $item)
                        <div class="py-3 flex items-center justify-between gap-3 text-xs">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-slate-950 overflow-hidden shrink-0 border border-slate-800">
                                    <img src="{{ $item->product->image ?: asset('images/default-product.svg') }}" alt="{{ $item->product->name }}" onerror="this.onerror=null;this.src='{{ asset('images/default-product.svg') }}';" class="w-full h-full object-cover">
                                </div>
                                <div class="overflow-hidden">
                                    <p class="font-bold text-white truncate max-w-[190px]">{{ $item->product->name }}</p>
                                    <p class="text-[10px] text-slate-400 font-mono">{{ $item->quantity }}x @ ${{ number_format($item->unit_price, 2) }} @if($item->variant) • {{ $item->variant->variant_name }} @endif</p>
                                </div>
                            </div>
                            <span class="font-mono font-bold text-white shrink-0">${{ number_format($item->subtotal, 2) }}</span>
                        </div>
                    @endforeach
                </div>

                <!-- Coupon Input -->
                <div class="pt-3 border-t border-slate-800">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Coupon / Voucher Code</label>
                    <div class="flex gap-2">
                        <input type="text" name="coupon_code" id="coupon_code" placeholder="Try TOS10 or WELCOME5" class="flex-1 bg-slate-950 text-white text-xs border border-slate-800 rounded-xl px-3 py-2.5 uppercase font-mono focus:outline-none focus:border-sky-400">
                        <button type="button" onclick="applyCoupon()" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition">Apply</button>
                    </div>
                </div>

                <!-- Cost Breakdown -->
                <div class="pt-4 border-t border-slate-800 space-y-2.5 text-xs">
                    <div class="flex items-center justify-between text-slate-400">
                        <span>Items Subtotal</span>
                        <span class="font-mono text-white font-semibold">${{ number_format($cart->subtotal, 2) }}</span>
                    </div>

                    <div class="flex items-center justify-between text-slate-400">
                        <span>Delivery Fee</span>
                        <span id="delivery-fee-display" class="font-mono text-white font-semibold">$1.50</span>
                    </div>

                    <div class="flex items-center justify-between text-slate-400" id="discount-row" style="display: none;">
                        <span class="text-sky-400">Coupon Discount</span>
                        <span id="discount-display" class="font-mono text-sky-400 font-bold">-$0.00</span>
                    </div>

                    <div class="pt-3 border-t border-slate-800 flex items-center justify-between">
                        <span class="font-display font-bold text-base text-white">Grand Total</span>
                        <div class="text-right">
                            <span id="grand-total-display" class="font-display font-black text-2xl text-sky-400 font-mono">
                                ${{ number_format($cart->subtotal + 1.50, 2) }}
                            </span>
                            <p id="khr-total-display" class="text-[11px] text-slate-500 font-mono">
                                ≈ {{ number_format(($cart->subtotal + 1.50) * 4100) }} KHR
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" id="btn-checkout-submit" class="w-full py-4 px-6 rounded-2xl bg-sky-600 hover:bg-sky-500 text-white font-display font-bold text-base shadow-xl shadow-sky-600/25 hover:scale-[1.01] transition flex items-center justify-center gap-2">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                    <span>Generate KHQR & Pay</span>
                </button>

                <p id="checkout-notice-text" class="text-[11px] text-slate-500 text-center">
                    By clicking Confirm Order, you will be directed to the Bakong KHQR dynamic gateway or order confirmation page.
                </p>
            </div>
        </div>
    </form>
</div>

<script>
    const subtotal = {{ $cart->subtotal }};
    let currentDeliveryFee = 1.50;
    let currentDiscount = 0.00;

    function updateProvinceFee(prov) {
        const select = document.getElementById('province_city');
        const option = select.options[select.selectedIndex];
        currentDeliveryFee = parseFloat(option.dataset.fee) || 1.50;
        recalculate();
    }

    function updateDelivery(method) {
        const container = document.getElementById('address-container');
        if (method === 'pickup') {
            currentDeliveryFee = 0.00;
            container.style.display = 'none';
        } else {
            container.style.display = 'block';
            updateProvinceFee(document.getElementById('province_city').value);
        }
        recalculate();
    }

    function applyCoupon() {
        const code = document.getElementById('coupon_code').value.trim().toUpperCase();
        if (code === 'TOS10' || code === 'SMASH10') {
            currentDiscount = subtotal * 0.10;
            document.getElementById('discount-row').style.display = 'flex';
            document.getElementById('discount-display').innerText = '-$' + currentDiscount.toFixed(2);
            if (typeof showNotificationToast === 'function') {
                showNotificationToast('Coupon Applied!', '10% discount has been applied to your order total.');
            } else {
                alert('Coupon ' + code + ' applied! 10% discount given.');
            }
        } else if (code === 'WELCOME5') {
            currentDiscount = 5.00;
            document.getElementById('discount-row').style.display = 'flex';
            document.getElementById('discount-display').innerText = '-$' + currentDiscount.toFixed(2);
            if (typeof showNotificationToast === 'function') {
                showNotificationToast('Coupon Applied!', '$5.00 discount has been applied to your order.');
            } else {
                alert('Coupon WELCOME5 applied! $5.00 discount given.');
            }
        } else {
            alert('Invalid coupon code. Try TOS10, SMASH10, or WELCOME5.');
        }
        recalculate();
    }

    // Double-submission protection
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('checkout-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const btn = document.getElementById('btn-checkout-submit');
                if (btn) {
                    if (btn.dataset.submitting === 'true') {
                        e.preventDefault();
                        return false;
                    }
                    btn.dataset.submitting = 'true';
                    btn.disabled = true;
                    btn.classList.add('opacity-75', 'pointer-events-none');
                    const span = btn.querySelector('span');
                    if (span) span.innerText = 'Submitting Order...';
                    form.submit();
                }
            });
        }
    });

    function togglePaymentFields(method) {
        const cardForm = document.getElementById('credit-card-form');
        const submitBtnText = document.querySelector('#btn-checkout-submit span');
        const noticeText = document.getElementById('checkout-notice-text');

        if (method === 'credit_card') {
            cardForm.classList.remove('hidden');
            if (submitBtnText) submitBtnText.innerText = 'Authorize Card Payment';
            if (noticeText) noticeText.innerText = 'Your card will be securely processed and your order will be confirmed immediately.';
            document.getElementById('cardholder_name').required = true;
            document.getElementById('card_number').required = true;
            document.getElementById('card_expiry').required = true;
            document.getElementById('card_cvv').required = true;
        } else {
            cardForm.classList.add('hidden');
            if (submitBtnText) {
                if (method === 'khqr') {
                    submitBtnText.innerText = 'Generate KHQR & Pay';
                } else {
                    submitBtnText.innerText = 'Confirm Order';
                }
            }
            if (noticeText) {
                if (method === 'khqr') {
                    noticeText.innerText = 'By clicking Confirm Order, you will be directed to the Bakong KHQR dynamic scan screen.';
                } else {
                    noticeText.innerText = 'Your order will be placed and you can pay upon receiving or picking up your gear.';
                }
            }
            document.getElementById('cardholder_name').required = false;
            document.getElementById('card_number').required = false;
            document.getElementById('card_expiry').required = false;
            document.getElementById('card_cvv').required = false;
        }
    }

    function formatCardNumber(input) {
        // Strip non-digits
        let val = input.value.replace(/\D/g, '');
        // Group in 4 digits
        let formatted = val.match(/.{1,4}/g)?.join(' ') || val;
        input.value = formatted.substring(0, 19);

        // Detect card brand
        const badge = document.getElementById('card-type-badge');
        if (val.startsWith('4')) {
            badge.innerText = 'VISA';
            badge.className = 'absolute right-3.5 top-2.5 text-[10px] font-bold uppercase tracking-wider text-sky-400 font-mono';
        } else if (val.startsWith('5')) {
            badge.innerText = 'MASTERCARD';
            badge.className = 'absolute right-3.5 top-2.5 text-[10px] font-bold uppercase tracking-wider text-amber-400 font-mono';
        } else if (val.startsWith('35')) {
            badge.innerText = 'JCB';
            badge.className = 'absolute right-3.5 top-2.5 text-[10px] font-bold uppercase tracking-wider text-emerald-400 font-mono';
        } else {
            badge.innerText = '';
        }
    }

    function formatCardExpiry(input) {
        let val = input.value.replace(/\D/g, '');
        if (val.length >= 2) {
            input.value = val.substring(0, 2) + '/' + val.substring(2, 4);
        } else {
            input.value = val;
        }
    }

    function recalculate() {
        document.getElementById('delivery-fee-display').innerText = '$' + currentDeliveryFee.toFixed(2);
        const total = Math.max(0, (subtotal - currentDiscount) + currentDeliveryFee);
        document.getElementById('grand-total-display').innerText = '$' + total.toFixed(2);
        document.getElementById('khr-total-display').innerText = '≈ ' + Math.round(total * 4100).toLocaleString() + ' KHR';
    }
</script>
@endsection
