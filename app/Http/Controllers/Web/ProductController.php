<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\SyncService;
use Illuminate\Http\Request;

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
}
