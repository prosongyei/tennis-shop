<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index()
    {
        $orders = Order::with(['items.product'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function show(string $orderNumber)
    {
        $order = Order::with(['items.product.brand', 'items.variant', 'payments'])
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        // Check customer permission if order is bound to a user
        if ($order->user_id) {
            if (!$user) {
                if (session('last_order_number') !== $order->order_number) {
                    return redirect()->guest(route('login'))->with('error', 'Please sign in to view your order.');
                }
            } elseif ($user->id !== $order->user_id && !$user->isAdmin() && !$user->isCashier()) {
                abort(403, 'Unauthorized access to this order.');
            }
        }

        return view('orders.show', compact('order'));
    }

    public function invoice(string $orderNumber)
    {
        $order = Order::with(['items.product.brand', 'items.variant', 'payments'])
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        return view('orders.invoice', compact('order'));
    }

    public function track(Request $request)
    {
        $order = null;
        $matchingOrders = collect();
        $userRecentOrders = collect();

        // If authenticated, prefetch user's latest orders for quick access
        if (Auth::check()) {
            $userRecentOrders = Order::with(['items.product', 'payments'])
                ->where('user_id', Auth::id())
                ->latest()
                ->take(5)
                ->get();
        }

        if ($request->filled('order_number') || $request->filled('phone') || $request->filled('email')) {
            $query = Order::with(['items.product.brand', 'items.variant', 'payments']);

            if ($request->filled('order_number')) {
                $cleanOrderNumber = strtoupper(trim(str_replace('#', '', (string) $request->input('order_number'))));
                $query->where('order_number', $cleanOrderNumber);
            }

            if ($request->filled('phone')) {
                $cleanPhone = preg_replace('/[^0-9+]/', '', trim((string) $request->input('phone')));
                if (!empty($cleanPhone)) {
                    $query->where('customer_phone', 'like', '%' . $cleanPhone . '%');
                }
            }

            if ($request->filled('email')) {
                $cleanEmail = strtolower(trim((string) $request->input('email')));
                if (!empty($cleanEmail)) {
                    $query->where('customer_email', 'like', '%' . $cleanEmail . '%');
                }
            }

            $matchingOrders = $query->latest()->get();
            $order = $matchingOrders->first();

            if (!$order) {
                return back()->withInput()->with('error', 'No order found with the provided details. Please verify your Order Number or Phone Number.');
            }
        }

        return view('orders.track', compact('order', 'matchingOrders', 'userRecentOrders'));
    }

    public function cancel(Request $request, string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($order->user_id) {
            if (Auth::id() !== $order->user_id && (!$user || !$user->isAdmin())) {
                abort(403, 'Unauthorized to cancel this order.');
            }
        } else {
            $sessionOrder = session('last_order_number');
            $providedPhone = preg_replace('/[^0-9]/', '', (string) $request->input('phone', ''));
            $orderPhone = preg_replace('/[^0-9]/', '', (string) $order->customer_phone);
            if ((!$user || !$user->isAdmin()) && $sessionOrder !== $order->order_number && (empty($providedPhone) || $providedPhone !== $orderPhone)) {
                abort(403, 'Authorization required to cancel this order.');
            }
        }

        try {
            $reason = $request->input('reason', 'Cancelled by customer');
            $this->orderService->cancelOrder($order, $reason);

            return back()->with('success', 'Order has been cancelled and items returned to stock.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
