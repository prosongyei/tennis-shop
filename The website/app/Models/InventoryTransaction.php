<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'product_variant_id',
        'type',
        'quantity_change',
        'previous_stock',
        'new_stock',
        'user_id',
        'reason',
        'reference_type',
        'reference_id',
    ];

    protected $casts = [
        'quantity_change' => 'integer',
        'previous_stock' => 'integer',
        'new_stock' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
