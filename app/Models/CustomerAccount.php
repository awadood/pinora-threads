<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Extends a user with commerce preferences, marketing consents, and defaults.
 *
 * @author Abdul Wadood
 */
class CustomerAccount extends AbstractLoggableModel
{
    protected $table = 'customer_accounts';

    protected $fillable = [
        'user_id',
        'marketing_email_opt_in',
        'marketing_email_consented_at',
        'marketing_email_revoked_at',
        'marketing_email_consent_ip',
        'marketing_email_consent_source',
        'marketing_sms_opt_in',
        'marketing_sms_consented_at',
        'marketing_sms_revoked_at',
        'marketing_sms_consent_ip',
        'marketing_sms_consent_source',
        'preferred_currency',
        'default_shipping_address_id',
        'default_billing_address_id',
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
            'marketing_email_consented_at' => 'datetime',
            'marketing_email_revoked_at' => 'datetime',
            'marketing_sms_consented_at' => 'datetime',
            'marketing_sms_revoked_at' => 'datetime',
            'default_shipping_address_id' => 'integer',
            'default_billing_address_id' => 'integer',
        ];
    }

    // Lifecycle

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function defaultShippingAddress(): BelongsTo
    {
        return $this->belongsTo(CustomerAddress::class, 'default_shipping_address_id');
    }

    public function defaultBillingAddress(): BelongsTo
    {
        return $this->belongsTo(CustomerAddress::class, 'default_billing_address_id');
    }
}
