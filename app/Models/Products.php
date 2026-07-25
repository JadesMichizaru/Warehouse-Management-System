<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'sku',
        'name',
        'origin',
        'category',
        'brand',
        'gross_weight',
        'weight_unit',
        'min_stock',
        'stock',
        'is_active'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
