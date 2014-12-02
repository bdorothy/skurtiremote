<?php if ( ! defined('CARTTHROB_PATH')) Cartthrob_core::core_error('No direct script access allowed');

class Cartthrob_item_product extends Cartthrob_item
{
	protected $defaults = array(
		'row_id' => NULL,
		'quantity' => 1,
		'product_id' => NULL,
		'site_id'	=> NULL, 
		'shipping' => NULL,
		'weight' => NULL,
		'price' => NULL,
		'no_tax' => FALSE,
		'no_shipping' => FALSE,
		'item_options' => array(),
		'meta' => array(),
		'title' => NULL,
		'discounts' => array(),
	);
	
	/**
	 * Get the product title
	 *
	 * @return  string
	 */
	public function title()
	{
		return ( ! $this->title) ? $this->product()->title() : $this->title;
	}
	
	/**
	 * True if product inventory is greater than zero
	 *
	 * @return  string
	 */
	public function in_stock()
	{
		return $this->product()->in_stock($this->item_options());
	}
	
	/**
	 * Get the product's inventory, checked against item_options
	 *
	 * @return  string
	 */
	public function inventory()
	{
		return $this->product()->inventory($this->item_options());
	}
	
	/**
	 * Get a value from the meta array, or
	 * from the product's meta array, or
	 * get the whole array by not specifying a key
	 *
	 * @param   string|false $key
	 * @return  mixed|false
	 */
	public function meta($key = FALSE)
	{
		if ($key === FALSE)
		{
			return array_merge(parent::meta(), $this->product()->meta());
		}
		
		$meta = parent::meta($key);
	
		if ($meta === FALSE)
		{
			return $this->product()->meta($key);
		}
		
		return $meta;
	}
	
	//shortcut to this item's corresponding product object
	public function product($force_create = TRUE)
	{
		if ( ! $product = $this->core->store->product($this->product_id))
		{
			//create a NULLed product
			if ($force_create)
			{
				$product = Cartthrob_core::create_child($this->core, 'product');
			}
		}
		
		return $product;
	}
	
	public function base_price()
	{
		$item = clone $this;
		
		$item->clear_item_options();
		
		return $this->product()->price($item);
	}
	
	public function price()
	{
		if ( ! is_null($this->price))
		{
			return $this->price;
		}
		
		return $this->product()->price($this);
	}
	
	public function weight()
	{
		if ( ! is_null($this->weight))
		{
			return $this->weight;
		}
		
		return $this->product()->weight($this);
	}
	
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
			$shipping = (is_null($this->shipping)) ? $this->product()->shipping() * $this->quantity() : $this->shipping * $this->quantity();
			
			if ($this->core->hooks->set_hook('item_shipping_end')->run($shipping) && $this->core->hooks->end())
			{
				$shipping = $this->core->hooks->value();
			}
		}
		
		return $this->core->round($shipping);
	}
	
	public function data()
	{
		$data = $this->product()->to_array();
		
		if (isset($data['inventory']))
		{
			$data['inventory'] = $this->product()->inventory($this->item_options);
		}
		
		foreach ($this->to_array() as $key => $value)
		{
			$data[$key] = $value;
		}
		
		return $data;
	}
}