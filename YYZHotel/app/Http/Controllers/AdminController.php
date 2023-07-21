<?php

namespace App\Http\Controllers;

use App\Factories\BookingFactory;
use App\Helper\Constants;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\RomResource;
use App\Http\Resources\RoomCategoryResource;
use App\Http\Resources\RoomReservationResource;
use App\Models\Payment;
use App\Models\Room;
use App\Models\RoomCategory;
use App\Models\RoomHistory;
use App\Models\RoomReservation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends Controller
{
    //

    public function createRoom(Request $request){
     
        $validator = Validator::make($request->all(), [
            'roomName'   => 'required|string',
            'categoryId' => 'required|numeric',
            'capacity'   => 'required|integer',
            'price'      => 'required|numeric',
            'description'=> 'nullable|string',
        ]);

        if ($validator->fails()) {
            return Constants::validationErrorResponse($validator->errors());
        }

        $roomName = $request->input('roomName');
        $categoryId = $request->input('categoryId');
        $capacity = $request->input('capacity');
        $price = $request->input('price');
        $description = $request->input('description');

        $newRoom = new Room();
        $newRoom->setRoomNumber($roomName);
        $newRoom->setCategoryId($categoryId);
        $newRoom->setCapacity($capacity);
        $newRoom->setPrice($price);
        $newRoom->setDescription($description);
        $newRoom->save();

        if($newRoom){
            return new RomResource($newRoom);
        }
    }

    public function editRoom(Request $request){

        $validator = Validator::make($request->all(), [
            'roomId'     => 'required|numeric',
            'roomName'   => 'required|string',
            'categoryId' => 'required|numeric',
            'capacity'   => 'required|integer',
            'price'      => 'required|numeric',
            'description'=> 'nullable|string',
        ]);

        if ($validator->fails()) {
            return Constants::validationErrorResponse($validator->errors());
        }

        
        $roomId = $request->input('roomId');

        $roomName = $request->input('roomName');
        $categoryId = $request->input('categoryId');
        $capacity = $request->input('capacity');
        $price = $request->input('price');
        $description = $request->input('description');

        $room = BookingFactory::lookUpRoom($roomId);

        if(!$room){
            return response()->json(['message' => Constants::ROOM_NOT_FOUND_MESSAGE], Response::HTTP_NOT_FOUND);
        }

        $newRoom = Room::ById($roomId)->first();
        $newRoom->setRoomNumber($roomName);
        $newRoom->setCategoryId($categoryId);
        $newRoom->setCapacity($capacity);
        $newRoom->setPrice($price);
        $newRoom->setDescription($description);
        $newRoom->save();

        if($newRoom){
            return new RomResource($newRoom);
        }
    }
    
    public function removeRoom(int $roomId)
    {
        $room = BookingFactory::lookUpRoom($roomId);
    
        if (!$room) {
            return response()->json(['message' => Constants::ROOM_NOT_FOUND_MESSAGE], Response::HTTP_NOT_FOUND);
        }
    
        $room->delete();
    
        return response()->json(['message' => 'Room deleted successfully']);
    }    

    public function getSpecificRoom(int $roomId){
        $room = BookingFactory::lookUpRoom($roomId);

        if(!$room){
            return response()->json(['message' => Constants::ROOM_NOT_FOUND_MESSAGE], Response::HTTP_NOT_FOUND);
        }

        $room = new RomResource($room);

        return response()->json([
            'roomDetails' => $room
        ]);
    }

    public function getAllRooms()
    {
        $rooms = Room::all();
    
        if ($rooms->isEmpty()) {
            return response()->json(['message' => 'No rooms found'], Response::HTTP_NOT_FOUND);
        }

        $rooms = RomResource::collection($rooms);

        return response()->json(['rooms' => $rooms], Response::HTTP_OK);
    }
    

    // -------------------------------------------------Categories--------------------------------------------------
    public function createRoomCategory(Request $request) {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string',
            'description' => 'required|string',
        ]);
    
        if ($validator->fails()) {
            return Constants::validationErrorResponse($validator->errors());
        }

        $name = $request->input('name');
        $description = $request->input('description');

        $roomCategory = new RoomCategory();
        $roomCategory->setName($name);
        $roomCategory->setDescription($description);
        $roomCategory->save();
        
        $roomCategory = new RoomCategoryResource($roomCategory);
        
        return response()->json([
            'roomCategoryDetails' => $roomCategory
        ], Response::HTTP_OK);
   }
    
    public function editRoomCategory(Request $request) {
        $validator = Validator::make($request->all(), [
            'categoryId'  => 'required|numeric',
            'name'        => 'required|string',
            'description' => 'required|string',
        ]);
    
        if ($validator->fails()) {
            return Constants::validationErrorResponse($validator->errors());
        }

        $categoryId = $request->input('categoryId');
        $name = $request->input('name');
        $description = $request->input('description');

        $roomCategory = RoomCategory::ById($categoryId)->first();
        if(!$roomCategory){
            return response()->json(['message' => Constants::ROOM_CATEGORY_NOT_FOUND_MESSAGE], Response::HTTP_NOT_FOUND);
        }

        $roomCategory->setName($name);
        $roomCategory->setDescription($description);
        $roomCategory->save();

        $roomCategory = new RoomCategoryResource($roomCategory);
        
        return response()->json([
            'roomCategoryDetails' => $roomCategory
        ], Response::HTTP_OK);
    }

    public function removeRoomCategory(int $categoryId){

        $roomCategory = RoomCategory::ById($categoryId)->first();
    
        if (!$roomCategory) {
            return response()->json(['message' => Constants::ROOM_CATEGORY_NOT_FOUND_MESSAGE], Response::HTTP_NOT_FOUND);
        }
    
        $roomCategory->delete();
    
        return response()->json(['message' => 'Room category deleted successfully'], Response::HTTP_OK);
    }

    public function getOneRoomCategory(int $categoryId){

        $roomCategory = RoomCategory::ById($categoryId)->first();
    
        if (!$roomCategory) {
            return response()->json(['message' => Constants::ROOM_CATEGORY_NOT_FOUND_MESSAGE], Response::HTTP_NOT_FOUND);
        }

        $roomCategory = new RoomCategoryResource($roomCategory);

        return response()->json([
            'roomCategoryDetails' => $roomCategory
        ], Response::HTTP_OK);
    }
    
    public function getAllRoomCategories(){

        $roomCategory = RoomCategory::all();
    
        if ($roomCategory->isEmpty()) {
            return response()->json(['message' => 'No rooms found'], Response::HTTP_NOT_FOUND);
        }

        $roomCategory = RoomCategoryResource::collection($roomCategory);

        return response()->json(['Room Category' => $roomCategory], Response::HTTP_OK);
    }
    // -------------------------------------------------Categories--------------------------------------------------



    public function checkInGuest(string $confirmationNumber){
        $reservation = BookingFactory::lookUpRoomReservation($confirmationNumber);
        if(!$reservation && $reservation == null){
            return response()->json(['message' => Constants::ROOM_RESERVATION_NOT_FOUND_MESSAGE], Response::HTTP_NOT_FOUND);
        }
        
        $currentDate = Carbon::now(); 
        
        if($currentDate < $reservation->getScheduledCheckInDate()){
            return response()->json(['message' => 'The guest can not check in before scheduled check in date'], Response::HTTP_METHOD_NOT_ALLOWED);
        }


        $reservation = new RoomReservationResource($reservation);

        $reservation->setActualCheckInDate($currentDate);
        $reservation->save();

        return response()->json(['CheckedIn' => $reservation], Response::HTTP_OK);

    }

    
    public function checkOutGuest(string $confirmationNumber){
        $reservation = BookingFactory::lookUpRoomReservation($confirmationNumber);
        if(!$reservation && $reservation == null){
            return response()->json(['message' => Constants::ROOM_RESERVATION_NOT_FOUND_MESSAGE], Response::HTTP_NOT_FOUND);
        }

        if (empty($reservation->getActualCheckInDate())) {
            return response()->json(['message' => 'The guest is not checked in yet and can not be checked out there for'], Response::HTTP_METHOD_NOT_ALLOWED);
        }
        
        $reservation->setActualCheckOutDate(Carbon::now());
        $reservation->save();

        $logRoomHistory = new RoomHistory();
        $logRoomHistory->setRoomId($reservation->getRoomId());
        $logRoomHistory->setCheckInDate($reservation->getActualCheckInDate());
        $logRoomHistory->setCheckInDate($reservation->getActualCheckOutDate());
        $logRoomHistory->setGuestNumber($reservation->getGuests());
        $logRoomHistory->save();

        $reservation = new RoomReservationResource($reservation);

        return response()->json(['CheckedOut' => $reservation], Response::HTTP_OK);

    }

    public function getAllCheckedInOutGuests(){
        $checkedInGuests = RoomReservation::whereNotNull(RoomReservation::COL_ACTUALCHECKINDATE)
        ->where(RoomReservation::COL_ACTUALCHECKINDATE, '!=', '')
        ->get();

        $checkedOutGuests = RoomReservation::whereNotNull(RoomReservation::COL_ACTUALCHECKOUTDATE)
        ->where(RoomReservation::COL_ACTUALCHECKOUTDATE, '!=', '')
        ->get();

        if ($checkedInGuests->isEmpty() && $checkedOutGuests->isEmpty()) {
            return response()->json(['message' => 'There are no checked-in or checked-out guests.'], Response::HTTP_METHOD_NOT_ALLOWED);
        } 

        $checkedInGuests = RoomReservationResource::collection($checkedInGuests);
        $checkedOutGuests = RoomReservationResource::collection($checkedOutGuests);

        $data = [
            'checkedInGuests' => $checkedInGuests,
            'checkedOutGuests' => $checkedOutGuests,
        ];

        return response()->json(['CheckedOutAndOut' => $data], Response::HTTP_OK);
        
    }
    
    public function getUpcomingGuestBookings() {
        $upcomingBookings = RoomReservation::whereNull(RoomReservation::COL_ACTUALCHECKINDATE)
        ->whereNull(RoomReservation::COL_ACTUALCHECKOUTDATE)
        ->get();

        if ($upcomingBookings->isEmpty()) {
            return response()->json(['message' => 'There are no upcoming booking.'], Response::HTTP_NOT_FOUND);
        } 

        $upcomingBookings = RoomReservationResource::collection($upcomingBookings);

        return response()->json(['upcomingBookings' => $upcomingBookings], Response::HTTP_OK);
    }

    public function getPastBookings(){
        $pastBookings = RoomReservation::whereNotNull(RoomReservation::COL_ACTUALCHECKINDATE)
        ->whereNotNull(RoomReservation::COL_ACTUALCHECKOUTDATE)
        ->get();

        if ($pastBookings->isEmpty()) {
            return response()->json(['message' => 'There are no past booking.'], Response::HTTP_NOT_FOUND);
        } 

        $pastBookings = RoomReservationResource::collection($pastBookings);

        return response()->json(['pastBookings' => $pastBookings], Response::HTTP_OK);
    }

    public function getStaysWithinThisWeek(){
        $startOfWeek = Carbon::now()->startOfWeek(); 
        $endOfWeek = Carbon::now()->endOfWeek(); 
    
        $staysWithinThisWeek = RoomReservation::whereDate(RoomReservation::COL_SCHEDULEDCHECKINDATE, '>=', $startOfWeek)
            ->whereDate(RoomReservation::COL_SCHEDULEDCHECKOUTDATE, '<=', $endOfWeek)
            ->get();
    
        if ($staysWithinThisWeek->isEmpty()) {
            return response()->json(['message' => 'No stays within this week'], Response::HTTP_OK);
        }
    
        $staysWithinThisWeek = RoomReservationResource::collection($staysWithinThisWeek);

        return response()->json(['staysWithinThisWeek' => $staysWithinThisWeek], Response::HTTP_OK);
    
    }

    public function getStaysWithinThisMonth(){
        $startOfMonth = Carbon::now()->startOfMonth(); 
        $endOfMonth = Carbon::now()->endOfMonth();

        $staysWithinThisWeek = RoomReservation::whereDate(RoomReservation::COL_SCHEDULEDCHECKINDATE, '>=', $startOfMonth)
            ->whereDate(RoomReservation::COL_SCHEDULEDCHECKOUTDATE, '<=', $endOfMonth)
            ->get();
    
        if ($staysWithinThisWeek->isEmpty()) {
            return response()->json(['message' => 'No stays within this month'], Response::HTTP_OK);
        }
    
        $staysWithinThisWeek = RoomReservationResource::collection($staysWithinThisWeek);

        return response()->json(['staysWithinThisWeek' => $staysWithinThisWeek], Response::HTTP_OK);
    
    }

    public function getStaysWithinThreeMonths(){
        $startOfThreeMonths = Carbon::now()->startOfMonth()->addMonths(1); 
        $endOfThreeMonths = Carbon::now()->endOfMonth()->addMonths(3); 

        $staysWithinThreeMonths = RoomReservation::whereDate(RoomReservation::COL_SCHEDULEDCHECKINDATE, '>=', $startOfThreeMonths)
            ->whereDate(RoomReservation::COL_SCHEDULEDCHECKOUTDATE, '<=', $endOfThreeMonths)
            ->get();

        if ($staysWithinThreeMonths->isEmpty()) {
            return response()->json(['message' => 'No stays within the next three months'], Response::HTTP_OK);
        }

        $staysWithinThreeMonths = RoomReservationResource::collection($staysWithinThreeMonths);

        return response()->json(['staysWithinThisWeek' => $staysWithinThreeMonths], Response::HTTP_OK);
    }

    public function getTotalNumberOfBookings()
    {
        $totalBookings = RoomReservation::count();
    
        if ($totalBookings === 0) {
            return response()->json(['message' => 'No bookings found'], 200);
        }

        return response()->json(['Bookings' => $totalBookings], Response::HTTP_OK);
    }

    public function getAllPayment()
    {
        $allPayments = Payment::all();

        if ($allPayments->isEmpty()) {
            return response()->json(['message' => 'No payments found'], 200);
        }

        $allPayments = PaymentResource::collection($allPayments);

        return response()->json($allPayments, 200);
    }

    public function getTotalPaymentAmounSinceBegining()
    {
        $totalPaymentAmount = Payment::sum('paymentAmount');

        if ($totalPaymentAmount === null) {
            return response()->json(['message' => 'No payments found'], 200);
        }

        return response()->json(['Total ernings in DKK' => $totalPaymentAmount], 200);
    }

    public function getTotalPaymentAmountThisWeek()
    {
        $startOfWeek = Carbon::now()->startOfWeek(); 
        $endOfWeek = Carbon::now()->endOfWeek();     

        $totalPaymentAmountThisWeek = Payment::whereBetween(Payment::COL_PAYMENTTRANSACTIONDATE, [$startOfWeek, $endOfWeek])
            ->sum('paymentAmount');

        if ($totalPaymentAmountThisWeek === null) {
            return response()->json(['message' => 'No payments found for this week'], 200);
        }

        return response()->json(['Total ernings in DKK this week' => $totalPaymentAmountThisWeek], 200);
    }

    public function getTotalPaymentAmountThisMonth()
    {
        $startOfMonth = Carbon::now()->startOfMonth(); 
        $endOfMonth = Carbon::now()->endOfMonth();     
        
        $totalPaymentAmountThisMonth = Payment::whereBetween(Payment::COL_PAYMENTTRANSACTIONDATE, [$startOfMonth, $endOfMonth])
            ->sum('paymentAmount');

        if ($totalPaymentAmountThisMonth === null) {
            return response()->json(['message' => 'No payments found for this month'], 200);
        }

        return response()->json(['Total ernings in DKK this month' => $totalPaymentAmountThisMonth], 200);
    }

    public function getPaymentTransaction(string $transactionId)
    {
        $payment = Payment::where(Payment::COL_PAYMENTTRANSACTIONID, $transactionId)->first();

        if (!$payment) {
            return response()->json(['message' => 'Payment transaction not found'], 404);
        }

        $payment = new PaymentResource($payment);

        return response()->json($payment, 200);
    }

    public function getPaymentHistoryForBookingByConfirmtionNumber(string $confirmationNumber)
    {
        $paymentHistory = Payment::where(Payment::COL_CONFIRMATIONNUMBER, $confirmationNumber)->get();

        if ($paymentHistory->isEmpty()) {
            return response()->json(['message' => 'No payment history found for the booking'], 200);
        }

        $paymentHistory = RoomReservationResource::collection($paymentHistory);

        return response()->json($paymentHistory, 200);
    }


    public function getAllUserCaases(){

    }

    public function getAllUserReviewsRatings(){

    }

    public function markUserReviewsRating(){

    }

    public function deleteUserReviewsRating(){

    }

    public function getRoomLogHistory(Request $request){
        $validator = Validator::make($request->all(), [
            'roomId' => 'nullable|numeric'
        ]);
    
        if ($validator->fails()) {
            return Constants::validationErrorResponse($validator->errors());
        }
    
        $roomId = $request->input('roomId');
    
        if ($roomId) {
            $room = BookingFactory::lookUpRoom($roomId);
    
            if (!$room) {
                return response()->json(['message' => Constants::ROOM_NOT_FOUND_MESSAGE], Response::HTTP_NOT_FOUND);
            }
    
            $roomHistory = RoomHistory::where(RoomHistory::COL_ROOMID, '=', $roomId)->first();
    
            if (!$roomHistory) {
                return response()->json(['message' => 'There are no recorded room logs with that ID.'], Response::HTTP_NOT_FOUND);
            }
    
            return response()->json(['roomHistoryLog' => $roomHistory], Response::HTTP_OK);
        }
    
        $allRoomHistory = RoomHistory::all();
    
        if ($allRoomHistory->isEmpty()) {
            return response()->json(['message' => 'There are no recorded room logs.'], Response::HTTP_NOT_FOUND);
        }
    
        return response()->json(['roomHistoryLog' => $allRoomHistory], Response::HTTP_OK);
            
    }

    public function updateHotelInfo(){
        
    }

    public function searchBookingsBetweenTwoBookingDates(Request $request){
        $validator = Validator::make($request->all(), [
            'startDate' => 'required|date',
            'endDate'   => 'required|date|after_or_equal:startDate',
        ]);
    
        if ($validator->fails()) {
            return Constants::validationErrorResponse($validator->errors());
        }
    
        $startDate = Carbon::parse($request->input('startDate'));
        $endDate = Carbon::parse($request->input('endDate'));
    
        $bookings = RoomReservation::whereBetween(RoomReservation::COL_BOOKINGDATE, [$startDate, $endDate])->get();

        $bookings = RoomReservationResource::collection($bookings);

        if(!$bookings){
            return response()->json(['message' => 'There are no bookings found between the dates.'], Response::HTTP_NOT_FOUND);
        }
    
        return response()->json(['bookings' => $bookings], Response::HTTP_OK);
    
    }

    public function searchBookingsBetweenCheckInDateCheckOutDate(Request $request){
        $validator = Validator::make($request->all(), [
            'startDate' => 'required|date',
            'endDate'   => 'required|date|after_or_equal:startDate',
        ]);
    
        if ($validator->fails()) {
            return Constants::validationErrorResponse($validator->errors());
        }
    
        $startDate = Carbon::parse($request->input('startDate'));
        $endDate = Carbon::parse($request->input('endDate'));
    
        $bookings = RoomReservation::where(function ($query) use ($startDate, $endDate) {
            $query->whereBetween(RoomReservation::COL_SCHEDULEDCHECKINDATE, [$startDate, $endDate])
                ->orWhereBetween(RoomReservation::COL_SCHEDULEDCHECKOUTDATE, [$startDate, $endDate]);
        })->get();

        if(!$bookings){
            return response()->json(['message' => 'There are no bookings found between the dates.'], Response::HTTP_NOT_FOUND);
        }

        $bookings = RoomReservationResource::collection($bookings);
    
        return response()->json(['bookings' => $bookings], Response::HTTP_OK);
    }

    

}
