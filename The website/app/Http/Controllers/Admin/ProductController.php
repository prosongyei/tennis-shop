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

        $products = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::all();
        $brands = Brand::all();

        return view('admin.products.index', compact('products', 'categories', 'brands'));
    }

    public function create()
    {
        $categories = Category::all();
        $brands = Brand::all();
        return view('admin.products.form', compact('categories', 'brands'));
    }

    public function store(Request $request)
    {
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
        $product = Product::with(['variants', 'images'])->findOrFail($id);
        $categories = Category::all();
        $brands = Brand::all();

        return view('admin.products.form', compact('product', 'categories', 'brands'));
    }

    public function update(Request $request, int $id)
    {
        $product = Product::findOrFail($id);

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

        if ($qty > 0) {
            $this->inventoryService->addStock($product, null, $qty, $reason, 'manual_adjustment');
        } elseif ($qty < 0) {
            $this->inventoryService->deductStock($product, null, abs($qty), $reason, 'manual_adjustment');
        }

        return back()->with('success', "Inventory adjusted for {$product->name}. Current stock: {$product->fresh()->stock_quantity}");
    }

    public function destroy(int $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }
}
