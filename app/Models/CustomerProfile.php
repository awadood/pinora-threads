<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CustomerProfile Eloquent model.
 *
 * @author Abdul Wadood
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
