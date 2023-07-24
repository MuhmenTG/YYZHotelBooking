<?php

namespace App\Factories;

use App\Models\Room;
use App\Models\RoomCategory;

class AdminFactory {


    public static function geCategoryNameById(int $categoryId){
        $roomCategory = RoomCategory::ById($categoryId)->first();
        if(!$roomCategory){
            return false;
        }
        $name = $roomCategory->getName();
        return $name;
    }

    public static function createRoom(string $roomName, int $categoryId, int $capacity, float $price, string $description){
        $newRoom = new Room();
        $newRoom->setRoomNumber($roomName);
        $newRoom->setCategoryId($categoryId);
        $newRoom->setCapacity($capacity);
        $newRoom->setPrice($price);
        $newRoom->setDescription($description);
        $newRoom->save();
        return $newRoom;
    }
}