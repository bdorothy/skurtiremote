<?php if ( ! defined('CARTTHROB_PATH')) Cartthrob_core::core_error('No direct script access allowed');

abstract class Cartthrob_item extends Cartthrob_child
{
	protected $row_id,$quantity, $product_id, $site_id, $price, $weight, $shipping, $title, $no_tax, $no_shipping, $meta, $item_options, $parent_item, $discounts;
	
	protected $defaults = array(
		'row_id' => NULL,
		'quantity' => 1,
		'product_id' => NULL,
		'site_id'	=> NULL, 
		'price' => 0,
		'weight' => 0,
		'shipping' => 0,
		'title' => '',
		'no_tax' => FALSE,
		'no_shipping' => FALSE,
		'item_options' => array(),
		'meta' => array(),
		'parent_item' => FALSE,
		'discounts' => array(),
	);

	public function initialize($params = array(), $defaults = array())
	{
		if (isset($params['discounts']) && is_array($params['discounts']))
		{
			foreach ($params['discounts'] as $row)
			{
				$this->discounts[] = Cartthrob_core::create_child($this->core, 'registered_discount', $row);
			}
		}

		unset($params['discounts']);

		return parent::initialize($params, $defaults);
	}
	
	public function add_discount($amount, $reason = '', $meta = NULL)
	{
		$caller = $this->core->caller(0);

		$discount_plugin = NULL;

		if (isset($caller['object']) && $caller['object'] instanceof Cartthrob_discount)
		{
			$discount_plugin = $caller['object'];
		}

		$coupon_code = $discount_plugin ? $discount_plugin->coupon_code() : FALSE;

		$this->discounts[] = Cartthrob_core::create_child($this->core, 'registered_discount', array(
			'amount' => $amount,
			'reason' => $discount_plugin ? sprintf($reason, $this->core->lang($discount_plugin->title)) : $reason,
			'meta' => $meta,
			'coupon_code' => $coupon_code,
		));
	}

	public function discounts()
	{
		return $this->discounts;
	}

	public function discount()
	{
		$discount = 0;

		foreach ($this->discounts as &$registered_discount)
		{
			//it's not a valid coupon, get rid of it
			if ($registered_discount->coupon_code() && ! in_array($registered_discount->coupon_code(), $this->core->cart->coupon_codes()))
			{
				unset($registered_discount);

				continue;
			}

			$discount += $registered_discount->amount();
		}

		//reset array keys in case a discount was removed
		$this->discounts = array_values($this->discounts);

		return $discount;
	}

	public function discounted_price()
	{
		$discounted_price = $this->price() - $this->discount();

		return $discounted_price >= 0 ? $this->core->round($discounted_price) : 0;
	}

	public function discounted_price_subtotal()
	{
		return $this->core->round( $this->discounted_price() ) * $this->quantity();
	}

	public function discounted_subtotal()
	{
		return $this->discounted_price_subtotal();
	}
	
	/**
	 * sub_items
	 *
	 * for non-package items this is always FALSE
	 * 
	 * @return bool|array array of sub-items, false if there are no sub-items
	 */
	public function sub_items()
	{
		return FALSE;
	}
	
	public function sub_item($row_id)
	{
		return FALSE;
	}
	
	public function parent_item()
	{
		return $this->parent_item;
	}
	
	public function is_sub_item()
	{
		return ( ! empty($this->parent_item));
	}
	
	public function set_parent_item($parent_item)
	{
		$this->parent_item =& $parent_item;
		
		return $this;
	}
	
	/**
	 * Get the product id
	 *
	 * @return int
	 */
	public function product_id()
	{
		return $this->product_id;
	}
	/**
	 * Get the site id
	 *
	 * @return int
	 */
	public function site_id()
	{
		return $this->site_id;
	}
	/**
	 * Get the row id
	 *
	 * @return int
	 */
	public function row_id()
	{
		return $this->row_id;
	}
	
	/**
	 * True if inventory is not zero
	 *
	 * @return array
	 */
	public function in_stock()
	{
		return TRUE;
	}
	
	/**
	 * Get the inventory for this product
	 *
	 * @return array
	 */
	public function inventory()
	{
		return PHP_INT_MAX;
	}
	
	/**
	 * Get a value from the item options array, or
	 * get the whole array by not specifying a key
	 *
	 * @param   string|false $key
	 * @return  mixed|false
	 */
	public function item_options($key = FALSE)
	{
		if ($key === FALSE)
		{
			return $this->item_options;
		}
		
		return (isset($this->item_options[$key])) ? $this->item_options[$key] : FALSE;
	}
	
	/**
	 * Set a value in the item options array,
	 * or set many values by providing an array.
	 *
	 * @param   array|string $key
	 * @param   mixed $value
	 * @return  Cartthrob_item
	 */
	public function set_item_options($key, $value = FALSE)
	{
		if (is_null($value))
		{
			unset($this->item_options[$key]);

			return $this;
		}

		if ( ! is_array($key))
		{
			$key = array($key => $value);
		}
		
		$this->item_options = array_merge($this->item_options, $key);
		
		return $this;
	}
	
	/**
	 * Empty the item_options array
	 *
	 * @return  Cartthrob_item
	 */
	public function clear_item_options()
	{
		$this->item_options = array();
		
		return $this;
	}
	
	/**
	 * Get a value from the meta array, or
	 * get the whole array by not specifying a key
	 *
	 * @param   string|false $key
	 * @return  mixed|false
	 */
	public function meta($key = FALSE)
	{
		if ($key === FALSE)
		{
			return $this->meta;
		}
		
		return (isset($this->meta[$key])) ? $this->meta[$key] : FALSE;
	}
	
	/**
	 * Set a value in the meta array,
	 * or set many values by providing an array.
	 *
	 * @param   array|string $key
	 * @param   mixed $value
	 * @return  Cartthrob_item
	 */
	public function set_meta($key, $value = FALSE)
	{
		if ( ! is_array($key))
		{
			$key = array($key => $value);
		}
		
		if (!is_array($this->meta))
		{
			 $this->meta = array(); 
		}
		$this->meta = array_merge(  $this->meta, $key);
		
		return $this;
	}
	
	/**
	 * Set this item's row id
	 *
	 * @param   int $row_id
	 * @return  Cartthrob_item
	 */
	public function set_row_id($row_id)
	{
		$this->row_id = $row_id;
		
		return $this;
	}
	
	/**
	 * Change this item's quantity
	 * a) removes if quantity is 0
	 * b) checks global quantity limit
	 * c) checks split items by quantity preference
	 *
	 * @param   int $quantity
	 * @return  Cartthrob_item
	 */
	public function set_quantity($quantity = 0)
	{
		$quantity = $this->core->store->config('allow_fractional_quantities') ? $this->core->sanitize_number($quantity) : $this->core->sanitize_integer($quantity);

		if ($quantity <= 0)
		{
			$this->remove();
			
			return $this;
		}
		
		if (is_numeric($quantity) && $quantity != $this->quantity())
		{
			if ($this->core->store->config('global_item_limit') && $quantity > $this->core->store->config('global_item_limit'))
			{
				$quantity = $this->core->store->config('global_item_limit');
			}

			if ($this->core->store->config('product_split_items_by_quantity') && $quantity > 1)
			{
				for ($i = 2; $i <= $quantity; $i++)
				{
					$this->core->cart->duplicate_item($this->row_id());
				}
			}
			else
			{
				$this->quantity = $quantity;
			}
		}
		
		return $this;
	}
	
	/**
	 * Increase the item's quantity
	 *
	 * @param   int $quantity
	 * @return  Cartthrob_item
	 */
	public function add_quantity($quantity = 1)
	{
		$this->quantity += $this->core->store->config('allow_fractional_quantities') ? $this->core->sanitize_number($quantity) : $this->core->sanitize_integer($quantity);
		
		return $this;
	}
	
	/**
	 * Decrease the item's quantity
	 *
	 * @param   int $quantity
	 * @return  Cartthrob_item
	 */
	public function remove_quantity($quantity = 1)
	{
		$this->quantity -= $this->core->store->config('allow_fractional_quantities') ? $this->core->sanitize_number($quantity) : $this->core->sanitize_integer($quantity);
		
		return $this;
	}
	
	/**
	 * Get the item's quantity
	 *
	 * @return  int
	 */
	public function quantity()
	{
		return $this->quantity;
	}
	
	/**
	 * Update the item's attributes with an array
	 *
	 * @param   array $params
	 * @return  Cartthrob_item
	 */
	public function update($params)
	{
		// don't want to update the site id of the item. it shoudl be set once, and not modified. 
		if (isset($params['site_id']))
		{
			unset($params['site_id']); 
		}
		foreach ($params as $key => $value)
		{
			if (in_array($key, $this->default_keys()))
			{
				if (is_array($value) && is_array($this->$key))
				{
					$this->$key = $this->core->array_merge($this->$key, $value);
				}
				else
				{
					$method = 'set_'.$key;
					
					if (method_exists($this, $method))
					{
						$this->{$method}($value);
					}
					else
					{
						$this->$key = $value;
					}
				}
			}
		}
	}
	
	/**
	 * Set the product id
	 *
	 * @param   int $product_id
	 * @return  Cartthrob_item
	 */
	public function set_product_id($product_id)
	{
		$this->product_id = $product_id;
		
		return $this;
	}
	
	/**
	 * Get the item title
	 *
	 * @return  string
	 */
	public function title()
	{
		return $this->title;
	}
	
	/**
	 * Get the item base price
	 *
	 * @return int|float
	 */
	public function base_price()
	{
		return  $this->core->round($this->price());
	}
	
	/**
	 * Get the item price
	 *
	 * @return int|float
	 */
	public function price()
	{
		return  $this->core->round($this->price);
	}
	
	
	/**
	 * Get the item price * quantity
	 *
	 * @return int|float
	 */
	public function price_subtotal()
	{
		return  $this->core->round($this->price()) * $this->quantity();
	}
	// alias for base price subtotal
	public function subtotal()
	{
		return $this->price_subtotal();
	}
	
	/**
	 * Get the item base price * quantity
	 *
	 * @return int|float
	 */

	public function base_price_subtotal()
	{
		return  $this->core->round($this->base_price()) * $this->quantity();
	}
	
	/**
	 * Get the item price w/ tax
	 *
	 * @return int|float
	 */
	public function taxed_price()
	{
		return $this->core->round( $this->tax() + $this->price() );
	}
	
	/**
	 * Get the item price w/ tax * quantity
	 *
	 * @return int|float
	 */
	public function taxed_price_subtotal()
	{
		return  $this->core->round(($this->tax() * $this->quantity()) + ($this->price() * $this->quantity()));
	}
	
	
	/**
	 * Get the item base price w/ tax
	 *
	 * @return int|float
	 */
	public function taxed_base_price()
	{
		return $this->core->round( $this->base_tax() + $this->base_price() );
	}
	
	/**
	 * Get the item base price w/ tax * quantity
	 *
	 * @return int|float
	 */
	public function taxed_base_price_subtotal()
	{
		return $this->core->round( $this->base_tax() + $this->base_price() ) * $this->quantity();
	}
	
	public function tax()
	{
		if (! $this->no_tax && $this->core->store->config('tax_plugin'))
		{
			if ($plugin = $this->core->store->plugin($this->core->store->config('tax_plugin')))
			{
				if (method_exists($plugin, 'get_tax'))
				{
					return $this->get_item_tax($plugin, $this->price() ); 
				}
			}
		}
		return 0; 
	}
	
	public function base_tax()
	{
		if (! $this->no_tax && $this->core->store->config('tax_plugin'))
		{
			if ($plugin = $this->core->store->plugin($this->core->store->config('tax_plugin')))
			{
				if (method_exists($plugin, 'get_tax'))
				{
					return $this->get_item_tax($plugin, $this->base_price() ); 
				}
			}
		}
		return 0; 
	}
	/**
	 * get_item_tax
	 *
	 * gets the item tax, and rounds if appropriate to the configuration
	 * 
	 * @param object $plugin 
	 * @param decimal $cost 
	 * @return decimal
	 * @author Chris Newton
	 */
	private function get_item_tax($plugin, $cost)
	{
		$tax = $plugin->get_tax($cost, $this);
		
		if ($this->core->store->config('round_tax_only_on_subtotal'))
		{
			return $tax; 
		}
		else
		{
			return $this->core->round($tax);
		}
	}
	/**
	 * Get the item weight
	 *
	 * @return int|float
	 */
	public function weight()
	{
		return $this->weight;
	}
	
	/**
	 * Get the item shipping cost
	 *
	 * @return int|float
	 */
	public function shipping()
	{
		if ($this->no_shipping)
		{
			return 0;
		}
		
		if ($this->core->hooks->set_hook('item_shipping_start')->run() && $this->core->hooks->end())
		{
			$shipping = $this->core->hooks->value();
		}
		else
		{
			$shipping = $this->shipping;
			
			/*@TODO bring this back...maybe
			$plugin = ($this->core->store->config('shipping_plugin')) ? Cartthrob_core::create_child($this->core, $this->core->store->config('shipping_plugin')) : NULL;
			
			if (method_exists($plugin, 'get_item_shipping') && is_callable(array($plugin, 'get_item_shipping')))
			{
				$shipping = $plugin->get_item_shipping($this);
			}
			*/
			
			$shipping *= $this->quantity();
			
			if ($this->core->hooks->set_hook('item_shipping_end')->run($shipping) && $this->core->hooks->end())
			{
				$shipping = $this->core->hooks->value();
			}
		}
		
		return $this->core->round($shipping);
	}
	
	/**
	 * Set the item title
	 *
	 * @param string $title
	 * @return Cartthrob_item
	 */
	public function set_title($title)
	{
		$this->title = $title;
		
		return $this;
	}
	
	/**
	 * Set the item price
	 *
	 * @param string $title
	 * @return Cartthrob_item
	 */
	public function set_price($price)
	{
		$this->price = $this->core->sanitize_number($price, TRUE);
		
		return $this;
	}
	
	/**
	 * Set the item weight
	 *
	 * @param string $title
	 * @return Cartthrob_item
	 */
	public function set_weight($weight)
	{
		$this->weight = $this->core->sanitize_number($weight, FALSE);
		
		return $this;
	}
	/**
	 * set_tax_exempt
	 *
	 * @param boolean $tax_exempt if set to TRUE, the item is not taxable
	 * @return void
	 * @author Chris Newton
	 */
	public function set_tax_exempt($tax_exempt=TRUE)
	{
		$this->no_tax = $tax_exempt; 
	}
	
	/**
	 * set_shipping_exempt
	 *
	 * @param boolean $tax_exempt if set to TRUE, the item is not taxable
	 * @return void
	 * @author Chris Newton
	 */
	public function set_shipping_exempt($shipping_exempt=TRUE)
	{
		$this->no_shipping = $shipping_exempt; 
	}
	/**
	 * Set the item shipping cost
	 *
	 * @param string $title
	 * @return Cartthrob_item
	 */
	public function set_shipping($shipping)
	{
		$this->shipping = $this->core->sanitize_number($shipping, FALSE);
		
		return $this;
	}
	
	/**
	 * True if item not marked no_shipping
	 *
	 * @return bool
	 */
	public function is_shippable()
	{
		return ! $this->no_shipping;
	}
	
	/**
	 * True if item not marked no_tax
	 *
	 * @return bool
	 */
	public function is_taxable()
	{
		return ! $this->no_tax;
	}
	
	/**
	 * Remove this item from the cart
	 *
	 * @return bool
	 */
	public function remove()
	{
		$this->core->cart->remove_item($this->row_id);
	}
	
	/**
	 * Convert self to array
	 * 
	 * @param bool $strip_defaults To minimize the size of the array, you can set to true to not save its values that are the default values
	 * 
	 * @return array
	 */
	public function to_array($strip_defaults = FALSE)
	{
		$data = parent::to_array();
		
		if ($this->sub_items())
		{
			foreach ($this->sub_items() as $row_id => $sub_item)
			{
				$data['sub_items'][$row_id] = $sub_item->to_array();
			}
		}

		if ($this->discounts)
		{
			$data['discounts'] = array();
			
			foreach ($this->discounts as $discount)
			{
				if(! in_array($discount->to_array(), $data['discounts']))
				{
					$data['discounts'][] = $discount->to_array();
				}
			}
		}
		
		if ($this->subclass())
		{
			$data['class'] = $this->subclass();
		}
		
		if ($strip_defaults)
		{
			foreach ($this->defaults as $key => $value)
			{
				if (isset($data[$key]) && $data[$key] === $value)
				{
					unset($data[$key]);
				}
			}
		}
		if (!empty($data['product_id']) && !isset($data['entry_id']))
		{
			$data['entry_id'] = $data['product_id']; 
		}
		return $data;
	}
	
	/**
	 * Validate/sanitize parameters when initializing
	 * 
	 * @param array $params
	 * 
	 * @return Cartthrob_item
	 */
	public function prepare_params(&$params)
	{
		if ( ! is_array($params))
		{
			return $this;
		}
		
		$numeric = array('quantity', 'price', 'weight', 'shipping');
		
		foreach ($numeric as $key)
		{
			if (isset($params[$key]))
			{
				$params[$key] = $this->core->sanitize_number($params[$key]);
			}
		}
		
		if (isset($params['quantity']))
		{
			if ($this->core->store->config('global_item_limit') != FALSE && $params['quantity'] > $this->core->store->config('global_item_limit'))
			{
				$params['quantity'] = $this->core->store->config('global_item_limit');
			}
		}
		
		if (isset($params['item_options']) && is_array($params['item_options']) && is_array($this->item_options))
		{
			$params['item_options'] = array_merge($this->item_options, $params['item_options']);
		}
		
		return $this;
	}
	
	/**
	 * Alias for to_array
	 * 
	 * @return array
	 */
	public function data()
	{
		return $this->to_array();
	}
}
