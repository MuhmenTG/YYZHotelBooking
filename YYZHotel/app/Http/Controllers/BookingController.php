<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Factories\BookingFactory;
use App\Helper\Constants;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\RomResource;
use App\Http\Resources\RoomReservationResource;
use App\Models\Payment;
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
        'checkInDate'   =>  'required|date',
        'checkOutDate'  =>  'required|date|after:checkInDate',
        ]);
    
        if ($validator->fails()) {
            return Constants::validationErrorResponse($validator->errors());
        }
        
        $checkInDate = Carbon::parse($request->input('checkInDate'));
        $checkOutDate = Carbon::parse($request->input('checkOutDate'));

        $avaliableRooms = BookingFactory::getAllAvailableRooms($checkInDate, $checkOutDate);
        if(empty($avaliableRooms)){
            return response()->json(['message' => Constants::NOT_AVALIABLE_ROOMS_ON_REQUESTED_DATES], Response::HTTP_NOT_FOUND);
        }

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
            'roomSeletionDetails' => $avaliableConfirm
        ]);
    }

    public function payAndBookRoom(Request $request){
        $validator = Validator::make($request->all(), [
            'headGuest'     => 'required|string|max:255',
            'email'         => 'required|email|max:255',
            'contact'       => 'required|string|max:20',
            'roomId'        => 'required|string',
            'checkInDate'   => 'required|date',
            'checkOutDate'  => 'required|date|after:checkInDate',
            'numberOfGuest' => 'required|integer|min:1',
            'specialRequests' => 'nullable|string',
            'transactionId' => 'required|string|max:255',
            'currency'      => 'required|string|max:3', 
            'cardNumber'    => 'required|string|digits:16',
            'expireYear'    => 'required|string|digits:4',
            'expireMonth'   => 'required|string|digits:2',
            'cvc'           => 'required|string|digits:3',
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
        $currency = $request->input('currency');
        $cardNumber = $request->input('cardNumber');
        $expireYear = $request->input('expireYear');
        $expireMonth = $request->input('expireMonth');
        $cvc = $request->input('cvc');
        
        $roomAvaliabilityCheck = BookingFactory::validateRoomExistBetweenDates($roomId, $checkInDate, $checkOutDate);
        if ($roomAvaliabilityCheck) {
            return response()->json(['message' => Constants::NOT_AVALIABLE_ROOMS_ON_REQUESTED_DATES], Response::HTTP_BAD_REQUEST);
        }


        $roomReservation = BookingFactory::createRoomReservation(
            $headGuest,
            $email,
            $contact,
            intval($roomId),
            $checkInDate,
            $checkOutDate,
            intval($numberOfGuest),
            $specialRequests,
            $transactionId
        );

        if(!$roomReservation){
            return response()->json(['message' => Constants::BOOKING_COULD_NOT_BE_DONE], Response::HTTP_BAD_REQUEST);
        }

        $totalPrice = BookingFactory::calculateTotalPrice($checkInDate, $checkOutDate, $roomId);

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

        $roomReservation = new RoomReservationResource($roomReservation);
        $payment = new PaymentResource($payment);
        
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
        $payment = Payment::where(Payment::COL_CONFIRMATIONNUMBER, $confirmationNumber)->first();

        if (!$reservation) {
            return response()->json([
                'message' => Constants::ROOM_RESERVATION_NOT_FOUND_MESSAGE
            ], Response::HTTP_NOT_FOUND);
        }

        $reservation = new RoomReservationResource($reservation);
        $payment = new PaymentResource($payment);
        
        $fullReservationDetails = [
            'room_reservation' => $reservation,
            'payment' => $payment,
        ];
        
        return response()->json([
            $fullReservationDetails
        ], Response::HTTP_OK);
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
            'headGuest'          => 'nullable|string|max:255',
            'email'              => 'nullable|email|max:255',
            'contact'            => 'nullable|string|max:20',
            'specialRequests'    => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $confirmationNumber = $request->input('confirmationNumber');        
        
        $roomReservation = BookingFactory::lookUpRoomReservation($confirmationNumber);
        if (!$roomReservation) {
            return response()->json(['error' => 'Booking not found'], Response::HTTP_NOT_FOUND);
        }

        if ($request->has('headGuest')) {
            $roomReservation->setHeadGuest($request->input('headGuest'));
        }
    
        if ($request->has('email')) {
            $roomReservation->setEmail($request->input('email'));
        }
    
        if ($request->has('contact')) {
            $roomReservation->setContact($request->input('contact'));
        }
    
        if ($request->has('specialRequests')) {
            $roomReservation->setSpecialRequests($request->input('specialRequests'));
        }    

        $roomReservation = new RoomReservationResource($roomReservation);

        return response()->json([$roomReservation], Response::HTTP_OK);

    }
    
    public function changeBookingOnlyBookedRoom(Request $request){
        $validator = Validator::make($request->all(), [
            'confirmationNumber' => 'required|string|max:255',
            'newRequestedRoomId' => 'required|string',
            'currency'           => 'required|string|max:3', 
            'card_number'        => 'required|string|digits:16',
            'expire_year'        => 'required|string|digits:4',
            'expire_month'       => 'required|string|digits:2',
            'cvc'                => 'required|string|digits:3',
        ]);

        if ($validator->fails()) {
            return Constants::validationErrorResponse($validator->errors());
        }
        
        $confirmationNumber = $request->input('confirmationNumber');
        $newRequestedRoomId = $request->input('newRequestedRoomId');
        $cardNumber = $request->input('card_number');
        $expireYear = $request->input('expire_year');
        $expireMonth = $request->input('expire_month');
        $cvc = $request->input('cvc');

        $roomReservation = BookingFactory::lookUpRoomReservation($confirmationNumber);
        if (!$roomReservation) {
            return response()->json(['error' => 'Booking not found'], Response::HTTP_NOT_FOUND);
        }
        
        $room = BookingFactory::lookUpRoom(intval($newRequestedRoomId));

        $specificRoom = BookingFactory::getSelectedRoomInfo($room);
        if(!$specificRoom){
            return response()->json(['message' => Constants::ROOM_NOT_FOUND_MESSAGE], Response::HTTP_NOT_FOUND);
        }
        
        $roomAvaliabilityCheck = BookingFactory::validateRoomExistBetweenDates($newRequestedRoomId, $roomReservation->getScheduledCheckInDate(), $roomReservation->getScheduledCheckOutDate());
        if($roomAvaliabilityCheck){
            return response()->json(['message' => Constants::NOT_AVALIABLE_ROOMS_ON_REQUESTED_DATES], Response::HTTP_BAD_REQUEST);
        }

        $totalPrice = BookingFactory::calculateTotalPrice($roomReservation->getScheduledCheckInDate(), $roomReservation->getScheduledCheckOutDate(), $newRequestedRoomId);

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

        $roomReservation->setRoomId($specificRoom->getRoomId());
        $roomReservation->save();

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
        $cardNumber = $request->input('card_number');
        $expireYear = $request->input('expire_year');
        $expireMonth = $request->input('expire_month');
        $cvc = $request->input('cvc');
        $newCheckInDate = Carbon::parse($request->input('newCheckInDate'));
        $newCheckOutDate = Carbon::parse($request->input('newCheckOutDate'));
        
        $roomReservation = BookingFactory::lookUpRoomReservation($confirmationNumber);
        if (!$roomReservation) {
            return response()->json(['error' => 'Booking not found'], Response::HTTP_NOT_FOUND);
        }

        $roomAvaliabilityCheck = BookingFactory::validateRoomExistBetweenDates($roomReservation->getRoomId(), $newCheckInDate, $newCheckOutDate);
        if($roomAvaliabilityCheck){
            return response()->json(['message' => Constants::NOT_AVALIABLE_ROOMS_ON_REQUESTED_DATES], Response::HTTP_BAD_REQUEST);
        }

        $roomReservation->setScheduledCheckInDate($newCheckInDate);
        $roomReservation->setScheduledCheckOutDate($newCheckOutDate);
        $roomReservation->save();

        $totalPrice = BookingFactory::calculateTotalPrice($newCheckInDate, $newCheckOutDate, $roomReservation->getRoomId());

        $roomReservation = new RoomReservationResource($roomReservation);
        
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

        $payment = new PaymentResource($payment);

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

        $roomReservation = new RoomReservationResource($roomReservation);

        return response()->json([
            'message' => 'Booking details updated successfully',
            'data' => $roomReservation,
        ], Response::HTTP_OK);    
    }

    
    
}

