<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomCategory extends Model
{
    use HasFactory;

    
	protected $table = 'room_categories';
	protected $primaryKey = 'id';
//	protected $guarded = [];
//	protected $fillable = [];

	const COL_ID = 'id';
	const COL_NAME = 'name';
	const COL_DESCRIPTION = 'description';
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

	public function getRoomCategorieId() {
		return $this->id;
	}

	public function getName() {
		return $this->name;
	}

	public function setName($value) {
		$this->name = $value;
	}

	public function getDescription() {
		return $this->description;
	}

	public function setDescription($value) {
		if (is_array($value)) $value = json_encode($value);
		$this->description = $value;
	}

	public function getCreatedAt() {
		return $this->created_at;
	}

	public function getUpdatedAt() {
		return $this->updated_at;
	}


}
