<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PublicController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'admin'], function () {
    Route::post('createRoom', [AdminController::class, 'createRoom']);
    Route::post('editRoom', [AdminController ::class, 'editRoom']);
    Route::delete('removeRoom/{roomId}', [AdminController::class, 'removeRoom']);
    Route::get('retriveRoomSpecs/{roomId}', [AdminController::class, 'getSpecificRoom']);
    Route::get('getAllRooms', [AdminController::class, 'getAllRooms']);

    Route::post('createRoomCategory', [AdminController::class, 'createRoomCategory']);
    Route::post('updateRoomCategory', [AdminController::class, 'editRoomCategory']);
    Route::delete('deleteRoomCategory/{categoryId}', [AdminController::class, 'removeRoomCategory']);
    Route::get('retriveRoomCategory/{categoryId}', [AdminController::class, 'getOneRoomCategory']);
    Route::get('getAllRoomCategory', [AdminController::class, 'getAllRoomCategories']);

    Route::post('check-in', [AdminController::class, 'checkInGuest']);
    Route::post('check-out', [AdminController::class, 'checkOutGuest']);

    Route::get('occupied-rooms', [AdminController::class, 'getAllOccupiedBookedRooms']);
    Route::get('booking-amount', [AdminController::class, 'getTotalAmountOfBookings']);

    Route::get('user-cases', [AdminController::class, 'getAllUserCases']);
    Route::get('user-reviews-ratings', [AdminController::class, 'getAllUserReviewsRatings']);
    Route::put('user-reviews-ratings/{id}', [AdminController::class, 'markUserReviewsRating']);
    Route::delete('user-reviews-ratings/{id}', [AdminController::class, 'deleteUserReviewsRating']);

    Route::get('room-history', [AdminController::class, 'getRoomLogHistory']);
    Route::get('search-Bookings-Between-Two-BookingDates', [AdminController::class, 'searchBookingsBetweenTwoBookingDates']);
    Route::get('search-Bookings-Between-CheckInDate-CheckOutDate', [AdminController::class, 'searchBookingsBetweenCheckInDateCheckOutDate']);

    
    Route::post('search-availability', [BookingController::class, 'searchRoomAvailability']);
    Route::get('available-room/{roomId}', [BookingController::class, 'selectAvailiableRoom']);
    Route::post('pay-and-book', [BookingController::class, 'payAndBookRoom']);
    Route::get('booking', [BookingController::class, 'retrieveBooking']);
    Route::delete('booking/{reservationId}', [BookingController::class, 'deleteBooking']);
    Route::put('booking/{reservationId}', [BookingController::class, 'modifyBooking']);
    Route::post('booking/{reservationId}/refund', [BookingController::class, 'refundBooking']);
    Route::post('booking/{reservationId}/cancel', [BookingController::class, 'cancelBooking']);
});

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
});

Route::group(['prefix' => 'booking'], function () {
    Route::post('search-availability', [BookingController::class, 'searchRoomAvailability']);
    Route::get('available-rooms/{roomId}', [BookingController::class, 'selectAvailiableRoom']);
    Route::post('pay-and-book', [BookingController::class, 'payAndBookRoom']);
    Route::get('booking', [BookingController::class, 'retrieveBooking']);
    Route::delete('booking/{reservationId}', [BookingController::class, 'deleteBooking']);
    Route::put('booking/{reservationId}', [BookingController::class, 'modifyBooking']);
    Route::post('booking/{reservationId}/refund', [BookingController::class, 'refundBooking']);
    Route::post('booking/{reservationId}/cancel', [BookingController::class, 'cancelBooking']);
});

Route::group(['prefix' => 'public'], function () {
    Route::post('contact-form', [PublicController::class, 'sendThroughContactForm']);
    Route::post('make-review', [PublicController::class, 'makeReviewOfStay']);
    Route::get('services', [PublicController::class, 'ourServices']);
    Route::get('about-us', [PublicController::class, 'aboutUs']);
    Route::get('our-room', [PublicController::class, 'ourRoom']);
});