<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Product;
use App\Models\Order;
use App\Services\SyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ShopifyController extends Controller
{
    public function install(Request $request)
    {
        $request->validate([
            'shop' => 'required|string',
        ]);

        $shop = $request->input('shop');
        $apiKey = config('services.shopify.api_key');
        $scopes = 'read_products,write_products,read_orders,write_orders';
        $redirectUri = route('shopify.callback');

        $authUrl = "https://{$shop}/admin/oauth/authorize?client_id={$apiKey}&scope={$scopes}&redirect_uri={$redirectUri}";

        return redirect($authUrl);
    }

    public function callback(Request $request)
    {
        $request->validate([
            'shop' => 'required|string',
            'code' => 'required|string',
            'hmac' => 'required|string',
        ]);

        // 1. HMAC Validation
        $hmac = $request->input('hmac');
        $query = $request->except('hmac');
        ksort($query);
        $queryString = http_build_query($query);

        $apiSecret = config('services.shopify.api_secret');
        $calculatedHmac = hash_hmac('sha256', $queryString, $apiSecret);

        if (!hash_equals($hmac, $calculatedHmac)) {
            Log::error('Shopify HMAC validation failed.');
            return response()->json(['message' => 'HMAC validation failed'], 403);
        }

        // 2. Exchange authorization code for an access token
        $shop = $request->input('shop');
        $code = $request->input('code');
        $apiKey = config('services.shopify.api_key');

        try {
            $response = Http::post("https://{$shop}/admin/oauth/access_token", [
                'client_id' => $apiKey,
                'client_secret' => $apiSecret,
                'code' => $code,
            ]);

            if ($response->failed()) {
                Log::error('Shopify token exchange failed.', $response->json());
                return response()->json(['message' => 'Failed to get access token from Shopify'], 500);
            }

            $accessToken = $response->json('access_token');

            // 3. Store the access token securely
            $store = Store::updateOrCreate(
                ['store_url' => $shop, 'platform' => 'shopify'],
                [
                    'access_token' => encrypt($accessToken),
                ]
            );

            Log::info("Successfully installed on shop: {$shop}");

            return response()->json([
                'message' => 'Shopify app installed successfully!',
                'store' => $store,
            ]);

        } catch (\Exception $e) {
            Log::error('Shopify callback error: ' . $e->getMessage());
            return response()->json(['message' => 'An unexpected error occurred.'], 500);
        }
    }

    public function testApi(Request $request, SyncService $syncService)
    {
        $syncService->syncShopifyProducts();

        $storeUrl = config('services.shopify.store_url');
        $store = Store::where('store_url', $storeUrl)->first();

        if (!$store) {
            return response()->json(['message' => 'Store not found in local database.'], 404);
        }

        $products = Product::where('store_id', $store->id)->get();

        return response()->json($products);
    }

    public function getRecentOrders(Request $request, SyncService $syncService)
    {
        $syncService->syncShopifyOrders();

        $storeUrl = config('services.shopify.store_url');
        $store = Store::where('store_url', $storeUrl)->first();

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
        $syncService->syncShopifyProducts();

        $storeUrl = config('services.shopify.store_url');
        $store = Store::where('store_url', $storeUrl)->first();

        if (!$store) {
            return response()->json(['message' => 'Store not found, cannot export.'], 404);
        }

        $products = Product::where('store_id', $store->id)->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="products.csv"',
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
        $syncService->syncShopifyOrders();

        $storeUrl = config('services.shopify.store_url');
        $store = Store::where('store_url', $storeUrl)->first();

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
            'Content-Disposition' => 'attachment; filename="orders.csv"',
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