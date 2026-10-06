<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'price',
        'volume',
        'abv',
        'image',
        'is_best_seller',
        'is_new_arrival',
        'is_hot_deal',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
