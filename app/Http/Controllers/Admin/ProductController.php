<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function index(Request $request)
    {
        $query = Product::with(['brand', 'category', 'variants']);

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->input('brand_id'));
        }

        if ($request->filled('stock_status')) {
            if ($request->input('stock_status') === 'low') {
                $query->where('stock_quantity', '<=', 5);
            } elseif ($request->input('stock_status') === 'out') {
                $query->where('stock_quantity', '<=', 0);
            }
        }

        $this->ensureCategoriesAndBrandsExist();
        $products = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories', 'brands'));
    }

    public function create()
    {
        $this->ensureCategoriesAndBrandsExist();
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        return view('admin.products.form', compact('categories', 'brands'));
    }

    public function store(Request $request)
    {
        // Support on-the-fly category creation if entered
        if ($request->filled('new_category_name')) {
            $cName = trim((string) $request->input('new_category_name'));
            $cSlug = Str::slug($cName);
            $cat = Category::firstOrCreate(['name' => $cName], ['slug' => $cSlug, 'icon' => 'tag', 'is_active' => true]);
            $request->merge(['category_id' => $cat->id]);
        }

        // Support on-the-fly brand creation if entered
        if ($request->filled('new_brand_name')) {
            $bName = trim((string) $request->input('new_brand_name'));
            $bSlug = Str::slug($bName);
            $br = Brand::firstOrCreate(['name' => $bName], ['slug' => $bSlug, 'is_active' => true]);
            $request->merge(['brand_id' => $br->id]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku'],
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['required', 'exists:brands,id'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'min_stock_level' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'string', 'max:500'],
            'image_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:3072'],
            'status' => ['required', 'in:active,inactive'],
            'is_featured' => ['nullable', 'boolean'],
            'spec_flex' => ['nullable', 'string'],
            'spec_frame' => ['nullable', 'string'],
            'spec_balance' => ['nullable', 'string'],
            'spec_tension' => ['nullable', 'string'],
            'spec_weight_grip' => ['nullable', 'string'],
        ]);

        $imagePath = $validated['image'] ?? null;
        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $uploadDir = public_path('uploads/products');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $filename = time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $imagePath = '/uploads/products/' . $filename;
        } elseif ($request->filled('image')) {
            $imagePath = trim($request->input('image'));
        }

        if (empty($imagePath)) {
            $category = Category::find($validated['category_id']);
            $catSlug = strtolower($category?->slug ?? '');
            $catName = strtolower($category?->name ?? '');

            if (str_contains($catSlug, 'shoe') || str_contains($catName, 'shoe')) {
                $imagePath = 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=800&auto=format&fit=crop&q=80';
            } elseif (str_contains($catSlug, 'shuttle') || str_contains($catName, 'shuttle')) {
                $imagePath = 'https://images.unsplash.com/photo-1613918108466-292b78a8ef95?w=800&auto=format&fit=crop&q=80';
            } elseif (str_contains($catSlug, 'bag') || str_contains($catName, 'bag')) {
                $imagePath = 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=800&auto=format&fit=crop&q=80';
            } elseif (str_contains($catSlug, 'string') || str_contains($catName, 'string')) {
                $imagePath = 'https://images.unsplash.com/photo-1521537634581-0dced2fed2a8?w=800&auto=format&fit=crop&q=80';
            } elseif (str_contains($catSlug, 'grip') || str_contains($catName, 'grip')) {
                $imagePath = 'https://images.unsplash.com/photo-1534158914592-062992fbe900?w=800&auto=format&fit=crop&q=80';
            } else {
                $imagePath = 'https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?w=800&auto=format&fit=crop&q=80';
            }
        }

        $specs = [
            'flex' => $validated['spec_flex'] ?? null,
            'frame' => $validated['spec_frame'] ?? null,
            'balance' => $validated['spec_balance'] ?? null,
            'string_tension' => $validated['spec_tension'] ?? null,
            'weight_grip' => $validated['spec_weight_grip'] ?? null,
        ];

        $product = Product::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . Str::random(5),
            'sku' => strtoupper($validated['sku']),
            'category_id' => $validated['category_id'],
            'brand_id' => $validated['brand_id'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'discount_price' => $validated['discount_price'] ?? null,
            'cost_price' => $validated['cost_price'] ?? null,
            'stock_quantity' => $validated['stock_quantity'],
            'min_stock_level' => $validated['min_stock_level'] ?? 5,
            'image' => $imagePath,
            'status' => $validated['status'],
            'is_featured' => $request->boolean('is_featured'),
            'specifications' => array_filter($specs),
        ]);

        return redirect()->route('admin.products.index')->with('success', "Product '{$product->name}' created successfully.");
    }

    public function edit(int $id)
    {
        $this->ensureCategoriesAndBrandsExist();
        $product = Product::with('variants')->findOrFail($id);
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();

        return view('admin.products.form', compact('product', 'categories', 'brands'));
    }

    public function update(Request $request, int $id)
    {
        $product = Product::findOrFail($id);

        if ($request->filled('new_category_name')) {
            $cName = trim((string) $request->input('new_category_name'));
            $cSlug = Str::slug($cName);
            $cat = Category::firstOrCreate(['name' => $cName], ['slug' => $cSlug, 'icon' => 'tag', 'is_active' => true]);
            $request->merge(['category_id' => $cat->id]);
        }

        if ($request->filled('new_brand_name')) {
            $bName = trim((string) $request->input('new_brand_name'));
            $bSlug = Str::slug($bName);
            $br = Brand::firstOrCreate(['name' => $bName], ['slug' => $bSlug, 'is_active' => true]);
            $request->merge(['brand_id' => $br->id]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku,' . $product->id],
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['required', 'exists:brands,id'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'min_stock_level' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'string', 'max:500'],
            'image_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:3072'],
            'status' => ['required', 'in:active,inactive'],
            'is_featured' => ['nullable', 'boolean'],
            'spec_flex' => ['nullable', 'string'],
            'spec_frame' => ['nullable', 'string'],
            'spec_balance' => ['nullable', 'string'],
            'spec_tension' => ['nullable', 'string'],
            'spec_weight_grip' => ['nullable', 'string'],
        ]);

        $imagePath = $product->getRawOriginal('image');
        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $uploadDir = public_path('uploads/products');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $filename = time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $imagePath = '/uploads/products/' . $filename;
        } elseif ($request->filled('image')) {
            $imagePath = trim($request->input('image'));
        }

        if (empty($imagePath)) {
            $category = Category::find($validated['category_id']);
            $catSlug = strtolower($category?->slug ?? '');
            $catName = strtolower($category?->name ?? '');

            if (str_contains($catSlug, 'shoe') || str_contains($catName, 'shoe')) {
                $imagePath = 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=800&auto=format&fit=crop&q=80';
            } elseif (str_contains($catSlug, 'shuttle') || str_contains($catName, 'shuttle')) {
                $imagePath = 'https://images.unsplash.com/photo-1613918108466-292b78a8ef95?w=800&auto=format&fit=crop&q=80';
            } elseif (str_contains($catSlug, 'bag') || str_contains($catName, 'bag')) {
                $imagePath = 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=800&auto=format&fit=crop&q=80';
            } elseif (str_contains($catSlug, 'string') || str_contains($catName, 'string')) {
                $imagePath = 'https://images.unsplash.com/photo-1521537634581-0dced2fed2a8?w=800&auto=format&fit=crop&q=80';
            } elseif (str_contains($catSlug, 'grip') || str_contains($catName, 'grip')) {
                $imagePath = 'https://images.unsplash.com/photo-1534158914592-062992fbe900?w=800&auto=format&fit=crop&q=80';
            } else {
                $imagePath = 'https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?w=800&auto=format&fit=crop&q=80';
            }
        }

        $specs = [
            'flex' => $validated['spec_flex'] ?? null,
            'frame' => $validated['spec_frame'] ?? null,
            'balance' => $validated['spec_balance'] ?? null,
            'string_tension' => $validated['spec_tension'] ?? null,
            'weight_grip' => $validated['spec_weight_grip'] ?? null,
        ];

        $product->update([
            'name' => $validated['name'],
            'sku' => strtoupper($validated['sku']),
            'category_id' => $validated['category_id'],
            'brand_id' => $validated['brand_id'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'discount_price' => $validated['discount_price'] ?? null,
            'cost_price' => $validated['cost_price'] ?? null,
            'stock_quantity' => $validated['stock_quantity'],
            'min_stock_level' => $validated['min_stock_level'] ?? 5,
            'image' => $imagePath,
            'status' => $validated['status'],
            'is_featured' => $request->boolean('is_featured'),
            'specifications' => array_filter($specs),
        ]);

        return redirect()->route('admin.products.index')->with('success', "Product '{$product->name}' updated successfully.");
    }

    public function adjustStock(Request $request, int $id)
    {
        $request->validate([
            'quantity' => ['required', 'integer'],
            'reason' => ['nullable', 'string'],
        ]);

        $product = Product::findOrFail($id);
        $qty = (int) $request->input('quantity');
        $reason = $request->input('reason', 'Manual Admin Inventory Adjustment');

        if ($qty !== 0) {
            $this->inventoryService->adjustStock($product, null, $qty, $reason);
        }

        return back()->with('success', "Inventory adjusted for {$product->name}. Current stock: {$product->fresh()->stock_quantity}");
    }

    public function destroy(int $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }

    /**
     * Quick Store for New Category (via AJAX modal or direct form)
     */
    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'icon' => ['nullable', 'string', 'max:50'],
        ]);

        $name = trim($validated['name']);
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        $category = Category::create([
            'name' => $name,
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'icon' => $validated['icon'] ?? 'tag',
            'is_active' => true,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'category' => $category,
                'message' => "Category '{$category->name}' created successfully!",
            ]);
        }

        return back()->with('success', "Category '{$category->name}' added successfully.");
    }

    /**
     * Quick Store for New Brand (via AJAX modal or direct form)
     */
    public function storeBrand(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $name = trim($validated['name']);
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;
        while (Brand::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        $brand = Brand::create([
            'name' => $name,
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'brand' => $brand,
                'message' => "Brand '{$brand->name}' created successfully!",
            ]);
        }

        return back()->with('success', "Brand '{$brand->name}' added successfully.");
    }

    /**
     * Auto-ensure baseline categories and brands exist
     */
    protected function ensureCategoriesAndBrandsExist(): void
    {
        try {
            if (Category::count() === 0) {
                $categories = [
                    ['name' => 'Badminton Rackets', 'slug' => 'badminton-rackets', 'icon' => 'zap', 'description' => 'Professional & intermediate racquets.'],
                    ['name' => 'Badminton Shoes', 'slug' => 'badminton-shoes', 'icon' => 'footprints', 'description' => 'Court shoes with Power Cushion grip.'],
                    ['name' => 'Shuttlecocks', 'slug' => 'shuttlecocks', 'icon' => 'feather', 'description' => 'BWF tournament feather and nylon shuttlecocks.'],
                    ['name' => 'Strings & Tension', 'slug' => 'strings-tension', 'icon' => 'activity', 'description' => 'High-repulsion strings.'],
                    ['name' => 'Bags & Backpacks', 'slug' => 'bags-backpacks', 'icon' => 'package', 'description' => 'Multi-racket tournament bags.'],
                    ['name' => 'Grips & Accessories', 'slug' => 'grips-accessories', 'icon' => 'tag', 'description' => 'Tacky overgrips and accessories.'],
                    ['name' => 'Tennis & Court Gear', 'slug' => 'tennis-court-gear', 'icon' => 'award', 'description' => 'Tennis equipment.'],
                ];
                foreach ($categories as $c) {
                    Category::updateOrCreate(['slug' => $c['slug']], $c);
                }
            }

            if (Brand::count() === 0) {
                $brands = [
                    ['name' => 'Yonex', 'slug' => 'yonex', 'description' => 'World #1 equipment manufacturer.'],
                    ['name' => 'Victor', 'slug' => 'victor', 'description' => 'Premium performance gear trusted by world champions.'],
                    ['name' => 'Li-Ning', 'slug' => 'li-ning', 'description' => 'Innovative materials and lightning speed frames.'],
                    ['name' => 'Mizuno', 'slug' => 'mizuno', 'description' => 'Japanese craftsmanship court shoes and rackets.'],
                    ['name' => 'Ashaway', 'slug' => 'ashaway', 'description' => 'Industry leaders in strings.'],
                    ['name' => 'Wilson', 'slug' => 'wilson', 'description' => 'World-class tennis and badminton equipment.'],
                    ['name' => 'Babolat', 'slug' => 'babolat', 'description' => 'High performance tournament racquets.'],
                    ['name' => 'Head', 'slug' => 'head', 'description' => 'Precision tennis racquets and court gear.'],
                ];
                foreach ($brands as $b) {
                    Brand::updateOrCreate(['slug' => $b['slug']], $b);
                }
            }
        } catch (\Throwable $e) {
            // Silently continue
        }
    }
}
