@extends('layouts.admin')

@section('title', isset($product) ? 'Edit Product - ' . $product->name : 'Create New Product - TosLengSey Admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-display font-black text-2xl sm:text-3xl text-white">
                {{ isset($product) ? 'Edit Product' : 'Add New Equipment' }}
            </h1>
            <p class="text-xs text-slate-400 mt-1">Configure specifications, pricing, and stock limits</p>
        </div>
        <a href="{{ route('admin.products.index') }}" class="text-xs text-slate-400 hover:text-white">
            &larr; Back to Catalog
        </a>
    </div>

    <form action="{{ isset($product) ? route('admin.products.update', $product->id) : route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8 bg-slate-900/80 border border-slate-800 rounded-3xl p-6 sm:p-10">
        @csrf
        @if(isset($product))
            @method('PUT')
        @endif

        <!-- General Information -->
        <div class="space-y-4">
            <h3 class="font-display font-bold text-base text-white pb-2 border-b border-slate-800">General Information</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Product Name *</label>
                    <input type="text" name="name" value="{{ old('name', $product->name ?? '') }}" required placeholder="e.g. Yonex Astrox 100ZZ" class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-2.5 focus:outline-none focus:border-lime-400">
                    @error('name') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">SKU Code *</label>
                    <input type="text" name="sku" value="{{ old('sku', $product->sku ?? '') }}" required placeholder="e.g. YON-AX100ZZ" class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-2.5 uppercase font-mono focus:outline-none focus:border-lime-400">
                    @error('sku') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Category *</label>
                        <button type="button" onclick="openCategoryModal()" class="text-xs text-lime-400 hover:text-lime-300 font-semibold flex items-center gap-1 transition cursor-pointer">
                            <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i> + Add New Category
                        </button>
                    </div>
                    <select id="category_id_select" name="category_id" required class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-2.5 focus:outline-none focus:border-lime-400">
                        <option value="" disabled {{ empty(old('category_id', $product->category_id ?? '')) ? 'selected' : '' }}>-- Select Category --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Brand *</label>
                        <button type="button" onclick="openBrandModal()" class="text-xs text-lime-400 hover:text-lime-300 font-semibold flex items-center gap-1 transition cursor-pointer">
                            <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i> + Add New Brand
                        </button>
                    </div>
                    <select id="brand_id_select" name="brand_id" required class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-2.5 focus:outline-none focus:border-lime-400">
                        <option value="" disabled {{ empty(old('brand_id', $product->brand_id ?? '')) ? 'selected' : '' }}>-- Select Brand --</option>
                        @foreach($brands as $br)
                            <option value="{{ $br->id }}" {{ old('brand_id', $product->brand_id ?? '') == $br->id ? 'selected' : '' }}>{{ $br->name }}</option>
                        @endforeach
                    </select>
                    @error('brand_id') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Description</label>
                <textarea name="description" rows="3" class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-2.5 focus:outline-none focus:border-lime-400">{{ old('description', $product->description ?? '') }}</textarea>
            </div>
        </div>

        <!-- Pricing & Stock -->
        <div class="space-y-4">
            <h3 class="font-display font-bold text-base text-white pb-2 border-b border-slate-800">Pricing & Inventory</h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Regular Price ($) *</label>
                    <input type="number" step="0.01" name="price" value="{{ old('price', $product->price ?? '') }}" required placeholder="229.00" class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-2.5 font-mono focus:outline-none focus:border-lime-400">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Sale Discount Price ($)</label>
                    <input type="number" step="0.01" name="discount_price" value="{{ old('discount_price', $product->discount_price ?? '') }}" placeholder="Optional" class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-2.5 font-mono focus:outline-none focus:border-lime-400">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Cost Price ($)</label>
                    <input type="number" step="0.01" name="cost_price" value="{{ old('cost_price', $product->cost_price ?? '') }}" placeholder="Optional" class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-2.5 font-mono focus:outline-none focus:border-lime-400">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Stock Quantity *</label>
                    <input type="number" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity ?? 10) }}" required class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-2.5 font-mono focus:outline-none focus:border-lime-400">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Min Stock Alert Level</label>
                    <input type="number" name="min_stock_level" value="{{ old('min_stock_level', $product->min_stock_level ?? 3) }}" class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-2.5 font-mono focus:outline-none focus:border-lime-400">
                </div>
            </div>
        </div>

        <!-- Technical Specifications -->
        <div class="space-y-4">
            <h3 class="font-display font-bold text-base text-white pb-2 border-b border-slate-800">Badminton Technical Specs</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Flexibility</label>
                    <input type="text" name="spec_flex" value="{{ old('spec_flex', $product->specifications['flex'] ?? '') }}" placeholder="e.g. Extra Stiff, Medium" class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-2.5 focus:outline-none focus:border-lime-400">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Balance</label>
                    <input type="text" name="spec_balance" value="{{ old('spec_balance', $product->specifications['balance'] ?? '') }}" placeholder="e.g. Head Heavy, Even Balance" class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-2.5 focus:outline-none focus:border-lime-400">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Max String Tension</label>
                    <input type="text" name="spec_tension" value="{{ old('spec_tension', $product->specifications['string_tension'] ?? '') }}" placeholder="e.g. 20-28 lbs" class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-2.5 focus:outline-none focus:border-lime-400">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Weight / Grip Size</label>
                    <input type="text" name="spec_weight_grip" value="{{ old('spec_weight_grip', $product->specifications['weight_grip'] ?? '') }}" placeholder="e.g. 4U (83g) G5" class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-2.5 focus:outline-none focus:border-lime-400">
                </div>
            </div>
        </div>

        <!-- Image & Visibility -->
        <div class="space-y-4">
            <h3 class="font-display font-bold text-base text-white pb-2 border-b border-slate-800">Media & Status</h3>

            <div class="grid grid-cols-1 sm:grid-cols-12 gap-6 items-start">
                <div class="sm:col-span-8 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Image URL (Web Link)</label>
                        <input type="text" id="product-img-url" name="image"
                            value="{{ old('image', (isset($product) && str_starts_with($product->getRawOriginal('image') ?? '', 'http')) ? $product->getRawOriginal('image') : '') }}"
                            oninput="previewFromUrl(this.value)" placeholder="https://images.unsplash.com/..." class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-2.5 focus:outline-none focus:border-indigo-500 font-mono">
                        <p class="text-[11px] text-slate-500 mt-1">Paste any direct web image link (JPG, PNG, WebP).</p>
                    </div>

                    <div class="relative flex py-1 items-center">
                        <div class="flex-grow border-t border-slate-800"></div>
                        <span class="flex-shrink mx-3 text-xs text-slate-500 font-bold uppercase">OR</span>
                        <div class="flex-grow border-t border-slate-800"></div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Upload Local Photo File</label>
                        <input type="file" id="product-img-file" name="image_file" accept="image/*" onchange="previewFromFile(this)" class="w-full text-xs text-slate-400 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-white hover:file:bg-slate-700 cursor-pointer">
                        <p class="text-[11px] text-slate-500 mt-1">Direct upload to store server (Max 3MB, saved directly to public storage).</p>
                    </div>
                </div>

                <!-- Live Image Preview Card -->
                <div class="sm:col-span-4 flex flex-col items-center">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Live Image Preview</p>
                    <div class="w-36 h-36 rounded-2xl bg-slate-950 border-2 border-slate-800 flex items-center justify-center overflow-hidden relative shadow-lg">
                        <img id="image-preview" src="{{ isset($product) && $product->image ? $product->image : 'https://images.unsplash.com/photo-1613918108466-292b78a8ef95?w=300' }}" alt="Preview" class="w-full h-full object-cover" onerror="this.src='https://images.unsplash.com/photo-1613918108466-292b78a8ef95?w=300'; document.getElementById('preview-error').classList.remove('hidden');" onload="document.getElementById('preview-error').classList.add('hidden');">
                        <div id="preview-error" class="absolute inset-0 bg-slate-950/90 text-rose-400 text-[10px] p-2 flex items-center justify-center text-center font-bold hidden">
                            Image link broken or unreachable
                        </div>
                    </div>
                    <span id="preview-caption" class="text-[10px] text-slate-400 font-mono mt-2 truncate max-w-[150px]">
                        {{ isset($product) && $product->image ? 'Current Image' : 'Default Preset' }}
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-6 pt-3">
                <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-white">
                    <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $product->is_featured ?? false) ? 'checked' : '' }} class="rounded bg-slate-950 border-slate-800 text-indigo-500 focus:ring-0">
                    <span>Feature on Homepage Equipment Highlights</span>
                </label>

                <div class="flex items-center gap-2 text-xs">
                    <span class="font-bold text-slate-400">Visibility Status:</span>
                    <select name="status" class="bg-slate-950 text-white text-xs border border-slate-800 rounded-lg px-3 py-1.5">
                        <option value="active" {{ old('status', $product->status ?? '') === 'active' ? 'selected' : '' }}>Active (Visible in Store)</option>
                        <option value="inactive" {{ old('status', $product->status ?? '') === 'inactive' ? 'selected' : '' }}>Inactive (Draft / Hidden)</option>
                    </select>
                </div>
            </div>
        </div>

        <script>
            function previewFromUrl(url) {
                const preview = document.getElementById('image-preview');
                const caption = document.getElementById('preview-caption');
                if (url && url.trim() !== '') {
                    preview.src = url.trim();
                    caption.innerText = 'Web URL Loaded';
                }
            }

            function previewFromFile(input) {
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('image-preview').src = e.target.result;
                        document.getElementById('preview-caption').innerText = input.files[0].name;
                        // clear URL input so it doesn't conflict
                        document.getElementById('product-img-url').value = '';
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }
        </script>

        <button type="submit" class="w-full py-4 px-6 rounded-2xl bg-lime-400 hover:bg-lime-300 text-slate-950 font-display font-black text-base shadow-xl shadow-lime-500/20 transition">
            {{ isset($product) ? 'Save Product Changes' : 'Publish Product to Store' }}
        </button>
    </form>
</div>

<!-- Quick Add Category Modal -->
<div id="category-modal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="font-display font-bold text-lg text-white flex items-center gap-2">
                <i data-lucide="folder-plus" class="w-5 h-5 text-lime-400"></i> Add New Category
            </h3>
            <button type="button" onclick="closeCategoryModal()" class="text-slate-400 hover:text-white text-xl font-bold">&times;</button>
        </div>
        <div class="space-y-3">
            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Category Name *</label>
                <input type="text" id="new-cat-name" placeholder="e.g. Tennis Racquets, Pickleball" class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-2.5 focus:outline-none focus:border-lime-400">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Description (Optional)</label>
                <textarea id="new-cat-desc" rows="2" placeholder="Brief overview of products in this category" class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-2.5 focus:outline-none focus:border-lime-400"></textarea>
            </div>
            <div id="cat-modal-error" class="text-xs text-rose-400 hidden"></div>
        </div>
        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
            <button type="button" onclick="closeCategoryModal()" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold cursor-pointer">
                Cancel
            </button>
            <button type="button" id="save-cat-btn" onclick="saveNewCategory()" class="px-5 py-2.5 rounded-xl bg-lime-400 hover:bg-lime-300 text-slate-950 text-xs font-bold shadow-lg shadow-lime-500/20 transition cursor-pointer flex items-center gap-1.5">
                <span>Save Category</span>
            </button>
        </div>
    </div>
</div>

<!-- Quick Add Brand Modal -->
<div id="brand-modal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="font-display font-bold text-lg text-white flex items-center gap-2">
                <i data-lucide="tag" class="w-5 h-5 text-lime-400"></i> Add New Brand
            </h3>
            <button type="button" onclick="closeBrandModal()" class="text-slate-400 hover:text-white text-xl font-bold">&times;</button>
        </div>
        <div class="space-y-3">
            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Brand Name *</label>
                <input type="text" id="new-brand-name" placeholder="e.g. Karakal, Dunlop, Wilson" class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-2.5 focus:outline-none focus:border-lime-400">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Description (Optional)</label>
                <textarea id="new-brand-desc" rows="2" placeholder="Brand overview" class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-2.5 focus:outline-none focus:border-lime-400"></textarea>
            </div>
            <div id="brand-modal-error" class="text-xs text-rose-400 hidden"></div>
        </div>
        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
            <button type="button" onclick="closeBrandModal()" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold cursor-pointer">
                Cancel
            </button>
            <button type="button" id="save-brand-btn" onclick="saveNewBrand()" class="px-5 py-2.5 rounded-xl bg-lime-400 hover:bg-lime-300 text-slate-950 text-xs font-bold shadow-lg shadow-lime-500/20 transition cursor-pointer flex items-center gap-1.5">
                <span>Save Brand</span>
            </button>
        </div>
    </div>
</div>

<script>
    function openCategoryModal() {
        document.getElementById('new-cat-name').value = '';
        document.getElementById('new-cat-desc').value = '';
        document.getElementById('cat-modal-error').classList.add('hidden');
        document.getElementById('category-modal').classList.remove('hidden');
        setTimeout(() => document.getElementById('new-cat-name').focus(), 50);
    }

    function closeCategoryModal() {
        document.getElementById('category-modal').classList.add('hidden');
    }

    function saveNewCategory() {
        const nameInput = document.getElementById('new-cat-name');
        const descInput = document.getElementById('new-cat-desc');
        const errorDiv = document.getElementById('cat-modal-error');
        const saveBtn = document.getElementById('save-cat-btn');

        const name = nameInput.value.trim();
        if (!name) {
            errorDiv.textContent = 'Please enter a category name.';
            errorDiv.classList.remove('hidden');
            return;
        }

        errorDiv.classList.add('hidden');
        saveBtn.disabled = true;
        saveBtn.innerHTML = 'Saving...';

        fetch('{{ route("admin.categories.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                name: name,
                description: descInput.value.trim()
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.category) {
                const select = document.getElementById('category_id_select');
                const opt = new Option(data.category.name, data.category.id, true, true);
                select.add(opt);
                closeCategoryModal();
            } else {
                errorDiv.textContent = data.message || 'Failed to save category.';
                errorDiv.classList.remove('hidden');
            }
        })
        .catch(err => {
            errorDiv.textContent = 'Network error while saving category.';
            errorDiv.classList.remove('hidden');
        })
        .finally(() => {
            saveBtn.disabled = false;
            saveBtn.innerHTML = 'Save Category';
            if (window.lucide) lucide.createIcons();
        });
    }

    function openBrandModal() {
        document.getElementById('new-brand-name').value = '';
        document.getElementById('new-brand-desc').value = '';
        document.getElementById('brand-modal-error').classList.add('hidden');
        document.getElementById('brand-modal').classList.remove('hidden');
        setTimeout(() => document.getElementById('new-brand-name').focus(), 50);
    }

    function closeBrandModal() {
        document.getElementById('brand-modal').classList.add('hidden');
    }

    function saveNewBrand() {
        const nameInput = document.getElementById('new-brand-name');
        const descInput = document.getElementById('new-brand-desc');
        const errorDiv = document.getElementById('brand-modal-error');
        const saveBtn = document.getElementById('save-brand-btn');

        const name = nameInput.value.trim();
        if (!name) {
            errorDiv.textContent = 'Please enter a brand name.';
            errorDiv.classList.remove('hidden');
            return;
        }

        errorDiv.classList.add('hidden');
        saveBtn.disabled = true;
        saveBtn.innerHTML = 'Saving...';

        fetch('{{ route("admin.brands.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                name: name,
                description: descInput.value.trim()
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.brand) {
                const select = document.getElementById('brand_id_select');
                const opt = new Option(data.brand.name, data.brand.id, true, true);
                select.add(opt);
                closeBrandModal();
            } else {
                errorDiv.textContent = data.message || 'Failed to save brand.';
                errorDiv.classList.remove('hidden');
            }
        })
        .catch(err => {
            errorDiv.textContent = 'Network error while saving brand.';
            errorDiv.classList.remove('hidden');
        })
        .finally(() => {
            saveBtn.disabled = false;
            saveBtn.innerHTML = 'Save Brand';
            if (window.lucide) lucide.createIcons();
        });
    }
</script>
@endsection
