<?php

namespace App\Models;

/**
 * Class DiscountType
 *
 * @author Abdul Wadood
 */
class DiscountType extends AbstractModel
{
    public const FIXED = 'FIXED';

    public const NEW_PRICE = 'NEW_PRICE';

    public const PERCENTAGE = 'PERCENTAGE';

    protected $fillable = [
        'code',
        'name',
    ];

    protected $primaryKey = 'code';

    protected $keyType = 'string';

    public $timestamps = false;

    // Relationships
}
