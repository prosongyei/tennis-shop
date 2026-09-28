<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
    ];

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getSubtotalAttribute(): float
    {
        return (float) $this->items->sum(function ($item) {
            return $item->subtotal;
        });
    }

    public function getTotalQuantityAttribute(): int
    {
        return (int) $this->items->sum('quantity');
    }

    /**
     * Get or create active cart with automatic guest session merging upon login
     */
    public static function getActiveCart(\Illuminate\Http\Request $request): self
    {
        $sessionId = $request->session()->getId();

        if (\Illuminate\Support\Facades\Auth::check()) {
            $user = \Illuminate\Support\Facades\Auth::user();
            $cart = static::firstOrCreate(['user_id' => $user->id]);

            // Merge guest session cart if user previously had items before login
            $guestCart = static::where('session_id', $sessionId)->whereNull('user_id')->first();
            if ($guestCart && $guestCart->id !== $cart->id) {
                foreach ($guestCart->items as $item) {
                    $existing = $cart->items()->where('product_id', $item->product_id)
                        ->where('product_variant_id', $item->product_variant_id)
                        ->first();
                    if ($existing) {
                        $existing->update(['quantity' => $existing->quantity + $item->quantity]);
                    } else {
                        $item->update(['cart_id' => $cart->id]);
                    }
                }
                $guestCart->delete();
            }

            return $cart;
        }

        return static::firstOrCreate(['session_id' => $sessionId, 'user_id' => null]);
    }
}
