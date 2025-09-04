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
        return Store::all();
    }

    /**
     * @OA\Post(
     *     path="/api/stores",
     *     summary="Create a new store",
     *     tags={"Stores"},
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
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'platform' => 'required|string',
            'store_url' => 'required|url',
            'api_key' => 'nullable|string',
            'api_secret' => 'nullable|string',
            'access_token' => 'nullable|string',
        ]);

        return Store::create($request->all());
    }

    /**
     * @OA\Get(
     *     path="/api/stores/{store}",
     *     summary="Get a specific store",
     *     tags={"Stores"},
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
        $request->validate([
            'user_id' => 'sometimes|required|exists:users,id',
            'platform' => 'sometimes|required|string',
            'store_url' => 'sometimes|required|url',
            'api_key' => 'nullable|string',
            'api_secret' => 'nullable|string',
            'access_token' => 'nullable|string',
        ]);

        $store->update($request->all());

        return $store;
    }

    /**
     * @OA\Delete(
     *     path="/api/stores/{store}",
     *     summary="Delete a specific store",
     *     tags={"Stores"},
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

