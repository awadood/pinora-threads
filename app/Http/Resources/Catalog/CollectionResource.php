<?php

namespace App\Http\Resources\Catalog;

use App\Http\Resources\Media\MediaAttachmentResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * CollectionResource
 *
 * @author Abdul Wadood
 */
class CollectionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sort' => $this->sort,
            'description' => $this->notes,
            'active' => $this->active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'products_count' => $this->when(isset($this->products_count), (int) $this->products_count),

            'hero_media' => MediaAttachmentResource::make($this->whenLoaded('heroMedia')),

            'og_image_media' => MediaAttachmentResource::make($this->whenLoaded('ogImageMedia')),

            'thumbnail_media' => MediaAttachmentResource::make($this->whenLoaded('thumbnailMedia')),

            'products' => ProductResource::collection($this->whenLoaded('products')),
        ];
    }
}
