<?php

namespace App\Factories;

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
}