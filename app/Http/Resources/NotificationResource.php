<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;

class NotificationResource extends JsonResource
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
            'title' => $this->data['title'],
            'body' => $this->data['body'],
            'is_read' => (boolean)isset($this->read_at),
            'read_at' => $this->read_at,
            'created_at' => $this->when(
                App::isLocale('fa'),
                $this->created_at->setTimezone('Asia/Tehran')->toDateTimeString(),
                $this->created_at
            ),
            'updated_at' => $this->when(
                App::isLocale('fa'),
                $this->updated_at->setTimezone('Asia/Tehran')->toDateTimeString(),
                $this->updated_at
            )
        ];
    }
}
