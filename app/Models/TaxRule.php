<?php

namespace App\Models;

/**
 * TaxRule Eloquent model.
 *
 * @author Abdul Wadood
 *
 * @property int $id
 * @property string $code
 * @property int $priority
 * @property int $position
 * @property bool $calculate_subtotal
 * @property bool $applies_to_shipping
 * @property bool $active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Database\Factories\TaxRuleFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRule query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRule whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRule whereAppliesToShipping($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRule whereCalculateSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRule whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRule wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRule wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRule whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class TaxRule extends AbstractModel
{
    protected $fillable = [
        'code',
        'priority',
        'position',
        'calculate_subtotal',
        'applies_to_shipping',
        'active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'calculate_subtotal' => 'boolean',
            'applies_to_shipping' => 'boolean',
            'active' => 'boolean',
        ];
    }

    // Lifecycle

    // Relationships
}
