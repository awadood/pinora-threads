<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * It uses the trait Illuminate\Database\Eloquent\Factories\HasFactory.
 *
 * @author Abdul Wadood
 */
abstract class AbstractModel extends Model
{
    use HasFactory;
}
