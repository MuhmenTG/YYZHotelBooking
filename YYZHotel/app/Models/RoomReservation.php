<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomReservation extends Model
{
    use HasFactory;
    

	protected $table = 'room_reservations';
	protected $primaryKey = 'id';
//	protected $guarded = [];
//	protected $fillable = [];

	const COL_ID = 'id';
	const COL_CONFIRMATIONNUMBER = 'confirmationNumber';
	const COL_HEADGUEST = 'headGuest';
	const COL_EMAIL = 'email';
	const COL_CONTACT = 'contact';
	const COL_ROOMID = 'roomId';
	const COL_SCHEDULEDCHECKINDATE = 'ScheduledCheckInDate';
	const COL_SCHEDULEDCHECKOUTDATE = 'ScheduledCheckOutDate';
	const COL_ACTUALCHECKINDATE = 'ActualCheckInDate';
	const COL_ACTUALCHECKOUTDATE = 'ActualCheckOutDate';
	const COL_GUESTS = 'guests';
	const COL_SPECIALREQUESTS = 'specialRequests';
	const COL_ISCONFIRMED = 'isConfirmed';
	const COL_PAYMENTID = 'paymentId';
	const COL_CREATED_AT = 'created_at';
	const COL_UPDATED_AT = 'updated_at';

	/*
	 * Eloquent Scopes
	 */

	public function scopeById($query, $val) {
		$query->where('id', $val);
	}

	/*
	 * GET / SET
	 */

	public function getRoomReservationId() {
		return $this->id;
	}

	public function getConfirmationNumber() {
		return $this->confirmationNumber;
	}

	public function setConfirmationNumber($value) {
		$this->confirmationNumber = $value;
	}

	public function getHeadGuest() {
		return $this->headGuest;
	}

	public function setHeadGuest($value) {
		$this->headGuest = $value;
	}

	public function getEmail() {
		return $this->email;
	}

	public function setEmail($value) {
		$this->email = $value;
	}

	public function getContact() {
		return $this->contact;
	}

	public function setContact($value) {
		$this->contact = $value;
	}

	public function getRoomId() {
		return $this->roomId;
	}

	public function setRoomId($value) {
		$this->roomId = $value;
	}

	public function getScheduledCheckInDate() {
		return $this->ScheduledCheckInDate;
	}

	public function setScheduledCheckInDate($value) {
		$this->ScheduledCheckInDate = $value;
	}

	public function getScheduledCheckOutDate() {
		return $this->ScheduledCheckOutDate;
	}

	public function setScheduledCheckOutDate($value) {
		$this->ScheduledCheckOutDate = $value;
	}

	public function getActualCheckInDate() {
		return $this->ActualCheckInDate;
	}

	public function setActualCheckInDate($value) {
		$this->ActualCheckInDate = $value;
	}

	public function getActualCheckOutDate() {
		return $this->ActualCheckOutDate;
	}

	public function setActualCheckOutDate($value) {
		$this->ActualCheckOutDate = $value;
	}

	public function getGuests() {
		return intval($this->guests);
	}

	public function setGuests($value) {
		$this->guests = $value;
	}

	public function getSpecialRequests() {
		return $this->specialRequests;
	}

	public function setSpecialRequests($value) {
		if (is_array($value)) $value = json_encode($value);
		$this->specialRequests = $value;
	}

	public function getIsConfirmed() {
		return $this->isConfirmed;
	}

	public function setIsConfirmed($value) {
		$this->isConfirmed = $value;
	}

	public function getPaymentId() {
		return $this->paymentId;
	}

	public function setPaymentId($value) {
		$this->paymentId = $value;
	}

	public function getCreatedAt() {
		return $this->created_at;
	}

	public function getUpdatedAt() {
		return $this->updated_at;
	}

}
