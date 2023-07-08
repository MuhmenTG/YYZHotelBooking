<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCases extends Model
{
    use HasFactory;
    
	protected $table = 'user_cases';
	protected $primaryKey = 'id';
//	protected $guarded = [];
//	protected $fillable = [];

	const COL_ID = 'id';
	const COL_NAME = 'name';
	const COL_EMAIL = 'email';
	const COL_SUBJECT = 'subject';
	const COL_RESERVATIONID = 'reservationId';
	const COL_MESSAGE = 'message';
	const COL_TIME = 'time';
	const COL_ISSOLVED = 'isSolved';
	const COL_CREATED_AT = 'created_at';
	const COL_UPDATED_AT = 'updated_at';

	/*
	 * Eloquent Scopes
	 */

	public function scopeById($query, $val) {
		$query->where('id', $val);
	}

	public function scopeFromTime($query, $val) {
		$query->where('time', '>=', $val);
	}

	public function scopeToTime($query, $val) {
		$query->where('time', '<', $val);
	}

	/*
	 * GET / SET
	 */

	public function getUserCaseId() {
		return $this->id;
	}

	public function getName() {
		return $this->name;
	}

	public function setName($value) {
		$this->name = $value;
	}

	public function getEmail() {
		return $this->email;
	}

	public function setEmail($value) {
		$this->email = $value;
	}

	public function getSubject() {
		return $this->subject;
	}

	public function setSubject($value) {
		$this->subject = $value;
	}

	public function getReservationId() {
		return $this->reservationId;
	}

	public function setReservationId($value) {
		$this->reservationId = $value;
	}

	public function getMessage() {
		return $this->message;
	}

	public function setMessage($value) {
		if (is_array($value)) $value = json_encode($value);
		$this->message = $value;
	}

	public function getTime() {
		return intval($this->time);
	}

	public function setTime($value) {
		$this->time = $value;
	}

	public function getIsSolved() {
		return $this->isSolved;
	}

	public function setIsSolved($value) {
		$this->isSolved = $value;
	}

	public function getCreatedAt() {
		return $this->created_at;
	}

	public function getUpdatedAt() {
		return $this->updated_at;
	}


}
