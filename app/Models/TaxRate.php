<?php

namespace App\Models;

/**
 * TaxRate Eloquent model.
 *
 * @author Abdul Wadood
 *
 * @property int $id
 * @property string $code e.g. US-CA-*-Rate 1, US-NY-*-Rate 1
 * @property string $amount
 * @property bool $percentage
 * @property bool $refundable
 * @property string $country_code
 * @property string|null $state_code it is null for PK
 * @property string $zipcode
 * @property bool|null $zip_is_range
 * @property string|null $zip_from
 * @property string|null $zip_to
 * @property bool $active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Database\Factories\TaxRateFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate whereCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate wherePercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate whereRefundable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate whereStateCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate whereZipFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate whereZipIsRange($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate whereZipTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate whereZipcode($value)
 *
 * @mixin \Eloquent
 */
class TaxRate extends AbstractModel
{
    protected $fillable = [
        'code',
        'amount',
        'percentage',
        'refundable',
        'country_code',
        'state_code',
        'zipcode',
        'zip_is_range',
        'zip_from',
        'zip_to',
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
            'percentage' => 'boolean',
            'refundable' => 'boolean',
            'zip_is_range' => 'boolean',
            'active' => 'boolean',
        ];
    }

    // Lifecycle

    // Relationships
}
