<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Crypt;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $_id = Crypt::encrypt($this->id);
        $_productId = Crypt::encrypt($this->product->id);
        return [
            '_id' => $_id,
            'name' => $this->name,
            'slug' => $this->product->slug,
            'thumbnail_url' => $this->thumbnail_url,
            'price' => $this->price,
            'qty' => $this->qty,
            'subtotal' => $this->subtotal,
            'url' => [
                '_id' => route('v1.products.show', [ 'product' => $_productId ]),
                'seo' => route('v1.products.show', [ 'product' => $this->product->slug ]),
            ],
        ];
    }
}
