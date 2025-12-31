<?php

namespace App\Http\Resources\Media;

use App\Support\OwnerTypeResolver;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * MediaAttachmentResource
 *
 * @author Abdul Wadood
 */
class MediaAttachmentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'media_asset_id' => $this->media_asset_id,
            'owner_type' => OwnerTypeResolver::keyFromClass($this->owner_type),
            'owner_id' => $this->owner_id,
            'role' => $this->role,
            'position' => $this->position,
            'is_primary' => $this->is_primary,
            'alt_text' => $this->alt_text,
            'caption' => $this->caption,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'media_asset' => MediaAssetResource::make($this->whenLoaded('asset')),
        ];
    }
}
