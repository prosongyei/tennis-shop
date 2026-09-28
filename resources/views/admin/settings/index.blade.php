@extends('layouts.admin')

@section('title', 'Payment Gateway & Store Settings')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-display font-black text-2xl text-white">Settings & Bank Gateway</h1>
            <p class="text-sm text-slate-400">Configure Bakong KHQR credentials, bank details, and store parameters.</p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" onclick="testBakongConnection()" id="btn-test-bakong" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-emerald-600/20 transition">
                <i data-lucide="zap" class="w-4 h-4"></i>
                <span id="test-btn-text">Test Bakong Token</span>
            </button>
        </div>
    </div>

    <!-- Live Test Result Banner -->
    <div id="test-result-box" class="hidden p-4 rounded-2xl border text-sm flex items-start gap-3 transition">
        <i data-lucide="info" id="test-result-icon" class="w-5 h-5 shrink-0 mt-0.5"></i>
        <div>
            <h4 class="font-bold text-white" id="test-result-title">Test Result</h4>
            <p class="text-xs text-slate-300 mt-0.5" id="test-result-msg"></p>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm flex items-center gap-3">
            <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-8">
        @csrf

        <!-- Bakong KHQR Gateway Configuration Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-800">
                <div class="w-10 h-10 rounded-xl bg-[#E1232A]/20 text-[#E1232A] flex items-center justify-center font-black text-base border border-[#E1232A]/30">
                    KH
                </div>
                <div>
                    <h2 class="font-display font-bold text-lg text-white">Bakong KHQR Bank Gateway</h2>
                    <p class="text-xs text-slate-400">National Bank of Cambodia universal QR payment setup.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Bakong Account Name</label>
                    <input type="text" name="bakong_account_name" value="{{ old('bakong_account_name', $settings['bakong_account_name']) }}" required class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:border-sky-400 transition" placeholder="e.g. SORSONGYEI SOY">
                    <p class="text-[11px] text-slate-500 mt-1">Displayed on the customer's banking app when scanning.</p>
                    @error('bakong_account_name') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Bakong Account Username (Bank ID)</label>
                    <input type="text" name="bakong_account_username" value="{{ old('bakong_account_username', $settings['bakong_account_username']) }}" required class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:border-sky-400 transition" placeholder="e.g. 010921061@aba">
                    <p class="text-[11px] text-slate-500 mt-1">Your registered Bakong ID (e.g. username@abaa, username@aclb, or phone number).</p>
                    @error('bakong_account_username') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Bakong Merchant Phone</label>
                    <input type="text" name="bakong_phone_number" value="{{ old('bakong_phone_number', $settings['bakong_phone_number']) }}" class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:border-sky-400 transition" placeholder="e.g. 010921061">
                    <p class="text-[11px] text-slate-500 mt-1">International format without +, e.g. 855967855710.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Merchant City</label>
                    <input type="text" name="bakong_city" value="{{ old('bakong_city', $settings['bakong_city']) }}" required class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:border-sky-400 transition" placeholder="PHNOM PENH">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Bakong API Gateway URL</label>
                    <select name="bakong_api_url" class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:border-sky-400 transition">
                        <option value="https://sit-api-bakong.nbc.gov.kh/v1" {{ $settings['bakong_api_url'] == 'https://sit-api-bakong.nbc.gov.kh/v1' ? 'selected' : '' }}>
                            Sandbox / SIT Testing API (https://sit-api-bakong.nbc.gov.kh/v1)
                        </option>
                        <option value="https://api-bakong.nbc.gov.kh/v1" {{ $settings['bakong_api_url'] == 'https://api-bakong.nbc.gov.kh/v1' ? 'selected' : '' }}>
                            Production Live API (https://api-bakong.nbc.gov.kh/v1)
                        </option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Bakong Access Token (JWT Bearer)</label>
                    <textarea name="bakong_access_token" rows="3" class="w-full bg-slate-950 text-white text-xs font-mono border border-slate-800 rounded-xl p-4 focus:outline-none focus:border-sky-400 transition" placeholder="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...">{{ old('bakong_access_token', $settings['bakong_access_token']) }}</textarea>
                    <p class="text-[11px] text-slate-500 mt-1">The JWT Bearer token issued by Bakong for real-time check_transaction_by_md5 verification.</p>
                </div>
            </div>
        </div>

        <!-- Store Profile & Exchange Rate Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-800">
                <div class="w-10 h-10 rounded-xl bg-sky-500/20 text-sky-400 flex items-center justify-center font-black text-base border border-sky-500/30">
                    <i data-lucide="store" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="font-display font-bold text-lg text-white">Store Profile & Currency</h2>
                    <p class="text-xs text-slate-400">Manage business information and exchange rates.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Store Business Name</label>
                    <input type="text" name="store_name" value="{{ old('store_name', $settings['store_name']) }}" required class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:border-sky-400 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">KHR Exchange Rate (1 USD = X KHR)</label>
                    <input type="number" name="khr_exchange_rate" value="{{ old('khr_exchange_rate', $settings['khr_exchange_rate']) }}" required min="1000" max="10000" class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:border-sky-400 transition">
                    <p class="text-[11px] text-slate-500 mt-1">Default: 4100 KHR per 1 USD.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Store Phone</label>
                    <input type="text" name="store_phone" value="{{ old('store_phone', $settings['store_phone']) }}" class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:border-sky-400 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Store Email</label>
                    <input type="email" name="store_email" value="{{ old('store_email', $settings['store_email']) }}" class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:border-sky-400 transition">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Store Address</label>
                    <input type="text" name="store_address" value="{{ old('store_address', $settings['store_address']) }}" class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:border-sky-400 transition">
                </div>
            </div>
        </div>

        <!-- Telegram Dual-Bot Notification Channels Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-sky-500/20 text-sky-400 flex items-center justify-center font-black text-base border border-sky-500/30">
                        <i data-lucide="send" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="font-display font-bold text-lg text-white">Telegram Dual-Bot & Group Channels</h2>
                        <p class="text-xs text-slate-400">Separated channels for payment confirmation approval and digital customer tax invoices.</p>
                    </div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="telegram_enabled" value="1" {{ !empty($settings['telegram_enabled']) ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-sky-500"></div>
                    <span class="ml-3 text-xs font-bold text-slate-300">Enabled</span>
                </label>
            </div>

            <!-- Bot 1: Payment Confirmation Bot -->
            <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-800/80">
                    <div class="flex items-center gap-2.5">
                        <span class="px-2 py-0.5 rounded-lg bg-emerald-500/20 text-emerald-400 font-bold text-[10px] uppercase tracking-wider">Bot 1</span>
                        <h3 class="font-display font-bold text-white text-sm">Payment Confirmation Bot (@TLS_Payment_bot)</h3>
                    </div>
                    <button type="button" onclick="testTelegramConfirm()" id="btn-test-confirm" class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold flex items-center gap-1.5 transition">
                        <i data-lucide="zap" class="w-3.5 h-3.5 text-emerald-400"></i>
                        <span>Test Confirmation Bot</span>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Confirmation Bot Token</label>
                        <input type="text" name="telegram_confirm_bot_token" value="{{ old('telegram_confirm_bot_token', $settings['telegram_confirm_bot_token']) }}" class="w-full bg-slate-900 text-white text-xs font-mono border border-slate-800 rounded-xl px-4 py-2.5 focus:outline-none focus:border-sky-400 transition" placeholder="8851308730:AAFIs5Dyu4exg6mXw0JLN1jbOuQyvgucrPc">
                        <p class="text-[11px] text-slate-500 mt-1">Bot token for @TLS_Payment_bot used to verify payments.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Admin / Cashier Confirmation Chat ID</label>
                        <input type="text" name="telegram_confirm_chat_id" value="{{ old('telegram_confirm_chat_id', $settings['telegram_confirm_chat_id']) }}" class="w-full bg-slate-900 text-white text-xs font-mono border border-slate-800 rounded-xl px-4 py-2.5 focus:outline-none focus:border-sky-400 transition" placeholder="6646751752">
                        <p class="text-[11px] text-slate-500 mt-1">Receives interactive [ ✅ Confirm Payment ] and [ ❌ Reject ] buttons.</p>
                    </div>
                </div>
            </div>

            <!-- Bot 2: Digital Invoice & Receipts Group -->
            <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-800/80">
                    <div class="flex items-center gap-2.5">
                        <span class="px-2 py-0.5 rounded-lg bg-sky-500/20 text-sky-400 font-bold text-[10px] uppercase tracking-wider">Bot 2 / Group</span>
                        <h3 class="font-display font-bold text-white text-sm">Tax Invoice & Order Receipts Group</h3>
                    </div>
                    <button type="button" onclick="testTelegramInvoice()" id="btn-test-invoice" class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold flex items-center gap-1.5 transition">
                        <i data-lucide="receipt" class="w-3.5 h-3.5 text-sky-400"></i>
                        <span>Test Invoice Group</span>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Invoice Bot Token</label>
                        <input type="text" name="telegram_invoice_bot_token" value="{{ old('telegram_invoice_bot_token', $settings['telegram_invoice_bot_token']) }}" class="w-full bg-slate-900 text-white text-xs font-mono border border-slate-800 rounded-xl px-4 py-2.5 focus:outline-none focus:border-sky-400 transition" placeholder="8862288371:AAGoz8XBLGz4eacOcIbprOWIn5eNiDAadrw">
                        <p class="text-[11px] text-slate-500 mt-1">Bot token for @TosLengSey_bot (falls back to Bot 1 if not in group).</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Invoice Group ID</label>
                        <input type="text" name="telegram_invoice_group_id" value="{{ old('telegram_invoice_group_id', $settings['telegram_invoice_group_id']) }}" class="w-full bg-slate-900 text-white text-xs font-mono border border-slate-800 rounded-xl px-4 py-2.5 focus:outline-none focus:border-sky-400 transition" placeholder="-5475494678">
                        <p class="text-[11px] text-slate-500 mt-1">Group chat ID where full customer invoices and item breakdowns are posted.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-4">
            <button type="submit" class="px-8 py-3.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-sm shadow-lg shadow-sky-600/20 transition">
                Save All Settings
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    function testBakongConnection() {
        const btn = document.getElementById('btn-test-bakong');
        const btnText = document.getElementById('test-btn-text');
        const box = document.getElementById('test-result-box');
        const title = document.getElementById('test-result-title');
        const msg = document.getElementById('test-result-msg');
        const icon = document.getElementById('test-result-icon');

        btn.disabled = true;
        btnText.innerText = "Connecting to Bakong...";

        fetch('{{ route("admin.settings.test-bakong") }}')
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btnText.innerText = "Test Bakong Token";
                box.classList.remove('hidden');

                if (data.success) {
                    box.className = "p-4 rounded-2xl border text-sm flex items-start gap-3 bg-emerald-500/10 border-emerald-500/30 text-emerald-400";
                    title.innerText = "Bakong Connection Successful!";
                    msg.innerText = data.message + " (" + data.endpoint + ")";
                } else {
                    box.className = "p-4 rounded-2xl border text-sm flex items-start gap-3 bg-rose-500/10 border-rose-500/30 text-rose-400";
                    title.innerText = "Bakong Connection Failed";
                    msg.innerText = data.message + " (" + data.endpoint + ")";
                }
            })
            .catch(err => {
                btn.disabled = false;
                btnText.innerText = "Test Bakong Token";
                box.classList.remove('hidden');
                box.className = "p-4 rounded-2xl border text-sm flex items-start gap-3 bg-rose-500/10 border-rose-500/30 text-rose-400";
                title.innerText = "Connection Error";
                msg.innerText = err.message;
            });
    }

    function testTelegramConfirm() {
        const btn = document.getElementById('btn-test-confirm');
        const box = document.getElementById('test-result-box');
        const title = document.getElementById('test-result-title');
        const msg = document.getElementById('test-result-msg');

        btn.disabled = true;
        btn.classList.add('opacity-75');

        fetch('{{ route("admin.settings.test-telegram-confirm") }}')
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.classList.remove('opacity-75');
                box.classList.remove('hidden');

                if (data.success) {
                    box.className = "p-4 rounded-2xl border text-sm flex items-start gap-3 bg-emerald-500/10 border-emerald-500/30 text-emerald-400";
                    title.innerText = "Confirmation Bot Ping Sent!";
                    msg.innerText = data.message;
                } else {
                    box.className = "p-4 rounded-2xl border text-sm flex items-start gap-3 bg-rose-500/10 border-rose-500/30 text-rose-400";
                    title.innerText = "Confirmation Bot Test Failed";
                    msg.innerText = data.message;
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.classList.remove('opacity-75');
                box.classList.remove('hidden');
                box.className = "p-4 rounded-2xl border text-sm flex items-start gap-3 bg-rose-500/10 border-rose-500/30 text-rose-400";
                title.innerText = "Test Error";
                msg.innerText = err.message;
            });
    }

    function testTelegramInvoice() {
        const btn = document.getElementById('btn-test-invoice');
        const box = document.getElementById('test-result-box');
        const title = document.getElementById('test-result-title');
        const msg = document.getElementById('test-result-msg');

        btn.disabled = true;
        btn.classList.add('opacity-75');

        fetch('{{ route("admin.settings.test-telegram-invoice") }}')
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.classList.remove('opacity-75');
                box.classList.remove('hidden');

                if (data.success) {
                    box.className = "p-4 rounded-2xl border text-sm flex items-start gap-3 bg-sky-500/10 border-sky-500/30 text-sky-400";
                    title.innerText = "Invoice Group Ping Sent!";
                    msg.innerText = data.message;
                } else {
                    box.className = "p-4 rounded-2xl border text-sm flex items-start gap-3 bg-rose-500/10 border-rose-500/30 text-rose-400";
                    title.innerText = "Invoice Group Test Failed";
                    msg.innerText = data.message;
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.classList.remove('opacity-75');
                box.classList.remove('hidden');
                box.className = "p-4 rounded-2xl border text-sm flex items-start gap-3 bg-rose-500/10 border-rose-500/30 text-rose-400";
                title.innerText = "Test Error";
                msg.innerText = err.message;
            });
    }
</script>
@endsection
