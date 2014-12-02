<?php if ( ! defined('CARTTHROB_PATH')) Cartthrob_core::core_error('No direct script access allowed');

class Cartthrob_discount_percentage_off_product extends Cartthrob_discount
{
	public $title = 'percentage_off_single_product_title';
	public $settings = array(
		array(
			'name' => 'percentage_off',
			'short_name' => 'percentage_off',
			'note' => 'percentage_off_note',
			'type' => 'text'
		),
		array(
			'name' => 'product_entry_id',
			'short_name' => 'entry_ids',
			'note' => 'separate_multiple_entry_ids_by_comma',
			'type' => 'text'
		)
	);
	
	public function get_discount()
	{
		$percentage_off = $this->core->sanitize_number($this->plugin_settings('percentage_off'));
		
		$discount = 0;
		
		if ($this->plugin_settings('entry_ids') && $entry_ids = preg_split('/\s*(,|\|)\s*/', trim($this->plugin_settings('entry_ids'))))
		{
			foreach ($this->core->cart->items() as $item)
			{
				if ($item->product_id() && in_array($item->product_id(), $entry_ids))
				{
					$item_discount = $item->price() * ($percentage_off / 100);

					$item->add_discount($item_discount, $this->core->lang('discount_reason_eligible_product'));

					$discount += $item_discount * $item->quantity();
				}
			}
		}
		
		return $discount;
	}
 	
	public function validate()
	{
		$valid = FALSE;
		
		if ($this->plugin_settings('entry_ids') && $entry_ids = preg_split('#\s*[,|]\s*#', trim($this->plugin_settings('entry_ids'))))
		{
			$valid = (count(array_intersect($this->core->cart->product_ids(), $entry_ids)) > 0);
		}

		if ( ! $valid)
		{
			$this->set_error( $this->core->lang('coupon_not_valid_for_items') );
		}

		return $valid;
	}
}