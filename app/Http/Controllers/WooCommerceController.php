<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Services\SyncService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WooCommerceController extends Controller
{
    public function getProducts(Request $request, SyncService $syncService)
    {
        $syncService->syncWooCommerceProducts();

        $storeUrl = config('services.woocommerce.store_url');
        $store = Store::where('store_url', $storeUrl)->where('platform', 'woocommerce')->first();

        if (!$store) {
            return response()->json(['message' => 'Store not found in local database.'], 404);
        }

        $products = Product::where('store_id', $store->id)->get();

        return response()->json($products);
    }

    public function getRecentOrders(Request $request, SyncService $syncService)
    {
        $syncService->syncWooCommerceOrders();

        $storeUrl = config('services.woocommerce.store_url');
        $store = Store::where('store_url', $storeUrl)->where('platform', 'woocommerce')->first();

        if (!$store) {
            return response()->json(['message' => 'Store not found in local database.'], 404);
        }

        $orders = Order::with('items')
            ->where('store_id', $store->id)
            ->where('order_date', '>=', now()->subDays(30))
            ->latest('order_date')
            ->get();

        return response()->json($orders);
    }

    public function exportProductsCsv(SyncService $syncService)
    {
        $syncService->syncWooCommerceProducts();

        $storeUrl = config('services.woocommerce.store_url');
        $store = Store::where('store_url', $storeUrl)->where('platform', 'woocommerce')->first();

        if (!$store) {
            return response()->json(['message' => 'Store not found, cannot export.'], 404);
        }

        $products = Product::where('store_id', $store->id)->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="woocommerce-products.csv"',
        ];

        $callback = function () use ($products) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Name', 'SKU', 'Price', 'Image URL']);
            foreach ($products as $product) {
                fputcsv($file, [
                    $product->name,
                    $product->sku ?? 'N/A',
                    $product->price ?? '0.00',
                    $product->image_url ?? 'N/A',
                ]);
            }
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    public function exportOrdersCsv(SyncService $syncService)
    {
        $syncService->syncWooCommerceOrders();

        $storeUrl = config('services.woocommerce.store_url');
        $store = Store::where('store_url', $storeUrl)->where('platform', 'woocommerce')->first();

        if (!$store) {
            return response()->json(['message' => 'Store not found, cannot export.'], 404);
        }

        $orders = Order::with('items')
            ->where('store_id', $store->id)
            ->where('order_date', '>=', now()->subDays(30))
            ->latest('order_date')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="woocommerce-orders.csv"',
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Order ID', 'Customer', 'Date', 'Status', 'Total', 'Products (Name x Qty)']);
            foreach ($orders as $order) {
                $lineItems = $order->items->map(function ($item) {
                    return sprintf('%s x %d', $item->product_name, $item->quantity);
                })->implode('; ');

                fputcsv($file, [
                    $order->platform_order_id,
                    $order->customer_name,
                    $order->order_date,
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
