<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Core\BaseLookupController;
use App\Http\Resources\ActivityResource;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends BaseLookupController
{
    protected string $modelClass = Activity::class;

    protected string $resourceClass = ActivityResource::class;

    protected array $allowedFilters = ['log_name', 'description', 'subject_id', 'subject_type', 'event', 'causer_id', 'properties', 'created_at'];

    protected array $likeFilters = ['log_name', 'description'];

    protected array $allowedSorts = ['log_name', 'description'];

    public function show(Activity $activity)
    {
        $activity->load(['causer', 'subject']);

        return ActivityResource::make($activity);
    }
}
