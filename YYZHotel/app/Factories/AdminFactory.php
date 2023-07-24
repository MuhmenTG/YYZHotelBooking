<?php

namespace App\Factories;

use App\Models\Payment;
use App\Models\Room;
use App\Models\RoomCategory;
use App\Models\RoomHistory;
use App\Models\RoomReservation;
use Illuminate\Database\Eloquent\Collection;

class AdminFactory {


    public static function geCategoryNameById(int $categoryId){
        $roomCategory = RoomCategory::ById($categoryId)->first();
        if(!$roomCategory){
            return false;
        }
        $name = $roomCategory->getName();
        return $name;
    }

    public static function lookUpRoomCategory(int $categoryId) : RoomCategory {
        $roomCategory = RoomCategory::ById($categoryId)->first();
        return $roomCategory;
    }

    public static function createRoom(string $roomName, string $categoryId, string $capacity, string $price, string $description){
        $newRoom = new Room();
        $newRoom->setRoomNumber($roomName);
        $newRoom->setCategoryId($categoryId);
        $newRoom->setCapacity($capacity);
        $newRoom->setPrice($price);
        $newRoom->setDescription($description);
        $newRoom->save();
        return $newRoom;
    }

    public static function editRoomDetails(string $roomId, string $roomName, string $categoryId, string $capacity, string $price, string $description){
        $newRoom = Room::ById($roomId)->first();
        $newRoom->setRoomNumber($roomName);
        $newRoom->setCategoryId($categoryId);
        $newRoom->setCapacity($capacity);
        $newRoom->setPrice($price);
        $newRoom->setDescription($description);
        $newRoom->save();
        return $newRoom;
    }

    public static function getAllRooms(){
        $rooms = Room::all();
        return $rooms;
    }

    public static function createRoomCategory(string $name, string $description){
        $roomCategory = new RoomCategory();
        $roomCategory->setName($name);
        $roomCategory->setDescription($description);
        $roomCategory->save();
        return $roomCategory;
    }

    public static function editRoomCategoriesDetails(RoomCategory $roomCategory, string $name, string $description){
        $roomCategory->setName($name);
        $roomCategory->setDescription($description);
        $roomCategory->save();
        return $roomCategory;

    }

    
    public static function logRoomHistory(RoomReservation $reservation)
    {
        $logRoomHistory = new RoomHistory();
        $logRoomHistory->setRoomId($reservation->getRoomId());
        $logRoomHistory->setCheckInDate($reservation->getActualCheckInDate());
        $logRoomHistory->setCheckOutDate($reservation->getActualCheckOutDate());
        $logRoomHistory->setGuestNumber($reservation->getGuests());
        $logRoomHistory->save();
    }

    public static function getCheckedInGuests(): Collection
    {
        return RoomReservation::whereNotNull(RoomReservation::COL_ACTUALCHECKINDATE)
            ->where(RoomReservation::COL_ACTUALCHECKINDATE, '!=', '')
            ->get();
    }

    public static function getCheckedOutGuests(): Collection
    {
        return RoomReservation::whereNotNull(RoomReservation::COL_ACTUALCHECKOUTDATE)
            ->where(RoomReservation::COL_ACTUALCHECKOUTDATE, '!=', '')
            ->get();
    }

    public static function getUpcomingBookings(): Collection
    {
        return RoomReservation::whereNull(RoomReservation::COL_ACTUALCHECKINDATE)
            ->whereNull(RoomReservation::COL_ACTUALCHECKOUTDATE)
            ->get();
    }

    public static function getPastBookings(): Collection
    {
        return RoomReservation::whereNotNull(RoomReservation::COL_ACTUALCHECKINDATE)
            ->whereNotNull(RoomReservation::COL_ACTUALCHECKOUTDATE)
            ->get();
    }

    public static function getStaysWithinTwoDates($startDate, $endDate): Collection
    {
        return RoomReservation::whereDate(RoomReservation::COL_SCHEDULEDCHECKINDATE, '>=', $startDate)
            ->whereDate(RoomReservation::COL_SCHEDULEDCHECKOUTDATE, '<=', $endDate)
            ->get();
    }

    public static function getTotalNumberOfBookings()
    {
        $totalBookings = RoomReservation::count();

        return $totalBookings;
    }

    public static function getAllPayment()
    {
        $allPayments = Payment::all();

        return $allPayments;
    }

    public  static function getTotalPaymentAmountSinceBeginning()
    {
        $totalPaymentAmount = Payment::sum('paymentAmount');

        return $totalPaymentAmount;
    }

}
