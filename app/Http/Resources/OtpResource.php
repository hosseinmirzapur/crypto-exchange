<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OtpResource extends JsonResource
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
            'email' => $this->email,
            'mobile' => $this->when(isset($this->profile), $this->modifyMobile()),
            'method' => $this->method,
            'token' => $this->token
        ];
    }

    public function modifyMobile()
    {
        return isset($this->profile->mobile)
            ? substr_replace($this->profile->mobile, '****', 4, 4)
            : '';
    }
}
