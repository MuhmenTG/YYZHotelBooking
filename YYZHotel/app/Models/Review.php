<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;
    
	protected $table = 'reviews';
	protected $primaryKey = 'id';
//	protected $guarded = [];
//	protected $fillable = [];

	const COL_ID = 'id';
	const COL_RESERVATIONID = 'reservationId';
	const COL_RATING = 'rating';
	const COL_COMMENT = 'comment';
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

	public function getReviewId() {
		return $this->id;
	}

	public function getReservationId() {
		return $this->reservationId;
	}

	public function setReservationId($value) {
		$this->reservationId = $value;
	}

	public function getRating() {
		return intval($this->rating);
	}

	public function setRating($value) {
		$this->rating = $value;
	}

	public function getComment() {
		return $this->comment;
	}

	public function setComment($value) {
		if (is_array($value)) $value = json_encode($value);
		$this->comment = $value;
	}

	public function getCreatedAt() {
		return $this->created_at;
	}

	public function getUpdatedAt() {
		return $this->updated_at;
	}


}
