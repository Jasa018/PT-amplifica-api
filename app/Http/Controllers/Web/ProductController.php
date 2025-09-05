<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\SyncService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param SyncService $syncService
     * @return \Illuminate\Http\Response
     */
    public function index(SyncService $syncService)
    {
        // Sync products from all platforms
        $syncService->syncShopifyProducts();
        $syncService->syncWooCommerceProducts();

        // Fetch all products from the local database
        $products = Product::with('store')->latest()->paginate(20);

        return view('products.index', compact('products'));
    }

    /**
     * Export products to a CSV file.
     *
     * @param SyncService $syncService
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportCsv(SyncService $syncService)
    {
        // Ensure data is up-to-date before exporting
        $syncService->syncShopifyProducts();
        $syncService->syncWooCommerceProducts();

        $products = Product::with('store')->latest()->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="products.csv"',
        ];

        $callback = function () use ($products) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Store', 'Platform Product ID', 'Name', 'SKU', 'Price', 'Image URL']);
            foreach ($products as $product) {
                fputcsv($file, [
                    $product->id,
                    $product->store->platform,
                    $product->platform_product_id,
                    $product->name,
                    $product->sku,
                    $product->price,
                    $product->image_url,
                ]);
            }
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}