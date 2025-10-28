<?php

namespace App\Models;

/**
 * Class Attribute
 *
 * @author Abdul Wadood
 */
class Attribute extends AbstractModel
{
    protected $fillable = [
        'name',
    ];

    public $timestamps = false;
}
