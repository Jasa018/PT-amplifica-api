<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Store::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'platform' => 'required|in:shopify,woocommerce',
            'store_url' => 'required|url',
            'api_key' => 'nullable|string',
            'api_secret' => 'nullable|string',
            'access_token' => 'nullable|string',
        ]);

        return Store::create($request->all());
    }

    /**
     * Display the specified resource.
     */
    public function show(Store $store)
    {
        return $store;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Store $store)
    {
        $request->validate([
            'user_id' => 'sometimes|required|exists:users,id',
            'platform' => 'sometimes|required|in:shopify,woocommerce',
            'store_url' => 'sometimes|required|url',
            'api_key' => 'nullable|string',
            'api_secret' => 'nullable|string',
            'access_token' => 'nullable|string',
        ]);

        $store->update($request->all());

        return $store;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Store $store)
    {
        $store->delete();

        return response()->noContent();
    }
}
