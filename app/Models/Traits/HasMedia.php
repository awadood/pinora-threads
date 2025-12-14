<?php

namespace App\Models\Traits;

use App\Models\MediaAttachment;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasMedia
{
    public function media(): MorphMany
    {
        return $this->morphMany(MediaAttachment::class, 'owner');
    }

    public function primaryMediaForRole(string $role): MorphOne
    {
        return $this->morphOne(MediaAttachment::class, 'owner')
            ->where('role', $role)
            ->where('is_primary', true);
    }

    public function mediaForRole(string $role): MorphMany
    {
        return $this->morphMany(MediaAttachment::class, 'owner')
            ->where('role', $role)
            ->orderBy('position');
    }
}
