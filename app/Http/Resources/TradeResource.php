<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use function App\Helpers\customRound;

class TradeResource extends JsonResource
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
            'binance_trade_id' => $this->binance_trade_id,
            'type' => $this->type,
            'amount' => round($this->amount * 1000000) / 1000000,
            'price' => $this->price,
            'total' => round($this->amount * 1000000 * $this->price) / 1000000,
            'price_toman' => customRound($this->price_toman, 0),
            'total_toman' => customRound($this->price_toman * $this->price, 0),
            'commission' => round($this->commission_amount * 1000000 * $this->price) / 100000,
            'commission_asset' => $this->commission_asset,
            'gain' => customRound($this->gain, 0),
            'order' => $this->whenLoaded('order'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
