<?php

namespace App\Models;

/**
 * Class PaymentStatus
 *
 * @author Abdul Wadood
 */
class PaymentStatus extends AbstractModel
{
    public const PAID_CASH = 'PAID_CASH';

    public const PAID_CREDIT_CARD = 'PAID_CREDIT_CARD';

    public const PAID_DEBIT_CARD = 'PAID_DEBIT_CARD';

    public const PAID_MIXED = 'PAID_MIXED';

    public const UNPAID = 'UNPAID';

    public const REFUNDED = 'REFUNDED';

    protected $fillable = [
        'code',
        'name',
    ];

    protected $primaryKey = 'code';

    protected $keyType = 'string';

    public $timestamps = false;

    // Relationships
}
