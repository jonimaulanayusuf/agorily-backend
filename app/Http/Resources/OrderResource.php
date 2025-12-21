<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Crypt;

class OrderResource extends JsonResource
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
            'invoice' => $this->invoice,
            'url' => [
                '_id' => route('v1.orders.show', [ 'order' => $_id ]),
            ],
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'total' => $this->total,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
