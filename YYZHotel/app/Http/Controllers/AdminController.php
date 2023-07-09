<?php

namespace App\Http\Controllers;

use App\Factories\BookingFactory;
use App\Helper\Constants;
use App\Http\Resources\RomResource;
use App\Models\Room;
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
            return new RomResource($room);
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
        
        return response()->json(['rooms' => $rooms]);
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
    }

    public function removeRoomCateGory(int $roomId){

    }

    public function getOneRoomCategory(int $categoryId){

    }
    
    public function getAllRoomCategories(Request $request){

    }
    // -------------------------------------------------Categories--------------------------------------------------



    public function checkInGuest(Request $request){

    }

    
    public function checkOutGuest(Request $request){

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

    public function getRoomLogHistory(){
        
    }

    public function updateHotelInfo(){
        
    }

    public function searchBookingsBetweenTwoBookingDates(){

    }

    public function searchBookingsBetweenCheckInDateCheckOutDate(){

    }

    

}
