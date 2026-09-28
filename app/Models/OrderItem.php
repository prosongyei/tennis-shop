<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'product_name',
        'variant_name',
        'sku',
        'unit_price',
        'quantity',
        'subtotal',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'quantity' => 'integer',
        'subtotal' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->product && !empty($this->product->image)) {
            return $this->product->image;
        }

        $nameLower = strtolower($this->product_name ?? '');
        if (str_contains($nameLower, 'shoe') || str_contains($nameLower, '65z') || str_contains($nameLower, 'p9200')) {
            return 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=800&auto=format&fit=crop&q=80';
        }
        if (str_contains($nameLower, 'shuttle') || str_contains($nameLower, 'aerosensa') || str_contains($nameLower, 'as-50') || str_contains($nameLower, 'mavis')) {
            return 'https://images.unsplash.com/photo-1613918108466-292b78a8ef95?w=800&auto=format&fit=crop&q=80';
        }
        if (str_contains($nameLower, 'bag')) {
            return 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=800&auto=format&fit=crop&q=80';
        }
        if (str_contains($nameLower, 'string') || str_contains($nameLower, 'bg80')) {
            return 'https://images.unsplash.com/photo-1521537634581-0dced2fed2a8?w=800&auto=format&fit=crop&q=80';
        }
        if (str_contains($nameLower, 'grip')) {
            return 'https://images.unsplash.com/photo-1534158914592-062992fbe900?w=800&auto=format&fit=crop&q=80';
        }

        return asset('images/default-product.svg');
    }
}
