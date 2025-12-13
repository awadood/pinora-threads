<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents an invoice lifecycle state (e.g., draft, issued, paid, overdue).
 *
 * @author Abdul Wadood
 */
class InvoiceStatus extends AbstractLoggableModel
{
    const ISSUED = 'issued';

    const VOIDED = 'voided';

    const PAID = 'paid';

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

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
