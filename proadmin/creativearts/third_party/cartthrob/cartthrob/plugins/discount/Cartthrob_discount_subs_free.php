<?php if ( ! defined('CARTTHROB_PATH')) Cartthrob_core::core_error('No direct script access allowed');

class Cartthrob_discount_subs_free extends Cartthrob_discount
{
	public $title = 'subs_free_months';
	public $settings = array(
		array(
			'name' => 'months_off',
			'short_name' => 'months_off',
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
		$months_off = $this->core->sanitize_number($this->plugin_settings('months_off'));

		$discount = 0;

 		if ($this->plugin_settings('entry_ids') && $entry_ids = preg_split('/\s*(,|\|)\s*/', trim($this->plugin_settings('entry_ids'))))
		{
			foreach ($this->core->cart->items() as $item)
			{
				if ($item->product_id() && in_array($item->product_id(), $entry_ids))
				{
					//$has_subscription = $this->apply('subscriptions', 'subscriptions_initialize', element('subscription', $options), element('subscription_options', $options, array()));
					if ($item->meta('subscription_options') && $item->meta('subscription') === TRUE)
					{
						$discount += ($item->quantity() * $item->price());
						$subscription_options = (is_array($item->meta("subscription_options")) ? $item->meta("subscription_options") : array()); 
						$subscription_options['trial_price'] = 0; 
						$subscription_options['trial_occurrences'] = $months_off; 
						
						// adding subscription meta. even if there's no new info, we still want the subscription meta set
						$item->set_meta('subscription_options', $subscription_options);
					}
				}
			}
		}
		
 		return $discount;
	}
}