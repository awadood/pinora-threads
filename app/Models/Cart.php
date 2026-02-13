<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Holds a shopper’s in-progress items, pricing, and promotion context.
 *
 * @author Abdul Wadood
 */
class Cart extends AbstractModel
{
    protected $fillable = [
        'user_id',
        'cookie_key',
        'currency_code',
        'shipping_method_code',
        'expires_at',
        'checked_out_at',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }

    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShipmentMethod::class, 'shipping_method_code', 'code');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }
}
