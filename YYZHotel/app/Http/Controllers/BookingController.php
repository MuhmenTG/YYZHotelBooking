<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Factories\BookingFactory;
use App\Helper\Constants;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\RomResource;
use App\Http\Resources\RoomReservationResource;
use App\Models\Payment;
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

       
    
}

