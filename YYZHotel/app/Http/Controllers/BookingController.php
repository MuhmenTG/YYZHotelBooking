<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Factories\BookingFactory;
use App\Helper\Constants;
use App\Http\Resources\RomResource;
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

    }

    
    public function changeBookingBookedRoom(Request $request){

    }

    public function changeBookingBookedDates(Request $request){

    }
    
    public function changeBookingCancelBookedReservation(Request $request){

    }

    public function refundBooking(string $confirmationNumber){

    }

    
    
}

