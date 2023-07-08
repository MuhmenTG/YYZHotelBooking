<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    
	protected $table = 'payment';
	protected $primaryKey = 'id';
//	protected $guarded = [];
//	protected $fillable = [];

	const COL_ID = 'id';
	const COL_RESERVATIONID = 'reservationId';
	const COL_AMOUNT = 'amount';
	const COL_PAYMENTMETHOD = 'paymentMethod';
	const COL_PAYMENTCURRENCY = 'paymentCurrency';
	const COL_LASTFOURDIGIT = 'lastFourDigit';
	const COL_PAYMENTDATE = 'paymentDate';
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

	public function getPaymentId() {
		return $this->id;
	}

	public function getReservationId() {
		return $this->reservationId;
	}

	public function setReservationId($value) {
		$this->reservationId = $value;
	}

	public function getAmount() {
		return $this->amount;
	}

	public function setAmount($value) {
		$this->amount = $value;
	}

	public function getPaymentMethod() {
		return $this->paymentMethod;
	}

	public function setPaymentMethod($value) {
		$this->paymentMethod = $value;
	}

	public function getPaymentCurrency() {
		return $this->paymentCurrency;
	}

	public function setPaymentCurrency($value) {
		$this->paymentCurrency = $value;
	}

	public function getLastFourDigit() {
		return $this->lastFourDigit;
	}

	public function setLastFourDigit($value) {
		$this->lastFourDigit = $value;
	}

	public function getPaymentDate() {
		return $this->paymentDate;
	}

	public function setPaymentDate($value) {
		$this->paymentDate = $value;
	}

	public function getCreatedAt() {
		return $this->created_at;
	}

	public function getUpdatedAt() {
		return $this->updated_at;
	}
}
