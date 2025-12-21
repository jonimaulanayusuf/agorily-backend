<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Crypt;

class CartResource extends JsonResource
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
            'product' => new ProductResource($this->product),
            'qty' => $this->qty,
            'subtotal' => $this->subtotal,
            'is_sufficient' => $this->product->stock >= $this->qty,
        ];
    }
}
