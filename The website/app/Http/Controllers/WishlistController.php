<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /**
     * Display the wishlist page
     */
    public function index()
    {
        $wishlist = session()->get('wishlist', []);
        $productIds = array_keys($wishlist);

        $products = Product::with(['brand', 'category', 'images', 'variants'])
            ->whereIn('id', $productIds)
            ->where('status', 'active')
            ->get();

        return view('shop.wishlist', compact('products'));
    }

    /**
     * Toggle a product into or out of the user's wishlist
     */
    public function toggle(Request $request, int $productId)
    {
        $product = Product::findOrFail($productId);
        $wishlist = session()->get('wishlist', []);

        if (isset($wishlist[$productId])) {
            unset($wishlist[$productId]);
            $added = false;
            $message = "Removed {$product->name} from your wishlist.";
        } else {
            $wishlist[$productId] = [
                'added_at' => now()->toDateTimeString(),
                'name' => $product->name,
                'price' => $product->effective_price,
            ];
            $added = true;
            $message = "Saved {$product->name} to your wishlist!";
        }

        session()->put('wishlist', $wishlist);
        $count = count($wishlist);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'added' => $added,
                'count' => $count,
                'message' => $message,
                'product_id' => $productId,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Clear all items from the wishlist
     */
    public function clear()
    {
        session()->forget('wishlist');
        return redirect()->route('wishlist.index')->with('success', 'Your wishlist has been cleared.');
    }
}
