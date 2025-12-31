<?php

namespace App\Http\Resources\Media;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * MediaVideoResource
 *
 * @author Abdul Wadood
 */
class MediaVideoResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'media_asset_id' => $this->media_asset_id,
            'provider' => $this->provider,
            'external_id' => $this->external_id,
            'duration_seconds' => $this->duration_seconds,
            'poster_media_asset_id' => $this->poster_media_asset_id,
            'autoplay' => $this->autoplay,
            'muted' => $this->muted,
            'loop' => $this->loop,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
