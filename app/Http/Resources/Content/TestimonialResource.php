<?php

namespace App\Http\Resources\Content;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * TestimonialResource
 *
 * @author Abdul Wadood
 */
class TestimonialResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'author_name' => $this->author_name,
            'content' => $this->content,
            'rating' => $this->rating,
            'photo_url' => $this->photo_url,
            'sort_order' => $this->sort_order,
            'published_at' => $this->published_at,
            'status' => $this->status,
            'reviewed_by' => $this->reviewed_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
