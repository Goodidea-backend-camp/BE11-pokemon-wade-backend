<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'order_no' => $this->order_no,
            'user_name' => $this->user->name,
            'total_price' => $this->total_price,
            'payment_method'=>$this->payment_method,
            'payment_status'=>$this->payment_status,
            'created_at'=>$this->created_at
        ];
    }
}
