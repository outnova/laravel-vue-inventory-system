<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\CategoryResource;

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
            'name' => $this->name,
            'sku' => $this->sku,
            'price' => (float) $this->price,
            'stock' => $this->stock,
            'category_id' => $this->category_id,

            'category' => new CategoryResource($this->whenLoaded('category')),

            'created_at' => $this->created_at->format('d/M/Y'),
        ];
    }
}
