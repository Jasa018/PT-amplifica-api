<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="Product",
 *     description="Product model",
 *     @OA\Xml(name="Product"),
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="Product ID"
 *     ),
 *     @OA\Property(
 *         property="store_id",
 *         type="integer",
 *         format="int64",
 *         description="ID of the associated store"
 *     ),
 *     @OA\Property(
 *         property="platform_product_id",
 *         type="string",
 *         description="Product ID from the e-commerce platform"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Name of the product"
 *     ),
 *     @OA\Property(
 *         property="sku",
 *         type="string",
 *         description="Stock Keeping Unit"
 *     ),
 *     @OA\Property(
 *         property="price",
 *         type="number",
 *         format="float",
 *         description="Price of the product"
 *     ),
 *     @OA\Property(
 *         property="image_url",
 *         type="string",
 *         format="url",
 *         description="URL of the product image"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp of product creation"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp of last update"
 *     )
 * )
 */
class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'platform_product_id',
        'name',
        'sku',
        'price',
        'image_url',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
