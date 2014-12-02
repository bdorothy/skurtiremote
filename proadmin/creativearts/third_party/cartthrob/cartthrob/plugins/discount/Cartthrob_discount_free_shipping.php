<?php if ( ! defined('CARTTHROB_PATH')) Cartthrob_core::core_error('No direct script access allowed');

class Cartthrob_discount_free_shipping extends Cartthrob_discount
{
	public $title = 'free_shipping';
	
	public function get_discount()
	{
		$this->core->cart->set_shipping(0);
		
		return 0;
		//return $this->core->cart->shipping();
	}
}