<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\SyncService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    /**
     * Export orders to a CSV file.
     *
     * @param SyncService $syncService
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportCsv(SyncService $syncService)
    {
        // Ensure data is up-to-date before exporting
        $syncService->syncShopifyOrders();
        $syncService->syncWooCommerceOrders();

        $orders = Order::with(['store', 'items'])->latest('order_date')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="orders.csv"',
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Order ID', 'Customer', 'Store', 'Date', 'Status', 'Total', 'Products']);
            foreach ($orders as $order) {
                $lineItems = $order->items->map(function ($item) {
                    return sprintf('%s x %d', $item->product_name, $item->quantity);
                })->implode('; ');

                fputcsv($file, [
                    $order->platform_order_id,
                    $order->customer_name,
                    $order->store->platform,
                    \Carbon\Carbon::parse($order->order_date)->format('Y-m-d H:i:s'),
                    $order->status,
                    $order->total_amount,
                    $lineItems,
                ]);
            }
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}