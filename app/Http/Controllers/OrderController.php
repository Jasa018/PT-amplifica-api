<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Order::with('items')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'platform_order_id' => 'required|string',
            'customer_name' => 'required|string',
            'order_date' => 'required|date',
            'status' => 'required|string',
            'total_amount' => 'required|numeric',
            'items' => 'required|array',
            'items.*.platform_product_id' => 'required|string',
            'items.*.product_name' => 'required|string',
            'items.*.quantity' => 'required|integer',
            'items.*.price' => 'required|numeric',
        ]);

        $order = Order::create($request->except('items'));

        $order->items()->createMany($request->items);

        return $order->load('items');
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        return $order->load('items');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        $request->validate([
            'store_id' => 'sometimes|required|exists:stores,id',
            'platform_order_id' => 'sometimes|required|string',
            'customer_name' => 'sometimes|required|string',
            'order_date' => 'sometimes|required|date',
            'status' => 'sometimes|required|string',
            'total_amount' => 'sometimes|required|numeric',
        ]);

        $order->update($request->all());

        return $order->load('items');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        $order->delete();

        return response()->noContent();
    }
}
