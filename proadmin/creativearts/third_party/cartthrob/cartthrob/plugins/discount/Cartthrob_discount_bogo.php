<?php if ( ! defined('CARTTHROB_PATH')) Cartthrob_core::core_error('No direct script access allowed');

class Cartthrob_discount_bogo extends Cartthrob_discount
{
	public $title = 'buy_x_get_x';
	public $item_limit = 0; 
	public $settings = array(
 		array(
			'name' => 'purchase_quantity',
			'short_name' => 'buy_x',
			'note' => 'enter_the_purchase_quantity',
			'type' => 'text'
		),
		array(
			'name' => 'discount_quantity',
			'short_name' => 'get_x_free',
			'note' => 'enter_the_number_of_items',
			'type' => 'text'
		),
		array(
			'name' => 'percentage_off',
			'short_name' => 'percentage_off',
			'note' => 'enter_the_percentage_discount',
			'type' => 'text'
		),
		array(
			'name' => 'amount_off',
			'short_name' => 'amount_off',
			'note' => 'enter_the_discount_amount',
			'type' => 'text'
		),
		array(
			'name' => 'qualifying_entry_ids',
			'short_name' => 'entry_ids',
			'note' => 'separate_multiple_entry_ids',
			'type' => 'text'
		),
		array(
			'name' 			=> 'group_by',
			'short_name' 	=> 'group_by',
			'note' => 'group_by_note',
			'type' => 'select',
			'default'	=> 'entry_id',
			'options'	=> array(
				'entry_id'	=> 'entry_id',
				'line_item'	=> 'line_item',
			)
		),
		array(
			'name' => 'per_item_limit',
			'short_name' => 'item_limit',
			'note' => 'per_item_limit_note',
			'type' => 'text'
		),
	);

	public function get_discount_per_entry_id($entry_id, $price, $percentage_off= NULL)
	{
		$product_count = $this->core->cart->count_all(array('items' => array('product_id' => $entry_id))); 
		$discount = 0; 
		// CHECK AMOUNTS AND PERCENTAGES
		if ($this->plugin_settings('percentage_off') !== '')
		{
			$percentage_off = ".01" * $this->core->sanitize_number( $this->plugin_settings('percentage_off') );

			if ($percentage_off > 1)
			{
				$percentage_off = 1; 
			}
			else if ($percentage_off < 0)
			{
				$percentage_off = 0; 
			}
		}
		else
		{
			$amount_off = $this->core->sanitize_number( $this->plugin_settings('amount_off') );
		}
		
 		if ($product_count)
		{
			while($product_count > 0 && $product_count > $this->plugin_settings('buy_x'))
			{
				if ($this->item_limit !== FALSE && $this->item_limit < 1)
				{
					break;
				}
				
				if ($this->plugin_settings('get_x_free'))
				{
					$free_count = ($product_count > $this->plugin_settings('get_x_free')) ? $this->plugin_settings('get_x_free') : $product_count;
				}
				else
				{
					$free_count = $product_count; 
				}
				
				if($this->item_limit !== FALSE)
				{
					if (!empty($percentage_off))
					{
						$discount += $price * $percentage_off;
					}
					else
					{
						$discount += $amount_off;
 					}
					$this->item_limit--;
				}
				else
				{
					
					while($free_count > 0 && $product_count > $this->plugin_settings('buy_x'))
						{
							if (!empty($percentage_off))
							{
								$discount += $price * $percentage_off;
							}
							else
							{
								$discount += $amount_off;
		 					}
							$free_count--;
							$product_count--;
						}
				}
				$product_count--;
				
				
 			}
		}
 		return $discount;
	}
	
	public function get_discount_per_line_item()
	{
		
		
	}
	function get_discount()
	{
		$discount 			= 0;
		$entry_ids 			= array();
		$not_entry_ids 		= array();
		$percentage_off = NULL; 
		$this->item_limit  = 0; 
		// CHECK AMOUNTS AND PERCENTAGES
		if ($this->plugin_settings('percentage_off') !== '')
		{
			$percentage_off = ".01" * $this->core->sanitize_number( $this->plugin_settings('percentage_off') );

			if ($percentage_off > 1)
			{
				$percentage_off = 1; 
			}
			else if ($percentage_off < 0)
			{
				$percentage_off = 0; 
			}
		}
		else
		{
			$amount_off = $this->core->sanitize_number( $this->plugin_settings('amount_off') );
		}
		
		// CHECK ENTRY IDS
		if ( $this->plugin_settings('entry_ids') )
		{
			if (preg_match('/^not (.*)/',  trim( $this->plugin_settings('entry_ids') ) , $matches))
			{
				$not_entry_ids = preg_split('/\s*,\s*/',  $matches[1]);
				}
			else
			{
				$entry_ids = preg_split('/\s*,\s*/', trim( $this->plugin_settings('entry_ids') ));
			}
		}
		
		$this->item_limit= ( $this->plugin_settings('item_limit') ) ? $this->plugin_settings('item_limit') : FALSE;
			
		$items = array(); 
		
 		if ($this->plugin_settings('group_by') == "entry_id")
		{
			if (count($entry_ids) > 0 || count($not_entry_ids) > 0)
			{
				foreach ($this->core->cart->items() as $item)
				{
					if (count($entry_ids) > 0)
					{
						if ( $item->product_id() && in_array( $item->product_id(), $entry_ids))
						{
							if (isset($items[$item->product_id()]))
							{
								if ( $item->price() <= $items[$item->product_id()])
								{
									$items[$item->product_id()] = $item->price(); 
								}
							}
							else
							{
								$items[$item->product_id()] = $item->price(); 
							}
						}
					}
					else
					{
						if ( $item->product_id()  && ! in_array($item->product_id(), $not_entry_ids))
						{
							if (isset($items[$item->product_id()]))
							{
								if ( $item->price() <= $items[$item->product_id()])
								{
									$items[$item->product_id()] = $item->price(); 
								}
							}
							else
							{
								$items[$item->product_id()] = $item->price(); 
							}
						}
					}

				}
			}
			
			foreach ($items as $entry_id => $price)
			{
				$discount += $this->get_discount_per_entry_id($entry_id, $price, $percentage_off);
			}
			return $discount; 
		}
		else
		{
			if (count($entry_ids) > 0 || count($not_entry_ids) > 0)
			{
				foreach ($this->core->cart->items() as $item)
				{
					if (count($entry_ids) > 0)
					{
						if ( $item->product_id() && in_array( $item->product_id(), $entry_ids))
						{
							for ($i=0; $i<$item->quantity() ;$i++)
							{
								$items[] = $item->price(); 
							}
						}
					}
					else
					{
						if ( $item->product_id()  && ! in_array($item->product_id(), $not_entry_ids))
						{
							for ($i=0;$i<$item->quantity();$i++)
							{
								$items[] = $item->price(); 
							}
						}
					}

				}

			}
			else
			{
				foreach ($this->core->cart->items() as $item)
				{
					for ($i=0;$i<$item->quantity();$i++)
					{
						$items[] = $item->price(); 
					}
				}

			}
		}

 		$counts = array();
		reset($items);			
		
		while (($price = current($items)) !== FALSE)
		{
			$key = key($items);

			$count = count($items);
			while($count > 0 && $count > $this->plugin_settings('buy_x') )
			{
					if ($this->item_limit !== FALSE && $this->item_limit < 1)
				{
					next($items);
						continue 2;
				}

				if (($count -= $this->plugin_settings('buy_x') ) > 0)
				{
					if ($this->plugin_settings('get_x_free'))
					{
					$free_count = ($count > $this->plugin_settings('get_x_free')) ? $this->plugin_settings('get_x_free') : $count;
					}
					else
					{
						$free_count = $count; 
					}
					if (isset($percentage_off))
					{
						//get the lowest price by grabbing the last array item
						//since our array is sorted by price
						for ($i=0;$i<$free_count;$i++)
						{
							$discount += end($items) * $percentage_off;
							array_pop($items);
						}

						//go back to where we were
						reset($items);
						while ($key != key($items)) next($items);
					}
					else
					{
						for ($i=0;$i<$free_count;$i++)
						{
							array_pop($items);
							$discount += $amount_off;
						}
					}

					//remove the buy_x items from begginning of array
					for ($i=0;$i<$this->plugin_settings('buy_x');$i++)
					{
						array_shift($items);
					}

					$count -= $free_count;
				}

				if ($this->item_limit !== FALSE)
				{
					$this->item_limit--;
				}
			}

			next($items);
		}

		return $discount;
	}

	function validate()
	{
		
		$entry_ids = array();
		$not_entry_ids = array();


		if (! $this->plugin_settings('entry_ids'))
		{
  			foreach ($this->core->cart->items() as $item)
			{
				if ( $item->quantity() > $this->core->sanitize_number( $this->plugin_settings('buy_x') ))
				{
					return TRUE;
				}
				$this->set_error( $this->core->lang('coupon_minimum_not_reached') );
				return FALSE;
			}
		}
		if (  $this->plugin_settings('entry_ids') )
		{
			$entry_ids = preg_split('/\s*,\s*/', trim($this->plugin_settings('entry_ids')));

			if (preg_match('/^not (.*)/',  trim( $this->plugin_settings('entry_ids') ) , $matches))
			{
				$codes = (explode('not', $matches[1], 2));
				$not_entry_ids = preg_split('/\s*,\s*/',  $codes[1]);
			}
		}
		
		
 		if (count($entry_ids))
		{
			if ($this->plugin_settings('group_by') == "entry_id")
	 		{
 				foreach ($this->core->cart->product_ids() as $entry_id)
				{
					if (  (in_array ($entry_id, $entry_ids) ||   !in_array ($entry_id, $not_entry_ids) ) && $this->core->cart->count_all(array('product_id' => $entry_id)) >   $this->core->sanitize_number( $this->plugin_settings('buy_x') ) )
					{
						return TRUE; 
					}
 				}
			}

			foreach ($this->core->cart->items() as $item)
			{
				if ( $item->product_id()  && in_array( $item->product_id(), $entry_ids))
				{
					if ( $item->quantity() > $this->core->sanitize_number( $this->plugin_settings('buy_x') ))
					{
						return TRUE;
					}
					$this->set_error( $this->core->lang('coupon_minimum_not_reached') );
				}
				$this->set_error( $this->core->lang('coupon_not_valid_for_items') );
				
			}
		}	
		elseif(count($not_entry_ids))
		{
			foreach ($this->core->cart->items() as $item)
			{
				if ( $item->product_id()  && ! in_array( $item->product_id(), $entry_ids))
				{
					if ( $item->quantity() > $this->core->sanitize_number( $this->plugin_settings('buy_x') ))
					{
						return TRUE;
					}
					$this->set_error( $this->core->lang('coupon_minimum_not_reached'));
				}
				$this->set_error( $this->core->lang('coupon_not_valid_for_items') );
				
			}	
		}
 		else
		{
			$this->set_error( $this->core->lang('coupon_not_valid_for_items') );
		}
 		return FALSE;
	}
}