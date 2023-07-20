<?php

namespace App\Http\Resources;

use App\Factories\BookingFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomReservationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'identifer' => $this->id,
            'confirmationNumber' => $this->confirmationNumber,
            'bookingDate' => $this->bookingDate,
            'headGuestName' => $this->headGuest,
            'headGuestemail' => $this->email,
            'headGuestcontact' => $this->contact,
            'assignedBookedRoom' =>  $this->roomId,
            'scheduledCheckInDate' => $this->ScheduledCheckInDate,
            'scheduledCheckOutDate' => $this->ScheduledCheckOutDate,
            'actualCheckInDate' => $this->ActualCheckInDate,
            'actualCheckOutDate' => $this->ActualCheckOutDate,
            'guests' => $this->guests,
            'specialRequests' => $this->specialRequests,
            'bookingStatus' => $this->isConfirmed,
        ];
    }
}
