<?php

namespace App\Models;

use App\Models\Traits\Loggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * It uses the trait Spatie\Activitylog\Traits\LogsActivity to record eloquent
 * events. The events created, updated, and deleted are monitored by default.
 * Note that deleted event is triggered only if the object is retrieved. For
 * example, User::find(1)->delete() will trigger event deleted while the code
 * User::where(1)->delete() will not trigger deleted.
 *
 * @author Abdul Wadood
 */
abstract class AbstractModel extends Model
{
    use HasFactory, Loggable;
}
