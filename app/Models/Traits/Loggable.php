<?php

namespace App\Models\Traits;

use Illuminate\Support\Str;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

/**
 * Trait to implement default Spatie Activity Log configuration.
 * Can be used by any model, including the User model.
 */
trait Loggable
{
    use LogsActivity;

    /**
     * Define the Activity Log options with smart defaults.
     * 
     * @return \Spatie\Activitylog\LogOptions
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
     *
     * @param string $eventName
     * @return string
     */
    public function descriptionForEvent(string $eventName): string
    {
        $modelName = Str::snake(class_basename($this), ' ');

        return $modelName . ' has been ' . $eventName . '.';
    }

    /**
     * Generate a default, useful log name based on the model class.
     *
     * @return string
     */
    public function getLogName(): string
    {
        return Str::snake(class_basename($this));
    }
}
