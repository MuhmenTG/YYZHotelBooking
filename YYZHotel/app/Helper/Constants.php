<?php

namespace App\Helper;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class Constants {
    public const ROOM_RESERVATION_NOT_FOUND_MESSAGE = 'Room reservation not found';
    public const ROOM_RESERVATION_DELETED_MESSAGE = 'Room reservation is deleted succesfully.';
    public const ROOM_RESERVATION_DELETION_FAILED_MESSAGE = 'An error occoured. Room reservation could not deleted.'; 
    public const ROOM_NOT_FOUND_MESSAGE = 'The requested room could not be found.';
    public const ROOM_CATEGORY_NOT_FOUND_MESSAGE = 'The requested room category could not be found.';

    public static function validationErrorResponse($errors): JsonResponse
    {
        return response()->json(['error' => 'Validation failed', 'details' => $errors], Response::HTTP_BAD_REQUEST);
    }

}