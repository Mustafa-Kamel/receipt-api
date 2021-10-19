<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'count' => $this->count,
            'price' => $this->price,
            'weight' => $this->weight,
            'type' => $this->itemtype->title,
            'country' => $this->country->title,
            'country_code' => $this->country->code
        ];
    }
}
