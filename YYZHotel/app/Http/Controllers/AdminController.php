<?php

namespace App\Http\Controllers;

use App\Factories\BookingFactory;
use App\Helper\Constants;
use App\Http\Resources\RomResource;
use App\Http\Resources\RoomCategoryResource;
use App\Http\Resources\RoomReservationResource;
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


    public function getAllOccipiedBookedRooms() {


    }

    public function getTotalAmmountofBookins(){

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
