<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'log_name' => $this->log_name,
            'description' => $this->description,
            'event' => $this->event,
            'subject_type' => $this->subject_type,
            'subject_id' => $this->subject_id,
            'causer_id' => $this->causer_id,
            'causer' => $this->causer ? [
                'id' => $this->causer->id,
                'name' => $this->causer->name ?? null,
                'email' => $this->causer->email ?? null,
            ] : null,
            'properties' => $this->properties,
            'created_at' => $this->created_at,
        ];
    }
}
