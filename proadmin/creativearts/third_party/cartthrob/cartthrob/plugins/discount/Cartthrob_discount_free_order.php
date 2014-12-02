<?php if ( ! defined('CARTTHROB_PATH')) Cartthrob_core::core_error('No direct script access allowed');

class Cartthrob_discount_free_order extends Cartthrob_discount
{
	public $title = 'free_order';
 
	public function get_discount()
	{
		$this->core->cart->set_discounted_shipping(0); 
		
		$this->core->cart->set_total(0);
		
		return 0;
		//return $this->core->cart->subtotal() + $this->core->cart->tax() + $this->core->cart->shipping(); 
	}
}