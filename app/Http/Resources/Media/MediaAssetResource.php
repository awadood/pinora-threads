<?php

namespace App\Http\Resources\Media;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * MediaAssetResource
 *
 * @author Abdul Wadood
 */
class MediaAssetResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'disk' => $this->disk,
            'key' => $this->key,
            'url' => config('app.cdn_base_url').'/'.ltrim($this->key ?? '', '/'),
            'mime_type' => $this->mime_type,
            'bytes' => $this->bytes,
            'width' => $this->width,
            'height' => $this->height,
            'alt_text' => $this->alt_text,
            'title' => $this->title,
            'caption' => $this->caption,
            'checksum' => $this->checksum,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'renditions' => MediaRenditionResource::collection($this->whenLoaded('renditions')),

            'video' => MediaVideoResource::make($this->whenLoaded('video')),
        ];
    }
}
