<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Services\OrderService;
use App\Services\PaymentService;
use Exception;
use Illuminate\Http\Request;

class PosController extends Controller
{
    protected OrderService $orderService;
    protected PaymentService $paymentService;

    public function __construct(OrderService $orderService, PaymentService $paymentService)
    {
        $this->orderService = $orderService;
        $this->paymentService = $paymentService;
    }

    public function index()
    {
        $categories = Category::all();
        $products = Product::with(['brand', 'category', 'variants'])
            ->where('status', 'active')
            ->where('stock_quantity', '>', 0)
            ->take(30)
            ->get();

        $merchantName = Setting::get('bakong_account_name', config('services.bakong.account_name', 'SMASH BADMINTON STORE'));
        $khrExchangeRate = (float) Setting::get('khr_exchange_rate', 4100);

        return view('pos.index', compact('categories', 'products', 'merchantName', 'khrExchangeRate'));
    }

    public function search(Request $request)
    {
        $query = $request->input('q');
        $categoryId = $request->input('category') ?: $request->input('category_id');

        $productsQuery = Product::with(['brand', 'variants'])
            ->where('status', 'active');

        if (!empty($categoryId)) {
            $productsQuery->where('category_id', $categoryId);
        }

        if (!empty($query)) {
            $productsQuery->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%");
            });
        }

        $products = $productsQuery->take(30)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'sku' => $p->sku,
                    'brand' => $p->brand ? $p->brand->name : '',
                    'price' => $p->effective_price,
                    'stock' => $p->stock_quantity,
                    'image' => $p->image,
                    'variants' => $p->variants->map(function ($v) {
                        return [
                            'id' => $v->id,
                            'name' => $v->variant_name,
                            'sku' => $v->sku,
                            'price' => $v->effective_price,
                            'stock' => $v->stock_quantity,
                        ];
                    }),
                ];
            });

        return response()->json($products);
    }

    public function processSale(Request $request)
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.variant_id' => ['nullable'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'payment_method' => ['required', 'in:cash_store,khqr'],
            'discount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $rawPhone = $validated['customer_phone'] ?? '';
        $cleanPhone = preg_replace('/[^0-9]/', '', (string) $rawPhone);

        try {
            $order = $this->orderService->createPosSale(
                $validated['items'],
                [
                    'customer_name' => $validated['customer_name'] ?? 'Walk-in Customer',
                    'customer_phone' => !empty($cleanPhone) ? $cleanPhone : 'N/A',
                ],
                $validated['payment_method'],
                (float) ($validated['discount'] ?? 0.00)
            );

            $order->load(['items']);

            if ($validated['payment_method'] === 'khqr') {
                return response()->json([
                    'success' => true,
                    'payment_method' => 'khqr',
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'total_amount' => $order->total_amount,
                    'khqr_string' => $order->khqr_string,
                    'khqr_md5' => $order->khqr_md5,
                    'receipt_url' => route('pos.receipt', $order->order_number),
                    'message' => 'KHQR generated for customer payment screen.',
                ]);
            }

            return response()->json([
                'success' => true,
                'payment_method' => 'cash_store',
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'total_amount' => $order->total_amount,
                'receipt_url' => route('pos.receipt', $order->order_number),
                'message' => 'Sale completed successfully! Cash recorded.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'POS Sale Failed: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function printReceipt(string $orderNumber)
    {
        $order = Order::with(['items', 'cashier'])
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        $merchantName = Setting::get('store_name', 'SMASH BADMINTON STORE');
        $phone = Setting::get('store_phone', '+855 12 345 678');
        $address = Setting::get('store_address', 'St. 2004, Sen Sok, Phnom Penh');
        $khrExchangeRate = (float) Setting::get('khr_exchange_rate', 4100);

        return view('pos.receipt', compact('order', 'merchantName', 'phone', 'address', 'khrExchangeRate'));
    }
}
