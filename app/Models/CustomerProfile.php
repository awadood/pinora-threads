<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CustomerProfile Eloquent model.
 *
 * @author Abdul Wadood
 *
 * @property int $id
 * @property int $user_id
 * @property int $tax_class_id
 * @property bool $marketing_email_opt_in Stay compliant (CAN-SPAM/CPRA best practices): only email people who consents.
 * @property bool $marketing_sms_opt_in
 * @property string $preferred_currency
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\TaxClass $taxClass
 * @property-read \App\Models\User $user
 *
 * @method static \Database\Factories\CustomerProfileFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfile whereMarketingEmailOptIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfile whereMarketingSmsOptIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfile wherePreferredCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfile whereTaxClassId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerProfile whereUserId($value)
 *
 * @mixin \Eloquent
 */
class CustomerProfile extends AbstractLoggableModel
{
    protected $fillable = [
        'user_id',
        'tax_class_id',
        'marketing_email_opt_in',
        'marketing_sms_opt_in',
        'preferred_currency',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'marketing_email_opt_in' => 'boolean',
            'marketing_sms_opt_in' => 'boolean',
        ];
    }

    // Lifecycle

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function taxClass(): BelongsTo
    {
        return $this->belongsTo(TaxClass::class);
    }
}
