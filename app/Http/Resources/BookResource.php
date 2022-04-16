<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    public function toArray($request):array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'coverUrl' => $this->coverUrl,
            'pages' => $this->whenLoaded('pages', function () {
                return PageResource::collection($this->pages);
            }),
        ];
    }
}
