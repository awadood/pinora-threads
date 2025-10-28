<?php

namespace App\Models;

/**
 * Class PromotionStatus
 *
 * @author Abdul Wadood
 */
class PromotionStatus extends AbstractModel
{
    public const SCHEDULED = 'SCHEDULED';

    public const ONGOING = 'ONGOING';

    public const COMPLETED = 'COMPLETED';

    public const STOPPED = 'STOPPED';

    protected $fillable = [
        'code',
        'name',
    ];

    protected $primaryKey = 'code';

    protected $keyType = 'string';

    public $timestamps = false;
}
