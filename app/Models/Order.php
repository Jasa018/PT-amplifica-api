<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="Order",
 *     description="Order model",
 *     @OA\Xml(name="Order"),
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="Order ID"
 *     ),
 *     @OA\Property(
 *         property="store_id",
 *         type="integer",
 *         format="int64",
 *         description="ID of the associated store"
 *     ),
 *     @OA\Property(
 *         property="platform_order_id",
 *         type="string",
 *         description="Order ID from the e-commerce platform"
 *     ),
 *     @OA\Property(
 *         property="customer_name",
 *         type="string",
 *         description="Name of the customer"
 *     ),
 *     @OA\Property(
 *         property="order_date",
 *         type="string",
 *         format="date",
 *         description="Date of the order"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         description="Status of the order (e.g., pending, completed)"
 *     ),
 *     @OA\Property(
 *         property="total_amount",
 *         type="number",
 *         format="float",
 *         description="Total amount of the order"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp of order creation"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp of last update"
 *     ),
 *     @OA\Property(
 *         property="items",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/OrderItem"),
 *         description="List of order items"
 *     )
 * )
 */
class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'platform_order_id',
        'customer_name',
        'order_date',
        'status',
        'total_amount',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
