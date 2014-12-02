<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cartthrob_product extends Cartthrob_child
{
	protected $product_id, $price, $weight, $shipping, $title, $item_options, $meta, $inventory, $categories;
	
	protected $defaults = array(
		'product_id' => NULL,
		'price' => 0,
		'weight' => 0,
		'shipping' => 0,
		'title' => '',
		'item_options' => array(),
		'meta' => array(),
		'inventory' => 0,
		'categories' => array(),
	);
	
	public function categories()
	{
		return $this->categories;
	}
	
	public function meta($key = FALSE)
	{
		$this->core->hooks->set_hook('product_meta');
		
		if ($this->core->hooks->run($this, $key) && $this->core->hooks->end())
		{
			return $this->core->hooks->value();
		}
		
		if ($key === FALSE)
		{
			return $this->meta;
		}
		
		return (isset($this->meta[$key])) ? $this->meta[$key] : FALSE;
	}
	
	public function title()
	{
		return $this->title;
	}
	
	public function inventory($item_options = array())
	{
		$this->core->hooks->set_hook('product_inventory');
		
		if ($this->core->hooks->run($this, $item_options) && $this->core->hooks->end())
		{
			return $this->core->hooks->value();
		}
		
		return $this->inventory;
	}
	
	public function reduce_inventory($quantity = 1)
	{
		$this->core->hooks->set_hook('product_reduce_inventory');
		
		$args = func_get_args();
		array_shift($args);
		
		if ($this->core->hooks->run($this, $quantity, $args) && $this->core->hooks->end())
		{
			return $this;
		}
		
		$this->inventory -= $quantity;
		
		return $this;
	}
	
	public function in_stock($item_options = array())
	{
		return $this->inventory($item_options) > 0;
	}
	
	public function item_options($key = FALSE)
	{
		if ($key === FALSE)
		{
			return $this->item_options;
		}
		
		return (isset($this->item_options[$key])) ? $this->item_options[$key] : FALSE;
	}
	
	public function set_item_options($data)
	{
		if (is_array($data))
		{
			$this->item_options = array_merge($this->item_options, $data);
		}
		
		return $this;
	}
	
	public function product_id()
	{
		return $this->product_id;
	}
	
	/**
	 * Get the product price, (option) w/ modifiers
	 * 
	 * @param array|Cartthrob_item|false $item a cart item object or an array of item_options
	 * 
	 * @return float
	 */
	public function price($item = FALSE)
	{
		$price = $this->price;
		
		if ($this->core->hooks->set_hook('product_price')->run($this, $item))
		{
			if ($this->core->hooks->end())
			{
				return $this->core->hooks->value();
			}
			
			if ( ! is_null($this->core->hooks->value()))
			{
				$price = $this->core->hooks->value();
			}
		}
		
		$item_options = array();
		
		if ($item instanceof Cartthrob_item)
		{
			$item_options = $item->item_options();
		}
		else if (is_array($item))
		{
			$item_options = $item;
		}
		
		foreach ($item_options as $key => $value)
		{
			if ( ! isset($this->item_options[$key]))
			{
				continue;
			}
			
			foreach ($this->item_options[$key] as $row)
			{
				if ($row['option_value'] === $value)
				{
					$price += $row['price'];
					break;
				}
			}
		}
		
		return $price;
	}
	
	public function weight($item = FALSE)
	{
		$weight = $this->weight;
		
		$item_options = array();
		
		if ($item instanceof Cartthrob_item)
		{
			$item_options = $item->item_options();
		}
		else if (is_array($item))
		{
			$item_options = $item;
		}
		
		// one of the above might turn item options into a string. oops. fix it here. 
		if (!is_array($item_options))
		{
			$item_options = array(); 
		}
		foreach ($item_options as $key => $value)
		{
			if ( ! isset($this->item_options[$key]))
			{
				continue;
			}
			
			foreach ($this->item_options[$key] as $row)
			{
				if ($row['option_value'] === $value)
				{
					if (isset($row['weight']))
					{
						$weight += $row['weight'];
					}
					break;
				}
			}
		}
		return $weight;
	}
	
	public function shipping()
	{
		if ($this->core->hooks->set_hook('product_shipping_start')->run() && $this->core->hooks->end())
		{
			$shipping = $this->core->hooks->value();
		}
		else
		{
			$shipping = $this->shipping;
			
			if ($this->core->hooks->set_hook('product_shipping_end')->run($shipping) && $this->core->hooks->end())
			{
				$shipping = $this->core->hooks->value();
			}
		}
		
		return $this->core->round($shipping);
	}
	
	public function to_array()
	{
		$data = array();
		
		foreach ($this->defaults as $key => $value)
		{
			if (in_array($key, array('item')))
			{
				continue;
			}
			
			$data[$key] = $this->$key;
		}
		
		return $data;
	}
}