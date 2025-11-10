<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Captures customer testimonials for products or the storefront.
 *
 * @author Abdul Wadood
 */
class Testimonial extends AbstractLoggableModel
{
    protected $fillable = [
        'author_name',
        'content',
        'rating',
        'photo_url',
        'sort_order',
        'published_at',
        'status',
        'reviewed_by',
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

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
