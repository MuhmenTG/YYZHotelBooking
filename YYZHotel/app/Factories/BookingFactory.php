<?php

namespace App\Factories;

use App\Models\Room;
use App\Models\RoomReservation;
use Carbon\Carbon;

class BookingFactory {

    /**
    * Remove a room reservation by confirmation number.
    *
    * @param  string  $confirmationNumber  The confirmation number of the room reservation.
    * @return void
    */

    public static function removeRoomReservation(string $confirmationNumber) {
        $roomReservation = BookingFactory::lookUpRoomReservation($confirmationNumber)->delete();
        return $roomReservation;
    }

    /**
    * Look up a room reservation by confirmation number.
    *
    * @param  string  $confirmationNumber  The confirmation number of the room reservation.
    * @return RoomReservation|null         The room reservation model instance, or null if not found.
    */
    public static function lookUpRoomReservation(string $confirmationNumber): ?RoomReservation
    {
        $roomReservation = RoomReservation::ByConfirmationNumber($confirmationNumber)->first();
        return $roomReservation;
    }
    
    /**
    * Create or update a room reservation.
    *
    * @param string|null  $confirmationNumber   The confirmation number of the room reservation, or null for a new reservation.
    * @param string       $headGuest            The name of the head guest for the reservation.
    * @param string       $email                The email address of the guest.
    * @param string       $contact              The contact number of the guest.
    * @param int          $roomId               The ID of the room for the reservation.
    * @param Carbon       $scheduledCheckInDate The scheduled check-in date for the reservation.
    * @param Carbon       $scheduledCheckOutDate The scheduled check-out date for the reservation.
    * @param int          $numberOfGuest        The number of guests for the reservation.
    * @param string       $specialRequests      Any special requests for the reservation.
    * @param string       $transactionId        The ID of the payment transaction.
    * @return bool                              True if the room reservation was saved successfully, false otherwise.
    */
    public static function createUpdateRoomReservation(?string $confirmationNumber = null, string $headGuest, string $email, string $contact, int $roomId, Carbon $scheduledCheckInDate,
    Carbon $scheduledCheckOutDate, int $numberOfGuest, string $specialRequets, string $transactionId){
        if($confirmationNumber){
            $roomReservation = RoomReservation::ByConfirmationNumber($confirmationNumber)->first();
        }
        else{
            $roomReservation = New RoomReservation();
        }
        $roomReservation->setConfirmationNumber(BookingFactory::generateConfirmationNumber());
        $roomReservation->setBookingDate(Carbon::now());
        $roomReservation->setHeadGuest($headGuest);
        $roomReservation->setEmail($email);
        $roomReservation->setContact($contact);
        $roomReservation->setRoomId($roomId);
        $roomReservation->setScheduledCheckInDate($scheduledCheckInDate);
        $roomReservation->setScheduledCheckOutDate($scheduledCheckOutDate);
        $roomReservation->setGuests($numberOfGuest);
        $roomReservation->setSpecialRequests($specialRequets);
        $roomReservation->setIsConfirmed(true);
        $roomReservation->setPaymentId($transactionId);
        $roomReservation->save();
        return $roomReservation;
    }


    /**
    * Get all available rooms within the specified date range.
    *
    * @param Carbon $scheduledCheckOutDate The desired check-out date.
    * @param Carbon $scheduledCheckInDate  The desired check-in date.
    * @return array An array of available rooms.
    */
    public static function getAllAvailableRooms(Carbon $scheduledCheckOutDate, Carbon $scheduledCheckInDate)
    {
        $allRooms = Room::all();
        
        $availableRooms = [];

        foreach ($allRooms as $room) {
              $bookings = RoomReservation::where(RoomReservation::COL_ROOMID, $room->getRoomId())
            ->where(function ($query) use ($scheduledCheckInDate, $scheduledCheckOutDate) {
                $query->where(function ($query) use ($scheduledCheckInDate, $scheduledCheckOutDate) {
                    $query->where(RoomReservation::COL_SCHEDULEDCHECKINDATE, '>=', $scheduledCheckInDate)
                        ->where(RoomReservation::COL_SCHEDULEDCHECKINDATE, '<', $scheduledCheckOutDate);
                })->orWhere(function ($query) use ($scheduledCheckInDate, $scheduledCheckOutDate) {
                    $query->where(RoomReservation::COL_SCHEDULEDCHECKOUTDATE, '>', $scheduledCheckInDate)
                        ->where(RoomReservation::COL_SCHEDULEDCHECKOUTDATE, '<=', $scheduledCheckOutDate);
                })->orWhere(function ($query) use ($scheduledCheckInDate, $scheduledCheckOutDate) {
                    $query->where(RoomReservation::COL_SCHEDULEDCHECKINDATE, '<=', $scheduledCheckInDate)
                        ->where(RoomReservation::COL_SCHEDULEDCHECKOUTDATE, '>=', $scheduledCheckOutDate);
                });
            })->get();
    
            if ($bookings->isEmpty()) {
                $availableRooms[] = $room;
            }
        }

        return $availableRooms;
    }

    /**
    * Get information about a selected room.
    *
    * @param int $roomId The ID of the room.
    * @return bool|Room The room object if it is still available, false if it is not available, or null if the room is not found.
    */
    public static function getSelectedRoomInfo(Room $room) : ?Room
    {
        $isStillAvailable = $room->getAvailability();
        if ($isStillAvailable == false) {
            return false;
        }

        return $room;
        
    }

    public static function lookUpRoom(int $roomId) : ?Room{
        $room = Room::ById($roomId)->first();
        return $room;
    }


    private static function generateConfirmationNumber(): string
    {
        $confirmationNumber = '';
        
        for ($i = 0; $i < 16; $i++) {
            $confirmationNumber .= random_int(0, 9);
        }

        return $confirmationNumber;
    }

}