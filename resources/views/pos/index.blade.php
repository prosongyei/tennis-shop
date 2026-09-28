<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Point of Sale Register #01 - TosLengSey Pro</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@600;700;800;900&family=JetBrains+Mono:wght@500;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-display { font-family: 'Outfit', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 h-screen flex flex-col antialiased select-none overflow-hidden">

    <!-- Top Station Header -->
    <header class="bg-slate-900 border-b border-slate-800 px-6 py-2.5 flex items-center justify-between shrink-0 z-20">
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-white/10 border border-slate-700 p-0.5 flex items-center justify-center">
                    <img src="{{ route('brand.logo') }}?v=2" alt="TosLengSey" class="w-full h-full object-contain">
                </div>
                <div>
                    <span class="font-display font-black text-white text-base tracking-tight">TOSLENGSEY <span class="text-sky-400">POS</span></span>
                    <span class="text-[10px] text-slate-400 font-mono block leading-none">Register #01 • Flagship Store</span>
                </div>
            </div>

            <div class="h-6 w-px bg-slate-800"></div>

            <div class="flex items-center gap-2 text-xs text-slate-300">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Active Cashier: <strong>{{ auth()->user()->name }}</strong></span>
            </div>
        </div>

        <div class="flex items-center gap-4 text-xs">
            <div class="bg-slate-950 border border-slate-800 rounded-xl px-3 py-1.5 flex items-center gap-2">
                <span class="text-slate-400">Rate:</span>
                <span class="font-mono font-bold text-sky-400">1 USD = {{ number_format($khrExchangeRate) }} KHR</span>
            </div>

            <button type="button" onclick="clearCart()" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold transition flex items-center gap-1.5">
                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5 text-slate-400"></i>
                <span>Reset Ticket</span>
            </button>

            <!-- Lock / Sign Out of POS -->
            <form action="{{ url('/pos/logout') }}" method="POST">
                @csrf
                <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30 text-rose-400 font-bold transition flex items-center gap-1.5">
                    <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                    <span>Lock Register</span>
                </button>
            </form>
        </div>
    </header>

    <!-- Main Workspace Split -->
    <div class="flex-1 flex overflow-hidden">
        <!-- Left Column: Catalog & Barcode Scanner -->
        <div class="flex-1 flex flex-col bg-slate-950 p-5 overflow-hidden border-r border-slate-800">
            <!-- Search & Barcode Input Field -->
            <div class="mb-4 relative">
                <input type="text" id="pos-search" onkeydown="handleBarcodeEnter(event)" oninput="debounceSearch()" placeholder="Scan Barcode or Search SKU / Product name (Press Enter)..." autofocus class="w-full bg-slate-900 text-white text-sm border-2 border-slate-800 focus:border-emerald-500 rounded-2xl px-4 py-3 pl-11 pr-24 focus:outline-none transition placeholder:text-slate-500 font-mono">
                <i data-lucide="barcode" class="w-5 h-5 text-emerald-400 absolute left-3.5 top-3.5"></i>
                <span class="absolute right-3 top-3 text-[10px] font-mono uppercase bg-slate-800 text-slate-400 px-2 py-0.5 rounded font-bold">Auto-Scan ON</span>
            </div>

            <!-- Category Filter Tabs -->
            <div class="flex items-center gap-2 pb-3 mb-3 overflow-x-auto no-scrollbar shrink-0 text-xs font-bold">
                <button type="button" onclick="filterCategory(null)" class="cat-pill active px-4 py-2 rounded-xl bg-emerald-500 text-slate-950 shrink-0 shadow-lg shadow-emerald-500/10">
                    All Categories
                </button>
                @foreach($categories as $cat)
                    <button type="button" onclick="filterCategory({{ $cat->id }})" class="cat-pill px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-300 shrink-0 border border-slate-800 transition">
                        {{ $cat->name }}
                    </button>
                @endforeach
            </div>

            <!-- Products Grid -->
            <div class="flex-1 overflow-y-auto pr-1">
                <div id="pos-product-grid" class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3.5">
                    @foreach($products as $prod)
                        <div onclick='addToCart(@json($prod))' class="cursor-pointer bg-slate-900 border border-slate-800 hover:border-emerald-500/50 rounded-2xl p-3 flex flex-col justify-between group transition hover:scale-[1.01] select-none">
                            <div class="aspect-square rounded-xl overflow-hidden bg-slate-950 mb-2 relative">
                                <img src="{{ $prod->image ?: asset('images/default-product.svg') }}" alt="{{ $prod->name }}" onerror="this.onerror=null;this.src='{{ asset('images/default-product.svg') }}';" class="w-full h-full object-cover group-hover:scale-105 transition">
                                <span class="absolute bottom-1 right-1 bg-slate-950/90 text-slate-300 text-[10px] font-mono px-1.5 py-0.5 rounded font-bold">
                                    Qty: {{ $prod->stock_quantity }}
                                </span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-500 font-bold uppercase">{{ $prod->brand ? $prod->brand->name : '' }}</span>
                                <h4 class="font-display font-bold text-xs text-white line-clamp-1 group-hover:text-emerald-400 transition">{{ $prod->name }}</h4>
                                <p class="font-display font-black text-sm text-emerald-400 mt-1 font-mono">${{ number_format($prod->effective_price, 2) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right Column: Register Bill & Tender Calculation -->
        <div class="w-full lg:w-96 xl:w-[420px] bg-slate-900 flex flex-col justify-between shrink-0">
            <!-- Customer Details Input -->
            <div class="p-3.5 border-b border-slate-800 bg-slate-900/90">
                <div class="grid grid-cols-2 gap-2">
                    <input type="text" id="cust-name" value="Walk-in Customer" placeholder="Customer Name" class="bg-slate-950 text-white text-xs border border-slate-800 rounded-xl px-3 py-2 focus:outline-none focus:border-emerald-500">
                    <input type="tel" 
                           inputmode="numeric" 
                           pattern="[0-9]*" 
                           id="cust-phone" 
                           placeholder="Phone (Digits only)" 
                           maxlength="15"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                           onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                           onpaste="setTimeout(() => { this.value = this.value.replace(/[^0-9]/g, ''); }, 0)"
                           class="bg-slate-950 text-white text-xs border border-slate-800 rounded-xl px-3 py-2 focus:outline-none focus:border-emerald-500">
                </div>
            </div>

            <!-- Items List -->
            <div class="flex-1 overflow-y-auto p-4 divide-y divide-slate-800" id="cart-item-list">
                <div id="cart-empty-msg" class="h-full flex flex-col items-center justify-center text-slate-500 py-12 text-center">
                    <i data-lucide="shopping-bag" class="w-12 h-12 mb-2 text-slate-700"></i>
                    <p class="text-xs font-bold text-slate-400">Current Ticket Empty</p>
                    <p class="text-[11px] text-slate-600">Scan barcode or click items to add to ticket</p>
                </div>
            </div>

            <!-- Computation & Change Calculation -->
            <div class="p-4 border-t border-slate-800 bg-slate-950 space-y-3">
                <div class="space-y-1.5 text-xs">
                    <div class="flex justify-between text-slate-400">
                        <span>Subtotal</span>
                        <span id="bill-subtotal" class="font-mono text-white font-semibold">$0.00</span>
                    </div>

                    <div class="flex items-center justify-between text-slate-400">
                        <span>Discount ($)</span>
                        <input type="number" id="bill-discount" value="0.00" step="0.5" min="0" oninput="renderCart()" class="w-20 bg-slate-900 border border-slate-800 rounded-lg px-2 py-1 text-right text-xs font-mono text-emerald-400 focus:outline-none">
                    </div>

                    <div class="pt-2 border-t border-slate-800 flex items-center justify-between">
                        <span class="font-display font-bold text-sm text-white">Total Due</span>
                        <div class="text-right">
                            <span id="bill-total-usd" class="font-display font-black text-2xl text-emerald-400 font-mono">$0.00</span>
                            <p id="bill-total-khr" class="text-[11px] text-slate-400 font-mono">0 KHR</p>
                        </div>
                    </div>

                    <!-- Real-Time Cash Change Calculator -->
                    <div class="pt-2 border-t border-slate-800/80">
                        <div class="flex items-center justify-between gap-2 mb-1.5">
                            <label for="cash-tendered-usd" class="text-[11px] font-bold text-slate-400 uppercase">Cash Tendered ($)</label>
                            <input type="number" id="cash-tendered-usd" placeholder="0.00" step="1" oninput="calculateChange()" class="w-24 bg-slate-900 border border-slate-800 rounded-lg px-2 py-1 text-right text-xs font-mono text-white focus:outline-none focus:border-emerald-500">
                        </div>
                        <div class="flex items-center justify-between text-[11px] bg-slate-900/60 p-2 rounded-xl border border-slate-800/60">
                            <span class="text-slate-400 font-bold">Change Due:</span>
                            <div class="text-right font-mono">
                                <span id="change-due-usd" class="font-bold text-emerald-400">$0.00</span>
                                <span class="text-slate-500 mx-1">/</span>
                                <span id="change-due-khr" class="text-slate-300">0 KHR</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fast Checkout Action Buttons -->
                <div class="grid grid-cols-2 gap-2.5 pt-1">
                    <!-- Cash Checkout -->
                    <button type="button" onclick="checkout('cash_store')" class="py-3 px-3 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-display font-black text-xs shadow-lg shadow-emerald-500/20 transition flex items-center justify-center gap-1.5">
                        <i data-lucide="banknote" class="w-4 h-4"></i>
                        <span>Cash Tendered</span>
                    </button>

                    <!-- Bakong KHQR Modal Trigger -->
                    <button type="button" onclick="checkout('khqr')" class="py-3 px-3 rounded-xl bg-[#E1232A] hover:bg-red-600 text-white font-display font-black text-xs shadow-lg shadow-red-600/30 transition flex items-center justify-center gap-1.5">
                        <span class="text-[10px] bg-white text-[#E1232A] px-1 rounded font-black">KHQR</span>
                        <span>Bakong Scan</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer-Facing Bakong KHQR Modal -->
    <div id="khqr-modal" class="fixed inset-0 bg-slate-950/85 backdrop-blur-md flex items-center justify-center p-4 z-50 hidden">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-sm w-full overflow-hidden shadow-2xl relative">
            <button onclick="closeKhqrModal()" class="absolute top-4 right-4 text-white/70 hover:text-white z-10 text-xl font-bold">&times;</button>

            <div class="bg-[#E1232A] p-5 text-white text-center">
                <div class="w-10 h-10 rounded-xl bg-white text-[#E1232A] font-black text-lg flex items-center justify-center mx-auto mb-1">
                    KHQR
                </div>
                <h3 class="font-display font-black text-base uppercase">Universal Bakong KHQR</h3>
                <p class="text-[11px] opacity-90">{{ $merchantName }}</p>
            </div>

            <div class="p-6 text-center space-y-4">
                <div>
                    <p class="text-[11px] text-slate-400 font-bold uppercase">Total Due</p>
                    <div id="modal-amount-usd" class="font-display font-black text-3xl text-emerald-400 my-0.5 font-mono">$0.00</div>
                    <div id="modal-amount-khr" class="text-xs text-slate-400 font-mono">0 KHR</div>
                </div>

                <!-- QR Container -->
                <div class="p-3 bg-white rounded-2xl inline-block mx-auto border-2 border-[#E1232A] shadow-lg">
                    <canvas id="pos-khqr-canvas" class="w-52 h-52"></canvas>
                </div>

                <!-- Polling Notification -->
                <div class="p-3 rounded-xl bg-slate-950 text-xs flex items-center justify-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></div>
                    <span id="pos-modal-status" class="text-slate-300 font-semibold">Listening for bank payment...</span>
                </div>

                <button type="button" onclick="simulatePosPayment()" class="w-full py-2 px-3 rounded-xl bg-amber-500/20 border border-amber-500/40 text-amber-300 font-bold text-xs hover:bg-amber-500/30 transition">
                    Simulate Customer Mobile Payment
                </button>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        const KHR_RATE = {{ $khrExchangeRate }};
        let posCart = [];
        let currentOrderNumber = null;
        let posPollInterval = null;
        let currentBillTotal = 0;

        function addToCart(prod) {
            const existing = posCart.find(item => item.product_id === prod.id);
            if (existing) {
                existing.quantity++;
            } else {
                posCart.push({
                    product_id: prod.id,
                    name: prod.name,
                    price: parseFloat(prod.effective_price || prod.price),
                    quantity: 1,
                    variant_id: null
                });
            }
            renderCart();
        }

        function handleBarcodeEnter(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const query = e.target.value.trim();
                if (!query) return;

                fetch(`/pos/search?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(products => {
                        if (products.length > 0) {
                            addToCart(products[0]);
                            e.target.value = '';
                        }
                    });
            }
        }

        function updateQty(index, delta) {
            posCart[index].quantity += delta;
            if (posCart[index].quantity <= 0) {
                posCart.splice(index, 1);
            }
            renderCart();
        }

        function removeItem(index) {
            posCart.splice(index, 1);
            renderCart();
        }

        function clearCart() {
            posCart = [];
            document.getElementById('bill-discount').value = "0.00";
            document.getElementById('cash-tendered-usd').value = "";
            renderCart();
        }

        function renderCart() {
            const list = document.getElementById('cart-item-list');

            if (posCart.length === 0) {
                list.innerHTML = `
                    <div id="cart-empty-msg" class="h-full flex flex-col items-center justify-center text-slate-500 py-12 text-center">
                        <i data-lucide="shopping-bag" class="w-12 h-12 mb-2 text-slate-700"></i>
                        <p class="text-xs font-bold text-slate-400">Current Ticket Empty</p>
                        <p class="text-[11px] text-slate-600">Scan barcode or click items to add to ticket</p>
                    </div>`;
                document.getElementById('bill-subtotal').innerText = "$0.00";
                document.getElementById('bill-total-usd').innerText = "$0.00";
                document.getElementById('bill-total-khr').innerText = "0 KHR";
                currentBillTotal = 0;
                calculateChange();
                lucide.createIcons();
                return;
            }

            let subtotal = 0;
            let html = '';

            posCart.forEach((item, index) => {
                const itemTotal = item.price * item.quantity;
                subtotal += itemTotal;

                html += `
                    <div class="py-2.5 flex items-center justify-between gap-2 text-xs group hover:bg-slate-800/40 rounded-xl px-2 transition">
                        <div class="overflow-hidden flex-1 min-w-0">
                            <p class="font-bold text-white truncate">${item.name}</p>
                            <p class="text-[11px] text-slate-400 font-mono">$${item.price.toFixed(2)}</p>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <div class="flex items-center bg-slate-950 border border-slate-800 rounded-lg p-0.5">
                                <button type="button" onclick="updateQty(${index}, -1)" title="Decrease" class="w-6 h-6 rounded bg-slate-900 hover:bg-slate-800 text-white font-bold flex items-center justify-center transition">-</button>
                                <span class="w-7 text-center font-bold text-white font-mono">${item.quantity}</span>
                                <button type="button" onclick="updateQty(${index}, 1)" title="Increase" class="w-6 h-6 rounded bg-slate-900 hover:bg-slate-800 text-white font-bold flex items-center justify-center transition">+</button>
                            </div>
                            <span class="font-mono font-bold text-white w-14 text-right">$${itemTotal.toFixed(2)}</span>
                            <button type="button" onclick="removeItem(${index})" title="Remove item from ticket" class="w-7 h-7 rounded-lg bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-white transition flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                            </button>
                        </div>
                    </div>`;
            });

            list.innerHTML = html;

            const discount = parseFloat(document.getElementById('bill-discount').value) || 0.00;
            const total = Math.max(0, subtotal - discount);
            currentBillTotal = total;

            document.getElementById('bill-subtotal').innerText = "$" + subtotal.toFixed(2);
            document.getElementById('bill-total-usd').innerText = "$" + total.toFixed(2);
            document.getElementById('bill-total-khr').innerText = Math.round(total * KHR_RATE).toLocaleString() + " KHR";

            calculateChange();
        }

        function calculateChange() {
            const tendered = parseFloat(document.getElementById('cash-tendered-usd').value) || 0;
            if (tendered <= 0 || currentBillTotal <= 0) {
                document.getElementById('change-due-usd').innerText = "$0.00";
                document.getElementById('change-due-khr').innerText = "0 KHR";
                return;
            }

            const changeUsd = Math.max(0, tendered - currentBillTotal);
            const changeKhr = Math.round(changeUsd * KHR_RATE);

            document.getElementById('change-due-usd').innerText = "$" + changeUsd.toFixed(2);
            document.getElementById('change-due-khr').innerText = changeKhr.toLocaleString() + " KHR";
        }

        function checkout(paymentMethod) {
            if (posCart.length === 0) {
                alert('Cannot checkout: Ticket is empty.');
                return;
            }

            const discount = parseFloat(document.getElementById('bill-discount').value) || 0.00;
            const payload = {
                items: posCart,
                customer_name: document.getElementById('cust-name').value || 'Walk-in Customer',
                customer_phone: document.getElementById('cust-phone').value || 'N/A',
                payment_method: paymentMethod,
                discount: discount
            };

            fetch('{{ route("pos.process") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    alert(data.message || 'POS Checkout failed');
                    return;
                }

                currentOrderNumber = data.order_number;

                if (paymentMethod === 'khqr') {
                    document.getElementById('modal-amount-usd').innerText = "$" + parseFloat(data.total_amount).toFixed(2);
                    document.getElementById('modal-amount-khr').innerText = Math.round(data.total_amount * KHR_RATE).toLocaleString() + " KHR";

                    new QRious({
                        element: document.getElementById('pos-khqr-canvas'),
                        value: data.khqr_string,
                        size: 200,
                        level: 'M'
                    });

                    document.getElementById('khqr-modal').classList.remove('hidden');
                    startPosPolling(data.order_number, data.receipt_url);
                } else {
                    clearCart();
                    window.open(data.receipt_url, '_blank', 'width=450,height=650');
                }
            })
            .catch(err => alert('Network error during checkout: ' + err));
        }

        function startPosPolling(orderNumber, receiptUrl) {
            if (posPollInterval) clearInterval(posPollInterval);

            posPollInterval = setInterval(() => {
                fetch(`/api/payments/check-status/${orderNumber}`)
                    .then(res => res.json())
                    .then(status => {
                        if (status.paid) {
                            clearInterval(posPollInterval);
                            document.getElementById('pos-modal-status').innerText = "Payment Received! Printing...";
                            document.getElementById('pos-modal-status').className = "text-emerald-400 font-bold";
                            setTimeout(() => {
                                closeKhqrModal();
                                clearCart();
                                window.open(receiptUrl, '_blank', 'width=450,height=650');
                            }, 1000);
                        }
                    })
                    .catch(() => {});
            }, 2500);
        }

        function simulatePosPayment() {
            if (!currentOrderNumber) return;
            fetch(`/payment/simulate/${currentOrderNumber}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(() => {
                document.getElementById('pos-modal-status').innerText = "Simulated Confirmed!";
            });
        }

        function closeKhqrModal() {
            if (posPollInterval) clearInterval(posPollInterval);
            document.getElementById('khqr-modal').classList.add('hidden');
        }

        let searchTimeout = null;
        function debounceSearch() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const query = document.getElementById('pos-search').value.trim();
                fetch(`/pos/search?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(products => {
                        const grid = document.getElementById('pos-product-grid');
                        grid.innerHTML = products.map(p => `
                            <div onclick='addToCart(${JSON.stringify(p)})' class="cursor-pointer bg-slate-900 border border-slate-800 hover:border-emerald-500/50 rounded-2xl p-3 flex flex-col justify-between group transition hover:scale-[1.01] select-none">
                                <div class="aspect-square rounded-xl overflow-hidden bg-slate-950 mb-2 relative">
                                    <img src="${p.image || 'https://images.unsplash.com/photo-1613918108466-292b78a8ef95?w=300'}" alt="${p.name}" class="w-full h-full object-cover group-hover:scale-105 transition">
                                    <span class="absolute bottom-1 right-1 bg-slate-950/90 text-slate-300 text-[10px] font-mono px-1.5 py-0.5 rounded font-bold">Qty: ${p.stock}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-500 font-bold uppercase">${p.brand}</span>
                                    <h4 class="font-display font-bold text-xs text-white line-clamp-1 group-hover:text-emerald-400 transition">${p.name}</h4>
                                    <p class="font-display font-black text-sm text-emerald-400 mt-1 font-mono">$${parseFloat(p.price).toFixed(2)}</p>
                                </div>
                            </div>
                        `).join('');
                    });
            }, 300);
        }

        function filterCategory(catId) {
            document.querySelectorAll('.cat-pill').forEach(btn => {
                btn.className = 'cat-pill px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-300 shrink-0 border border-slate-800 transition';
            });
            event.target.className = 'cat-pill active px-4 py-2 rounded-xl bg-emerald-500 text-slate-950 shrink-0 shadow-lg shadow-emerald-500/10';

            const url = catId ? `/pos/search?category=${catId}` : `/pos/search`;
            fetch(url)
                .then(res => res.json())
                .then(products => {
                    const grid = document.getElementById('pos-product-grid');
                    grid.innerHTML = products.map(p => `
                        <div onclick='addToCart(${JSON.stringify(p)})' class="cursor-pointer bg-slate-900 border border-slate-800 hover:border-emerald-500/50 rounded-2xl p-3 flex flex-col justify-between group transition hover:scale-[1.01] select-none">
                            <div class="aspect-square rounded-xl overflow-hidden bg-slate-950 mb-2 relative">
                                <img src="${p.image || 'https://images.unsplash.com/photo-1613918108466-292b78a8ef95?w=300'}" alt="${p.name}" class="w-full h-full object-cover group-hover:scale-105 transition">
                                <span class="absolute bottom-1 right-1 bg-slate-950/90 text-slate-300 text-[10px] font-mono px-1.5 py-0.5 rounded font-bold">Qty: ${p.stock}</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-500 font-bold uppercase">${p.brand}</span>
                                <h4 class="font-display font-bold text-xs text-white line-clamp-1 group-hover:text-emerald-400 transition">${p.name}</h4>
                                <p class="font-display font-black text-sm text-emerald-400 mt-1 font-mono">$${parseFloat(p.price).toFixed(2)}</p>
                            </div>
                        </div>
                    `).join('');
                });
        }
    </script>
</body>
</html>
