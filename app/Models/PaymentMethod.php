<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents an available payment method (e.g., cash, card, stripe, cod).
 *
 * @author Abdul Wadood
 */
class PaymentMethod extends AbstractModel
{
    const STRIPE = 'stripe';

    const PAYPAL = 'paypal';

    const PAYFAST = 'payfast';

    const COD = 'cod';

    const EASYPAISA = 'easypaisa';

    const JAZZCASH = 'jazzcash';

    protected $fillable = [
        'code',
        'name',
        'sort_order',
        'active',
    ];

    protected $primaryKey = 'code';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = true;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'active' => 'boolean',
        ];
    }

    // Lifecycle

    // Relationships

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
