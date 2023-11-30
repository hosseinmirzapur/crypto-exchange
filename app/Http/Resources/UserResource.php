<?php

namespace App\Http\Resources;

use App\Models\Admin;
use Illuminate\Http\Resources\Json\JsonResource;
use function App\Helpers\current_user;

class UserResource extends JsonResource
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
            'id' => $this->when((current_user() instanceof Admin), $this->id),
            'username' => $this->email,
            'email' => $this->email,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'profile' => $this->whenLoaded('profile'),
            'rank' => $this->rank,
            'is_banned' => $this->is_banned,
            'credits' => CreditResource::collection($this->whenLoaded('coins')),
            'settings' => $this->whenLoaded('settings'),
            'accounts' => $this->whenLoaded('accounts'),
            'documents' => $this->whenLoaded('documents'),
            'rank_correction_factor' => $this->whenAppended('rankCorrectionFactor'),
            'net_assets' => $this->whenAppended('netAssetsInToman'),
        ];
    }
}
