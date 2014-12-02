<?php if ( ! defined('CARTTHROB_PATH')) Cartthrob_core::core_error('No direct script access allowed');

/**
 * CartThrob Shopping Cart Class
 *
 * @package CartThrob
 * @subpackage Core
 */
class Cartthrob_cart extends Cartthrob_child
{
	protected $items, $total, $subtotal, $shippable_subtotal, $taxable_subtotal, $taxable_discount,
		  $tax, $shipping, $discount, $customer_info, $shipping_info, $custom_data,
		  $coupon_codes, $order, $meta, $config, $id;
	
	protected $defaults = array(
		'items' => array(),
		'total' => NULL,
		'subtotal' => NULL,
		'tax' => NULL,
		'shipping' => NULL,
		'discount' => NULL,
		'taxable_discount' => NULL, 
		'shippable_subtotal' => NULL,
		'taxable_subtotal' => NULL,
		//'customer_info' => array(),
		'shipping_info' => array(),
		'custom_data' => array(),
		'coupon_codes' => array(),
		'order' => array(),
		'config' => array(),
		'meta' => array()
	);
	
	private $calculation_caching = TRUE;
	
	public function id()
	{
		return $this->id;
	}
	
	public function set_id($id)
	{
		$this->id = $id;
	}
	
	private $cache = array();
	
	public function cache()
	{
		switch(func_num_args())
		{
			case 0:
				return $this->cache;
			case 1:
				return (isset($this->cache[func_get_arg(0)])) ? $this->cache[func_get_arg(0)] : NULL;
		}
		
		return $this->cache[func_get_arg(0)] = func_get_arg(1);
	}
	
	/**
	 * Retrive the meta array or a meta key value
	 *
	 * @access public
	 * @param string|boolean $key
	 * @return mixed
	 */
	public function meta($key = FALSE)
	{
		if ($key === FALSE)
		{
			return $this->meta;
		}
		
		return (array_key_exists($key, $this->meta)) ? $this->meta[$key] : FALSE;
	}
	
	/**
	 * Set one or more meta array values
	 *
	 * @access public
	 * @param string|array $key
	 * @param mixed $value
	 * @return Cartthrob_cart
	 */
	public function set_meta($key, $value = FALSE)
	{
		if (is_array($key))
		{
			foreach ($key as $k => $v)
			{
				$this->set_meta($k, $v);
			}
		}
		else
		{
			$this->meta[$key] = $value;
		}
		
		return $this;
	}
	
	/**
	 * Set a global config value to be overridden for this cart only
	 *
	 * @access public
	 * @param string|array $key
	 * @param mixed $value
	 * @return Cartthrob_cart
	 */
	public function set_config($key, $value = FALSE)
	{
		$this->core->store->set_config($key, $value);
		
		if (is_array($key))
		{
			foreach ($key as $k => $v)
			{
				$this->config[$k] = $v;
			}
		}
		else
		{
			$this->config[$key] = $value;
		}
		
		return $this;
	}
	
	/**
	 * Turn calculation caching on and off
	 *
	 * @access public
	 * @param bool $calculation_caching
	 * @return Cartthrob_cart
	 */
	public function set_calculation_caching($calculation_caching = TRUE)
	{
		$this->calculation_caching = (bool) $calculation_caching;
		
		return $this;
	}
	
	/**
	 * Set the order data array
	 *
	 * @access public
	 * @param array $order
	 * @return Cartthrob_cart
	 */
	public function set_order($order)
	{
		if (is_array($order))
		{
			$this->order = $order;
		}
		
		return $this;
	}
	
	/**
	 * Update the order data array
	 *
	 * @access public
	 * @param array $order
	 * @return Cartthrob_cart
	 */
	public function update_order($order)
	{
		if (is_array($order))
		{
			$this->order = array_merge($this->order, $order);
		}
		
		return $this;
	}
	
	/**
	 * Retrieve the entire order data array or just a key's value
	 *
	 * @access public
	 * @param string|bool $key
	 * @return array
	 */
	public function order($key = FALSE)
	{
		if ($key !== FALSE)
		{
			return (isset($this->order[$key])) ? $this->order[$key] : FALSE;
		}
		
		return $this->order;
	}
	
	/**
	 * Add an item to cart
	 *
	 * @access public
	 * @param array $params
	 * @return Cartthrob_item
	 */
	public function add_item($params = array())
	{
		$this->clear_errors();
		
		$item_options = (isset($params['item_options'])) ? $params['item_options'] : array();
		
		//check if row_id exists,
		//if so, update it, and move on
		//or remove that paramter if no row_id isset
		if (isset($params['row_id']))
		{
			if ($item = $this->item($params['row_id']))
			{
				return $item->update($params);
			}
			
			//unset($params['row_id']);
		}
		
		if ( ! isset($params['class']))
		{
			$params['class'] = 'default';
		}
		
		if ($this->core->hooks->set_hook('cart_add_item_start')->run($params) && $this->core->hooks->end())
		{
			$params = $this->core->hooks->value();
		}
		
		$find_params = $params;
		unset($find_params['quantity']);//, $find_params['row_id']);
		
		$item = $this->find_item($find_params);
		
		if ( ! isset($params['row_id']))
		{
			$params['row_id'] = (count($this->items) > 0) ? end(array_keys($this->items)) + 1 : 0;
		}
		
		if ( ! isset($params['quantity']))
		{
			$params['quantity'] = 1;
		}
		
		if ($item !== FALSE && ! $this->core->store->config('allow_products_more_than_once'))
		{
			if ( ! $item->in_stock($item_options))
			{
				$this->core->set_error(sprintf($this->core->lang('item_not_in_stock_add_to_cart'), $item->title()));
			}
			
			$final_quantity = $item->quantity() + $params['quantity'];

			if ($this->core->store->config('global_item_limit') && $params['quantity'] + $this->count_all(array('product_id' => $params['product_id'])) > $this->core->store->config('global_item_limit'))
			{
				$final_quantity = $this->core->store->config('global_item_limit');
			}
			
			$inventory = $item->inventory($item_options);
			
			$this->core->hooks->set_hook('quantity_in_cart')->run($item);
			
			$quantity_in_cart = $this->core->hooks->value();
			
			if ($inventory !== FALSE && ($params['quantity'] + $quantity_in_cart) > $inventory)
			{
				$msg = ($inventory == 1) ? $this->core->lang('item_quantity_greater_than_stock_add_to_cart_one') : $this->core->lang('item_quantity_greater_than_stock_add_to_cart');
				
				$this->core->set_error(sprintf($msg, $inventory, $item->title(), $inventory));
			}

			$item->set_quantity($final_quantity);
		}
		else
		{
			//$product = (isset($params['product_id'])) ? $this->core->store->product($params['product_id']) : FALSE;
			
			$item = Cartthrob_core::create_child($this->core, 'item_'.$params['class'], $params, $this->core->item_defaults);
			
			$this->core->hooks->set_hook('quantity_in_cart')->run($item);
			
			$quantity_in_cart = $this->core->hooks->value();
			
			$error = FALSE;
			
			$inventory = $item->inventory($item_options);
			
			if ( ! $item->in_stock($item_options))
			{
				$this->core->set_error(sprintf($this->core->lang('item_not_in_stock_add_to_cart'), $item->title(), $item->inventory($item_options)));
			
				$error = TRUE;
			}
			else if ($params['quantity'] > $inventory || ($params['quantity'] + $quantity_in_cart) > $inventory)
			{
				$msg = ($inventory == 1) ? $this->core->lang('item_quantity_greater_than_stock_add_to_cart_one') : $this->core->lang('item_quantity_greater_than_stock_add_to_cart');
				
				$this->core->set_error(sprintf($msg, $inventory, $item->title(), $inventory));
				
				$error = TRUE;
			}
			
			if ($error === FALSE)
			{
				if ($this->core->store->config('product_split_items_by_quantity') && $params['quantity'] > 1)
				{
					unset($item);
					
					$quantity = $params['quantity'];
					
					$params['quantity'] = 1;
					
					for ($i = 1; $i <= $quantity; $i++)
					{
						$this->items[$params['row_id']] = Cartthrob_core::create_child($this->core, 'item_'.$params['class'], $params, $this->core->item_defaults);
						
						$params['row_id']++;
					}
					
					$params['row_id']--;
				}
				else
				{
					$this->items[$params['row_id']] = $item;
				}
			}
		}
		
		//this hook call doesn't return a value
		$this->core->hooks->set_hook('cart_add_item_end')->run($this->item($params['row_id']), $params);
		
		return $this->item($params['row_id']);
	}
	
	/**
	 * Update an item in cart, identified by row_id
	 *
	 * @access public
	 * @param int $row_id
	 * @param array $params
	 * @return Cartthrob_cart
	 */
	public function update_item($row_id, $params = array())
	{
		if (FALSE !== ($item = $this->item($row_id)))
		{
			$item->update($params);
		}
		
		return $this;
	}
	
	/**
	 * Retrieve the saved coupon codes for this cart
	 *
	 * @access public
	 * @return array
	 */
	public function coupon_codes()
	{
		return $this->coupon_codes;
	}
	
	/**
	 * Clear this cart's coupon codes
	 *
	 * @access public
	 * @return Cartthrob_cart
	 */
	public function clear_coupon_codes()
	{
		$this->coupon_codes = array();
		
		return $this;
	}
	
	/**
	 * Add a coupon code to this cart, with validation
	 *
	 * @access public
	 * @param string $coupon_code
	 * @return Cartthrob_cart
	 */
	public function add_coupon_code($coupon_code)
	{
		if ($coupon_code && $this->core->validate_coupon_code($coupon_code))
		{
			//in the case of a coupon limit of 1, we'll overwrite the coupon code
			if ($this->core->store->config('global_coupon_limit') == 1 && count($this->coupon_codes()) >= 1)
			{
				$this->coupon_codes = array($coupon_code);
			}
			else
			{
				if ( ! in_array($coupon_code, $this->coupon_codes))
				{
					$this->coupon_codes[] = $coupon_code;
				}
			}
		}
		
		return $this;
	}
	
	/**
	 * Remove an item from cart, indentified by row_id
	 *
	 * @access public
	 * @param int $row_id
	 * @return Cartthrob_cart
	 */
	public function remove_item($row_id)
	{
		if ($this->item($row_id))
		{
			unset($this->items[$row_id]);
		}
		
		return $this;
	}
	
	/**
	 * Duplicate an item from cart, indentified by row_id
	 *
	 * @access public
	 * @param array $params
	 * @return Cartthrob_cart
	 */
	public function duplicate_item($row_id, $params = array())
	{
		if (is_object($row_id) && $row_id instanceof Cartthrob_item)
		{
			$item = $row_id;
		}
		else
		{
			$item = $this->item($row_id);
		}
		
		if ($item)
		{
			$params = array_merge($item->to_array(), $params);
			
			$params['row_id'] = (count($this->items) > 0) ? end(array_keys($this->items)) + 1 : 0;
			
			return $this->add_item($params);
		}
		
		return FALSE;
	}
	
	/**
	 * Check the inventory status of each item in cart
	 *
	 * errors are added to $this->errors
	 *
	 * @access public
	 * @return bool
	 */
	public function check_inventory()
	{
		//$this->clear_errors();
		$pass = TRUE;
		
		$items_already_checked = array();
		
		foreach ($this->items() as $row_id => $item)
		{
			if ( ! $item->product_id() || in_array($row_id, $items_already_checked))
			{
				continue;
			}
			
			$this->core->hooks->set_hook('quantity_in_cart')->run($item);
			
			$quantity = $this->core->hooks->value();
			
			if (!$quantity)
			{
				return FALSE; 
			}
			$inventory = $item->inventory($item->item_options());
			
			if ($inventory !== FALSE)
			{
				if ($inventory <= 0)
				{
					$this->core->set_error(sprintf($this->core->lang('item_not_in_stock'), $item->title()));
					
					$pass = FALSE;
					return $pass; 
				}
				if ($quantity > $inventory )
				{
					$msg = ($inventory == 1) ? $this->core->lang('item_quantity_greater_than_stock_one') : $this->core->lang('item_quantity_greater_than_stock');
					
					$this->core->set_error(sprintf($msg, $inventory, $item->title(), $quantity - $inventory));
					
					$pass = FALSE;
					return $pass; 
				}
			}
		}
		
		return $pass;
	}
	
	public function last_item()
	{
		$item = end($this->items);
		
		reset($this->items);
		
		return $item;
	}
	
	public function last_row_id()
	{
		$item = end($this->items);
		
		reset($this->items);
		
		return ($item) ? $item->row_id() : FALSE;
	}
	
	/**
	 * Retrieve an item from the cart, or return false
	 *
	 * @access public
	 * @param string $row_id
	 * @return Cartthrob_item|false
	 */
	public function item($row_id)
	{
		return ($row_id !== FALSE && isset($this->items[$row_id])) ? $this->items[$row_id] : FALSE;
	}
	
	/**
	 * Find the first item in cart that matches all the data provided, or FALSE if no item found
	 *
	 * @access public
	 * @param array $data
	 * @return Cartthrob_item|false
	 */
	public function find_item(array $data)
	{
		$filtered_items = $this->filter_items($data); 
		if (!empty($filtered_items) && count($filtered_items) >0)
		{
			return current($this->filter_items($data));
		}
		return FALSE;
	}
	
	/**
	 * Retrieve all the items in the cart that match the provided data
	 *
	 * @access public
	 * @param array $data
	 * @return array of Cartthrob_item
	 */
	public function filter_items($data = FALSE, $include_sub_items = FALSE)
	{
		$all_items = $this->items();
	
		if ($include_sub_items)
		{
			foreach ($this->items() as $item)
			{
				if ($item->sub_items())
				{
					foreach ($item->sub_items() as $_item)
					{
						$all_items[$_item->row_id()] = $_item;
					}
				}
			}
		}
		
		if ( ! $data || ! is_array($data))
		{
			return $all_items;
		}
		
		$items = array();
		
		foreach ($all_items as $item)
		{
			$array = $item->to_array();
			
			$match = TRUE;
			
			foreach ($data as $key => $value)
			{
				if (is_array($value))
				{
					foreach ($value as $k => $v)
					{
						if (is_array($v))
						{
							foreach ($v as $_k => $_v)
							{
								if (isset($array[$key][$k][$_k]) && $_v !== $array[$key][$k][$_k])
								{
									$match = FALSE;
								}
							}
						}
						else
						{
							if (isset($array[$key][$k]) && $v !== $array[$key][$k])
							{
								$match = FALSE;
							}
						}
					}
				}
				else
				{
					if (isset($array[$key]) && $value !== $array[$key])
					{
						$match = FALSE;
					}
				}
			}
			
			if ($match)
			{
				$items[$item->row_id()] = $item;
			}
		}
		
		return $items;
	}
	
	/**
	 * Retrieve all the items in the cart
	 *
	 * @access public
	 * @return array
	 */
	public function items()
	{
		return $this->items;
	}
	
	/**
	 * Retrieve all the items in the cart in array form
	 *
	 * @access public
	 * @return array
	 */
	public function items_array()
	{
		$items = array();
		
		foreach ($this->items as $row_id => $item)
		{
			$items[$row_id] = $item->data();
		}
		
		return $items;
	}
	
	/**
	 * Set a value in the customer info array,
	 * or set many values by providing an array.
	 *
	 * @param   array|string $key
	 * @param   mixed $value
	 * @return  Cartthrob_cart
	 */
	public function set_customer_info($key, $value = FALSE)
	{
		if ( ! is_array($key))
		{
			$key = array($key => $value);
		}
		
		$this->customer_info = array_merge($this->customer_info, $key);
		
		return $this;
	}
	
	/**
	 * Get a value from the customer info array, or
	 * get the whole array by not specifying a key
	 *
	 * @param   string|false $key
	 * @return  mixed|false
	 */
	public function customer_info($key = FALSE)
	{
		if ($key === FALSE)
		{
			return $this->customer_info;
		}
		
		return (isset($this->customer_info[$key])) ? $this->customer_info[$key] : FALSE;
	}
	
	/**
	 * Reset all default customer info values to empty
	 *
	 * @return  Cartthrob_cart
	 */
	public function clear_customer_info()
	{
		foreach ($this->core->customer_info_defaults as $key => $value)
		{
			$this->customer_info[$key] = $value;
		}
		
		return $this;
	}
	
	/**
	 * Set a value in the shipping info array,
	 * or set many values by providing an array.
	 *
	 * @param   array|string $key
	 * @param   mixed $value
	 * @return  Cartthrob_cart
	 */
	public function set_shipping_info($key, $value = FALSE)
	{
		if ( ! is_array($key))
		{
			$key = array($key => $value);
		}
		
		$this->shipping_info = array_merge($this->shipping_info, $key);
		
		return $this;
	}
	
	/**
	 * Get a value from the shipping info array, or
	 * get the whole array by not specifying a key
	 *
	 * @param   string|false $key
	 * @return  mixed|false
	 */
	public function shipping_info($key = FALSE)
	{
		if ($key === FALSE)
		{
			return $this->shipping_info;
		}
		
		return (isset($this->shipping_info[$key])) ? $this->shipping_info[$key] : FALSE;
	}
	
	/**
	 * Set a value in the custom data array,
	 * or set many values by providing an array.
	 *
	 * @param   array|string $key
	 * @param   mixed $value
	 * @return  Cartthrob_cart
	 */
	public function set_custom_data($key, $value = FALSE)
	{
		if ( ! is_array($key))
		{
			$key = array($key => $value);
		}
		
		$this->custom_data = array_merge($this->custom_data, $key);
		
		return $this;
	}
	
	/**
	 * Get a value from the custom data array, or
	 * get the whole array by not specifying a key
	 *
	 * @param   string|false $key
	 * @return  mixed|false
	 */
	public function custom_data($key = FALSE)
	{
		if ($key === FALSE)
		{
			return $this->custom_data;
		}
		
		return (isset($this->custom_data[$key])) ? $this->custom_data[$key] : FALSE;
	}
	
	/**
	 * Removes all custom data
	 * 
	 * @return Cartthrob_cart
	 */
	public function clear_custom_data()
	{
		$this->custom_data = array();
		
		return $this;
	}
	
	/**
	 * Clears all manually entered totals
	 * 
	 * @return Cartthrob_cart
	 */
	public function clear_totals()
	{
		foreach (array('subtotal', 'total', 'tax', 'shipping', 'discount', 'shippable_subtotal', 'taxable_subtotal') as $key)
		{
			$this->{'set_'.$key}(NULL);
		}
		
		return $this;
	}
	
	/**
	 * Get the number of items in the cart,
	 * W/ optional filter array
	 *
	 * @param   array|false $key
	 * @return  int
	 */
	public function count($filter = FALSE)
	{
		return count($this->filter_items($filter));
	}
	
	/**
	 * True if no items in cart
	 *
	 * @param   array|false $key
	 * @return  bool
	 */
	public function is_empty()
	{
		return $this->count() === 0;
	}
	
	/**
	 * Get all of the unique product ids in the cart
	 *
	 * @return  array
	 */
	public function product_ids()
	{
		$product_ids = array();
		
		foreach ($this->items as $item)
		{
			if ($item->product_id())
			{
				$product_ids[] = $item->product_id();
			}
		}
		
		return array_unique($product_ids);
	}
	
	/**
	 * Get the sum of the quantity of items in the cart
	 * Optional filter array
	 *
	 * @param array|false $key
	 * @return int
	 */
	public function count_all($filter = FALSE)
	{
		$count = 0;
		
		foreach ($this->filter_items($filter) as $item)
		{
			$count += $item->quantity();
		}
		
		return $count;
	}
	
	/**
	 * Remove all items from the cart
	 *
	 * @return Cartthrob_cart
	 */
	public function clear()
	{
		$this->items = array();
		
		return $this;
	}
	
	/**
	 * Remove all the shipping info values
	 *
	 * @return  Cartthrob_cart
	 */
	public function clear_shipping_info()
	{
		$this->shipping_info = array();
		
		return $this;
	}
	
	/**
	 * Manually set the cart discount amount, set to null to return to normal calculation
	 * 
	 * @param string|int|float|null $discount
	 * 
	 * @return Cartthrob_cart
	 */
	public function set_discount($discount)
	{
		$this->discount = $discount;
		
		$this->cache('discount', NULL);
		
		return $this;
	}
	
	/**
	 * Get the amount of discount associated with this cart
	 *
	 * @param bool $clear_cache reset the cached value
	 * @param string $id id of the coupon/discount. If you only want the value of ONE in use discount, use this. 
	 * 
	 * @return string|float
	 */
	public function discount($clear_cache = FALSE, $id = NULL, $code = NULL)
	{
		if ($clear_cache === TRUE)
		{
			$this->cache(__FUNCTION__, NULL);
		}
		
		$cache = $this->cache(__FUNCTION__);
		
		$coupon_data = md5(serialize( $this->coupon_codes() )); 
		$discount_data = md5(serialize( $this->core->get_discount_data() )); 
		$item_data = md5(serialize( $this->items_array() )); 
		if ($item_data != $this->meta("item_hash") || $coupon_data != $this->meta("coupon_hash") || $discount_data != $this->meta('discount_hash'))
		{
		// when discounts & coupons manually set the shipping cost... it never gets unset, which is a problem if the coupon's cancelled out. 

		// this method clears the discounted shipping cost each time the discount function is run
		// and the coupon can manually set it again
		// coupons should use $this->core->cart->set_discounted_shipping($value); 
			// when the discounts are updated, the shipping discount is reset. 
			$this->reset_discounted_shipping(); 
		}
		
		$this->set_meta("coupon_hash", $coupon_data); 
		$this->set_meta("discount_hash", $discount_data); 
		$this->set_meta("item_hash", $item_data); 
		$this->core->save_cart();
		
		if ($this->calculation_caching === FALSE || is_null($cache))
		{
			if ( ! is_null($this->discount))
			{
				$cache = $this->discount;
			}
			else if ($this->core->hooks->set_hook('cart_discount_start')->run() && $this->core->hooks->end())
			{
				$cache = $this->core->hooks->value();
			}
			else
			{
				$cache = 0;
		
				foreach ($this->coupon_codes() as $coupon_code)
				{
					$data = $this->core->get_coupon_code_data($coupon_code);
					
					if ($this->core->validate_coupon_code($coupon_code) && ! empty($data['type']))
					{
						$plugin = $this->core->create_child($this->core, $data['type'], $data);

						$plugin->set_coupon_code($coupon_code);
						
						$d = 0; 
						if (method_exists($plugin, 'get_discount'))
						{
							$d = $this->core->round($plugin->get_discount());
							$cache += $d; 
						}

						unset($plugin);
 
						// @NOTE is cartthrob always going to have an entry id for discounts? 
						if ($code && $code == $coupon_code && $id && isset($data['metadata']['entry_id']) && $id == $data['metadata']['entry_id'])
						{
							return $d; 
						}
					}
				}
				
				foreach ($this->core->get_discount_data() as $data)
				{
					if (empty($data['type']))
					{
						continue;
					}

					$plugin = $this->core->create_child($this->core, $data['type'], $data);
						
					$d = 0; 
					
					if (method_exists($plugin, 'get_discount'))
					{
						$d = $this->core->round($plugin->get_discount()); 
						$cache += $d; 
					}

					unset($plugin);
					
					// @NOTE is cartthrob always going to have an entry id for discounts? 
					if ($id && isset($data['entry_id']) && $id == $data['entry_id'])
					{
						return $d; 
					}
				}
		
				$cache = ($cache > 0) ? $this->core->round($cache) : 0;
				
				if ($this->core->hooks->set_hook('cart_discount_end')->run($cache) && $this->core->hooks->end())
				{
					$cache = $this->core->hooks->value();
				}
			}
			
			$this->cache(__FUNCTION__, $cache);
		}
		
		return $this->core->round($cache);
	}
	
	/**
	 * Manually set the cart total, set to null to return to normal calculation
	 * 
	 * @param string|int|float|null $total
	 * 
	 * @return Cartthrob_cart
	 */
	public function set_total($total)
	{
		$this->total = $total;
		
		$this->cache('total', NULL);
		
		return $this;
	}
	/*
	 * outputs the tax on all of the items in the cart, excluding shipping taxes
	* do not use this for calculations
	*
	* @return float
	*/ 
	public function item_tax()
	{
		$amount = 0; 

		foreach ($this->taxable_items() as $item)
		{
			$amount +=  $item->tax() * $item->quantity();
		}

		return $amount; 
	}
	public function subtotal_with_tax()
	{
		$amount = 0; 
		
		foreach ($this->taxable_items() as $item)
		{
			$amount += $this->core->round($item->taxed_price_subtotal()); 
		}
	
		foreach ($this->non_taxable_items() as $item)
		{
			$amount +=  $this->core->round($item->price_subtotal()); 
		}

		return $this->core->round($amount); 
	}
	public function shipping_tax()
	{
		$amount = 0; 

		if ($this->core->store->config('tax_plugin'))
		{
			$plugin = $this->core->create_child($this->core, $this->core->store->config('tax_plugin'));
			
			if (method_exists($plugin, 'get_tax'))
			{
				if ($plugin->tax_shipping())
				{
					$amount =  $plugin->get_tax($this->core->cart->shipping(), "shipping" );
					if ( ! $this->core->store->config('round_tax_only_on_subtotal'))
					{
						$amount = $this->core->round($amount);
					}
					
				}
			}
		}
		return $amount;
	}
	public function shipping_plus_tax()
	{
		$shipping_plus_tax = $this->shipping() + $this->shipping_tax(); 
		
		return $this->core->round($shipping_plus_tax); 
	}
	public function discount_tax()
	{
		$this->taxable_discount = 0; 
		$amount = 0; 
		$subtotal = (float) $this->subtotal(); 
		$discount = (float) $this->discount(); 

 		if ($this->core->config('exempt_discount_from_tax') == TRUE)
		{
  			return 0; 
		}
 		
		// setting taxable discount amount
		if ( $subtotal && $discount)
		{
			$discount_percent = $discount / $subtotal;
			foreach ($this->taxable_items() as $item)
			{
				$this->taxable_discount += ($item->price() * $item->quantity() ) * $discount_percent; 
			}
			
			if ($this->core->store->config('tax_plugin'))
			{
				$plugin = $this->core->create_child($this->core, $this->core->store->config('tax_plugin'));

				if (method_exists($plugin, 'get_tax'))
				{
					if ($this->core->cart->discount())
					{
						$amount =  $plugin->get_tax( $this->taxable_discount, "discount" ); 
						
						if ( ! $this->core->store->config('round_tax_only_on_subtotal'))
						{
 							$amount = $this->core->round($amount);
						}
						
					}
				}
			}
		}
		
		return $amount;
	}
 
	/**
	 * Get the total cost associated with this cart
	 *
	 * @param bool $clear_cache reset the cached value
	 *
	 * @return string|float
	 */
	public function total($clear_cache = TRUE)
	{
		if ($clear_cache === TRUE)
		{
			$this->cache(__FUNCTION__, NULL);
		}
		
		$cache = $this->cache(__FUNCTION__);
		
		if ($this->calculation_caching === FALSE || is_null($cache))
		{
			if ( ! is_null($this->total))
			{
				$cache = $this->total;
			}
			else if ($this->core->hooks->set_hook('cart_total_start')->run() && $this->core->hooks->end())
			{
				$cache = $this->core->hooks->value();
			}
			if ($cache <= 0)
			{
				$subtotal_with_tax = $this->subtotal_with_tax(); 
				if ($this->core->hooks->set_hook('cart_tax_end')->run($cache) && $this->core->hooks->end())
				{
					$tax = $this->core->hooks->value();
					if ($tax)
					{
						$subtotal_with_tax = $this->subtotal() + $tax; 
					}
				}
				// don't want to use subtotal function... because it uses rounding. we need to get the number without rounding. 
				$cache = $subtotal_with_tax +  $this->shipping() + $this->shipping_tax() - $this->discount() - $this->discount_tax();
				
				if ($this->core->hooks->set_hook('cart_total_end')->run($cache) && $this->core->hooks->end())
				{
					$cache = $this->core->hooks->value();
				}
			}
			
			$this->cache(__FUNCTION__, $cache);
		}
		
		if ($cache < 0)
		{
			$cache = 0;
		}
		
		return $this->core->round($cache);
	}
	
	/**
	 * Manually set the cart subtotal, set to null to return to normal calculation
	 * 
	 * @param string|int|float|null $subtotal
	 * 
	 * @return Cartthrob_cart
	 */
	public function set_subtotal($subtotal)
	{
		$this->subtotal = $subtotal;
		
		$this->cache('subtotal', NULL);
		
		return $this;
	}
	
	/**
	 * Get the subtotal cost associated with this cart
	 *
	 * @param bool $clear_cache reset the cached value
	 *
	 * @return string|float
	 */
	public function subtotal($clear_cache = FALSE)
	{
		if ($clear_cache === TRUE)
		{
			$this->cache(__FUNCTION__, NULL);
		}
		
		$cache = $this->cache(__FUNCTION__);
		
		if ($this->calculation_caching === FALSE || is_null($cache))
		{
			if ( ! is_null($this->subtotal))
			{
				$cache = $this->subtotal;
			}
			else
			{
				$cache = 0;
				
				foreach ($this->items() as $item)
				{
					$cache += $item->price_subtotal(); 
				}
			}
			
			$this->cache(__FUNCTION__, $cache);
		}
		
		return $this->core->round($cache);
	}
	
	/**
	 * Manually set the cart tax cost, set to null to return to normal calculation
	 * 
	 * @param string|int|float|null $tax
	 * 
	 * @return Cartthrob_cart
	 */
	public function set_tax($tax)
	{
		$this->tax = $tax;
		
		$this->cache('tax', NULL);
		
		return $this;
	}
	
	/**
	 * Get the amount of tax associated with this cart
	 *
	 * @param bool $clear_cache reset the cached value
	 *
	 * @return string|float
	 */
	public function tax($clear_cache = FALSE)
	{
		if ($clear_cache === TRUE)
		{
			$this->cache(__FUNCTION__, NULL);
		}
		
		$cache = $this->cache(__FUNCTION__);
		
		if ($this->calculation_caching === FALSE || is_null($cache))
		{
			if ( ! is_null($this->tax))
			{
				$cache = $this->tax;
			}
			else if ( ! $this->taxable_items())
			{
				//$cache = 0;
				// need to include the shipping tax here in the cases where users are taxing shipping, but there are no taxable items
				$cache = $this->shipping_tax();
			}
			else if ($this->core->hooks->set_hook('cart_tax_start')->run() && $this->core->hooks->end())
			{
				$cache = $this->core->hooks->value();
			}
			else
			{
				$cache = 0;
				$tax = 0;
						
				$tax += $this->item_tax(); 
				$tax -= $this->discount_tax(); 
				$tax += $this->shipping_tax(); 
						
				if ($tax < 0)
				{
					$tax = 0;
				}
				
				$cache = $this->core->round($tax);
				
				if ($this->core->hooks->set_hook('cart_tax_end')->run($cache) && $this->core->hooks->end())
				{
					$cache = $this->core->hooks->value();
				}
			}
			
			$this->cache(__FUNCTION__, $cache);
		}
		
		return $this->core->round($cache);
	}
	
	/**
	 * Manually set the cart shipping cost, set to null to return to normal calculation
	 * 
	 * @param string|int|float|null $shipping
	 * 
	 * @return Cartthrob_cart
	 */
	public function set_shipping($shipping)
	{
		$this->shipping = $shipping;
		
		$this->cache('shipping', NULL);
		
		return $this;
	}
	
	public function reset_discounted_shipping()
	{
		$this->set_discounted_shipping(NULL); 
		$this->shipping = NULL; 
	}
	/**
	 * Manually set the cart shipping discount, set to null to return to normal calculation
	 * 
	 * @param string|int|float|null $shipping
	 * 
	 * @return Cartthrob_cart
	 */
	public function set_discounted_shipping($discounted_shipping)
	{
 		if ($discounted_shipping === FALSE || !is_numeric($discounted_shipping) )
		{
			$discounted_shipping = NULL ; 
		}
		
 		$this->set_meta("discounted_shipping", $discounted_shipping);
		#$this->set_shipping($discounted_shipping); 
		return $this;
	}
	
	public function discounted_shipping()
	{
		if ($this->meta('discounted_shipping') === FALSE || !is_numeric($this->meta('discounted_shipping')))
		{
			return NULL; 
		}
		return $this->meta('discounted_shipping'); 
		#return $this->discounted_shipping; 
	}
	
	/**
	 * Get the shipping cost associated with this cart
	 *
	 * @param bool $clear_cache reset the cached value
	 *
	 * @return string|float
	 */
	public function shipping($clear_cache = FALSE)
	{
		if ($clear_cache === TRUE)
		{
			$this->cache(__FUNCTION__, NULL);
		}
		
		$cache = $this->cache(__FUNCTION__);
		
		if ($this->calculation_caching === FALSE || is_null($cache))
		{
			if ( ! is_null($this->shipping))
			{
				$cache = $this->shipping;
			}
			else if ( ! $this->shippable_items())
			{
				$cache = 0;
			}
			else if ($this->core->hooks->set_hook('cart_shipping_start')->run() && $this->core->hooks->end())
			{
				$cache = $this->core->hooks->value();
			}
			else
			{
				$cache = 0;
				
				if ($this->core->store->config('shipping_plugin'))
				{
					$plugin = $this->core->create_child($this->core, $this->core->store->config('shipping_plugin'));
					
					if (method_exists($plugin, 'get_shipping'))
					{
						$cache = $this->core->round($plugin->get_shipping());
					}
				}
				else
				{
					foreach ($this->core->cart->items() as $item)
					{
						$cache += $item->shipping();
					}
				}
				
				if ($this->core->hooks->set_hook('cart_shipping_end')->run($this->shipping) && $this->core->hooks->end())
				{
					$cache = $this->core->hooks->value();
				}
			}
			$this->set_shipping_before_discount($cache); 
			
			$this->cache(__FUNCTION__, $cache);
		}
		
 		if ( $this->discounted_shipping() !== NULL)
		{
  			$this->cache(__FUNCTION__,  $this->discounted_shipping() );
			return $this->core->round( $this->discounted_shipping() );
		}
		
		return $this->core->round($cache);
	}
	private function set_shipping_before_discount($cache)
	{
		$this->set_meta("shipping_before_discount", $cache); 
	}
	public function shipping_before_discount()
	{
		return $this->meta("shipping_before_discount"); 
	}
	/**
	 * Get the customer's selected shipping option
	 *
	 * @return string|false
	 */
	public function shipping_option()
	{
		return $this->shipping_info('shipping_option');
	}
	
	/**
	 * Save the serialized cart to session
	 * using core driver's save_cart method
	 *
	 * @return Cartthrob_cart
	 */
	public function save()
	{
		$this->core->save_cart();
		
		return $this;
	}
	
	/**
	 * Get the total weight of the items in cart
	 *
	 * @param $items array|false an array of cart items to use in the weight calculation, set to false to check all items
	 * @return int|float
	 */
	public function weight($items = FALSE)
	{
		$weight = 0;
		
		if ($items === FALSE)
		{
			$items = $this->items;
		}
		
		foreach ($items as $item)
		{
			$weight += $item->quantity() * $item->weight();
		}
		
		return $weight;
	}
	
	/**
	 * Get an array of items that are not marked no_shipping
	 *
	 * @return array
	 */
	public function shippable_items()
	{
		$items = array();
		
		foreach ($this->items as $item)
		{
			if ($item->is_shippable())
			{
				$items[$item->row_id()] = $item;
			}
		}
		
		return $items;
	}
	
	/**
	 * Get an array of items that are marked no_shipping
	 *
	 * @return array
	 */
	public function non_shippable_items()
	{
		$items = array();
		
		foreach ($this->items as $item)
		{
			if ( ! $item->is_shippable())
			{
				$items[$item->row_id()] = $item;
			}
		}
		
		return $items;
	}
	
	/**
	 * Manually set the cart shippable subtotal, set to null to return to normal calculation
	 * 
	 * @param string|int|float|null $shippable_subtotal
	 * 
	 * @return Cartthrob_cart
	 */
	public function set_shippable_subtotal($shippable_subtotal)
	{
		$this->shippable_subtotal = $shippable_subtotal;
		
		$this->cache('shippable_subtotal', NULL);
		
		return $this;
	}
	
	/**
	 * Get the subtotal cost of items not marked no_shipping
	 *
	 * @return int|float
	 */
	public function shippable_subtotal($clear_cache = FALSE)
	{
		if ($clear_cache === TRUE)
		{
			$this->cache(__FUNCTION__, NULL);
		}
		
		$cache = $this->cache(__FUNCTION__);
		
		if ($this->calculation_caching === FALSE || is_null($cache))
		{
			if ( ! is_null($this->shippable_subtotal))
			{
				$cache = $this->shippable_subtotal;
			}
			else
			{
				$cache = 0;
				
				foreach ($this->shippable_items() as $item)
				{
					$cache += $item->price_subtotal();
				}
			}
			
			$this->cache(__FUNCTION__, $cache);
		}
		
		return $this->core->round($cache);
	}
	
	/**
	 * Get the total weight of shippable items in the cart
	 * 
	 * @return Type    Description
	 */
	public function shippable_weight()
	{
		return $this->weight($this->shippable_items());
	}
	
	/**
	 * Get an array of items that are not marked no_tax
	 *
	 * @return array
	 */
	public function taxable_items()
	{
		$items = array();
		
		foreach ($this->items as $item)
		{
			if ($item->is_taxable())
			{
				$items[$item->row_id()] = $item;
			}
		}
		
		return $items;
	}
	
	/**
	 * Get an array of items that are marked no_tax
	 *
	 * @return array
	 */
	public function non_taxable_items()
	{
		$items = array();
		
		foreach ($this->items as $item)
		{
			if ( ! $item->is_taxable())
			{
				$items[$item->row_id()] = $item;
			}
		}
		
		return $items;
	}
	
	/**
	 * Manually set the cart taxable subtotal, set to null to return to normal calculation
	 * 
	 * @param string|int|float|null $taxable_subtotal
	 * 
	 * @return Cartthrob_cart
	 */
	public function set_taxable_subtotal($taxable_subtotal)
	{
		$this->taxable_subtotal = $taxable_subtotal;
		
		$this->cache('taxable_subtotal', NULL);
		
		return $this;
	}
	
	/**
	 * Get the subtotal cost of items not marked no_tax
	 * This can not be used elswhere to calculate taxes. This should only be used when displaying the subtotal of items that include tax, and not in calculations
	 *
	 * @return int|float
	 */
	public function taxable_subtotal($clear_cache = FALSE)
	{
		if ($clear_cache === TRUE)
		{
			$this->cache(__FUNCTION__, NULL);
		}
		
		$cache = $this->cache(__FUNCTION__);
		
		if ($this->calculation_caching === FALSE || is_null($cache))
		{
			if ( ! is_null($this->taxable_subtotal))
			{
				$cache = $this->taxable_subtotal;
			}
			else
			{
				$cache = 0;
				
				foreach ($this->taxable_items() as $item)
				{
					$cache += $item->subtotal(); 
				}
			}
			
			$this->cache(__FUNCTION__, $cache);
		}
		return $this->core->round($cache);
	}
	
	/* Cartthrob_child */
	
	public function to_array()
	{
		$data = parent::to_array();
		
		$data['items'] = $this->items_array();
		/*
		$data['items'] = array();
		
		foreach ($this->items as $row_id => $item)
		{
			$data['items'][$row_id] = $item->to_array();
		}
		*/
		
		return $data;
	}
	
	public function initialize($params = array(), $defaults = array())
	{
		if (isset($params['id']))
		{
			$this->id = $params['id'];
		}
		
		$this->defaults['customer_info'] = $this->core->customer_info_defaults;
		
		$items = (isset($params['items'])) ? $params['items'] : array();
		
		unset($params['items']);
		
		if (is_array($this->core->store->config('default_location')))
		{
			foreach ($this->core->store->config('default_location') as $key => $value)
			{
				$this->defaults['customer_info'][$key] = $value;
			}
		}
		
		$this->defaults['customer_info']['currency_code'] = (string) $this->core->store->config('number_format_defaults_currency_code');
		
		parent::initialize($params);
		
		foreach ($items as $row_id => $item)
		{
			$class = (isset($item['class'])) ? $item['class'] : 'default';
			
			if (isset($item['row_id']))
			{
				$row_id = $item['row_id'];
			}
			
			$this->items[$row_id] = Cartthrob_core::create_child($this->core, 'item_'.$class, $item, $this->core->item_defaults);
			//$this->add_item($item);
		}
		
		$this->core->store->override_config($this->config);
		
		return $this;
	}
}
