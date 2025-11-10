<?php

namespace App\Models\Traits;

use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Trait to implement default Spatie Activity Log configuration.
 * Can be used by any model, including the User model.
 *
 * @author Abdul Wadood
 */
trait Loggable
{
    use LogsActivity;

    /**
     * Define the Activity Log options with smart defaults.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => $this->descriptionForEvent($eventName))
            ->useLogName($this->getLogName());
    }

    /**
     * The default description for activity log.
     */
    public function descriptionForEvent(string $eventName): string
    {
        $modelName = Str::snake(class_basename($this), ' ');

        return $modelName.' has been '.$eventName.'.';
    }

    /**
     * Generate a default, useful log name based on the model class.
     */
    public function getLogName(): string
    {
        return Str::snake(class_basename($this));
    }
}
