<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="OrderItem",
 *     description="Order Item model",
 *     @OA\Xml(name="OrderItem"),
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="Order Item ID"
 *     ),
 *     @OA\Property(
 *         property="order_id",
 *         type="integer",
 *         format="int64",
 *         description="ID of the associated order"
 *     ),
 *     @OA\Property(
 *         property="platform_product_id",
 *         type="string",
 *         description="Product ID from the e-commerce platform"
 *     ),
 *     @OA\Property(
 *         property="product_name",
 *         type="string",
 *         description="Name of the product"
 *     ),
 *     @OA\Property(
 *         property="quantity",
 *         type="integer",
 *         description="Quantity of the product"
 *     ),
 *     @OA\Property(
 *         property="price",
 *         type="number",
 *         format="float",
 *         description="Price of the product"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp of order item creation"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp of last update"
 *     )
 * )
 */
class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'platform_product_id',
        'product_name',
        'quantity',
        'price',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
