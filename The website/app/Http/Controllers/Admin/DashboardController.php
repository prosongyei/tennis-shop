<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->startOfDay();

        $totalRevenue = Order::where('payment_status', 'paid')->sum('total_amount');
        $todayRevenue = Order::where('payment_status', 'paid')->where('created_at', '>=', $today)->sum('total_amount');

        $totalOrders = Order::count();
        $todayOrders = Order::where('created_at', '>=', $today)->count();
        $pendingOrders = Order::where('order_status', 'pending')->count();

        $totalProducts = Product::count();
        $lowStockProducts = Product::where('stock_quantity', '<=', 5)->count();

        $recentOrders = Order::with(['items', 'user'])
            ->latest()
            ->take(8)
            ->get();

        $lowStockItems = Product::with(['brand', 'category'])
            ->where('stock_quantity', '<=', 5)
            ->take(6)
            ->get();

        $topProducts = OrderItem::select('product_name', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(subtotal) as total_sales'))
            ->groupBy('product_name')
            ->orderByDesc('total_sold')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalRevenue',
            'todayRevenue',
            'totalOrders',
            'todayOrders',
            'pendingOrders',
            'totalProducts',
            'lowStockProducts',
            'recentOrders',
            'lowStockItems',
            'topProducts'
        ));
    }
}
