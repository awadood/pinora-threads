<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Discount
 *
 * @author Abdul Wadood
 */
class Discount extends AbstractModel
{
    protected $fillable = [
        'name',
        'amount',
        'active',
        'discount_type_code',
        'customer_group_id',
        'client_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'float',
        ];
    }

    // Relationships

    public function customerGroup(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class);
    }

    public function discountType(): BelongsTo
    {
        return $this->belongsTo(DiscountType::class);
    }
}
