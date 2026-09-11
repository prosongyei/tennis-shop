<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'variant_name',
        'size',
        'color',
        'attributes',
        'price_override',
        'stock_quantity',
    ];

    protected $casts = [
        'attributes' => 'array',
        'price_override' => 'decimal:2',
        'stock_quantity' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getEffectivePriceAttribute(): float
    {
        if ($this->price_override !== null && $this->price_override > 0) {
            return (float) $this->price_override;
        }
        return $this->product->effective_price;
    }

    public function getIsOutOfStockAttribute(): bool
    {
        return $this->stock_quantity <= 0;
    }
}
