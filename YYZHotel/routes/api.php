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

    Route::get('check-in/{reservationId}', [AdminController::class, 'checkInGuest']);
    Route::get('check-out/{reservationId}', [AdminController::class, 'checkOutGuest']);
    Route::get('getAllCheckedInOutGuests', [AdminController::class, 'getAllCheckedInOutGuests']);
    Route::get('getUpcomingGuestBookings', [AdminController::class, 'getUpcomingGuestBookings']);

    Route::get('occupied-rooms', [AdminController::class, 'getAllOccupiedBookedRooms']);
    Route::get('booking-amount', [AdminController::class, 'getTotalAmountOfBookings']);

    Route::get('user-cases', [AdminController::class, 'getAllUserCases']);
    Route::get('user-reviews-ratings', [AdminController::class, 'getAllUserReviewsRatings']);
    Route::put('user-reviews-ratings/{id}', [AdminController::class, 'markUserReviewsRating']);
    Route::delete('user-reviews-ratings/{id}', [AdminController::class, 'deleteUserReviewsRating']);

    Route::post('room-history', [AdminController::class, 'getRoomLogHistory']);
    Route::post('search-Bookings-Between-Two-BookingDates', [AdminController::class, 'searchBookingsBetweenTwoBookingDates']);
    Route::post('search-Bookings-Between-CheckInDate-CheckOutDate', [AdminController::class, 'searchBookingsBetweenCheckInDateCheckOutDate']);
    
    Route::post('search-availability', [BookingController::class, 'searchRoomAvailability']);
    Route::get('available-room/{roomId}', [BookingController::class, 'selectAvailiableRoom']);
    Route::post('pay-and-book', [BookingController::class, 'payAndBookRoom']);
    Route::get('booking', [BookingController::class, 'retrieveBooking']);
    Route::delete('booking/{reservationId}', [BookingController::class, 'deleteBooking']);
    Route::post('changeBooking-GuestDetails', [BookingController::class, 'changeBookingGuestDetails']);
    Route::post('changeBooking-OnlyBookedRoom', [BookingController::class, 'changeBookingOnlyBookedRoom']);
    Route::post('changeBooking-OnlyBookedDates', [BookingController::class, 'changeBookingOnlyBookedDates']);
    Route::post('changeBooking-CancelBookedReservation', [BookingController::class, 'changeBookingCancelBookedReservation']);
    Route::post('booking/{reservationId}/refund', [BookingController::class, 'refundBooking']);
});

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
});

Route::group(['prefix' => 'booking'], function () {
    Route::post('search-availability', [BookingController::class, 'searchRoomAvailability']);
    Route::get('available-room/{roomId}', [BookingController::class, 'selectAvailiableRoom']);
    Route::post('pay-and-book', [BookingController::class, 'payAndBookRoom']);
    Route::get('booking', [BookingController::class, 'retrieveBooking']);
    Route::delete('booking/{reservationId}', [BookingController::class, 'deleteBooking']);
    Route::post('changeBooking-GuestDetails', [BookingController::class, 'changeBookingGuestDetails']);
    Route::post('changeBooking-OnlyBookedRoom', [BookingController::class, 'changeBookingOnlyBookedRoom']);
    Route::post('changeBooking-OnlyBookedDates', [BookingController::class, 'changeBookingOnlyBookedDates']);
    Route::post('changeBooking-CancelBookedReservation', [BookingController::class, 'changeBookingCancelBookedReservation']);
    Route::post('booking/{reservationId}/refund', [BookingController::class, 'refundBooking']);
});

Route::group(['prefix' => 'public'], function () {
    Route::post('contact-form', [PublicController::class, 'sendThroughContactForm']);
    Route::post('make-review', [PublicController::class, 'makeReviewOfStay']);
    Route::get('services', [PublicController::class, 'ourServices']);
    Route::get('about-us', [PublicController::class, 'aboutUs']);
    Route::get('our-room', [PublicController::class, 'ourRoom']);
});