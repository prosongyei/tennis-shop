<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Get or create active cart for guest or authenticated user
     */
    protected function getActiveCart(Request $request): Cart
    {
        $sessionId = $request->session()->getId();

        if (Auth::check()) {
            $user = Auth::user();
            $cart = Cart::firstOrCreate(['user_id' => $user->id]);

            // Merge guest session cart if user previously had items before login
            $guestCart = Cart::where('session_id', $sessionId)->whereNull('user_id')->first();
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

        return Cart::firstOrCreate(['session_id' => $sessionId, 'user_id' => null]);
    }

    public function index(Request $request)
    {
        $cart = $this->getActiveCart($request);
        $cart->load(['items.product.brand', 'items.variant']);

        return view('cart.index', compact('cart'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'variant_id' => ['nullable', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::findOrFail($request->product_id);
        $variant = $request->filled('variant_id') ? ProductVariant::find($request->variant_id) : null;
        $quantity = (int) $request->quantity;

        // Check stock availability
        $availableStock = $variant ? $variant->stock_quantity : $product->stock_quantity;
        if ($availableStock < $quantity) {
            $message = "Insufficient stock. Only {$availableStock} items available.";
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return back()->with('error', $message);
        }

        $cart = $this->getActiveCart($request);

        $existingItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->where('product_variant_id', $variant ? $variant->id : null)
            ->first();

        $price = $variant ? $variant->effective_price : $product->effective_price;

        if ($existingItem) {
            $newQuantity = $existingItem->quantity + $quantity;
            if ($availableStock < $newQuantity) {
                $newQuantity = $availableStock;
            }
            $existingItem->update([
                'quantity' => $newQuantity,
                'unit_price' => $price,
            ]);
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'product_variant_id' => $variant ? $variant->id : null,
                'quantity' => $quantity,
                'unit_price' => $price,
            ]);
        }

        $cart->refresh();
        $cartCount = $cart->total_quantity;
        $cartSubtotal = $cart->subtotal;

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "{$product->name} added to cart!",
                'cart_count' => $cartCount,
                'cart_subtotal' => number_format($cartSubtotal, 2),
                'redirect' => $request->boolean('buy_now') ? route('checkout.index') : null,
            ]);
        }

        if ($request->boolean('buy_now')) {
            return redirect()->route('checkout.index')->with('success', "Proceeding to checkout with {$product->name}.");
        }

        return back()->with('cart_toast', [
            'message' => "{$product->name} added to your cart!",
            'product_name' => $product->name,
            'cart_count' => $cartCount,
            'cart_subtotal' => number_format($cartSubtotal, 2),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = $this->getActiveCart($request);
        $cartItem = CartItem::where('cart_id', $cart->id)->findOrFail($id);

        $product = $cartItem->product;
        $variant = $cartItem->variant;
        $quantity = (int) $request->quantity;

        $availableStock = $variant ? $variant->stock_quantity : $product->stock_quantity;
        if ($availableStock < $quantity) {
            $quantity = max(1, $availableStock);
        }

        $cartItem->update(['quantity' => $quantity]);
        $cart->refresh();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'item_subtotal' => number_format($cartItem->subtotal, 2),
                'cart_subtotal' => number_format($cart->subtotal, 2),
                'cart_count' => $cart->total_quantity,
                'quantity' => $cartItem->quantity,
            ]);
        }

        return back()->with('success', 'Cart updated.');
    }

    public function remove(Request $request, int $id)
    {
        $cart = $this->getActiveCart($request);
        $cartItem = CartItem::where('cart_id', $cart->id)->findOrFail($id);
        $cartItem->delete();

        $cart->refresh();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Item removed.',
                'cart_subtotal' => number_format($cart->subtotal, 2),
                'cart_count' => $cart->total_quantity,
            ]);
        }

        return back()->with('success', 'Item removed from cart.');
    }

    public function clear(Request $request)
    {
        $cart = $this->getActiveCart($request);
        $cart->items()->delete();

        return redirect()->route('cart.index')->with('success', 'Cart emptied.');
    }

    public function count(Request $request)
    {
        $cart = $this->getActiveCart($request);
        return response()->json([
            'count' => $cart->total_quantity,
            'subtotal' => number_format($cart->subtotal, 2),
        ]);
    }
}
