<?php if ( ! defined('CARTTHROB_PATH')) Cartthrob_core::core_error('No direct script access allowed');

class Cartthrob_core_ee extends Cartthrob_core
{
	public $item_defaults = array(
		'entry_id' => NULL,
		//'expiration_date' => NULL,
		//'license_number' => NULL
	);
	
	public $product_defaults = array(
		'entry_id' => NULL,
		'url_title' => NULL
	);
	
	public $hooks = array(
		'cart_total_start',
		'cart_total_end',
		'cart_discount_start',
		'cart_tax_end',
		'cart_shipping_end',
		'product_reduce_inventory',
		'product_meta',
		'product_price',
		'product_inventory',
		'quantity_in_cart',
	);
	
	private $cart_hash;
	
	public function __construct()
	{
		$this->EE =& get_instance();
		
		$this->EE->load->model('cartthrob_settings_model');
		
		$this->config =& $this->EE->cartthrob_settings_model->get_settings();
		
		$this->customer_info_defaults = $this->config('customer_info_defaults');
		
		$this->EE->lang->loadfile('cartthrob_errors', 'cartthrob');
		
		$this->EE->lang->loadfile('cartthrob', 'cartthrob');
		
		if ( ! $third_party_path = $this->EE->config->item('cartthrob_third_party_path'))
		{
			$third_party_path = PATH_THIRD.'cartthrob/third_party/';
		}
		
		self::add_plugin_path('shipping', $third_party_path.'shipping_plugins/');
		self::add_plugin_path('discount', $third_party_path.'discount_plugins/');
		self::add_plugin_path('tax', $third_party_path.'tax_plugins/');
		self::add_plugin_path('price', $third_party_path.'price_plugins/');
	}
	
	/* core */
	
	public function set_config($key, $value = FALSE)
	{
		$this->EE->cartthrob_settings_model->set_item($key, $value);
		
		return $this;
	}
	
	public function override_config($override_config)
	{
		if ( ! is_array($override_config))
		{
			return $this;
		}
		
		foreach ($override_config as $key => $value)
		{
			$this->EE->cartthrob_settings_model->set_item($key, $value);
		}
		
		return $this;
	}
	
	public function config($args = NULL)
	{
		$args = (is_array($args)) ? $args : func_get_args();
		
		//this shouldn't really ever happen, but this will pick it up from the cache
		if ( ! $args)
		{
			return $this->EE->cartthrob_settings_model->get_settings();
		}
		
		if ( ! $config_key = array_shift($args))
		{
			return FALSE;
		}
		
		$config = $this->EE->config->item('cartthrob:'.$config_key);
		
		foreach ($args as $key)
		{
			if (isset($config[$key]))
			{
				$config = $config[$key];
			}
			else
			{
				return FALSE;
			}
		}
		
		return $config;
	}
	
	public function log($msg)
	{
		//log_message('debug', $msg);
		$this->EE->load->model('log_model');
		$this->EE->log_model->log($msg);
	}
	
	public function lang($key)
	{
		return $this->EE->lang->line($key);
	}
	
	public function get_hooks()
	{
		return array(
			'cart_total_start',
			'cart_total_end',
			'cart_discount_start',
			'cart_tax_end',
			'cart_shipping_end',
			'product_reduce_inventory',
			'product_meta',
			'product_price',
			'product_inventory',
			'quantity_in_cart',
		);
	}
	
	public function get_product($entry_id)
	{
		$this->EE->load->model('product_model');
		
		$product = self::create_child($this, 'product', $this->EE->product_model->get_product($entry_id), $this->product_defaults);
		
		$product->set_item_options($this->EE->product_model->get_all_price_modifiers($entry_id));
		
		return $product;
	}
	
	public function get_categories()
	{
		$this->EE->load->model('product_model');
		
		$categories = array();
		
		foreach ($this->EE->product_model->get_categories() as $category)
		{
			$categories[$category['category_id']] = $category['category_name'];
		}
		
		return $categories;
	}
	
	public function cart_array()
	{
		$cart = $this->cart->to_array();
		
		//let's strip the array of data that matches the default data
		//to minimize the size of the array before we save it
		foreach ($cart as $key => $value)
		{
			if ($value === $this->cart->defaults($key))
			{
				unset($cart[$key]);
			}
		}
		
		if (isset($cart['items']))
		{
			foreach ($cart['items'] as $row_id => $item)
			{
				foreach ($item as $key => $value)
				{
					if ($value === $this->cart->item($row_id)->defaults($key))
					{
						unset($cart['items'][$row_id][$key]);
					}
				}
			}
		}
		
		return $cart;
	}
	
	public function action_complete()
	{
		$this->save_cart();
		
		if ($this->EE->input->is_ajax_request())
		{
			$cart_info = $this->cart_info();
			
			$json_keys = array(
				'cart_total',
				'cart_subtotal',
				'cart_discount',
				'cart_tax',
				'cart_shipping',
			);
			
			foreach ($json_keys as $key)
			{
				$this->EE->session->set_flashdata($key, $cart_info[$key]);
			}
		}
	}
	
	public function save_cart()
	{
		$this->EE->load->model('cart_model');
		
		$id = $this->EE->cart_model->update_cart($this->cart->id(), $this->cart_array(), $this->EE->functions->fetch_current_uri());
		
		$this->cart->set_id($id);
	}
	
	public function process_inventory()
	{
		$inventory_reduce = array();

		foreach ($this->cart->items() as $item)
		{
			if ($item->product_id() && $product = $this->store->product($item->product_id()))
			{
				$product->reduce_inventory($item->quantity(), $item->item_options());
			}
			
			if ($item->sub_items())
			{
				foreach ($item->sub_items() as $sub_item)
				{
					if ($sub_item->product_id() && $product = $this->store->product($sub_item->product_id()))
					{
						// we should make it possible to set the sub item quantity in the package select. 
						// right now it's not possible to set, 
						// but the default value is 1. So X * package quantity will correctly reduce inventory in either case. 
						$product->reduce_inventory(($sub_item->quantity() * $item->quantity() ), $sub_item->item_options());
					}
				}
			}

			if (is_array($item->meta('inventory_reduce')))
			{
				foreach ($item->meta('inventory_reduce') as $entry_id => $quantity)
				{
					$inventory_reduce[] = array(
						'entry_id' => $entry_id,
						'quantity' => $quantity
					);
				}
			}
		}

		foreach ($inventory_reduce as $row)
		{
			if ($product = $this->store->product($row['entry_id']))
			{
				$product->reduce_inventory($row['quantity']);
			}
		}
		
		return $this;
	}
	
	//formerly process_coupon_codes
	public function process_discounts()
	{
		$this->EE->load->model('discount_model');
		
		$this->EE->load->model('coupon_code_model');
		
		$this->EE->discount_model->process_discounts();
		
		$this->EE->coupon_code_model->process_coupon_codes();
		
		return $this;
	}
	
	public function validate_coupon_code($coupon_code)
	{
		$this->EE->load->model('coupon_code_model');
		
		return $this->EE->coupon_code_model->validate_coupon_code($coupon_code);
	}
	
	public function get_coupon_code_data($coupon_code)
	{
		$this->EE->load->model('coupon_code_model');
		
		return $this->EE->coupon_code_model->get_coupon_code_data($coupon_code);
	}
	
	public function get_discount_data()
	{
		$this->EE->load->model('discount_model');
		
		return $this->EE->discount_model->get_valid_discounts();
	}
	
	public function set_config_customer_info($params)
	{
		if ( ! empty($params['field']) && isset($params['value']))
		{
			if (preg_match('/^customer_(.*)/', $params['field'], $match))
			{
				$params['field'] = $match[1];
			}
			
			$this->cart->set_customer_info($params['field'], $params['value']);
			
			$this->EE->load->model(array('member_model', 'customer_model'));
			
			if ($this->EE->session->userdata('member_id') && $this->store->config('save_member_data') && $field_id = $this->store->config('member_'.$params['field'].'_field'))
			{
				if (is_numeric($field_id))
				{
					if ($this->EE->customer_model->load_profile_edit())
					{
						$this->EE->db->update('channel_data', array('field_id_'.$field_id => $params['value']), array('entry_id' => $this->EE->profile_model->get_profile_id($this->EE->session->userdata('member_id'))));
					}
					else
					{
						$this->EE->member_model->update_member_data($this->EE->session->userdata('member_id'), array('m_field_id_'.$field_id => $params['value']));
					}
				}
				else
				{
					$this->EE->member_model->update_member($this->EE->session->userdata('member_id'), array($field_id => $params['value']));
				}
			}
		}
	}
	
	/**
	 * _set_config_shipping_plugin
	 *
	 * sets the selected shipping plugin
	 * 
	 * @param string $params shipping parameter short_name (ie. by_weight_ups_xml)
	 * @return void
	 * @author Chris Newton
	 * @since 1.0
	 */
	public function set_config_shipping_plugin($params)
	{
		if (isset($params['value']))
		{
			if (strpos($params['value'], 'shipping_') !== 0)
			{
				$params['value'] = 'shipping_'.$params['value'];
			}
			
			$this->cart->set_config('shipping_plugin', 'Cartthrob_'.$params['value']);
			
			$this->cart->shipping(TRUE);
		}
	}
	
	public function set_config_price_field($params)
	{
		if (empty($params['field']))
		{
			if (empty($params['value']))
			{
				return;
			}
			else
			{
				$params['field'] = $params['value'];
			}
		}
		
		if (empty($params['channel_id']) && empty($params['channel']))
		{
			return;
		}

		$this->EE->load->model('cartthrob_field_model');
		
		if ( ! ($field_id = $this->EE->cartthrob_field_model->get_field_id($params['field'])))
		{
			return;
		}
		if (!empty($params['channel']))
		{
			$params['channel_id'] = $this->EE->db->select('channel_id')->where('channel_name', $params['channel'])->get('channels')->row('channel_id');
 		}
		$product_channel_fields = ($this->store->config('product_channel_fields')) ? $this->store->config('product_channel_fields') : array();
		
		$product_channel_fields[$params['channel_id']]['price'] = $field_id;
		
		$this->cart->set_config('product_channel_fields', $product_channel_fields);
	}
	
	/* non-core utilities */
	// this has to be hit with POST data. 
	public function save_customer_info()
	{
		$this->EE->load->library('locales');
		$this->EE->load->model(array('cartthrob_members_model'));
		$this->EE->load->helper('data_formatting_helper');
	
		if ( ! isset($_POST['country_code']))
		{
			if ($this->EE->input->post('country') && $country_code = $this->EE->locales->country_code($this->EE->input->post('country')))
			{
				$_POST['country_code'] = $country_code;
			}
		}
		$this->EE->cartthrob->cart->meta('checkout_as_member'); 
		

		// there is a member id AND the person using the member id is an admin. If you're not an admin... this is ignored. 
		if ($this->EE->cartthrob->cart->meta('checkout_as_member')  && in_array($this->EE->session->userdata('group_id'), $this->EE->config->item('cartthrob:admin_checkout_groups')) )
		{
			$member_id = $this->EE->cartthrob->cart->meta('checkout_as_member'); 
		}
       		 elseif ($this->EE->session->userdata('member_id'))
        	{
            		$member_id = $this->EE->session->userdata('member_id');
		}
		
      		  $customer_info = $this->cart->customer_info();

      		  if (is_array($customer_info))
       		 {
            		foreach (array_keys($customer_info) as $field)
			{
				if ($this->EE->input->post($field) !== FALSE)
				{
					$this->cart->set_customer_info($field, $this->EE->input->post($field, TRUE));
			
					if (bool_string($this->cart->customer_info('use_billing_info')) && strpos($field, 'shipping_') !== FALSE)
					{
					/*
					$billing_field = str_replace("shipping_", "", $field); 
					// we're going to get the data from the billing field
					$this->cart->set_customer_info($field, $this->cart->customer_info($billing_field));
					*/ 
					
						// we're going to get the data from the billing field
						$this->cart->set_customer_info($field, $this->cart->customer_info($field));
					
					}
				}
			}
		
	        }
		// moved the custom data setting above the member update to make sure we have fresh custom data for members
		if (($data = $this->EE->input->post('custom_data', TRUE)) && is_array($data))
		{
			foreach ($data as $key => $value)
			{
				$this->cart->set_custom_data($key, $value);
			}
		}

		if (isset($member_id))
		{
 			$manually_save_customer_info = FALSE; 
			if ($this->EE->input->post('save_member_data'))
			{
				$manually_save_customer_info = TRUE; 
			}
			
			$this->EE->cartthrob_members_model->update_member($member_id, $this->cart->customer_info(), $manually_save_customer_info); 
		}
		
		$this->EE->load->library('languages');
		
		$this->EE->languages->set_language($this->EE->input->post('language', TRUE));
		
		if ($this->EE->input->post('shipping_option'))
		{
			$this->cart->set_shipping_info('shipping_option', $this->EE->input->post('shipping_option', TRUE));
		}
		
       		 /**
         	* @property array|bool $data
         	*/
       		 $data = $this->EE->input->post('shipping', TRUE);

		if ( is_array($data))
		{
			foreach ($data as $key => $value)
			{
				$this->cart->set_shipping_info($key, $value);
			}
		}
	}
	
	/**
	 * Hooks
	 *
	 * To use the hooks found in the Cartthrob_child objects, create a method
	 * by prefixing the class short name and the hook name. For example, to
	 * use the Cartthrob_cart class' add_item_end hook, add a method here
	 * called cart_add_item_end.
	 * 
	 */
	/*
	function cart_add_item_end($item, $params)
	{
		if (is_null($item->product_id()) && isset($params['entry_id']))
		{
			$item->set_product_id($params['entry_id']);
		}
	}
	*/
	
	function cart_total_start()
	{
		// cartthrob_calculate_total hook
		if ($this->EE->extensions->active_hook('cartthrob_calculate_total') === TRUE)
		{
			if (($total = $this->EE->extensions->call('cartthrob_calculate_total')) !== FALSE)
			{
				$this->hooks->set_end();
				
				return $total;
			}
		}
	}
	
	public function cart_discount_start()
	{
		if ($this->EE->extensions->active_hook('cartthrob_calculate_discount') === TRUE)
		{
			if (($discount = $this->EE->extensions->call('cartthrob_calculate_discount')) !== FALSE)
			{
				$this->hooks->set_end();
				
				return $discount;
			}
		}
	}
	
	public function cart_shipping_end($shipping)
	{
		if ($this->EE->extensions->active_hook('cartthrob_calculate_shipping') === TRUE)
		{
			$this->hooks->set_end();
			
			return $this->EE->extensions->call('cartthrob_calculate_shipping', $shipping);
		}
	}
	
	public function cart_tax_end($tax)
	{
		if ($this->EE->extensions->active_hook('cartthrob_calculate_tax') === TRUE)
		{
			$this->hooks->set_end();
			
			return $this->EE->extensions->call('cartthrob_calculate_tax', $tax);
		}
	}
	
	public function item_shipping_end($shipping)
	{
		if ($this->EE->extensions->active_hook('cartthrob_calculate_item_shipping') === TRUE)
		{
			$this->hooks->set_end();
			
			return $this->EE->extensions->call('cartthrob_calculate_item_shipping', $shipping);
		}
	}
	
	public function product_meta(Cartthrob_product $product, $key)
	{
		$this->EE->load->model(array('cartthrob_field_model', 'product_model'));
		
		$data = $this->EE->product_model->get_product($product->product_id());
		
		if ($key === FALSE)
		{
			$this->hooks->set_end();
			return $data;
		}
		
		if (isset($data[$key]))
		{
			$this->hooks->set_end();
			return $data[$key];
		}
		
		$field_id = $this->EE->cartthrob_field_model->get_field_id($key) ; 
		
		if ($field_id && isset($data['field_id_'.$field_id]))
		{
			$this->hooks->set_end();
			return $data['field_id_'.$field_id];
		}
	}
	
	public function product_price(Cartthrob_product $product, $item = FALSE)
	{
		$this->EE->load->model(array('cartthrob_field_model', 'product_model'));
		
		$data = $this->EE->product_model->get_product($product->product_id());
		
		if ($channel_id = element('channel_id', $data))
		{
			$global_price = $this->store->config('product_channel_fields', $channel_id, 'global_price');
			
			if ($global_price !== FALSE && $global_price !== '')
			{
				$this->hooks->set_end();
				
				return $global_price;
			}
			
			if ($item instanceof Cartthrob_item)
			{
				$field_id = $this->store->config('product_channel_fields', $channel_id, 'price');
				
				if ($field_id && $field_type = $this->EE->cartthrob_field_model->get_field_type($field_id))
				{
 					$this->EE->load->library('api');
					
					$this->EE->api->instantiate('channel_fields');
					
					$this->EE->api_channel_fields->include_handler($field_type);
					
					if ($this->EE->api_channel_fields->setup_handler($field_type) && $this->EE->api_channel_fields->check_method_exists('cartthrob_price'))
					{
 						$price =   $this->EE->api_channel_fields->apply('cartthrob_price', array($data['field_id_'.$field_id], $item));

 						if (is_numeric($price))
						{
							return $price; 
						}
						else
						{
							return 0; 
						}
					}
					// matrix always returns 1 if there's content in the matrix field. if the matrix field is set as a price field and there's content in it, it'll always add $1 to the price.
					elseif ($field_type=="matrix")
					{
						return 0; 
					}
				}
			}
		}
	}
	
	public function product_inventory(Cartthrob_product $product, $item_options)
	{
		$this->hooks->set_end();
		
		$hash = md5($product->product_id().serialize($item_options));
		
		if (FALSE !== ($inventory = $this->cache($hash)))
		{
			return $inventory;
		}
		
		$inventory = PHP_INT_MAX;
		
		$this->EE->load->model(array('cartthrob_field_model', 'product_model'));
		
		$data = $this->EE->product_model->get_product($product->product_id());
		
		$channel_id = element('channel_id', $data);
		
		if ($channel_id && $field_id = $this->store->config('product_channel_fields', $channel_id, 'inventory'))
		{
			$field_name = $this->EE->cartthrob_field_model->get_field_name($field_id);
			
			$field_type = $this->EE->cartthrob_field_model->get_field_type($field_id); 
			
			$is_modifier = (in_array($field_type, array('cartthrob_price_modifiers', 'matrix')) || strncmp($field_type, 'cartthrob_price_modifiers', 25) === 0);
			
			if ($is_modifier)
			{
				$price_modifiers = $this->EE->product_model->get_price_modifiers($product->product_id(), $field_id);
				
				if (isset($item_options[$field_name]))
				{
					foreach ($price_modifiers as $row)
					{
						if ($item_options[$field_name] == $row['option_value'])
						{
							if (array_key_exists('inventory', $row))
							{
								// do not use this. it makes it FALSE when it needs to be 0 
								//$inventory = element('inventory', $row);
								
								$inventory = FALSE; 
 								if ($row['inventory'] === 0 || $row['inventory'] === "0" )
								{
									$inventory = 0; 
								}
								elseif ($row['inventory'] === FALSE || $row['inventory'] === NULL || $row['inventory'] === "")
								{
									$inventory = FALSE; 
								}
								else
								{
									$inventory = $row['inventory']; 
								}
							}
							
							continue;
						}
					}
				}
			}
			else
			{
   				$inventory = element('field_id_'.$field_id, $data);

				if ($inventory === 0 || $inventory === '0')
				{
						$inventory = 0; 
				}
				elseif ($inventory === FALSE || $inventory === NULL || $inventory === "")
				{
						$inventory = FALSE; 
				}
			}
		}
		
		if ($inventory === FALSE || $inventory === '')
		{
			$inventory = PHP_INT_MAX;
		}
		
		$this->set_cache($hash, $inventory);
		
		return $inventory;
	}
	
	/**
	 * return the total number of items in the cart that match the item
	 * 
	 * @param Cartthrob_item $item
	 * 
	 * @return int
	 */
	public function quantity_in_cart(Cartthrob_item $item)
	{
		$this->EE->load->model(array('cartthrob_field_model', 'product_model'));
		
		$product_id = $item->product_id();
		
		$item_options = $item->item_options();
		
		$sub_items = element('sub_items', $item->to_array());
		
		$channel_id = $item->meta('channel_id');
		
		$items = NULL;
		
		if ($channel_id && $field_id = $this->store->config('product_channel_fields', $channel_id, 'inventory'))
		{
			$field_name = $this->EE->cartthrob_field_model->get_field_name($field_id);
			
			$field_type = $this->EE->cartthrob_field_model->get_field_type($field_id); 
			
			$is_modifier = (in_array($field_type, array('cartthrob_price_modifiers', 'matrix')) || strncmp($field_type, 'cartthrob_price_modifiers', 25) === 0);
			
			if ($is_modifier && isset($item_options[$field_name]))
			{
				$items = $this->cart->filter_items(array('product_id' => $product_id, 'item_options' => array($field_name => $item_options[$field_name])), TRUE);
			}
			
			if ($field_type === 'cartthrob_package')
			{
				$items = $this->cart->filter_items(array('product_id' => $product_id, 'sub_items' => $sub_items));
			}
		}
		
		if (is_null($items))
		{
			$items = $this->cart->filter_items(array('product_id' => $product_id), TRUE);
		}
		
		$quantity = 0;
		
		foreach ($items as $item)
		{
			$quantity += ($item->is_sub_item()) ? $item->quantity() * $item->parent_item()->quantity() : $item->quantity();
		}
		
		return $quantity;
	}
	
	public function product_reduce_inventory(Cartthrob_product $product, $quantity, $args)
	{
		//because of the way hooks work,
		//and how we call this in the process_inventory method above
		//item_options are the first arg in args
		$item_options = (isset($args[0])) ? $args[0] : array();
		
		$this->EE->load->model('product_model');
		
		$inventory = $this->EE->product_model->reduce_inventory($product->product_id(), $quantity, $item_options);
		if ($inventory!== FALSE && $this->store->config('send_inventory_email'))
		{
			if ($inventory <= $this->store->config('low_stock_level'))
			{
				$this->EE->load->library('cartthrob_emails');
				$emails = $this->EE->cartthrob_emails->get_email_for_event("low_stock"); 
				if (!empty($emails))
				{
					foreach ($emails as $email_content)
					{
						$this->EE->cartthrob_emails->send_email($email_content, array('entry_id' =>$product->product_id() , 'inventory'=> $inventory)); 
					}
				}
			}
		}
		$this->hooks->set_end();
	}
	
	/**
	 * Custom Methods
	 *
	 * Create custom methods for the Cartthrob_child objects by prefixing the
	 * class short name. For example, to create an Cartthrob_item::entry_id()
	 * method, add a method here called item_entry_id().
	 *
	 * Please use sparingly as __call and call_user_func_array are expensive.
	 * 
	 */
	public function item_entry_id(Cartthrob_item $item)
	{
		return $item->product_id();
	}
	
	public function item_product_entry_id(Cartthrob_item $item)
	{
		return $item->product_id();
	}
	
	public function cart_info()
	{
		$this->EE->load->library(array('number'));
		
 		return array(
			'total_unique_items' => 		$this->cart->count(),
			'cart_tax_name' => 				$this->store->tax_name(), // this should really be set per item
			'total_items' => 				$this->cart->count_all(),
			'cart_subtotal' => 				$this->EE->number->format($this->cart->subtotal()),
			'cart_subtotal_plus_tax' => 	$this->EE->number->format($this->cart->subtotal_with_tax()),
			'cart_subtotal:plus_tax' => 	$this->EE->number->format($this->cart->subtotal_with_tax()),
			'cart_tax' => 					$this->EE->number->format($this->cart->tax()),
			'cart_shipping' => 				$this->EE->number->format($this->cart->shipping()),
			'cart_shipping_plus_tax' => 	$this->EE->number->format($this->cart->shipping_plus_tax()),
			'cart_shipping:plus_tax' => 	$this->EE->number->format($this->cart->shipping_plus_tax()),
			'cart_discount' => 				$this->EE->number->format($this->cart->discount()), 
			'cart_total' => 				$this->EE->number->format($this->cart->total()),
			'cart_total:plus_tax' =>		$this->EE->number->format($this->cart->total()), // already includes tax, but what the hell. 
			'cart_total_plus_tax' => 		$this->EE->number->format($this->cart->total()), // already includes tax, but what the hell. 
			'cart_subtotal_numeric' => 		$this->cart->subtotal(),
			'cart_tax_numeric' => 			$this->cart->tax(),
			'cart_shipping_numeric' => 		$this->cart->shipping(),
			'cart_discount_numeric' => 		$this->cart->discount(), 
			'cart_total_numeric' => 		$this->cart->total(),
			'cart_weight' =>				$this->cart->weight(), // added in 2.601
			'cart_tax_rate' => 				$this->store->tax_rate(), // this should really be set per item
			'cart_entry_ids' => 			implode('|', $this->cart->product_ids()),
			'shipping_option' =>			 $this->cart->shipping_info('shipping_option')
		);
	}
 	public function get_tax_rates($location_data, $limit=100, $order_by="id")
	{
		$this->EE->load->model('tax_model');
		$taxes = $this->EE->tax_model->get_by_location($location_data, $limit, $order_by);

		return $taxes; 
	}
 }
