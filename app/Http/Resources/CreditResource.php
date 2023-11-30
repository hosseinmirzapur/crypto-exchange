<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CreditResource extends JsonResource
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
            "code" => $this->code,
            "name" => $this->name,
            "label" => $this->label,
            "credit" => $this->credit->credit,
            "blocked" => $this->credit->blocked,
            "net" => $this->credit->credit - $this->credit->blocked,
        ];
    }
}
