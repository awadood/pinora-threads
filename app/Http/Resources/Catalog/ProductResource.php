<?php

namespace App\Http\Resources\Catalog;

use App\Http\Resources\Media\MediaAttachmentResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * ProductResource
 *
 * @author Abdul Wadood
 */
class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type,
            'description' => $this->description,
            'tax_class_id' => $this->tax_class_id,
            'active' => $this->active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'related_products' => RelatedProductResource::collection($this->whenLoaded('relatedProducts')),

            'variants' => ProductResource::collection($this->whenLoaded('variants')),

            'bundles' => ProductBundleResource::collection($this->whenLoaded('bundles')),

            'attributes' => ProductAttributeResource::collection($this->whenLoaded('attributes')),

            'prices' => ProductPriceResource::collection($this->whenLoaded('prices')),

            'categories' => CategoryResource::collection($this->whenLoaded('categories')),

            'sort' => $this->whenPivotLoaded('collection_product', function () {
                return $this->pivot->sort;
            }),

            'thumbnail_media' => MediaAttachmentResource::make($this->whenLoaded('thumbnailMedia')),
            'hero_media' => MediaAttachmentResource::make($this->whenLoaded('heroMedia')),
            'og_image_media' => MediaAttachmentResource::make($this->whenLoaded('ogImageMedia')),
            'gallery_media' => MediaAttachmentResource::collection($this->whenLoaded('galleryMedia')),

            'variants_count' => $this->variants_count ?? 0,

            'availability' => [
                'in_stock' => $this->in_stock ?? false,
                'country_qty' => $this->country_qty ?? 0,
            ],
        ];
    }
}
