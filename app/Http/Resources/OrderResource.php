<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use function App\Helpers\customRound;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'payment_method' => $this->payment_method,
            'fee' => $this->fee,
            'amount' => round($this->amount * 100000) / 100000,
            'price' => $this->price,
            'total' => customRound($this->price * $this->amount),
            'bill_number' => $this->bill_number,
            'status' => $this->status,
            'cost' => $this->amount * $this->price,
            'created_at' => $this->created_at,
            'market' => $this->whenLoaded('market'),
            'user' => $this->whenLoaded('user')
        ];
    }
}
