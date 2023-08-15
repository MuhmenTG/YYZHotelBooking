<?php

namespace App\Http\Controllers;

use App\Factories\BookingFactory;
use App\Helper\Constants;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\RoomReservationResource;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class PublicController extends Controller
{
    //

    public function sendThoughContactForm(Request $request){

    }

    public function makeReviewOfStay(Request $request){

    }

    public function ourServices(){

    }

    public function aboutUs(){

    }

    public function ourRoom(){
        
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
}
