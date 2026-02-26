<?php

namespace App\Http\Resources\Catalog;

use App\Http\Resources\Media\MediaAttachmentResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * AttributeOptionResource
 *
 * @author Abdul Wadood
 */
class AttributeOptionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'attribute_id' => $this->attribute_id,
            'value' => $this->value,
            'sort' => $this->sort,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'attribute' => $this->whenLoaded('attribute', fn () => [
                'id' => $this->attribute?->id,
                'code' => $this->attribute?->code,
                'label' => $this->attribute?->label,
            ]),

            'thumbnail_media' => MediaAttachmentResource::make($this->whenLoaded('thumbnailMedia')),
        ];
    }
}
