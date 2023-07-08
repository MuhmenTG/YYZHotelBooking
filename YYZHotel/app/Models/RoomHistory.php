<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomHistory extends Model
{
    use HasFactory;
    
	protected $table = 'room_history';
	protected $primaryKey = 'id';
//	protected $guarded = [];
//	protected $fillable = [];

	const COL_ID = 'id';
	const COL_ROOMID = 'roomId';
	const COL_CHECKOUTDATE = 'checkOutDate';
	const COL_CHECKINDATE = 'checkInDate';
	const COL_GUESTNUMBER = 'guestNumber';
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

	public function getRoomHistoryId() {
		return $this->id;
	}

	public function getRoomId() {
		return $this->roomId;
	}

	public function setRoomId($value) {
		$this->roomId = $value;
	}

	public function getCheckOutDate() {
		return $this->checkOutDate;
	}

	public function setCheckOutDate($value) {
		$this->checkOutDate = $value;
	}

	public function getCheckInDate() {
		return $this->checkInDate;
	}

	public function setCheckInDate($value) {
		$this->checkInDate = $value;
	}

	public function getGuestNumber() {
		return intval($this->guestNumber);
	}

	public function setGuestNumber($value) {
		$this->guestNumber = $value;
	}

	public function getCreatedAt() {
		return $this->created_at;
	}

	public function getUpdatedAt() {
		return $this->updated_at;
	}
}
