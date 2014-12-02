<?php if ( ! defined('CARTTHROB_PATH')) Cartthrob_core::core_error('No direct script access allowed');

class Cartthrob_item_package extends Cartthrob_item
{
	protected $sub_items;
	
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
		'sub_items' => array(),
		'discounts' => array(),
	);
	
	public function initialize($params)
	{	
		parent::initialize($params);
		
		if (isset($params['sub_items']))
		{
			$this->set_sub_items($params['sub_items']);
		}
	}
	
	public function sub_items()
	{
		return $this->sub_items;
	}
	
	public function sub_item($row_id)
	{
		return (isset($this->sub_items[$row_id])) ? $this->sub_items[$row_id] : FALSE;
	}
	
	protected function set_sub_items($items)
	{
		foreach ($items as $item)
		{
			$class = (isset($item['class'])) ? $item['class'] : 'default';
			
			//$item['parent_item'] =& $this;
			
			$this->sub_items[$item['row_id']] = Cartthrob_core::create_child($this->core, 'item_'.$class, $item, $this->core->item_defaults);
			
			$this->sub_items[$item['row_id']]->set_parent_item($this);
		}
	}
	
	//@TODO add fixed pricing too
	public function price()
	{
		//if the price is set explicitly via the product, then return it
		if (is_numeric($this->product()->price()))
		{
			return $this->product()->price($this->item_options());
		}
		
		$price = 0;
		
		foreach ($this->sub_items() as $row_id => $item)
		{
			$price += $item->price_subtotal(); 
		}
		
		return $price;
	}
	
	public function taxed_price()
	{
		if (is_numeric($this->product()->price()))
		{
			// @TODO if possible make this use item's methods of getting taxes. this may not always work if the item has a specific tax class
			return $this->product()->price($this) * (1 + $this->core->store->tax_rate());
		}
		
		$price = 0;
		
		foreach ($this->sub_items() as $item)
		{
			$price += $item->taxed_price_subtotal();
		}
		
		return $price;
	}
	
	public function inventory()
	{
		$inventory = FALSE;
		
		foreach ($this->sub_items() as $row_id => $item)
		{
			if ( ! $item->product_id())
			{
				continue;
			}
			
			$_inventory = floor($item->inventory($item->item_options()) / $item->quantity());
			
			if ($inventory === FALSE || $_inventory < $inventory)
			{
				$inventory = $_inventory;
			}
		}
		
		return ($inventory === FALSE) ? parent::inventory() : $inventory;
	}
	
	public function in_stock()
	{
		return $this->inventory() > 0;
	}
	
	public function weight()
	{
		//if the price is set explicitly via the product, then return it
		if (is_numeric($this->product()->weight()))
		{
 			return $this->product()->weight($this);
		}
		
		$weight = 0;
		
		foreach ($this->sub_items() as $item)
		{
			$weight += $item->weight() * $item->quantity();
		}
		
		return $weight;
	}
	
	public function data()
	{
		$data = $this->product()->to_array();

		foreach ($this->to_array() as $key => $value)
		{
			$data[$key] = $value;
		}
		
		foreach ($this->sub_items() as $row_id => $item)
		{
			$data['sub_items'][$row_id] = $item->data();
		}
		
		return $data;
	}
	
	public function to_array($strip_defaults = FALSE)
	{
		$data = parent::to_array($strip_defaults);
		
		foreach ($this->sub_items() as $row_id => $item)
		{
			$data['sub_items'][$row_id] = array_merge($item->to_array(), array('row_id' => $row_id));
		}
		
		return $data;
	}
	
	/**
	 * Get the product title
	 *
	 * @return  string
	 */
	public function title()
	{
		return $this->product()->title();
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
	
	/*item_product*/
	
	public function base_price()
	{
		$item = clone $this;
		
		$item->clear_item_options();
		
		return $this->product()->price($item);
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
	
	/**
	 * Update the item's attributes with an array
	 *
	 * @param   array $params
	 * @return  Cartthrob_item
	 */
	public function update($params)
	{
		$sub_items = (isset($params['sub_items'])) ? $params['sub_items'] : array();
		
		unset($params['sub_items']);
		
		parent::update($params);
		
		foreach ($sub_items as $row_id => $item)
		{
			if (isset($this->sub_items[$row_id]))
			{
				$this->sub_items[$row_id]->update($item);
			}
		}
		
		//@TODO do something with sub_items
	}
}