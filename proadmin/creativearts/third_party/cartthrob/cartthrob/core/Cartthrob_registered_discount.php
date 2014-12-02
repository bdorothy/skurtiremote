<?php if ( ! defined('CARTTHROB_PATH')) Cartthrob_core::core_error('No direct script access allowed');

class Cartthrob_registered_discount extends Cartthrob_child
{
	protected $amount, $reason, $meta, $coupon_code;

	protected $defaults = array(
		'amount' => 0,
		'reason' => '',
		'meta' => NULL,
		'coupon_code' => FALSE,
	);

	public function amount()
	{
		return $this->amount;
	}

	public function reason()
	{
		return $this->reason;
	}

	public function meta()
	{
		return $this->meta;
	}

	public function coupon_code()
	{
		return $this->coupon_code;
	}
}
