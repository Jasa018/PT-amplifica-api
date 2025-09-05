<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Automattic\WooCommerce\Client;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WooCommerceController extends Controller
{
    private function getClient()
    {
        $storeUrl = config('services.woocommerce.store_url');
        $consumerKey = config('services.woocommerce.consumer_key');
        $consumerSecret = config('services.woocommerce.consumer_secret');

        if (!$storeUrl || !$consumerKey || !$consumerSecret) {
            return null;
        }

        return new Client(
            $storeUrl,
            $consumerKey,
            $consumerSecret,
            [
                'version' => 'wc/v3',
            ]
        );
    }

    public function getProducts(Request $request)
    {
        $products = $this->fetchProducts();
        if ($products instanceof \Illuminate\Http\JsonResponse) {
            return $products;
        }
        return response()->json($products);
    }

    public function getRecentOrders(Request $request)
    {
        $orders = $this->fetchRecentOrders();
        if ($orders instanceof \Illuminate\Http\JsonResponse) {
            return $orders;
        }
        return response()->json($orders);
    }

    public function exportProductsCsv()
    {
        $products = $this->fetchProducts();
        if ($products instanceof \Illuminate\Http\JsonResponse) {
            return $products;
        }

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
                    $product->images[0]->src ?? 'N/A',
                ]);
            }
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    public function exportOrdersCsv()
    {
        $orders = $this->fetchRecentOrders();
        if ($orders instanceof \Illuminate\Http\JsonResponse) {
            return $orders;
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="woocommerce-orders.csv"',
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Order ID', 'Customer', 'Email', 'Date', 'Status', 'Total', 'Products (Name)']);
            foreach ($orders as $order) {
                $lineItems = array_map(function ($item) {
                    return $item->name;
                }, $order->line_items);

                fputcsv($file, [
                    $order->id,
                    ($order->billing->first_name ?? '') . ' ' . ($order->billing->last_name ?? ''),
                    $order->billing->email ?? 'N/A',
                    $order->date_created,
                    $order->status,
                    $order->total,
                    implode('; ', $lineItems),
                ]);
            }
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    private function fetchProducts()
    {
        $woocommerce = $this->getClient();
        if (!$woocommerce) {
            return response()->json(['message' => 'WooCommerce service is not configured.'], 400);
        }

        try {
            return $woocommerce->get('products');
        } catch (\Exception $e) {
            Log::error('WooCommerce API error fetching products: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch products from WooCommerce.'], 500);
        }
    }

    private function fetchRecentOrders()
    {
        $woocommerce = $this->getClient();
        if (!$woocommerce) {
            return response()->json(['message' => 'WooCommerce service is not configured.'], 400);
        }

        $dateFrom = now()->subDays(30)->toIso8601String();

        try {
            return $woocommerce->get('orders', ['after' => $dateFrom]);
        } catch (\Exception $e) {
            Log::error('WooCommerce API error fetching orders: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch orders from WooCommerce.'], 500);
        }
    }
}
