<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
