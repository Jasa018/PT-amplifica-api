<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $stores = Store::where('user_id', Auth::id())->get(); // Only show stores for the logged-in user
        return view('stores.index', compact('stores'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('stores.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'platform' => ['required', Rule::in(['shopify', 'woocommerce'])],
            'store_url' => 'required|url',
            'name' => 'required|string|max:255',
            'api_key' => 'required|string',
            'api_secret' => 'required|string',
            'access_token' => 'required_if:platform,shopify|nullable|string',
        ]);

        $store = new Store($request->all());
        $store->user_id = Auth::id(); // Associate with current user
        $store->save();

        return redirect()->route('stores.index')->with('success', 'Store added successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Store  $store
     * @return \Illuminate\Http\Response
     */
    public function edit(Store $store)
    {
        // Ensure user can only edit their own stores
        if ($store->user_id !== Auth::id()) {
            abort(403);
        }
        return view('stores.edit', compact('store'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Store  $store
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Store $store)
    {
        // Ensure user can only update their own stores
        if ($store->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'platform' => ['required', Rule::in(['shopify', 'woocommerce'])],
            'store_url' => 'required|url',
            'name' => 'required|string|max:255',
            'api_key' => 'required|string',
            'api_secret' => 'required|string',
            'access_token' => 'required_if:platform,shopify|nullable|string',
        ]);

        $store->update($request->all());

        return redirect()->route('stores.index')->with('success', 'Store updated successfully!');
    }

    /**
     * Remove the specified resource from storage (soft delete).
     *
     * @param  \App\Models\Store  $store
     * @return \Illuminate\Http\Response
     */
    public function destroy(Store $store)
    {
        // Ensure user can only delete their own stores
        if ($store->user_id !== Auth::id()) {
            abort(403);
        }

        $store->delete(); // This will set deleted_at timestamp and is_deleted to true

        return redirect()->route('stores.index')->with('success', 'Store deleted successfully!');
    }
}
