<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'price',
        'cost_price',
        'stock_quantity',
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

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class);
    }

    public function scopeWithAvailableStock(Builder $query): Builder
    {
        return $query->withSum([
            'orderItems as reserved_quantity' => fn (Builder $orderItems): Builder => $orderItems
                ->whereHas('order', fn (Builder $orders): Builder => $orders->whereIn('status', Order::STOCK_RESERVED_STATUSES)),
        ], 'quantity');
    }

    public function scopeWithinAbvRange(Builder $query, ?float $minimum, ?float $maximum): Builder
    {
        if ($minimum === null && $maximum === null) {
            return $query;
        }

        $abvValue = "CAST(REPLACE(abv, '%', '') AS DECIMAL(5, 2))";

        return $query
            ->whereNotNull('abv')
            ->whereRaw("TRIM(abv) != ''")
            ->when($minimum !== null, fn (Builder $products): Builder => $products->whereRaw("{$abvValue} >= ?", [$minimum]))
            ->when($maximum !== null, fn (Builder $products): Builder => $products->whereRaw("{$abvValue} <= ?", [$maximum]));
    }

    public function getAvailableStockAttribute(): int
    {
        return max(0, (int) $this->stock_quantity - (int) ($this->reserved_quantity ?? 0));
    }

    public function getGrossMarginPercentageAttribute(): ?float
    {
        $priceInCents = (int) round((float) $this->price * 100);

        if ($this->cost_price === null || $priceInCents <= 0) {
            return null;
        }

        $costInCents = (int) round((float) $this->cost_price * 100);

        return (($priceInCents - $costInCents) / $priceInCents) * 100;
    }

    public function getGrossProfitPerUnitCentsAttribute(): ?int
    {
        if ($this->cost_price === null) {
            return null;
        }

        return (int) round((float) $this->price * 100)
            - (int) round((float) $this->cost_price * 100);
    }
}
