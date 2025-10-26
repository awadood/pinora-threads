<?php

namespace App\Exceptions;

use Exception;

/**
 * Class OutOfStockException
 * 
 * @package App\Exceptions
 * @author Abdul Wadood
 */
class OutOfStockException extends Exception
{
    protected $code = 440;
}
