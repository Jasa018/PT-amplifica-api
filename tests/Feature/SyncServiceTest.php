<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Store;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Log as LogModel;
use App\Services\SyncService;
use Illuminate\Support\Facades\Http;
use Automattic\WooCommerce\Client;
use Mockery;

class SyncServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure the logs table is clean for each test
        LogModel::truncate();
    }

    /** @test */
    public function a_product_can_be_created_in_test_database()
    {
        $store = Store::factory()->create();
        Product::create([
            'store_id' => $store->id,
            'platform_product_id' => 'test_product_id',
            'name' => 'Test Product',
            'sku' => 'TESTSKU',
            'price' => 99.99,
        ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'store_id' => $store->id,
        ]);
    }

    /** @test */
    public function it_persists_data_with_real_db_calls_for_shopify_products()
    {
        // Create a Shopify store with real-like data (replace with your actual test store details)
        $shopifyStore = Store::factory()->create([
            'platform' => 'shopify',
            'name' => 'Real Test Shopify Store',
            'store_url' => 'your-test-store.myshopify.com', // REPLACE WITH A REAL TEST SHOPIFY STORE URL
            'access_token' => 'your_real_shopify_access_token', // REPLACE WITH A REAL SHOPIFY ACCESS TOKEN
        ]);

        $syncService = $this->app->make(SyncService::class);
        $syncService->syncShopifyProducts($shopifyStore);

        // Assert that products are saved (you'll need to know what products to expect from your real test store)
        $this->assertDatabaseHas('products', [
            'store_id' => $shopifyStore->id,
            // Add assertions for a product you expect to be synced from your real test store
            // e.g., 'platform_product_id' => 'expected_id',
            // 'name' => 'Expected Product Name',
        ]);
    }

    /** @test */
    public function it_synchronizes_shopify_products_and_orders_correctly()
    {
        // Create a Shopify store
        $shopifyStore = Store::factory()->create([
            'platform' => 'shopify',
            'name' => 'Test Shopify Store',
            'store_url' => 'test-shopify.myshopify.com',
            'access_token' => 'shpat_test_token',
        ]);

        // Mock Shopify product and order responses
        Http::fake([
            '*test-shopify.myshopify.com/admin/api/2023-10/products.json*' => Http::response([
                'products' => [
                    [
                        'id' => 1001,
                        'title' => 'Shopify Product 1',
                        'variants' => [['sku' => 'SP1', 'price' => '10.00']],
                        'image' => ['src' => 'http://example.com/sp1.jpg'],
                    ],
                ],
            ], 200),
            '*test-shopify.myshopify.com/admin/api/2023-10/orders.json*' => Http::response([
                'orders' => [
                    [
                        'id' => 2001,
                        'created_at' => '2025-01-01T10:00:00Z',
                        'financial_status' => 'paid',
                        'total_price' => '25.00',
                        'customer' => ['first_name' => 'John', 'last_name' => 'Doe'],
                        'line_items' => [
                            ['id' => 3001, 'name' => 'Shopify Item 1', 'quantity' => 1, 'price' => '15.00'],
                            ['id' => 3002, 'name' => 'Shopify Item 2', 'quantity' => 1, 'price' => '10.00'],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $syncService = $this->app->make(SyncService::class);
        $syncService->syncAllStores();

        // Assert products are saved
        $this->assertDatabaseHas('products', [
            'store_id' => $shopifyStore->id,
            'platform_product_id' => 1001,
            'name' => 'Shopify Product 1',
            'sku' => 'SP1',
            'price' => 10.00,
        ]);

        // Assert orders are saved
        $this->assertDatabaseHas('orders', [
            'store_id' => $shopifyStore->id,
            'platform_order_id' => 2001,
            'customer_name' => 'John Doe',
            'status' => 'paid',
            'total_amount' => 25.00,
        ]);

        // Assert order items are saved
        $order = Order::where('platform_order_id', 2001)->first();
        $this->assertNotNull($order);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'platform_product_id' => 3001,
            'product_name' => 'Shopify Item 1',
            'quantity' => 1,
            'price' => 15.00,
        ]);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'platform_product_id' => 3002,
            'product_name' => 'Shopify Item 2',
            'quantity' => 1,
            'price' => 10.00,
        ]);

        // Assert no errors logged for successful sync
        $this->assertDatabaseMissing('logs', [
            'level' => 'error',
            'store_id' => $shopifyStore->id,
        ]);
    }

    /** @test */
    public function it_synchronizes_woocommerce_products_and_orders_correctly()
    {
        // Create a WooCommerce store
        $woocommerceStore = Store::factory()->create([
            'platform' => 'woocommerce',
            'name' => 'Test WooCommerce Store',
            'store_url' => 'http://test-woo.com',
            'api_key' => 'ck_test',
            'api_secret' => 'cs_test',
        ]);

        // Mock WooCommerce client
        $mockWooCommerceClient = Mockery::mock(Client::class);
        $this->app->instance(Client::class, $mockWooCommerceClient);

        $mockWooCommerceClient->shouldReceive('get')
            ->with('products')
            ->andReturn([ // Products response
                (object)[
                    'id' => 1002,
                    'name' => 'WooCommerce Product 1',
                    'sku' => 'WP1',
                    'price' => '20.00',
                    'images' => [(object)['src' => 'http://example.com/wp1.jpg']],
                ],
            ]);

        $mockWooCommerceClient->shouldReceive('get')
            ->with('orders', Mockery::any())
            ->andReturn([ // Orders response
                (object)[
                    'id' => 2002,
                    'date_created' => '2025-01-02T11:00:00',
                    'status' => 'processing',
                    'total' => '50.00',
                    'billing' => (object)['first_name' => 'Jane', 'last_name' => 'Doe'],
                    'line_items' => [
                        (object)['id' => 3003, 'name' => 'WooCommerce Item 1', 'quantity' => 2, 'price' => '15.00'],
                        (object)['id' => 3004, 'name' => 'WooCommerce Item 2', 'quantity' => 1, 'price' => '20.00'],
                    ],
                ],
            ]);

        $syncService = $this->app->make(SyncService::class);
        $syncService->syncAllStores();

        // Assert products are saved
        $this->assertDatabaseHas('products', [
            'store_id' => $woocommerceStore->id,
            'platform_product_id' => 1002,
            'name' => 'WooCommerce Product 1',
            'sku' => 'WP1',
            'price' => 20.00,
        ]);

        // Assert orders are saved
        $this->assertDatabaseHas('orders', [
            'store_id' => $woocommerceStore->id,
            'platform_order_id' => 2002,
            'customer_name' => 'Jane Doe',
            'status' => 'processing',
            'total_amount' => 50.00,
        ]);

        // Assert order items are saved
        $order = Order::where('platform_order_id', 2002)->first();
        $this->assertNotNull($order);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'platform_product_id' => 3003,
            'product_name' => 'WooCommerce Item 1',
            'quantity' => 2,
            'price' => 15.00,
        ]);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'platform_product_id' => 3004,
            'product_name' => 'WooCommerce Item 2',
            'quantity' => 1,
            'price' => 20.00,
        ]);

        // Assert no errors logged for successful sync
        $this->assertDatabaseMissing('logs', [
            'level' => 'error',
            'store_id' => $woocommerceStore->id,
        ]);
    }

    /** @test */
    public function it_logs_errors_when_shopify_sync_fails()
    {
        $shopifyStore = Store::factory()->create([
            'platform' => 'shopify',
            'name' => 'Failing Shopify Store',
            'store_url' => 'failing-shopify.myshopify.com',
            'access_token' => 'invalid_token',
        ]);

        Http::fake([
            '*failing-shopify.myshopify.com/admin/api/2023-10/products.json*' => Http::response([], 500),
            '*failing-shopify.myshopify.com/admin/api/2023-10/orders.json*' => Http::response([], 500),
        ]);

        $syncService = $this->app->make(SyncService::class);
        $syncService->syncAllStores();

        $this->assertDatabaseHas('logs', [
            'level' => 'error',
            'store_id' => $shopifyStore->id,
            'message' => 'Shopify API product sync failed for store Failing Shopify Store.',
        ]);

        $this->assertDatabaseHas('logs', [
            'level' => 'error',
            'store_id' => $shopifyStore->id,
            'message' => 'Shopify API order sync failed for store Failing Shopify Store.',
        ]);
    }

    /** @test */
    public function it_logs_errors_when_woocommerce_sync_fails()
    {
        $woocommerceStore = Store::factory()->create([
            'platform' => 'woocommerce',
            'name' => 'Failing WooCommerce Store',
            'store_url' => 'http://failing-woo.com',
            'api_key' => 'invalid_key',
            'api_secret' => 'invalid_secret',
        ]);

        $mockWooCommerceClient = Mockery::mock(Client::class);
        $this->app->instance(Client::class, $mockWooCommerceClient);

        $mockWooCommerceClient->shouldReceive('get')
            ->with('products')
            ->andThrow(new \Exception('cURL Error: Could not resolve host: failing-woo.com'));

        $mockWooCommerceClient->shouldReceive('get')
            ->with('orders', Mockery::any())
            ->andThrow(new \Exception('cURL Error: Could not resolve host: failing-woo.com'));

        $syncService = $this->app->make(SyncService::class);
        $syncService->syncAllStores();

        $this->assertDatabaseHas('logs', [
            'level' => 'error',
            'store_id' => $woocommerceStore->id,
            'message' => 'WooCommerce product sync error for store Failing WooCommerce Store: cURL Error: Could not resolve host: failing-woo.com',
        ]);

        $this->assertDatabaseHas('logs', [
            'level' => 'error',
            'store_id' => $woocommerceStore->id,
            'message' => 'WooCommerce order sync error for store Failing WooCommerce Store: cURL Error: Could not resolve host: failing-woo.com',
        ]);
    }

    /** @test */
    public function it_skips_stores_with_missing_credentials_and_logs_error()
    {
        $woocommerceStore = Store::factory()->create([
            'platform' => 'woocommerce',
            'name' => 'Incomplete WooCommerce Store',
            'store_url' => 'http://incomplete-woo.com',
            'api_key' => null, // Missing API key
            'api_secret' => null,
        ]);

        $syncService = $this->app->make(SyncService::class);
        $syncService->syncAllStores();

        $this->assertDatabaseHas('logs', [
            'level' => 'error',
            'store_id' => $woocommerceStore->id,
            'message' => 'WooCommerce credentials missing for store: Incomplete WooCommerce Store',
        ]);

        // Assert that no products or orders were attempted to be synced for this store
        $this->assertDatabaseMissing('products', ['store_id' => $woocommerceStore->id]);
        $this->assertDatabaseMissing('orders', ['store_id' => $woocommerceStore->id]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
