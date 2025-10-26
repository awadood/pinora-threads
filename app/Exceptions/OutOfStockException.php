<?php

namespace App\Exceptions;

use Exception;

/**
 * Class OutOfStockException
 *
 * @author Abdul Wadood
 */
class OutOfStockException extends Exception
{
    protected $code = 440;
}
