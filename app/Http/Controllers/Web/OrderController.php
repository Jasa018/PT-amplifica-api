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
     * Synchronize orders from all stores.
     *
     * @param SyncService $syncService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sync(SyncService $syncService)
    {
        $syncService->syncAllOrders();
        return redirect()->route('orders.index')->with('success', 'Orders are being synchronized.');
    }
    /**
     * Display a listing of the resource.
     *
     * @param SyncService $syncService
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Fetch all orders from the local database
        $orders = Order::with(['store', 'items']);

        // Apply filters
        if ($request->filled('start_date')) {
            $orders->whereDate('order_date', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $orders->whereDate('order_date', '<=', $request->input('end_date'));
        }

        if ($request->filled('customer_name')) {
            $orders->where('customer_name', 'like', '%' . $request->input('customer_name') . '%');
        }

        if ($request->filled('status')) {
            $orders->where('status', $request->input('status'));
        }

        if ($request->filled('store_name')) {
            $storeName = $request->input('store_name');
            $orders->whereHas('store', function ($query) use ($storeName) {
                $query->where('name', 'like', '%' . $storeName . '%');
            });
        }

        $orders = $orders->latest('order_date')->paginate(20)->withQueryString();

        // Get distinct statuses for the filter dropdown
        $statuses = Order::distinct()->pluck('status')->sort()->toArray();

        return view('orders.index', compact('orders', 'statuses'));
    }

    /**
     * Export orders to a CSV file.
     *
     * @param SyncService $syncService
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportCsv()
    {
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
                    $order->store->name, // Changed from platform to name
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