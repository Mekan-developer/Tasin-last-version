<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'price',
        'currency',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Effective price: the variant's own price, or the parent product's
     * price when the variant has none of its own.
     */
    public function getEffectivePriceAttribute()
    {
        return $this->price ?? $this->product?->price;
    }

    /**
     * Currency that pairs with the effective price — falls back to the
     * parent product when the variant inherits the price.
     */
    public function getEffectiveCurrencyAttribute(): ?string
    {
        return $this->price !== null ? $this->currency : $this->product?->currency;
    }
}
