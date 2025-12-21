<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Crypt;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $_id = Crypt::encrypt($this->id);
        return [
            '_id' => $_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'thumbnail_url' => $this->thumbnail_url,
            'price' => $this->price,
            'stock' => $this->stock,
            'is_favorite' => $this->favorites->first() ? true : false,
            'url' => [
                '_id' => route('v1.products.show', [ 'product' => $_id ]),
                'seo' => route('v1.products.show', [ 'product' => $this->slug ]),
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
