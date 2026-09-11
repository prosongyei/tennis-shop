@extends('layouts.admin')

@section('title', 'Manage Products & Stock - TosLengSey Admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-display font-black text-3xl text-white">Products & Inventory</h1>
            <p class="text-xs text-slate-400 mt-1">Manage catalog, pricing, variants, and stock quantities</p>
        </div>

        <a href="{{ route('admin.products.create') }}" class="px-5 py-3 rounded-2xl bg-lime-400 hover:bg-lime-300 text-slate-950 font-bold text-xs shadow-lg shadow-lime-500/20 flex items-center gap-2 transition self-start sm:self-auto">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Add New Product</span>
        </a>
    </div>

    <!-- Filters Bar -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-3xl p-4">
        <form action="{{ route('admin.products.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name or SKU..." class="w-full bg-slate-950 text-white text-xs border border-slate-800 rounded-xl px-3 py-2.5 focus:outline-none focus:border-lime-400">
            </div>

            <div>
                <select name="category_id" onchange="this.form.submit()" class="w-full bg-slate-950 text-white text-xs border border-slate-800 rounded-xl px-3 py-2.5 focus:outline-none focus:border-lime-400">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="brand_id" onchange="this.form.submit()" class="w-full bg-slate-950 text-white text-xs border border-slate-800 rounded-xl px-3 py-2.5 focus:outline-none focus:border-lime-400">
                    <option value="">All Brands</option>
                    @foreach($brands as $br)
                        <option value="{{ $br->id }}" {{ request('brand_id') == $br->id ? 'selected' : '' }}>{{ $br->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="stock_status" onchange="this.form.submit()" class="w-full bg-slate-950 text-white text-xs border border-slate-800 rounded-xl px-3 py-2.5 focus:outline-none focus:border-lime-400">
                    <option value="">All Stock Levels</option>
                    <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Low Stock (&le; 5)</option>
                    <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>Out of Stock (0)</option>
                </select>
            </div>
        </form>
    </div>

    <!-- Products Table -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-3xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead>
                    <tr class="text-slate-400 uppercase tracking-wider border-b border-slate-800 bg-slate-950/40">
                        <th class="p-4">Product</th>
                        <th class="p-4">Brand</th>
                        <th class="p-4">Category</th>
                        <th class="p-4 text-right">Price</th>
                        <th class="p-4 text-center">Stock</th>
                        <th class="p-4 text-center">Status</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($products as $prod)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="p-4 flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-slate-950 overflow-hidden shrink-0">
                                    <img src="{{ $prod->image ?: 'https://images.unsplash.com/photo-1613918108466-292b78a8ef95?w=200' }}" alt="{{ $prod->name }}" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <h4 class="font-bold text-white text-sm">{{ $prod->name }}</h4>
                                    <span class="text-slate-500 font-mono">{{ $prod->sku }}</span>
                                </div>
                            </td>
                            <td class="p-4 text-slate-300">{{ $prod->brand?->name ?? 'N/A' }}</td>
                            <td class="p-4 text-slate-300">{{ $prod->category?->name ?? 'N/A' }}</td>
                            <td class="p-4 text-right font-mono font-bold text-white">
                                ${{ number_format($prod->effective_price, 2) }}
                            </td>
                            <td class="p-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-black font-mono {{ $prod->stock_quantity <= 3 ? 'bg-rose-500/20 text-rose-400' : ($prod->stock_quantity <= 10 ? 'bg-amber-500/20 text-amber-400' : 'bg-emerald-500/20 text-emerald-400') }}">
                                    {{ $prod->stock_quantity }}
                                </span>
                            </td>
                            <td class="p-4 text-center">
                                <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded {{ $prod->status === 'active' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-slate-800 text-slate-500' }}">
                                    {{ $prod->status }}
                                </span>
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" onclick="openStockModal({{ $prod->id }}, '{{ addslashes($prod->name) }}', {{ $prod->stock_quantity }})" class="p-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300" title="Adjust Stock">
                                        <i data-lucide="layers" class="w-4 h-4"></i>
                                    </button>
                                    <a href="{{ route('admin.products.edit', $prod->id) }}" class="p-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-lime-400" title="Edit">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>
                                    <form action="{{ route('admin.products.destroy', $prod->id) }}" method="POST" onsubmit="return confirm('Delete this product?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 rounded-lg bg-slate-800 hover:bg-rose-500/20 text-rose-400" title="Delete">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-500">No products found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-800">
            {{ $products->links() }}
        </div>
    </div>
</div>

<!-- Quick Adjust Stock Modal -->
<div id="stock-modal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4 z-50 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-sm w-full p-6 space-y-4 shadow-2xl">
        <div class="flex items-center justify-between">
            <h3 class="font-display font-bold text-base text-white">Adjust Stock Level</h3>
            <button onclick="closeStockModal()" class="text-slate-400 hover:text-white">&times;</button>
        </div>

        <p id="modal-product-name" class="text-xs text-slate-300 font-bold"></p>

        <form id="stock-form" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs text-slate-400 mb-1">Add or Deduct Quantity (e.g. +10 or -5)</label>
                <input type="number" name="quantity" required placeholder="10" class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-2.5 focus:outline-none focus:border-lime-400 font-mono">
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1">Reason / Note</label>
                <input type="text" name="reason" value="Restock shipment from Yonex distributor" class="w-full bg-slate-950 text-white text-xs border border-slate-800 rounded-xl px-4 py-2.5 focus:outline-none focus:border-lime-400">
            </div>

            <button type="submit" class="w-full py-3 rounded-xl bg-lime-400 hover:bg-lime-300 text-slate-950 font-bold text-xs shadow-lg transition">
                Confirm Inventory Update
            </button>
        </form>
    </div>
</div>

<script>
    function openStockModal(id, name, currentStock) {
        document.getElementById('modal-product-name').innerText = name + ` (Current: ${currentStock})`;
        document.getElementById('stock-form').action = `/admin/products/${id}/adjust-stock`;
        document.getElementById('stock-modal').classList.remove('hidden');
    }
    function closeStockModal() {
        document.getElementById('stock-modal').classList.add('hidden');
    }
</script>
@endsection
