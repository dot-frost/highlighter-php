<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'imageUrl' => $this->imageUrl,
            'imageThumbnail300Url' => $this->imageThumbnail300Url,
            'highlights' => $this->highlights,
            'status' => $this->status,
        ];
    }
}
