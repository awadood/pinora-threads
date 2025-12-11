<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $subject = $this->subject;

        return [
            'id' => $this->id,
            'log_name' => $this->log_name,
            'description' => $this->description,
            'event' => $this->event,

            'subject_type' => $this->subject_type,
            'subject_id' => $this->subject_id,
            'subject_label' => $this->subjectLabel($subject),

            'causer_type' => $this->causer_type,
            'causer_id' => $this->causer_id,
            'causer_name' => optional($this->causer)->name,

            'properties' => $this->properties ?? [],

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Try to build a simple, useful label for the subject.
     */
    protected function subjectLabel($subject): ?string
    {
        if (! $subject) {
            return null;
        }

        // Prefer common naming fields if they exist
        foreach (['name', 'title', 'code', 'email'] as $field) {
            if (isset($subject->{$field}) && $subject->{$field}) {
                return (string) $subject->{$field};
            }
        }

        // Fallback to class name + primary key
        return class_basename($subject).' #'.$subject->getKey();
    }
}
