<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Display the analytics dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // 1. Total Sales (Last 30 Days)
        $totalSales = Order::where('order_date', '>=', now()->subDays(30))->sum('total_amount');

        // 2. Total Orders (Last 30 Days)
        $totalOrders = Order::where('order_date', '>=', now()->subDays(30))->count();

        // 3. Average Order Value (Last 30 Days)
        $averageOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        // 4. Top 5 Best-Selling Products (All Time)
        $topProducts = OrderItem::select('product_name', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('product_name')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get();

        return view('analytics.index', [
            'totalSales' => $totalSales,
            'totalOrders' => $totalOrders,
            'averageOrderValue' => $averageOrderValue,
            'topProducts' => $topProducts,
        ]);
    }
}
