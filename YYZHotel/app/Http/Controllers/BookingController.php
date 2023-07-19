<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Factories\BookingFactory;
use App\Helper\Constants;
use App\Http\Resources\RomResource;
use App\Http\Resources\RoomReservationResource;
use App\Models\Room;
use App\Models\RoomReservation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
class BookingController extends Controller
{
    //

    public function searchRoomAvailability(Request $request){
    
        $validator = Validator::make($request->all(), [
        'checkInDate' => 'required|date',
        'checkOutDate' => 'required|date|after:checkInDate',
        ]);
    
        if ($validator->fails()) {
            return Constants::validationErrorResponse($validator->errors());
        }
        
        $checkInDate = Carbon::parse($request->input('checkInDate'));
        $checkOutDate = Carbon::parse($request->input('checkOutDate'));

        $avaliableRooms = BookingFactory::getAllAvailableRooms($checkInDate, $checkOutDate);

        $avaliableRooms = RomResource::collection($avaliableRooms);

        return response()->json([
            'avaliableRooms' => $avaliableRooms
        ]);
    }

    public function selectAvailiableRoom(int $roomId){
        $room = BookingFactory::lookUpRoom($roomId);

        if(!$room){
            return response()->json(['message' => Constants::ROOM_NOT_FOUND_MESSAGE], Response::HTTP_NOT_FOUND);
        }

        $avaliableConfirm = BookingFactory::getSelectedRoomInfo($room);

        if(!$avaliableConfirm){
            return response()->json(['message' => Constants::ROOM_NOT_FOUND_MESSAGE], Response::HTTP_NOT_FOUND);
        }
    
        $avaliableConfirm = new RomResource($avaliableConfirm);

        return response()->json([
            'roomDeSeletiontails' => $avaliableConfirm
        ]);
    }

    public function payAndBookRoom(Request $request){
        $validator = Validator::make($request->all(), [
            'headGuest' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'contact' => 'required|string|max:20',
            'room_id' => 'required|integer',
            'checkInDate' => 'required|date',
            'checkOutDate' => 'required|date|after:checkInDate',
            'numberOfGuest' => 'required|integer|min:1',
            'specialRequests' => 'nullable|string',
            'transactionId' => 'required|string|max:255',
            'amount' => 'required|integer|min:1',
            'currency' => 'required|string|max:3', 
            'card_number' => 'required|string|digits:16',
            'expire_year' => 'required|string|digits:4',
            'expire_month' => 'required|string|digits:2',
            'cvc' => 'required|string|digits:3',
        ]);

        if ($validator->fails()) {
            return Constants::validationErrorResponse($validator->errors());
        }
        
        $headGuest = $request->input('headGuest');
        $email = $request->input('email');
        $contact = $request->input('contact');
        $roomId = $request->input('roomId');
        $checkInDate = Carbon::parse($request->input('checkInDate'));
        $checkOutDate = Carbon::parse($request->input('checkOutDate'));
        $numberOfGuest = $request->input('numberOfGuest');
        $specialRequests = $request->input('specialRequests');
        $transactionId = $request->input('transactionId');
                
        $roomReservation = BookingFactory::createRoomReservation(
            $headGuest,
            $email,
            $contact,
            $roomId,
            $checkInDate,
            $checkOutDate,
            $numberOfGuest,
            $specialRequests,
            $transactionId
        );

        $roomReservation = new RoomReservationResource($roomReservation);
        $numberOfNightStay = $checkInDate->diffInDays($checkOutDate);
        
        $room = Room::ByRoomNumber($roomId)->first();
        $pricePerNight = $room->getPrice();
        $totalPrice = $numberOfNightStay * $pricePerNight;
        
        $currency = $request->input('currency');
        $cardNumber = $request->input('card_number');
        $expireYear = $request->input('expire_year');
        $expireMonth = $request->input('expire_month');
        $cvc = $request->input('cvc');

        // Call createCharge method
        $payment = BookingFactory::createCharge(
            $totalPrice,
            $currency,
            $cardNumber,
            $expireYear,
            $expireMonth,
            $cvc,
            $roomReservation->getConfirmationNumber(),
            "Issue Room Reservation"
        );

        $fullReservationDetails = [
            'room_reservation' => $roomReservation,
            'payment' => $payment,
        ];

        return response()->json([
            $fullReservationDetails
        ], Response::HTTP_OK);
    }

    public function retrieveBooking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'confirmationNumber' => 'required|string',
        ]);

        if ($validator->fails()) {
            return Constants::validationErrorResponse($validator->errors());
        }

        $confirmationNumber = $request->input('confirmationNumber');
        $reservation = BookingFactory::lookUpRoomReservation($confirmationNumber);

        if (!$reservation) {
            return response()->json([
                'message' => Constants::ROOM_RESERVATION_NOT_FOUND_MESSAGE
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'reservation' => $reservation
        ]);
    }

    public function deleteBooking(string $confirmationNumber){
        $reservation = BookingFactory::lookUpRoomReservation($confirmationNumber);
        if(!$reservation && $reservation == null){
            return response()->json(['message' => Constants::ROOM_RESERVATION_NOT_FOUND_MESSAGE], Response::HTTP_NOT_FOUND);
        }

        $deleteReservation = BookingFactory::removeRoomReservation($confirmationNumber);
        if($deleteReservation){ 
            return response()->json([
                'message' => Constants::ROOM_RESERVATION_DELETED_MESSAGE
            ], Response::HTTP_OK);
        }

        return response()->json([
            'message' => Constants::ROOM_RESERVATION_DELETION_FAILED_MESSAGE
        ], Response::HTTP_BAD_REQUEST);
    }

    public function changeBookingGuestDetails(Request $request){
        $validator = Validator::make($request->all(), [
            'confirmationNumber' => 'required|string|max:255',
            'headGuest' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'contact' => 'required|string|max:20',
            'specialRequests' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $confirmationNumber = $request->input('confirmationNumber');
        $roomReservation = RoomReservation::ByConfirmationNumber($confirmationNumber)->first();

        if (!$roomReservation) {
            return response()->json(['error' => 'Booking not found'], Response::HTTP_NOT_FOUND);
        }

        $headGuest = $request->input('headGuest');
        $email = $request->input('email');
        $contact = $request->input('contact');
        $specialRequests = $request->input('specialRequests');

        $roomReservation->setHeadGuest($headGuest);
        $roomReservation->setEmail($email);
        $roomReservation->setContact( $contact);
        $roomReservation->setSpecialRequests($specialRequests);
        $roomReservation->save();

        $roomReservation = new RoomReservationResource($roomReservation);


        // Return a success response or any other response as needed.
        return response()->json([$roomReservation], Response::HTTP_OK);

    }
    
    public function changeBookingOnlyBookedRoom(Request $request){
        $validator = Validator::make($request->all(), [
            'confirmationNumber' => 'required|string|max:255',
            'newRequestedRoomId' => 'required|string',
            'currency' => 'required|string|max:3', 
            'card_number' => 'required|string|digits:16',
            'expire_year' => 'required|string|digits:4',
            'expire_month' => 'required|string|digits:2',
            'cvc' => 'required|string|digits:3',
        ]);

        if ($validator->fails()) {
            return Constants::validationErrorResponse($validator->errors());
        }
        
        $confirmationNumber = $request->input('confirmationNumber');
        $roomReservation = RoomReservation::ByConfirmationNumber($confirmationNumber)->first();
        $numberOfNightStay = $roomReservation->getScheduledCheckInDate()->diffInDays($roomReservation->getScheduledCheckOutDate());

    
        if (!$roomReservation) {
            return response()->json(['error' => 'Booking not found'], 404);
        }
    
        $newRequestedRoomId = $request->input('newRequestedRoomId');
        $room = BookingFactory::getSelectedRoomInfo($newRequestedRoomId);

        if(!$room){
            return response()->json(['message' => Constants::ROOM_NOT_FOUND_MESSAGE], Response::HTTP_NOT_FOUND);
        }

        $roomReservation->setRoomId($room->getRoomId());
        $roomReservation->save();

        $room = Room::ByRoomNumber($room->getRoomId())->first();
        $pricePerNight = $room->getPrice();
        $totalPrice = $numberOfNightStay * $pricePerNight;
    
        $cardNumber = $request->input('card_number');
        $expireYear = $request->input('expire_year');
        $expireMonth = $request->input('expire_month');
        $cvc = $request->input('cvc');

        $payment = BookingFactory::createCharge(
            $totalPrice,
            "DKK",
            $cardNumber,
            $expireYear,
            $expireMonth,
            $cvc,
            $roomReservation->getConfirmationNumber(),
            "Change Room reservation"
        );

        $roomReservation = new RoomReservationResource($roomReservation);

        
        $fullReservationDetails = [
            'room_reservation' => $roomReservation,
            'payment' => $payment,
        ];

        return response()->json([
            $fullReservationDetails
        ], Response::HTTP_OK);
    
    }

    public function changeBookingOnlyBookedDates(Request $request){
        $validator = Validator::make($request->all(), [
            'confirmationNumber' => 'required|string|max:255',
            'newCheckInDate' => 'required|date',
            'newCheckOutDate' => 'required|date|after:newCheckInDate',
            'currency' => 'required|string|max:3', 
            'card_number' => 'required|string|digits:16',
            'expire_year' => 'required|string|digits:4',
            'expire_month' => 'required|string|digits:2',
            'cvc' => 'required|string|digits:3',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    
        $confirmationNumber = $request->input('confirmationNumber');
        $roomReservation = RoomReservation::ByConfirmationNumber($confirmationNumber)->first();
    
        if (!$roomReservation) {
            return response()->json(['error' => 'Booking not found'], Response::HTTP_NOT_FOUND);
        }
    
        $newCheckInDate = Carbon::parse($request->input('newCheckInDate'));
        $newCheckOutDate = Carbon::parse($request->input('newCheckOutDate'));
          $conflictingBookings = RoomReservation::where(RoomReservation::COL_ROOMID, $roomReservation->getRoomId())
        ->where(function ($query) use ($newCheckInDate, $newCheckOutDate) {
            $query->where(function ($query) use ($newCheckInDate, $newCheckOutDate) {
                $query->where(RoomReservation::COL_SCHEDULEDCHECKINDATE, '>=', $newCheckInDate)
                    ->where(RoomReservation::COL_SCHEDULEDCHECKINDATE, '<', $newCheckOutDate);
            })->orWhere(function ($query) use ($newCheckInDate, $newCheckOutDate) {
                $query->where(RoomReservation::COL_SCHEDULEDCHECKOUTDATE, '>', $newCheckInDate)
                    ->where(RoomReservation::COL_SCHEDULEDCHECKOUTDATE, '<=', $newCheckOutDate);
            })->orWhere(function ($query) use ($newCheckInDate, $newCheckOutDate) {
                $query->where(RoomReservation::COL_SCHEDULEDCHECKINDATE, '<=', $newCheckInDate)
                    ->where(RoomReservation::COL_SCHEDULEDCHECKOUTDATE, '>=', $newCheckOutDate);
            });
        })->get();

        if (!$conflictingBookings->isEmpty()) {
            return response()->json(['error' => 'The room is not available for the selected dates.'], Response::HTTP_CONFLICT);
        }
        
        $roomReservation->setScheduledCheckInDate($newCheckInDate);
        $roomReservation->setScheduledCheckOutDate($newCheckOutDate);
        $roomReservation->save();

        $numberOfNightStay = $newCheckInDate->diffInDays($newCheckOutDate);
    
        $room = Room::ByRoomNumber($roomReservation->getRoomId())->first();
        $pricePerNight = $room->getPrice();
        $totalPrice = $numberOfNightStay * $pricePerNight;
    
        $roomReservation = new RoomReservationResource($roomReservation);
        $cardNumber = $request->input('card_number');
        $expireYear = $request->input('expire_year');
        $expireMonth = $request->input('expire_month');
        $cvc = $request->input('cvc');
        
        $payment = BookingFactory::createCharge(
            $totalPrice,
            "DKK",
            $cardNumber,
            $expireYear,
            $expireMonth,
            $cvc,
            $roomReservation->getConfirmationNumber(),
            "New date change for Room reservation"
        );
    
        $fullReservationDetails = [
            'room_reservation' => $roomReservation,
            'payment' => $payment,
        ];
    
        return response()->json($fullReservationDetails, Response::HTTP_OK);
    
    }
    
    public function changeBookingCancelBookedReservation(Request $request){
        $validator = Validator::make($request->all(), [
            'confirmationNumber' => 'required|string|max:255',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    
        $confirmationNumber = $request->input('confirmationNumber');
        $roomReservation = RoomReservation::ByConfirmationNumber($confirmationNumber)->first();
    
        if (!$roomReservation) {
            return response()->json(['error' => 'Booking not found'], Response::HTTP_NOT_FOUND);
        }
    
        $roomReservation->setIsConfirmed(false);
        $roomReservation->save();
  
        return response()->json(['message' => 'Booking reservation canceled successfully'], Response::HTTP_OK);
    
    }

    public function refundBooking(string $confirmationNumber){

    }

    
    
}

