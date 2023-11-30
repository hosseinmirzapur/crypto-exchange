<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'api_status' => $this->api_status ?? 'PENDING',
            'amount' => $this->getAmount($this->amount),
            'type' => $this->type,
            'payment_method' => $this->payment_method,
            'coin' => $this->whenLoaded('coin'),
            'account' => $this->whenLoaded('account'),
            'payment' => $this->whenLoaded('payment'),
            'user' => $this->whenLoaded('user'),
            'created_at' => $this->created_at,
        ];
    }

    public function getAmount($amount) {
        if (isset($this->coin) && $this->coin->code !== 'TOMAN') {
            return round($amount * 10000) / 10000;
        }
        return round($amount);
    }
}
