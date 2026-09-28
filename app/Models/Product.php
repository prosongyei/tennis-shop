<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'sku',
        'category_id',
        'brand_id',
        'description',
        'price',
        'discount_price',
        'cost_price',
        'stock_quantity',
        'min_stock_level',
        'image',
        'status',
        'is_featured',
        'specifications',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'min_stock_level' => 'integer',
        'is_featured' => 'boolean',
        'specifications' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class)->where('is_approved', true);
    }

    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function getEffectivePriceAttribute(): float
    {
        return (float) ($this->discount_price && $this->discount_price > 0 ? $this->discount_price : $this->price);
    }

    public function getHasDiscountAttribute(): bool
    {
        return $this->discount_price && $this->discount_price > 0 && $this->discount_price < $this->price;
    }

    public function getDiscountPercentageAttribute(): int
    {
        if ($this->has_discount && $this->price > 0) {
            return (int) round((($this->price - $this->discount_price) / $this->price) * 100);
        }
        return 0;
    }

    public function getTotalStockAttribute(): int
    {
        if ($this->relationLoaded('variants')) {
            return $this->variants->isNotEmpty()
                ? (int) $this->variants->sum('stock_quantity')
                : (int) $this->stock_quantity;
        }

        if ($this->variants()->exists()) {
            return (int) $this->variants()->sum('stock_quantity');
        }
        return (int) $this->stock_quantity;
    }

    public function getIsOutOfStockAttribute(): bool
    {
        return $this->total_stock <= 0;
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->total_stock > 0 && $this->total_stock <= $this->min_stock_level;
    }

    public function getAverageRatingAttribute(): float
    {
        if ($this->relationLoaded('reviews')) {
            return (float) ($this->reviews->isNotEmpty() ? $this->reviews->avg('rating') : 5.0);
        }

        return (float) ($this->reviews()->avg('rating') ?: 5.0);
    }

    public function getSmartFallbackImage(): string
    {
        $categorySlug = strtolower($this->category?->slug ?? '');
        $categoryName = strtolower($this->category?->name ?? '');
        $nameLower = strtolower($this->name ?? '');

        // Shoes
        if (str_contains($categorySlug, 'shoe') || str_contains($categoryName, 'shoe') || str_contains($nameLower, 'shoe') || str_contains($nameLower, '65z') || str_contains($nameLower, 'p9200')) {
            return 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=800&auto=format&fit=crop&q=80';
        }

        // Shuttlecocks
        if (str_contains($categorySlug, 'shuttle') || str_contains($categoryName, 'shuttle') || str_contains($nameLower, 'shuttle') || str_contains($nameLower, 'aerosensa') || str_contains($nameLower, 'as-50') || str_contains($nameLower, 'mavis')) {
            return 'https://images.unsplash.com/photo-1613918108466-292b78a8ef95?w=800&auto=format&fit=crop&q=80';
        }

        // Bags
        if (str_contains($categorySlug, 'bag') || str_contains($categoryName, 'bag') || str_contains($nameLower, 'bag') || str_contains($nameLower, 'pack')) {
            return 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=800&auto=format&fit=crop&q=80';
        }

        // Strings
        if (str_contains($categorySlug, 'string') || str_contains($categoryName, 'string') || str_contains($nameLower, 'string') || str_contains($nameLower, 'bg80') || str_contains($nameLower, 'nanogy')) {
            return 'https://images.unsplash.com/photo-1521537634581-0dced2fed2a8?w=800&auto=format&fit=crop&q=80';
        }

        // Grips / Accessories
        if (str_contains($categorySlug, 'grip') || str_contains($categoryName, 'grip') || str_contains($nameLower, 'grip') || str_contains($nameLower, 'grap') || str_contains($nameLower, 'wrist')) {
            return 'https://images.unsplash.com/photo-1534158914592-062992fbe900?w=800&auto=format&fit=crop&q=80';
        }

        // Default: Professional Racket
        return 'https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?w=800&auto=format&fit=crop&q=80';
    }

    public function getPrimaryImageUrlAttribute(): string
    {
        // 1. Check if product has related gallery images loaded
        if ($this->relationLoaded('images') && $this->images->isNotEmpty()) {
            $primary = $this->images->firstWhere('is_primary', true) ?: $this->images->first();
            if ($primary && !empty($primary->image_path)) {
                $path = trim($primary->image_path);
                if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'data:')) {
                    return $path;
                }
                $clean = ltrim($path, '/');
                if (file_exists(public_path($clean))) {
                    return asset($clean);
                }
            }
        }

        // 2. Check if product has an image field in DB
        $raw = $this->attributes['image'] ?? null;
        if (!empty($raw)) {
            $raw = trim($raw);
            if (str_starts_with($raw, 'http://') || str_starts_with($raw, 'https://') || str_starts_with($raw, 'data:')) {
                return $raw;
            }
            $clean = ltrim($raw, '/');
            if (file_exists(public_path($clean))) {
                return asset($clean);
            }
        }

        // 3. Fallback to category/name based high quality image
        return $this->getSmartFallbackImage();
    }

    public function getImageAttribute($value): string
    {
        if (empty($value)) {
            return $this->primary_image_url;
        }

        $value = trim($value);

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, 'data:')) {
            return $value;
        }

        $clean = ltrim($value, '/');
        if (file_exists(public_path($clean))) {
            return asset($clean);
        }

        // If local file does not exist, return smart fallback instead of broken 404 URL
        return $this->primary_image_url;
    }
}
