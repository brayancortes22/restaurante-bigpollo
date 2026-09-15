<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductIngredient extends Pivot
{
    use HasFactory;

    protected $table = 'product_ingredients';

    protected $fillable = [
        'product_id',
        'ingredient_id',
        'quantity_required',
    ];

    protected $casts = [
        'quantity_required' => 'decimal:3',
    ];
}
