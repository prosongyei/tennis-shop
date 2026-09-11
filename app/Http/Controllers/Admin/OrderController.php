<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\PaymentService;
use Exception;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected OrderService $orderService;
    protected PaymentService $paymentService;

    public function __construct(OrderService $orderService, PaymentService $paymentService)
    {
        $this->orderService = $orderService;
        $this->paymentService = $paymentService;
    }

    public function index(Request $request)
    {
        $query = Order::with(['user', 'items']);

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('order_status')) {
            $query->where('order_status', $request->input('order_status'));
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }

        if ($request->filled('source')) {
            $query->where('source', $request->input('source'));
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(int $id)
    {
        $order = Order::with(['user', 'cashier', 'items.product.brand', 'items.variant', 'payments'])->findOrFail($id);

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, int $id)
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'order_status' => ['required', 'in:pending,confirmed,processing,ready_pickup,shipped,delivered,completed,cancelled'],
        ]);

        $order->update([
            'order_status' => $validated['order_status'],
        ]);

        return back()->with('success', "Order #{$order->order_number} status updated to " . strtoupper($validated['order_status']));
    }

    public function verifyPayment(Request $request, int $id)
    {
        $order = Order::findOrFail($id);

        $this->paymentService->markOrderAsPaid(
            $order,
            $order->payment_method ?? 'manual_bank',
            'MANUAL-' . time(),
            'Manually verified by admin ' . auth()->user()->name,
            auth()->id()
        );

        return back()->with('success', "Payment for Order #{$order->order_number} marked as Paid.");
    }

    public function cancel(Request $request, int $id)
    {
        $order = Order::findOrFail($id);

        try {
            $reason = $request->input('reason', 'Cancelled by store admin');
            $this->orderService->cancelOrder($order, $reason);

            return back()->with('success', "Order #{$order->order_number} cancelled and items returned to stock.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
