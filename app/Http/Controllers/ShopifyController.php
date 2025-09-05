<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ShopifyController extends Controller
{
    /**
     * Redirects the user to the Shopify authorization page.
     */
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

    /**
     * Handles the callback from Shopify after authorization.
     */
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
                    // You might want to associate this with the logged-in user
                    // 'user_id' => auth()->id() ?? 1, // Example: replace with actual user ID
                ]
            );

            Log::info("Successfully installed on shop: {$shop}");

            // You can redirect the user to your app's frontend
            return response()->json([
                'message' => 'Shopify app installed successfully!',
                'store' => $store,
            ]);

        } catch (\Exception $e) {
            Log::error('Shopify callback error: ' . $e->getMessage());
            return response()->json(['message' => 'An unexpected error occurred.'], 500);
        }
    }

    /**
     * Makes a test request to the Shopify API to fetch products.
     */
    public function testApi(Request $request)
    {
        $products = $this->getProducts();

        if ($products instanceof \Illuminate\Http\JsonResponse) {
            return $products; // Return error response if something went wrong
        }

        return response()->json($products);
    }

    /**
     * Fetches recent orders from the Shopify API and returns as JSON.
     */
    public function getRecentOrders(Request $request)
    {
        $orders = $this->getOrders();

        if ($orders instanceof \Illuminate\Http\JsonResponse) {
            return $orders; // Return error response
        }

        return response()->json($orders);
    }

    /**
     * Exports products to a CSV file.
     */
    public function exportProductsCsv()
    {
        $products = $this->getProducts();

        if ($products instanceof \Illuminate\Http\JsonResponse) {
            return $products; // Return error response
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="products.csv"',
        ];

        $callback = function () use ($products) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Name', 'SKU', 'Price', 'Image URL']);
            foreach ($products['products'] as $product) {
                fputcsv($file, [
                    $product['title'],
                    $product['variants'][0]['sku'] ?? 'N/A',
                    $product['variants'][0]['price'] ?? '0.00',
                    $product['image']['src'] ?? 'N/A',
                ]);
            }
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    /**
     * Exports recent orders to a CSV file.
     */
    public function exportOrdersCsv()
    {
        $orders = $this->getOrders();

        if ($orders instanceof \Illuminate\Http\JsonResponse) {
            return $orders; // Return error response
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="orders.csv"',
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Order ID', 'Customer', 'Email', 'Date', 'Status', 'Total', 'Products (SKU)']);
            foreach ($orders['orders'] as $order) {
                $lineItems = array_map(function ($item) {
                    return $item['sku'] ?? 'N/A';
                }, $order['line_items']);

                fputcsv($file, [
                    $order['id'],
                    ($order['customer']['first_name'] ?? '' . ' ' . $order['customer']['last_name'] ?? ''),
                    $order['email'] ?? 'N/A',
                    $order['created_at'],
                    $order['financial_status'],
                    $order['total_price'],
                    implode('; ', $lineItems),
                ]);
            }
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    /**
     * Private method to fetch all products.
     */
    private function getProducts()
    {
        $token = config('services.shopify.admin_access_token');
        $storeUrl = config('services.shopify.store_url');

        if (!$token || !$storeUrl) {
            return response()->json(['message' => 'Shopify token or store URL is not configured.'], 400);
        }

        try {
            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $token,
            ])->get("https://{$storeUrl}/admin/api/2023-10/products.json", [
                'limit' => 250, // Max limit
            ]);

            if ($response->failed()) {
                Log::error('Shopify API test failed.', $response->json());
                return response()->json(['message' => 'Failed to fetch data from Shopify API.', 'details' => $response->json()], $response->status());
            }

            return $response->json();

        } catch (\Exception $e) {
            Log::error('Shopify API test error: ' . $e->getMessage());
            return response()->json(['message' => 'An unexpected error occurred during the API test.'], 500);
        }
    }

    /**
     * Private method to fetch recent orders.
     */
    private function getOrders()
    {
        $token = config('services.shopify.admin_access_token');
        $storeUrl = config('services.shopify.store_url');

        if (!$token || !$storeUrl) {
            return response()->json(['message' => 'Shopify token or store URL is not configured.'], 400);
        }

        $dateFrom = now()->subDays(30)->toIso8601String();

        try {
            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $token,
            ])->get("https://{$storeUrl}/admin/api/2023-10/orders.json", [
                'status' => 'any',
                'created_at_min' => $dateFrom,
                'limit' => 250,
            ]);

            if ($response->failed()) {
                Log::error('Shopify API failed to get orders.', $response->json());
                return response()->json(['message' => 'Failed to fetch orders from Shopify API.', 'details' => $response->json()], $response->status());
            }

            return $response->json();

        } catch (\Exception $e) {
            Log::error('Shopify get orders error: ' . $e->getMessage());
            return response()->json(['message' => 'An unexpected error occurred while fetching orders.'], 500);
        }
    }
}
