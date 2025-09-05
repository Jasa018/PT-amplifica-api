<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\SyncService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param SyncService $syncService
     * @return \Illuminate\Http\Response
     */
    public function index(SyncService $syncService)
    {
        // Sync orders from all platforms
        $syncService->syncShopifyOrders();
        $syncService->syncWooCommerceOrders();

        // Fetch all orders from the local database
        $orders = Order::with(['store', 'items'])->latest('order_date')->paginate(20);

        return view('orders.index', compact('orders'));
    }
}
