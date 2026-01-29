<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A merchandising section for storefront.
 *
 * @author Abdul Wadood
 */
class MerchSection extends AbstractLoggableModel
{
    protected $fillable = [
        'code',
        'name',
        'surface',
        'item_type',
        'mode',
        'default_limit',
        'country_code',
        'starts_at',
        'ends_at',
        'sort',
        'active',
        'query_payload',
        'meta',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'active' => 'boolean',
            'query_payload' => 'array',
            'meta' => 'array',
        ];
    }

    // Lifecycle

    // Relationships

    public function items(): HasMany
    {
        return $this->hasMany(MerchSectionItem::class)->orderBy('position');
    }

    // Scopes

    /**
     * Scheduling semantics (LOCKED)
     * - starts_at null => active immediately
     * - ends_at null   => active indefinitely
     * - outside window => hidden (404 at storefront layer)
     */
    public function scopeWithinSchedule(Builder $q): Builder
    {
        return $q->where(function (Builder $w) {
            $w->whereNull('starts_at')->orWhere('starts_at', '<=', now());
        })->where(function (Builder $w) {
            $w->whereNull('ends_at')->orWhere('ends_at', '>=', now());
        });
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('active', true);
    }
}
