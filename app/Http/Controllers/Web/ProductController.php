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
     * Synchronize products from all stores.
     *
     * @param SyncService $syncService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sync(SyncService $syncService)
    {
        $syncService->syncAllProducts();
        return redirect()->route('products.index')->with('success', 'Products are being synchronized.');
    }
    /**
     * Display a listing of the resource.
     *
     * @param SyncService $syncService
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Fetch all products from the local database
        $products = Product::with('store');

        // Apply filters
        if ($request->filled('name')) {
            $products->where('name', 'like', '%' . $request->input('name') . '%');
        }

        if ($request->filled('sku')) {
            $products->where('sku', 'like', '%' . $request->input('sku') . '%');
        }

        if ($request->filled('store_name')) {
            $storeName = $request->input('store_name');
            $products->whereHas('store', function ($query) use ($storeName) {
                $query->where('name', 'like', '%' . $storeName . '%');
            });
        }

        $products = $products->latest()->paginate(20)->withQueryString();

        return view('products.index', compact('products'));
    }

    /**
     * Export products to a CSV file.
     *
     * @param SyncService $syncService
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportCsv()
    {
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
                    $product->store->name, // Changed from platform to name
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
