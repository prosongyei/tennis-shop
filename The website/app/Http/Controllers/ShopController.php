<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index()
    {
        $featuredProducts = Product::with(['brand', 'category', 'variants', 'images'])
            ->where('status', 'active')
            ->where('is_featured', true)
            ->take(8)
            ->get();

        $latestProducts = Product::with(['brand', 'category', 'variants', 'images'])
            ->where('status', 'active')
            ->latest()
            ->take(8)
            ->get();

        $categories = Category::withCount(['products' => function ($q) {
            $q->where('status', 'active');
        }])->take(6)->get();

        $brands = Brand::withCount(['products' => function ($q) {
            $q->where('status', 'active');
        }])->get();

        return view('shop.index', compact('featuredProducts', 'latestProducts', 'categories', 'brands'));
    }

    public function catalog(Request $request)
    {
        $query = Product::with(['brand', 'category', 'variants', 'images'])
            ->where('status', 'active');

        // Search query
        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $category = Category::where('slug', $request->input('category'))->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        // Brand filter
        if ($request->filled('brand')) {
            $brand = Brand::where('slug', $request->input('brand'))->first();
            if ($brand) {
                $query->where('brand_id', $brand->id);
            }
        }

        // Price range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->input('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->input('max_price'));
        }

        // In-stock only
        if ($request->boolean('in_stock')) {
            $query->where('stock_quantity', '>', 0);
        }

        // Sorting
        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'price_low' => $query->orderBy('price', 'asc'),
            'price_high' => $query->orderBy('price', 'desc'),
            'name_asc' => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            'popular' => $query->orderBy('is_featured', 'desc')->latest(),
            default => $query->latest(),
        };

        $products = $query->paginate(12)->withQueryString();

        $categories = Category::withCount(['products' => function ($q) {
            $q->where('status', 'active');
        }])->get();

        $brands = Brand::withCount(['products' => function ($q) {
            $q->where('status', 'active');
        }])->get();

        $selectedCategory = $request->filled('category') ? Category::where('slug', $request->input('category'))->first() : null;
        $selectedBrand = $request->filled('brand') ? Brand::where('slug', $request->input('brand'))->first() : null;

        return view('shop.catalog', compact(
            'products',
            'categories',
            'brands',
            'selectedCategory',
            'selectedBrand'
        ));
    }

    public function show(string $slug)
    {
        $product = Product::with(['brand', 'category', 'variants', 'images', 'reviews.user'])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $relatedProducts = Product::with(['brand', 'category', 'variants', 'images'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'active')
            ->take(4)
            ->get();

        return view('shop.show', compact('product', 'relatedProducts'));
    }

    public function quickView(int $id)
    {
        $product = Product::with(['brand', 'category', 'variants', 'images'])
            ->findOrFail($id);

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'sku' => $product->sku,
            'brand' => $product->brand ? $product->brand->name : null,
            'category' => $product->category ? $product->category->name : null,
            'price' => $product->effective_price,
            'original_price' => $product->price,
            'has_discount' => $product->has_discount,
            'discount_percentage' => $product->discount_percentage,
            'image' => $product->image,
            'total_stock' => $product->total_stock,
            'is_out_of_stock' => $product->is_out_of_stock,
            'specifications' => $product->specifications,
            'variants' => $product->variants->map(function ($v) {
                return [
                    'id' => $v->id,
                    'name' => $v->variant_name,
                    'sku' => $v->sku,
                    'price' => $v->effective_price,
                    'stock' => $v->stock_quantity,
                ];
            }),
        ]);
    }
}
