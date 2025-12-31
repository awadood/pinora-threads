<?php

namespace App\Http\Resources\Media;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * MediaRenditionResource
 *
 * @author Abdul Wadood
 */
class MediaRenditionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'media_asset_id' => $this->media_asset_id,
            'profile' => $this->profile,
            'disk' => $this->disk,
            'key' => $this->key,
            'url' => config('app.cdn_base_url').'/'.ltrim($this->key ?? '', '/'),
            'mime_type' => $this->mime_type,
            'bytes' => $this->bytes,
            'width' => $this->width,
            'height' => $this->height,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
