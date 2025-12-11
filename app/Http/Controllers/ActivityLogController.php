<?php

namespace App\Http\Controllers;

use App\Http\Resources\ActivityResource;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'causer_type' => ['nullable', 'string', 'max:255'],
            'subject_type' => ['nullable', 'string', 'max:255'],
            'subject_id' => ['nullable', 'integer'],
            'log_name' => ['nullable', 'string', 'max:255'],
            'event' => ['nullable', 'string', 'max:255'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $perPage = $data['per_page'] ?? 25;

        $query = Activity::query()->with(['causer', 'subject'])->latest('created_at');

        if (! empty($data['user_id'])) {
            $query->where('causer_id', $data['user_id'])
                ->where('causer_type', User::class);
        }

        if (! empty($data['causer_type'])) {
            $query->where('causer_type', $data['causer_type']);
        }

        if (! empty($data['subject_type'])) {
            $query->where('subject_type', $data['subject_type']);
        }

        if (! empty($data['subject_id'])) {
            $query->where('subject_id', $data['subject_id']);
        }

        if (! empty($data['log_name'])) {
            $query->where('log_name', $data['log_name']);
        }

        if (! empty($data['event'])) {
            $query->where('event', $data['event']);
        }

        if (! empty($data['date_from'])) {
            $query->where('created_at', '>=', $data['date_from']);
        }

        if (! empty($data['date_to'])) {
            $query->where('created_at', '<=', $data['date_to']);
        }

        if (! empty($data['search'])) {
            $search = $data['search'];

            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('event', 'like', "%{$search}%")
                    ->orWhere('log_name', 'like', "%{$search}%");
            });
        }

        $activities = $query->paginate($perPage)->appends($request->query());

        return ActivityResource::collection($activities);
    }

    public function filters()
    {
        // Distinct log names
        $logNames = Activity::query()
            ->select('log_name')
            ->whereNotNull('log_name')
            ->distinct()
            ->orderBy('log_name')
            ->pluck('log_name')
            ->values();

        // Distinct events
        $events = Activity::query()
            ->select('event')
            ->whereNotNull('event')
            ->distinct()
            ->orderBy('event')
            ->pluck('event')
            ->values();

        // Distinct subject types (class names)
        $subjectTypes = Activity::query()
            ->select('subject_type')
            ->whereNotNull('subject_type')
            ->distinct()
            ->orderBy('subject_type')
            ->pluck('subject_type')
            ->map(function (string $class) {
                return [
                    'class' => $class,
                    'label' => class_basename($class),
                ];
            })
            ->values();

        // Users who have activity logs (assuming causer_type = User::class)
        $userIds = Activity::query()
            ->where('causer_type', User::class)
            ->whereNotNull('causer_id')
            ->distinct()
            ->pluck('causer_id');

        $users = User::query()
            ->whereIn('id', $userIds)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json([
            'users' => $users,
            'log_names' => $logNames,
            'events' => $events,
            'subject_types' => $subjectTypes,
        ]);
    }

    public function subject(Request $request)
    {
        $data = $request->validate([
            'subject_type' => ['required', 'string', 'max:255'],
            'subject_id' => ['required', 'integer'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $perPage = $data['per_page'] ?? 25;

        $query = Activity::query()
            ->with(['causer', 'subject'])
            ->where('subject_type', $data['subject_type'])
            ->where('subject_id', $data['subject_id'])
            ->latest('created_at');

        $activities = $query->paginate($perPage)->appends($request->query());

        return ActivityResource::collection($activities);
    }

    public function show(int $id)
    {
        $activity = Activity::query()->with(['causer', 'subject'])->findOrFail($id);

        return ActivityResource::make($activity);
    }
}
