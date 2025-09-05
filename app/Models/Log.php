<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="Log",
 *     description="Log model",
 *     @OA\Xml(name="Log"),
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="Log ID"
 *     ),
 *     @OA\Property(
 *         property="store_id",
 *         type="integer",
 *         format="int64",
 *         nullable=true,
 *         description="ID of the associated store (if applicable)"
 *     ),
 *     @OA\Property(
 *         property="level",
 *         type="string",
 *         enum={"info", "warning", "error"},
 *         description="Log level"
 *     ),
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         description="Log message"
 *     ),
 *     @OA\Property(
 *         property="context",
 *         type="object",
 *         nullable=true,
 *         description="Additional context data (JSON string)"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp of log creation"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp of last update"
 *     )
 * )
 */
class Log extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'level',
        'message',
        'context',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
