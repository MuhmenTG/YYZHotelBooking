<?php

namespace App\Http\Resources;

use App\Factories\AdminFactory;
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
            'roomCategory'    => AdminFactory::geCategoryNameById($this->categoryId),
            'roomGuestCapacity' => $this->capacity,
            'roomPricePrNight'  => $this->price,
            'roomDescription' => $this->description,
        ];
    }
}
