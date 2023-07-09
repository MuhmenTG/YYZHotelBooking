<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'roomName'      => $this->roomNumber,
            'roomNumber'    => $this->id,
            'categoryId'    => $this->categoryId,
            'GuestCapacity' => $this->capacity,
            'pricePrNight'  => $this->price,
            'roomDescription' => $this->description,
        ];
    }
}
