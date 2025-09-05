<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="Store",
 *     description="Store model",
 *     @OA\Xml(name="Store"),
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="Store ID"
 *     ),
 *     @OA\Property(
 *         property="user_id",
 *         type="integer",
 *         format="int64",
 *         description="ID of the associated user"
 *     ),
 *     @OA\Property(
 *         property="platform",
 *         type="string",
 *         description="E-commerce platform (e.g., Shopify, WooCommerce)"
 *     ),
 *     @OA\Property(
 *         property="store_url",
 *         type="string",
 *         format="url",
 *         description="URL of the store"
 *     ),
 *     @OA\Property(
 *         property="api_key",
 *         type="string",
 *         description="API Key for the store (sensitive, handle with care)"
 *     ),
 *     @OA\Property(
 *         property="api_secret",
 *         type="string",
 *         description="API Secret for the store (sensitive, handle with care)"
 *     ),
 *     @OA\Property(
 *         property="access_token",
 *         type="string",
 *         description="Access Token for the store (sensitive, handle with care)"
 *     ),
 *     @OA\Property(
 *         property="is_deleted",
 *         type="boolean",
 *         description="Flag indicating if the store is soft deleted"
 *     ),
 *     @OA\Property(
 *         property="deleted_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp of soft deletion"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp of store creation"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp of last update"
 *     )
 * )
 */
class Store extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'platform',
        'store_url',
        'name',
        'api_key',
        'api_secret',
        'access_token',
    ];

    protected $dates = ['deleted_at'];

    public function delete()
    {
        $this->is_deleted = true;
        $this->save();

        return parent::delete();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}