<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;
    
	protected $table = 'rooms';
	protected $primaryKey = 'id';
//	protected $guarded = [];
//	protected $fillable = [];

	const COL_ID = 'id';
	const COL_ROOMNUMBER = 'roomNumber';
	const COL_CATEGORYID = 'categoryId';
	const COL_CAPACITY = 'capacity';
	const COL_PRICE = 'price';
	const COL_DESCRIPTION = 'description';
	const COL_AVAILABILITY = 'availability';
	const COL_CREATED_AT = 'created_at';
	const COL_UPDATED_AT = 'updated_at';

	/*
	 * Eloquent Scopes
	 */

	public function scopeById($query, $val) {
		$query->where('id', $val);
	}

	
	public function scopeByRoomNumber($query, $val) {
		$query->where('id', $val);
	}


	/*
	 * GET / SET
	 */

	public function getRoomId() {
		return $this->id;
	}

	public function getRoomNumber() {
		return $this->roomNumber;
	}

	public function setRoomNumber($value) {
		$this->roomNumber = $value;
	}

	public function getCategoryId() {
		return $this->categoryId;
	}

	public function setCategoryId($value) {
		$this->categoryId = $value;
	}

	public function getCapacity() {
		return intval($this->capacity);
	}

	public function setCapacity($value) {
		$this->capacity = $value;
	}

	public function getPrice() {
		return $this->price;
	}

	public function setPrice($value) {
		$this->price = $value;
	}

	public function getDescription() {
		return $this->description;
	}

	public function setDescription($value) {
		if (is_array($value)) $value = json_encode($value);
		$this->description = $value;
	}

	public function getAvailability() {
		return $this->availability;
	}

	public function setAvailability($value) {
		$this->availability = $value;
	}

	public function getCreatedAt() {
		return $this->created_at;
	}

	public function getUpdatedAt() {
		return $this->updated_at;
	}


}
