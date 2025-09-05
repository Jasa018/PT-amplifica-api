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
     * Synchronize products and orders for all active stores.
     */
    public function syncAllStores()
    {
        Log::info('Starting synchronization for all active stores.');
        $this->syncAllProducts();
        $this->syncAllOrders();
        Log::info('All active stores have been synchronized.');
    }

    /**
     * Synchronize products for all active stores.
     */
    public function syncAllProducts()
    {
        $stores = Store::all();
        if ($stores->isEmpty()) {
            Log::info('No active stores found to sync products from.');
            return;
        }

        foreach ($stores as $store) {
            if (empty($store->name) || empty($store->platform)) {
                Log::warning('Skipping a store for product sync due to missing name or platform.', ['store_id' => $store->id]);
                continue;
            }

            Log::info("Synchronizing products for store: {$store->name} ({$store->platform})");
            try {
                if ($store->platform === 'shopify') {
                    $this->syncShopifyProducts($store);
                } elseif ($store->platform === 'woocommerce') {
                    $client = $this->getWooCommerceClient($store);
                    if ($client) {
                        $this->syncWooCommerceProducts($store, $client);
                    }
                }
            } catch (\Exception $e) {
                Log::error("Failed to synchronize products for store {$store->name}: " . $e->getMessage(), ['store_id' => $store->id]);
            }
        }
    }

    /**
     * Synchronize orders for all active stores.
     */
    public function syncAllOrders()
    {
        $stores = Store::all();
        if ($stores->isEmpty()) {
            Log::info('No active stores found to sync orders from.');
            return;
        }

        foreach ($stores as $store) {
            if (empty($store->name) || empty($store->platform)) {
                Log::warning('Skipping a store for order sync due to missing name or platform.', ['store_id' => $store->id]);
                continue;
            }

            Log::info("Synchronizing orders for store: {$store->name} ({$store->platform})");
            try {
                if ($store->platform === 'shopify') {
                    $this->syncShopifyOrders($store);
                } elseif ($store->platform === 'woocommerce') {
                    $client = $this->getWooCommerceClient($store);
                    if ($client) {
                        $this->syncWooCommerceOrders($store, $client);
                    }
                }
            } catch (\Exception $e) {
                Log::error("Failed to synchronize orders for store {$store->name}: " . $e->getMessage(), ['store_id' => $store->id]);
            }
        }
    }

    /**
     * Synchronize products and orders for a specific Shopify store.
     *
     * @param Store $store
     */
    public function syncShopifyStore(Store $store)
    {
        $this->syncShopifyProducts($store);
        $this->syncShopifyOrders($store);
    }

    /**
     * Synchronize products and orders for a specific WooCommerce store.
     *
     * @param Store $store
     */
    public function syncWooCommerceStore(Store $store)
    {
        $client = $this->getWooCommerceClient($store);
        if (!$client) {
            Log::error("Could not initialize WooCommerce client for store: {$store->name}", ['store_id' => $store->id]);
            return;
        }
        $this->syncWooCommerceProducts($store, $client);
        $this->syncWooCommerceOrders($store, $client);
    }

    /**
     * Synchronize products from Shopify API to the local database for a given store.
     * @param Store $store
     */
    public function syncShopifyProducts(Store $store)
    {
        try {
            $host = preg_replace('#^https?://#', '', $store->store_url);
            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $store->access_token,
            ])->get("https://{$host}/admin/api/2023-10/products.json", [
                'limit' => 250,
            ]);

            if ($response->failed()) {
                Log::error("Shopify API product sync failed for store {$store->name}.", array_merge($response->json(), ['store_id' => $store->id]));
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

            Log::info("Shopify products synchronized successfully for store: {$store->name}");

        } catch (\Exception $e) {
            Log::error("Shopify product sync error for store {$store->name}: " . $e->getMessage(), ['store_id' => $store->id]);
        }
    }

    /**
     * Synchronize orders from Shopify API to the local database for a given store.
     * @param Store $store
     */
    public function syncShopifyOrders(Store $store)
    {
        $dateFrom = now()->subDays(30)->toIso8601String();
        Log::debug("Fetching Shopify orders for store {$store->name} from: {$dateFrom}");

        try {
            $host = preg_replace('#^https?://#', '', $store->store_url);
            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $store->access_token,
            ])->get("https://{$host}/admin/api/2023-10/orders.json", [
                'status' => 'any',
                'created_at_min' => $dateFrom,
                'limit' => 250,
            ]);

            if ($response->failed()) {
                Log::error("Shopify API order sync failed for store {$store->name}.", array_merge($response->json(), ['store_id' => $store->id]));
                return;
            }

            $orders = $response->json('orders');

            foreach ($orders as $orderData) {
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
                            'platform_product_id' => $itemData['id'],
                        ],
                        [
                            'product_name' => $itemData['name'],
                            'quantity' => $itemData['quantity'],
                            'price' => $itemData['price'],
                        ]
                    );
                }
            }

            Log::info("Shopify orders synchronized successfully for store: {$store->name}");

        } catch (\Exception $e) {
            Log::error("Shopify order sync error for store {$store->name}: " . $e->getMessage(), ['store_id' => $store->id]);
        }
    }

    /**
     * Synchronize products from WooCommerce API to the local database for a given store.
     * @param Store $store
     * @param Client $client
     */
    public function syncWooCommerceProducts(Store $store, Client $client)
    {
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

            Log::info("WooCommerce products synchronized successfully for store: {$store->name}");

        } catch (\Exception $e) {
            Log::error("WooCommerce product sync error for store {$store->name}: " . $e->getMessage(), ['store_id' => $store->id]);
        }
    }

    /**
     * Synchronize orders from WooCommerce API to the local database for a given store.
     * @param Store $store
     * @param Client $client
     */
    public function syncWooCommerceOrders(Store $store, Client $client)
    {
        $dateFrom = now()->subDays(30)->toIso8601String();
        Log::debug("Fetching WooCommerce orders for store {$store->name} from: {$dateFrom}");

        try {
            $orders = $client->get('orders', ['after' => $dateFrom, 'per_page' => 100]);

            foreach ($orders as $orderData) {
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
                            'platform_product_id' => $itemData->id,
                        ],
                        [
                            'product_name' => $itemData->name,
                            'quantity' => $itemData->quantity,
                            'price' => $itemData->price,
                        ]
                    );
                }
            }

            Log::info("WooCommerce orders synchronized successfully for store: {$store->name}");

        } catch (\Exception $e) {
            Log::error("WooCommerce order sync error for store {$store->name}: " . $e->getMessage(), ['store_id' => $store->id]);
        }
    }

    /**
     * Get a configured WooCommerce client instance for a given store.
     *
     * @param Store $store
     * @return Client|null
     */
    private function getWooCommerceClient(Store $store)
    {
        if (!$store->store_url || !$store->api_key || !$store->api_secret) {
            Log::error("WooCommerce credentials missing for store: {$store->name}", ['store_id' => $store->id]);
            return null;
        }

        return new Client(
            $store->store_url,
            $store->api_key,
            $store->api_secret,
            [
                'version' => 'wc/v3',
                'timeout' => 30,
            ]
        );
    }
}