<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents an order lifecycle state (e.g., pending, confirmed, fulfilled, cancelled).
 *
 * @author Abdul Wadood
 */
class OrderStatus extends AbstractModel
{
    const PENDING = 'pending';

    const PAID = 'paid';

    const PICKING = 'picking';

    const SHIPPED = 'shipped';

    const DELIVERED = 'delivered';

    const CLOSED = 'closed';

    const CANCELLED = 'cancelled';

    const REFUNDED = 'refunded';

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

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
