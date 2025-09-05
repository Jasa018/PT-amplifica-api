<?php

namespace App\Services;

use App\Models\Store;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use Automattic\WooCommerce\Client;

class SyncService
{
    /**
     * Synchronize products from Shopify API to the local database.
     */
    public function syncShopifyProducts()
    {
        $token = config('services.shopify.admin_access_token');
        $storeUrl = config('services.shopify.store_url');

        if (!$token || !$storeUrl) {
            Log::error('Shopify token or store URL is not configured for sync.');
            return; // Or throw an exception
        }

        // Find or create the store record to get its ID
        $store = Store::firstOrCreate(
            ['store_url' => $storeUrl, 'platform' => 'shopify'],
            ['name' => 'Default Shopify Store'] // You might want a better name
        );

        try {
            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $token,
            ])->get("https://{$storeUrl}/admin/api/2023-10/products.json", [
                'limit' => 250,
            ]);

            if ($response->failed()) {
                Log::error('Shopify API product sync failed.', $response->json());
                return;
            }

            $products = $response->json('products');

            foreach ($products as $productData) {
                Product::updateOrCreate(
                    [
                        'store_id' => $store->id,
                        'platform_product_id' => $productData['id'],
                    ],
                    [
                        'name' => $productData['title'],
                        'sku' => Arr::get($productData, 'variants.0.sku', 'N/A'),
                        'price' => Arr::get($productData, 'variants.0.price', 0.00),
                        'image_url' => Arr::get($productData, 'image.src'),
                    ]
                );
            }

            Log::info('Shopify products synchronized successfully.');

        } catch (\Exception $e) {
            Log::error('Shopify product sync error: ' . $e->getMessage());
        }
    }

    /**
     * Synchronize orders from Shopify API to the local database.
     */
    public function syncShopifyOrders()
    {
        Log::debug('Starting Shopify order synchronization.');
        $token = config('services.shopify.admin_access_token');
        $storeUrl = config('services.shopify.store_url');

        if (!$token || !$storeUrl) {
            Log::error('Shopify token or store URL is not configured for sync.');
            return;
        }

        $store = Store::firstOrCreate(
            ['store_url' => $storeUrl, 'platform' => 'shopify'],
            ['name' => 'Default Shopify Store']
        );

        $dateFrom = now()->subDays(30)->toIso8601String();
        Log::debug("Fetching Shopify orders from: {$dateFrom}");

        try {
            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $token,
            ])->get("https://{$storeUrl}/admin/api/2023-10/orders.json", [
                'status' => 'any',
                'created_at_min' => $dateFrom,
                'limit' => 250,
            ]);

            if ($response->failed()) {
                Log::error('Shopify API order sync failed.', $response->json());
                return;
            }

            $orders = $response->json('orders');
            Log::debug('Shopify API response for orders:', ['count' => count($orders), 'data' => $orders]);

            foreach ($orders as $orderData) {
                Log::debug("Processing Shopify order: {$orderData['id']} - {$orderData['financial_status']}");
                $order = Order::updateOrCreate(
                    [
                        'store_id' => $store->id,
                        'platform_order_id' => $orderData['id'],
                    ],
                    [
                        'customer_name' => (Arr::get($orderData, 'customer.first_name', '') . ' ' . Arr::get($orderData, 'customer.last_name', '')),
                        'order_date' => $orderData['created_at'],
                        'status' => $orderData['financial_status'],
                        'total_amount' => $orderData['total_price'],
                    ]
                );

                foreach ($orderData['line_items'] as $itemData) {
                    OrderItem::updateOrCreate(
                        [
                            'order_id' => $order->id,
                            'platform_product_id' => $itemData['id'], // Using line item id as unique id for the item within the order
                        ],
                        [
                            'product_name' => $itemData['name'],
                            'quantity' => $itemData['quantity'],
                            'price' => $itemData['price'],
                        ]
                    );
                }
            }

            Log::info('Shopify orders synchronized successfully.');

        } catch (\Exception $e) {
            Log::error('Shopify order sync error: ' . $e->getMessage());
        }
    }

    /**
     * Synchronize products from WooCommerce API to the local database.
     */
    public function syncWooCommerceProducts()
    {
        $storeUrl = config('services.woocommerce.store_url');
        $client = $this->getWooCommerceClient();

        if (!$client || !$storeUrl) {
            Log::error('WooCommerce client or store URL is not configured for sync.');
            return;
        }

        $store = Store::firstOrCreate(
            ['store_url' => $storeUrl, 'platform' => 'woocommerce'],
            ['name' => 'Default WooCommerce Store']
        );

        try {
            $products = $client->get('products');

            foreach ($products as $productData) {
                Product::updateOrCreate(
                    [
                        'store_id' => $store->id,
                        'platform_product_id' => $productData->id,
                    ],
                    [
                        'name' => $productData->name,
                        'sku' => $productData->sku ?? 'N/A',
                        'price' => $productData->price ?? 0.00,
                        'image_url' => $productData->images[0]->src ?? null,
                    ]
                );
            }

            Log::info('WooCommerce products synchronized successfully.');

        } catch (\Exception $e) {
            Log::error('WooCommerce product sync error: ' . $e->getMessage());
        }
    }

    /**
     * Synchronize orders from WooCommerce API to the local database.
     */
    public function syncWooCommerceOrders()
    {
        Log::debug('Starting WooCommerce order synchronization.');
        $storeUrl = config('services.woocommerce.store_url');
        $client = $this->getWooCommerceClient();

        if (!$client || !$storeUrl) {
            Log::error('WooCommerce client or store URL is not configured for sync.');
            return;
        }

        $store = Store::firstOrCreate(
            ['store_url' => $storeUrl, 'platform' => 'woocommerce'],
            ['name' => 'Default WooCommerce Store']
        );

        $dateFrom = now()->subDays(30)->toIso8601String();
        Log::debug("Fetching WooCommerce orders from: {$dateFrom}");

        try {
            $orders = $client->get('orders', ['after' => $dateFrom, 'per_page' => 100]);
            Log::debug('WooCommerce API response for orders:', ['count' => count($orders), 'data' => $orders]);

            foreach ($orders as $orderData) {
                Log::debug("Processing WooCommerce order: {$orderData->id} - {$orderData->status}");
                $order = Order::updateOrCreate(
                    [
                        'store_id' => $store->id,
                        'platform_order_id' => $orderData->id,
                    ],
                    [
                        'customer_name' => trim(($orderData->billing->first_name ?? '') . ' ' . ($orderData->billing->last_name ?? '')),
                        'order_date' => $orderData->date_created,
                        'status' => $orderData->status,
                        'total_amount' => $orderData->total,
                    ]
                );

                foreach ($orderData->line_items as $itemData) {
                    OrderItem::updateOrCreate(
                        [
                            'order_id' => $order->id,
                            'platform_product_id' => $itemData->id, // Using line item id as unique id
                        ],
                        [
                            'product_name' => $itemData->name,
                            'quantity' => $itemData->quantity,
                            'price' => $itemData->price,
                        ]
                    );
                }
            }

            Log::info('WooCommerce orders synchronized successfully.');

        } catch (\Exception $e) {
            Log::error('WooCommerce order sync error: ' . $e->getMessage());
        }
    }

    /**
     * Get a configured WooCommerce client instance.
     *
     * @return Client|null
     */
    private function getWooCommerceClient()
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
                'timeout' => 30,
            ]
        );
    }
}
