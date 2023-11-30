<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use function App\Helpers\current_user;

class CoinResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $array = [
            "id" => $this->id,
            "code" => $this->code,
            "name" => $this->name,
            "label" => $this->label,
            "status" => $this->status,
            "constant_fee" => $this->constant_fee,
            "logo" => $this->logo
        ];

        if (auth()->check()) {
            $array["networks"] = $this->whenLoaded('networks');

            if (current_user()->isAdmin()) {
                $array["amount"] = $this->amount;
            }
        }

        return $array;
    }


}
