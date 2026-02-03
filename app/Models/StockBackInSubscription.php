<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Captures a user’s subscription to back-in-stock notifications.
 *
 * @author Abdul Wadood
 */
class StockBackInSubscription extends AbstractLoggableModel
{
    protected $fillable = [
        'product_id',
        'user_id',
        'email',
        'notified_at',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
