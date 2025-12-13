<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Base price list entry for a product (currency, amount, rules).
 *
 * @author Abdul Wadood
 */
class ProductPrice extends AbstractLoggableModel
{
    protected $fillable = [
        'product_id',
        'currency_code',
        'amount',
        'compare_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [];
    }

    // Lifecycle

    // Relationships

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }
}
