<?php

declare(strict_types=1);
namespace App\Factories;

use App\Models\Payment;
use App\Models\Room;
use App\Models\RoomReservation;
use Carbon\Carbon;
use PhpParser\Node\Stmt\Echo_;
use Stripe\BalanceTransaction;

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
    * @return RoomReservation|bool              RoomReservation if successfully booked otherwise false.
    */
    public static function createRoomReservation(string $headGuest, string $email, string $contact, int $roomId, Carbon $scheduledCheckInDate,
    Carbon $scheduledCheckOutDate, int $numberOfGuest, string $specialRequets, string $transactionId){
        $roomReservation = New RoomReservation();
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
        if($roomReservation->save()){
            return $roomReservation;
        }
        return false;
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
            $bookings = BookingFactory::validateRoomExistBetweenDates($room->getRoomId(), $scheduledCheckInDate, $scheduledCheckOutDate);
    
            if ($bookings === null || $bookings->isEmpty()) {
                $availableRooms[] = $room;
            }
        }
    
        return $availableRooms;
    }
    
    public static function validateRoomExistBetweenDates(string $roomId, Carbon $scheduledCheckInDate, Carbon $scheduledCheckOutDate)
    {
        $conflictingBookings = RoomReservation::where(RoomReservation::COL_ROOMID, $roomId)
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
            if ($conflictingBookings->isEmpty()) {
                return null; // Return null to indicate no conflicts found
            }
        
        return $conflictingBookings; // Return the conflicting bookings if found
        
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

    public static function calculateTotalPrice(Carbon $checkInDate, Carbon $checkOutDate, int $roomId) {
        $checkInDate = Carbon::parse($checkInDate);
        $checkOutDate = Carbon::parse($checkOutDate);

        $numberOfNightStay = $checkInDate->diffInDays($checkOutDate);
        $room = Room::ByRoomNumber($roomId)->first();
    
        if (!$room) {
            throw new \Exception("Room with ID $roomId not found.");
        }
    
        $pricePerNight = $room->getPrice();
        $pricePerNight = intval($pricePerNight);
        $totalPrice = $numberOfNightStay * $pricePerNight;
    
        return $totalPrice;
    }    

    public static function createCharge(float $amount, string $currency, string $cardNumber, string $expireYear, string $expireMonth, string $cvc, string $confirmationNumber, string $description) 
    {
        $stripe = BookingFactory::createCardRecord($cardNumber, $expireYear, $expireMonth, $cvc);
        
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));

        $charge = $stripe->charges->create([
            'amount' => $amount,
            'currency' => 'dkk',
            'source' => 'tok_mastercard',
            'description' => $description,
        ]);
        
        
        if($charge){    
            $payment = New Payment();
            $payment->setPaymentAmount($amount);       
            $payment->setPaymentCurrency($currency);
            $payment->setPaymentType("Online");
            $payment->setPaymentTransactionId($charge->id);
            $payment->setPaymentMethod("MasterCard");
            $payment->setPaymentGatewayProcessor("Stripe APi");
            $payment->setPaymentStatus("Proceed");
            $payment->setPaymentNoteComments($description);
            $payment->setConfirmationNumber($confirmationNumber);
            $payment->save();
        }
        return $payment;
    }

    private static function createCardRecord(string $cardNumber, string $expYear, string $expMonth, string $cvc){

        if (!ctype_digit($cardNumber) || strlen($cardNumber) < 12 || strlen($cardNumber) > 19) {
            throw new \InvalidArgumentException('Invalid card numer given Should be 12 digits long.');
        }

        if (!ctype_digit($expMonth) || $expMonth < 1 || $expMonth > 12) {

            throw new \InvalidArgumentException('Invalid expiry Date of card.');
        }
        
        if (!ctype_digit($expYear) || strlen($expYear) != 4 || $expYear < date('Y')) {

        }
        
        if (!ctype_digit($cvc) || strlen($cvc) < 3 || strlen($cvc) > 4) {
        
        }
      
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
        
        $stripe->tokens->create([
            'card' => [
              'number' => $cardNumber,
              'exp_month' => $expMonth,
              'exp_year' => $expYear,
              'cvc' => $cvc,
            ],
        ]);

        return $stripe;
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