<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class StoreController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/stores",
     *     summary="Get all stores",
     *     tags={"Stores"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Store")
     *         )
     *     )
     * )
     */
    public function index()
    {
        return Store::withTrashed()->get();
    }

    /**
     * @OA\Post(
     *     path="/api/stores",
     *     summary="Create a new store",
     *     tags={"Stores"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Store")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Store created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Store")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'platform' => ['required', 'in:shopify,woocommerce'],
            'store_url' => 'required|url',
            'name' => 'required|string|max:255',
            'api_key' => 'nullable|string|required_if:platform,woocommerce',
            'api_secret' => 'nullable|string|required_if:platform,woocommerce',
            'access_token' => 'nullable|string|required_if:platform,shopify',
        ]);

        return Store::create(array_merge($validated, ['user_id' => auth()->id()]));
    }

    /**
     * @OA\Get(
     *     path="/api/stores/{store}",
     *     summary="Get a specific store",
     *     tags={"Stores"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="store",
     *         in="path",
     *         required=true,
     *         description="ID of the store to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Store")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Store not found"
     *     )
     * )
     */
    public function show(Store $store)
    {
        return $store;
    }

    /**
     * @OA\Put(
     *     path="/api/stores/{store}",
     *     summary="Update a specific store",
     *     tags={"Stores"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="store",
     *         in="path",
     *         required=true,
     *         description="ID of the store to update",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Store")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Store updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Store")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Store not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, Store $store)
    {
        $validated = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'platform' => ['sometimes', 'in:shopify,woocommerce'],
            'store_url' => 'sometimes|url',
            'name' => 'sometimes|string|max:255',
            'api_key' => 'sometimes|nullable|string|required_if:platform,woocommerce',
            'api_secret' => 'sometimes|nullable|string|required_if:platform,woocommerce',
            'access_token' => 'sometimes|nullable|string|required_if:platform,shopify',
        ]);

        $store->update($request->all());

        return $store;
    }

    /**
     * @OA\Delete(
     *     path="/api/stores/{store}",
     *     summary="Delete a specific store",
     *     tags={"Stores"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="store",
     *         in="path",
     *         required=true,
     *         description="ID of the store to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Store deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Store not found"
     *     )
     * )
     */
    public function destroy(Store $store)
    {
        $store->delete();

        return response()->noContent();
    }
}

