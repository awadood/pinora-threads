<?php

namespace App\Http\Resources;

use App\Util\Constant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            // 'name' => $this->name,
            // Only include price if the user is authenticated (e.g., in a B2B app)
            // 'price' => $this->when(auth()->check(), $this->price),
            // 'sku' => $this->sku,
            // Conditionally load relationship data to prevent N+1 queries
            // 'category' => new CategoryResource($this->whenLoaded('category')),
            'created_at' => $this->created_at->format(Constant::DATE_TIME),
        ];
    }
}
