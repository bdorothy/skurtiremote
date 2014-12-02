<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @property CI_Controller $EE
 * @property Cartthrob_core_ee $cartthrob;
 * @property Cartthrob_cart $cart
 * @property Cartthrob_store $store
 */
class Cartthrob
{
	public $cartthrob, $store, $cart;
	
	public function __construct()
	{
		$this->EE =& get_instance();
		
		$this->EE->load->library('cartthrob_loader');
		$this->EE->cartthrob_loader->setup($this);
		
		$this->EE->lang->loadfile('cartthrob');
		
		$this->EE->load->helper(array('security', 'data_formatting', 'credit_card', 'form'));
		
		$this->EE->load->library(array('cartthrob_variables', 'template_helper'));
		
		$this->EE->load->model('product_model');
		$this->EE->product_model->load_products($this->EE->cartthrob->cart->product_ids());
		
		$this->EE->load->helper('debug');
	}
	
	public function __call($method, $args)
	{
		$this->EE->load->library('cartthrob_addons');
		
		if ( ! $this->EE->cartthrob_addons->method_exists($method))
		{
			throw new Exception("Call to undefined method Cartthrob::$method()");
		}
		
		return $this->EE->cartthrob_addons->call($method);
	}
	
	public function convert_simple_commerce_purchases()
	{
		$this->EE->load->model('order_model'); 
		$this->EE->load->model('cartthrob_entries_model'); 
		$this->EE->load->model('purchased_items_model'); 


		$this->EE->load->library('get_settings');
 			
		/*
		$where = array(
			'site_id' => $this->EE->config->item('site_id'),
			'`key`' => 'last_simple_commerce_transaction'
		);
		$temp['serialized'] = 0;
		$temp['value'] = 0;

		$this->EE->db->update('cartthrob_settings', $temp, $where);
		// exit; 
		*/
			
		$this->EE->db->where('site_id', $this->EE->config->item('site_id')); 
		$this->EE->db->where('`key`', 'last_simple_commerce_transaction'); 
		$this->EE->db->limit('1'); 
		$dat = $this->EE->db->get('cartthrob_settings')->result_array(); 
		$last_id = NULL; 
  		
		if (!empty($dat[0]))
		{
			$z = $dat[0]['value']; 
			$orig_last_id =$z; 
			$last_id = $orig_last_id; 
			echo $last_id; 
		}

		if ($last_id)
		{
			$this->EE->db->where('purchase_id > '. $last_id); 
		}
		$this->EE->db->where('CHAR_LENGTH(`txn_id`) > 16');
		$this->EE->db->limit(200); 
		$this->EE->db->order_by('purchase_id', 'asc'); 
		
		$query = $this->EE->db->get('exp_simple_commerce_purchases'); 
		
		if ($query->result() && $query->num_rows())
		{
			foreach ($query->result_array() as $q)
			{
				$last_id = $q['purchase_id']; 

				$total = $q['item_cost'];

				$order_data = array(
					'CVV2'						=> NULL,
					'expiration_month'			=> NULL,
					'expiration_year'			=> NULL,
					'items' => array(),
					'card_type' => 				NULL,  
					'shipping' => 				NULL, 
					'shipping_plus_tax'	=>  	NULL, 
					'tax' => 					NULL, 
					'subtotal' => 				$this->EE->cartthrob->round($total), 
					'subtotal_plus_tax' => 		NULL, 
					'discount' => 				NULL, 
					'total' => 					$this->EE->cartthrob->round($total), 
					'customer_name' => 			NULL, 
					'customer_email' => 		NULL, 
					'email_address' => 			NULL, 
					'customer_ip_address' => 	NULL, 
					'ip_address' => 			NULL, 
					'customer_phone' => 		NULL,
					'coupon_codes' => 			NULL, 
					'coupon_codes_array' => 	NULL, 
					'last_four_digits' => 		NULL, 
					'full_billing_address' => 	NULL, 
												NULL, 
												NULL, 
					'full_shipping_address' => 	NULL, 
												NULL, 
												NULL, 
												NULL, 
												NULL, 
					'billing_first_name' => 	NULL, 
					'billing_last_name' => 		NULL, 
					'billing_company' => 		NULL, 
					'billing_address' => 		NULL, 
					'billing_address2' => 		NULL, 
					'billing_city' => 			NULL, 
					'billing_state' => 			NULL, 
					'billing_zip' => 			NULL, 
					'billing_country' => 		NULL, 
					'billing_country_code' => 	NULL, 
                                                NULL, 
					'first_name' => 			NULL, 
					'last_name' => 				NULL, 
					'company' => 				NULL, 
					'address' => 				NULL, 
					'address2' => 				NULL, 
					'city' => 					NULL, 
					'state' => 					NULL, 
					'zip' => 					NULL, 
					'country' => 				NULL, 
					'country_code' => 			NULL, 
                                                NULL, 
					'shipping_first_name' => 	NULL, 
					'shipping_last_name' => 	NULL, 
					'shipping_company' => 		NULL, 
					'shipping_address' => 		NULL, 
					'shipping_address2' => 		NULL, 
					'shipping_city' => 			NULL, 
					'shipping_state' => 		NULL, 
					'shipping_zip' => 			NULL, 
					'shipping_country' => 		NULL, 
					'shipping_country_code' => 	NULL, 
                                                NULL, 
					'currency_code'	=> 			NULL, 
					'entry_id' => 				'',
					'order_id' => 				'',
					'total_cart' => 			$this->EE->cartthrob->round($total),
					'auth' => 					array(),
					'purchased_items' => 		array(),
					'create_user' => 			FALSE,
					'member_id' => 				$q['member_id'],
					'group_id' => 				FALSE, 
					'return' => 				'',
					'site_name' => 				$this->EE->config->item('site_name'),
					'custom_data' => 			array(),
					'subscription'	=> 			NULL,
					'subscription_options'	=> 	array(),
					'payment_gateway' => 		'offline_payments',
					'subscription_id' =>		NULL,
				);

				#$order_data['subscriber'] = $q['paypal_subscriber_id']; 
				$order_data['entry_date'] = $q['purchase_date']; 
				$order_data['transaction_id'] = $q['txn_id']; 
 				$title = "Item ID ". $q['item_id']; 
 				$entry_id = NULL; 
				$this->EE->db->where('item_id', $q['item_id']); 
				$this->EE->db->limit(1); 
				$query2 = $this->EE->db->get('exp_simple_commerce_items');

				if ($query2->result() && $query2->num_rows())
				{
					$d = $query2->row_array(); 
					$entry_id = $d['entry_id']; 
					if ($entry_id)
					{
						$entry_data = $this->EE->cartthrob_entries_model->entry($entry_id); 
						if (!empty($entry_data['title']))
						{
							$title = $entry_data['title']; 
						}
					}
	 			}
				$row = array(); 
				
				$row['entry_id'] = $entry_id; 
				$row['title'] = $title; 
				$row['quantity'] = 1; 
				$row['price'] = $this->EE->cartthrob->round($total);
				$row['price_plus_tax'] = $this->EE->cartthrob->round($total);
				$row['weight'] = 0; 
				$row['shipping'] = 0; 
				$row['title'] = $title; 
				$order_data['items'][] = $row;
				$order_data['title'] = "Conversion ". $last_id; 
				
				// other custom fields
				#$order_data['bl_order_price'] = $this->EE->cartthrob->round($total);
				#$order_data['bl_order_qty'] =  1; 
				#$order_data['bl_order_id'] =  $last_id; 
				#
				
 			 	$order = $this->EE->order_model->create_order($order_data); 
				$this->EE->cartthrob->cart->save(); 

				$row['order_id'] = $order['entry_id']; 
				echo "new order_id: (".$last_id.") ".$row['order_id']."<br>"; 
				
				$this->EE->db->where('site_id', $this->EE->config->item('site_id')); 
				$this->EE->db->where('`key`', 'last_order_number'); 
				$this->EE->db->limit('1'); 
				$dat = $this->EE->db->get('cartthrob_settings')->result_array(); 
				echo "last order ".
				var_dump($dat);
				echo "<br><br>";
				
				
				$this->EE->order_model->update_order($row['order_id'], array('status' => 'open', 'title' => $order_data['title'], 'url_title' => 'conversion_'.$last_id."_".uniqid(NULL, TRUE)  ) );
				$this->EE->db->insert('cartthrob_order_items', $row);

				$row['product_id'] = $entry_id; 
				$row['author_id'] = $order_data['member_id']; 
				$purch_id = $this->EE->purchased_items_model->create_purchased_item($row, $row['order_id'], $this->EE->cartthrob->store->config('purchased_items_default_status'));
				$this->EE->purchased_items_model->update_purchased_item($purch_id, array('author_id' => $order_data['member_id'])); 
				
				

 			}
			$where = array(
				'site_id' => $this->EE->config->item('site_id'),
				'`key`' => 'last_simple_commerce_transaction'
			);

 			if (isset($orig_last_id))
			{
				$temp['serialized'] = 0;
				$temp['value'] = $last_id;

				$this->EE->db->update('cartthrob_settings', $temp, $where);
			}
			else
			{
				$temp['serialized'] = 0;
				$temp['value'] = $last_id;

				$this->EE->db->insert('cartthrob_settings', array_merge($temp, $where));
			}
			echo $last_id; 
#			$this->EE->cartthrob->cart->set_meta('simple_id', $last_id);
#			$this->EE->cartthrob->cart->save(); 
 		}

	}
	public function delete_from_cart_action()
	{
		if ( ! $this->EE->input->get_post('ACT'))
		{
			return;
		}
		
		if ($this->EE->extensions->active_hook('cartthrob_delete_from_cart_start') === TRUE)
		{
			$this->EE->extensions->call('cartthrob_delete_from_cart_start');
			if ($this->EE->extensions->end_script === TRUE) return;
		}
		
		$this->EE->load->library('form_builder');
		
		if ( ! $this->EE->form_builder->validate())
		{
			return $this->EE->form_builder->action_complete();
		}
		
		$this->EE->cartthrob->save_customer_info();
		
		if ($this->EE->input->post('row_id') !== FALSE)
		{
			$this->EE->cartthrob->cart->remove_item($this->EE->input->post('row_id', TRUE));
		}
		
		if ($this->EE->extensions->active_hook('cartthrob_delete_from_cart_end') === TRUE)
		{
			$this->EE->extensions->call('cartthrob_delete_from_cart_end');
			if ($this->EE->extensions->end_script === TRUE) return;
		}
		
		$this->EE->form_builder->set_success_callback(array($this->EE->cartthrob, 'action_complete'))
					->action_complete();
 	}
	public function field()
	{
		//@TODO make this field figure out the field type, and make that field type handle the output. 
		
		$entry_id = $this->EE->TMPL->fetch_param('entry_id'); 
		$field = $this->EE->TMPL->fetch_param('field'); 
		
		$this->EE->load->model('cartthrob_entries_model');

		$entry = $this->EE->cartthrob_entries_model->entry($this->EE->TMPL->fetch_param('entry_id'));
			
		$this->EE->load->helper('array');
 		return element($this->EE->TMPL->fetch_param('field'), $entry);
	}
	public function download_file_action()
	{
		//@TODO add in debug to output member and group id, and whether the file's protected or not
		if ( ! $this->EE->input->get_post('ACT'))
		{
			return;
		}
		
		// cartthrob_download_start hook
		if ($this->EE->extensions->active_hook('cartthrob_download_start') === TRUE)
		{
			//@TODO work on hook parameters
			//$edata = $EXT->universal_call_extension('cartthrob_download_start');
			$this->EE->extensions->call('cartthrob_download_start');
			if ($this->EE->extensions->end_script === TRUE) return;
		}
		
		$this->EE->load->library('form_builder');
		$this->EE->load->library('cartthrob_file');
		$this->EE->load->library('curl');
		$this->EE->load->library('paths');
		$this->EE->load->library('encrypt');
		$this->EE->load->helper(array('string'));
		
		$this->EE->form_builder->set_require_form_hash(FALSE);
		$this->EE->form_builder->set_require_rules(FALSE);
		$this->EE->form_builder->set_require_errors(FALSE);

		$path = NULL;		
		
		if (!$this->EE->input->get('FP') && !$this->EE->input->get('FI'))
		{
			if ( ! $this->EE->form_builder->validate())
			{
				return $this->EE->form_builder->action_complete();
			}
		}
		
		$this->EE->cartthrob->save_customer_info();
		
		// Check member id. 
		if ($this->EE->input->get_post('MI') == TRUE)
		{
			// have to check for get or post due to slightly different encoding types
			if ($this->EE->input->get('MI'))
			{
				$member_id = sanitize_number(xss_clean($this->EE->encrypt->decode(base64_decode(rawurldecode($this->EE->input->get('MI'))))));
			}
			else
			{
				$member_id = sanitize_number(xss_clean($this->EE->encrypt->decode($this->EE->input->post('MI'))));
			}
 		}
			
		// Check group id. 
		if ($this->EE->input->get_post('GI'))
		{
			// have to check for get or post due to slightly different encoding types
			if ($this->EE->input->get('GI'))
			{
				$group_id = sanitize_number(xss_clean($this->EE->encrypt->decode(base64_decode(rawurldecode($this->EE->input->get('GI'))))));
			}
			else
			{
				$group_id = sanitize_number(xss_clean($this->EE->encrypt->decode($this->EE->input->post('GI'))));
			}
 		}
		// standard file from form, or free_file from download link
		if ($this->EE->input->get_post('FI'))
		{
			// have to check for get or post due to slightly different encoding types
			if ($this->EE->input->get('FI'))
			{
				$path = xss_clean($this->EE->encrypt->decode(base64_decode(rawurldecode($this->EE->input->get('FI')))));
			}
			else
			{
				$path = xss_clean($this->EE->encrypt->decode($this->EE->input->post('FI')));
			}
			
			if (substr($path, 0, 2) !== 'FI')
			{
				$this->EE->form_builder->add_error($this->EE->lang->line('download_file_not_authorized'));
			}
			else
			{
				$path = substr($path, 2);
			}
			
			
		}
		// protected file from the download link
		elseif ($this->EE->input->get_post('FP'))
		{	
			if ($this->EE->input->get('FP'))
			{
				$path = xss_clean($this->EE->encrypt->decode(base64_decode(rawurldecode($this->EE->input->get('FP')))));
			}
			else
			{
				$path = xss_clean($this->EE->encrypt->decode($this->EE->input->post('FP')));
			}
			
			if (substr($path, 0, 2) !== 'FP')
			{
				$this->EE->form_builder->add_error($this->EE->lang->line('download_file_not_authorized'));
			}
			else
			{
				$path = substr($path, 2);
			}
 			
			if (empty($member_id) && empty($group_id))
			{
				$this->EE->form_builder->add_error($this->EE->lang->line('download_file_not_authorized'));
			}
		}
		else
		{
 			$this->EE->form_builder->add_error($this->EE->lang->line('download_url_not_specified'));
 		}
		
		if ($this->EE->form_builder->errors())
		{
			$this->EE->form_builder->action_complete();
		}
 	
		// Check member id. 
		if ( ! empty($member_id) && $member_id != $this->EE->session->userdata('member_id'))
		{
			$this->EE->form_builder->add_error($this->EE->lang->line('download_file_not_authorized_for_member'));
 		}
		
 		// Check group id
		if ( ! empty($group_id) && $group_id != $this->EE->session->userdata('group_id'))
		{
			$this->EE->form_builder->add_error($this->EE->lang->line('download_file_not_authorized_for_group'));
		}

		
		// cartthrob_download_end hook
		if ($this->EE->extensions->active_hook('cartthrob_download_end') === TRUE)
		{
			//@TODO work on hook parameters
			//$edata = $EXT->universal_call_extension('cartthrob_download_end', $path);
			$path = $this->EE->extensions->call('cartthrob_download_end', $path);
			if ($this->EE->extensions->end_script === TRUE) return;
		}
		
		if ( ! $this->EE->form_builder->errors())
		{
			$this->EE->cartthrob_file->force_download($path, $this->EE->input->get('debug'));
		
			if ($this->EE->cartthrob_file->errors())
			{
				$this->EE->form_builder->add_error($this->EE->cartthrob_file->errors());
			}
		}
	
		$this->EE->form_builder->action_complete();
	}
	public function clean_sub_data($item_subscription_options = array(), $update = FALSE)
	{
		$this->EE->load->library('encrypt'); 
		
		// if item and SUB OR subscription (to account for select boxes)
		// OR if item AND corresponding product has subscription fieldtype and is enabled
		if ( ! empty($item_subscription_options['subscription_enabled']) || (($this->EE->input->post('SUB') && bool_string($this->EE->encrypt->decode($this->EE->input->post('SUB')))) || ($this->EE->input->post('subscription') && bool_string($this->EE->input->post('subscription')))))
		{
			// these are all of the subscription options
			//@TODO make a decision about these? do we need allow_user_subscription_trial_price or allow_user="trial_price|start_date"
			$subscription = array(); 
			
			$this->EE->load->model('subscription_model');
			
			// iterating through those options. if they're in post, we'll add them to the "subscription_options" meta	
			foreach ($this->EE->subscription_model->option_keys() as $encoded_key => $key)
			{
				$option = NULL;
				
				if ($update)
				{
					if (array_key_exists($key, $item_subscription_options))
					{
						$option = $item_subscription_options[$key]; 
					}
				}
				// a couple of these things can be plain text
				if ($this->EE->input->post("subscription_".$key))
				{
					switch($key)
					{
						case "name": 
							$option = $this->EE->input->post("subscription_".$key);
						break;
						case "description": 
							$option = $this->EE->input->post("subscription_".$key);
						break;
					}
				}
				
				if (!$option && $this->EE->input->post($encoded_key))
				{
					$option = $this->EE->encrypt->decode($this->EE->input->post($encoded_key));
				}
				else if (! $option && $this->EE->input->post($key))
				{
					$option = $this->EE->encrypt->decode($this->EE->input->post($key)); 
				}
				else if (! $option && isset($item_subscription_options['subscription_'.$key]))
				{
					switch($key)
					{
						case 'end_date':
						case 'start_date':
							if ( ! $item_subscription_options['subscription_'.$key])
							{
								$option = '';
							}
							else if ( ! is_numeric($item_subscription_options['subscription_'.$key]))
							{
								$option = strtotime($item_subscription_options['subscription_'.$key]);
							}
							else
							{
								$option = $item_subscription_options['subscription_'.$key];
							}
							break;
						default:
							if (!$update)
							{
							$option = $item_subscription_options['subscription_'.$key];
							}
					}
				}
				if ( ! is_null($option))
				{
					if (in_array($encoded_key, $this->EE->subscription_model->encoded_bools()))
					{
						if ($update)
						{
							if ($key != "allow_modification")
							{
								$option = bool_string($option);
							}
						}
						else
						{
							$option = bool_string($option);
						}
					}
					
					if (strncmp($key, 'subscription_', 13) === 0)
					{
						$key = substr($key, 13);
					}
					
					$subscription[$key] = $option;
				}
			}

			if ( empty($subscription['price']) && $this->EE->input->post('AUP') && $this->EE->input->post('price') !== FALSE && bool_string($this->EE->encrypt->decode($this->EE->input->post('AUP'))))
			{
				$subscription['price'] = sanitize_number($this->EE->input->post('price', TRUE));
			}
		}
		
		if (isset($subscription))
		{
			return $subscription; 
		}
		return FALSE; 
	}
	public function add_to_cart_action()
	{
		if ( ! $this->EE->input->get_post('ACT'))
		{
			return;
		}
		
		$this->EE->load->library('form_builder');
		$this->EE->load->library('encrypt');
		$this->EE->load->model(array('cartthrob_field_model', 'product_model'));
		
		// cartthrob_add_to_cart_start hook
		if ($this->EE->extensions->active_hook('cartthrob_add_to_cart_start') === TRUE)
		{
			//@TODO work on hook parameters
			//$edata = $EXT->universal_call_extension('cartthrob_add_to_cart_start', $this, $_SESSION['cartthrob']);
			$this->EE->extensions->call('cartthrob_add_to_cart_start');
			if ($this->EE->extensions->end_script === TRUE) return;
		}
		
		if ( ! $this->EE->form_builder->validate())
		{
			$this->EE->cartthrob_variables->set_global_values();
			
			$this->EE->form_builder->set_value(array(
				'item_options',
				'quantity',
				'title',
			));
			
			return $this->EE->form_builder->action_complete();
		}
		
		$this->EE->cartthrob->save_customer_info();
		
		$data = array(
			'entry_id' => $this->EE->input->post('entry_id', TRUE),
			'class'	=> 'product'
		);
		
		
		$item_options = $this->EE->input->post('item_options', TRUE);
		
		if ($item_options && is_array($item_options))
		{
			$configuration = array(); 
			$fields_list = array(); 
			foreach ($item_options as $key => $value)
			{
				if (strpos($key, 'configuration:') !== FALSE)
				{
					list($a, $field, $option_group) = explode(":", $key); 
					$fields_list[] = $field; 
					$configuration[$field][$option_group] = $value; 
					
					unset($item_options[$key]); 
					//$data['item_options'][$key] = $value;
				}
			}
 
			if (!empty($configuration))
			{
				$fields_list = array_filter($fields_list); 
				foreach ($fields_list as $field_name)
				{
					$entry = $this->EE->product_model->get_product($data['entry_id']);
					if ($entry)
					{
						$sku = $this->EE->product_model->get_base_variation($data['entry_id'], $field, $configuration[$field_name] );
						if ($sku)
						{
							$item_options[$field_name] = $sku; 
						
							$inventory = $this->EE->product_model->check_inventory($data['entry_id'], $field, $sku);

							if ($inventory !== FALSE && $inventory <=0)
							{
								$this->EE->form_builder->set_errors(array(sprintf(lang('configuration_not_in_stock'), $entry['title'])))
											->set_success_callback(array($this->EE->cartthrob, 'action_complete'))
											->action_complete();								
							}
						}
						
					}
				}
			}
			//don't grab numeric item_options, those are for sub_items
			foreach ($item_options as $key => $value)
			{
				if (strpos($key, ':') === FALSE)
				{
					$data['item_options'][$key] = $value;
				}
			}
		}
		
		// normally all of this data would be handled as part of the subs options, but we 
		// have to mess around and get a price in the cart anyway, so we'll go ahead and look into it. 
		if ($this->EE->input->post('subscription_plan_id') || $this->EE->input->post('PI') )
		{
			// look to see if this was overridden by customer, and then look for the parameter 
			$plan_id = xss_clean($this->EE->encrypt->decode($this->EE->input->post('subscription_plan_id')));
			if (!$plan_id)
			{
				$plan_id = xss_clean($this->EE->encrypt->decode($this->EE->input->post('PI')));
			}
			
			$this->EE->load->model('subscription_model'); 
			$plan_data = $this->EE->subscription_model->get_plan($plan_id); 
			
			if (isset($plan_data['permissions']))
			{
				$perms =  @unserialize(base64_decode($plan_data['permissions']));
				if (is_array($perms))
				{
					$data['permissions'] = $plan_data['permissions'] = implode("|", $perms); 
				} 
				else
				{
					$data['permissions'] = $plan_data['permissions'] = $perms; 
				}
			}
			
			if (!empty($plan_data['trial_price']) || $plan_data['trial_price']==="0" )
			{
				$data['price'] = $plan_data['trial_price']; 
			}
			elseif (!empty($plan_data['price']))
			{
				$data['price'] = $plan_data['price'];
			}
			
			if (isset($plan_data['name']))
			{
				$data['title'] = $plan_data['name'];
			}
			
		}
		
		if ($this->EE->input->post('AUP') && $this->EE->input->post('price') !== FALSE && bool_string($this->EE->encrypt->decode($this->EE->input->post('AUP'))))
		{
			$data['price'] = sanitize_number($this->EE->input->post('price', TRUE));
		}

		if ($this->EE->input->post('PR'))
		{
			$PR = xss_clean($this->EE->encrypt->decode($this->EE->input->post('PR')));

			if ($PR == sanitize_number($PR))
			{
				$data['price'] = $PR;
			}
		}

		if ($this->EE->input->post('WGT'))
		{
			$WGT = xss_clean($this->EE->encrypt->decode($this->EE->input->post('WGT')));

			if ($WGT == sanitize_number($WGT))
			{
				$data['weight'] = $WGT;
			}
		}
		elseif ($this->EE->input->post('AUW') && bool_string($this->EE->encrypt->decode($this->EE->input->post('AUW'))) && $this->EE->input->post('weight') !== FALSE)
		{
			$data['weight'] = $this->EE->input->post('weight', TRUE);
		}

		if ($this->EE->input->post('SHP'))
		{
			$SHP = xss_clean($this->EE->encrypt->decode($this->EE->input->post('SHP')));

			if ($SHP == sanitize_number($SHP))
			{
				$data['shipping'] = $SHP;
			}
		}
		elseif ($this->EE->input->post('AUS') && bool_string($this->EE->encrypt->decode($this->EE->input->post('AUS'))) && $this->EE->input->post('shipping') !== FALSE)
		{
			$data['shipping'] = $this->EE->input->post('shipping', TRUE);
		}

		if ($this->EE->input->post('NSH'))
		{
			$data['no_shipping'] = bool_string($this->EE->encrypt->decode($this->EE->input->post('NSH')));
		}

		if ($this->EE->input->post('NTX'))
		{
			$data['no_tax'] = bool_string($this->EE->encrypt->decode($this->EE->input->post('NTX')));
		}
		
		$data['product_id'] = $data['entry_id'];
		
		if ($this->EE->input->post('quantity'))
		{
			$data['quantity'] = $this->EE->input->post('quantity', TRUE);
		}
		
		if ($this->EE->input->post('title'))
		{
			$data['title'] = $this->EE->input->post('title', TRUE);
		}
		if (!empty($_FILES['userfile']))
		{
			$this->EE->load->library("cartthrob_file"); 
			
			$directory = NULL; 
			if ($this->EE->input->post('UPL'))
			{
				$directory =  $this->EE->encrypt->decode($this->EE->input->post('UPL'));
			}
			
			$file_data = $this->EE->cartthrob_file->upload($directory); 

			if ($this->EE->cartthrob_file->errors())
			{
				$this->EE->form_builder->set_errors($this->EE->cartthrob_file->errors())->action_complete();
			}
			else
			{
 				$data['item_options']['upload'] = $file_data['file_name'];  
  				$data['item_options']['upload_directory'] = $file_data['file_path'];
			}
		}
		
		//if it's not on_the_fly, it's a product-based item
		if ( ! $this->EE->input->post('OTF') || ! bool_string($this->EE->encrypt->decode($this->EE->input->post('OTF'))))
		{
			if ($this->EE->input->post('title'))
			{
				$data['title'] = $this->EE->input->post('title', TRUE);
			}
			$data['site_id'] = 1; 
			if ($entry = $this->EE->product_model->get_product($data['entry_id']))
			{
				if (isset($entry['site_id']))
				{
					$data['site_id'] = $entry['site_id'];
				}
				$field_id = $this->EE->cartthrob_field_model->channel_has_fieldtype($entry['channel_id'], 'cartthrob_package', TRUE);
				
				if ($field_id && ! empty($entry['field_id_'.$field_id]))
				{
					//it's a package
					$data['class'] = 'package';
					
					$this->EE->load->library('api');
					
					$this->EE->api->instantiate('channel_fields');
					
					if (empty($this->EE->api_channel_fields->field_types))
					{
						$this->EE->api_channel_fields->fetch_installed_fieldtypes();
					}
					
					$data['sub_items'] = array();
					
					if ($this->EE->api_channel_fields->setup_handler('cartthrob_package'))
					{
						$field_data = $this->EE->api_channel_fields->apply('pre_process', array($entry['field_id_'.$field_id]));
						
						foreach ($field_data as $row_id => $row)
						{
							$item = array(
								'entry_id' => $row['entry_id'],
								'product_id' => $row['entry_id'],
								'row_id' => $row_id,
								'class' => 'product',
								'site_id' => $data['site_id'], // assuming it has to be from the same site id as the parent based on EE's structure
							);
							
							$item['item_options'] = (isset($row['option_presets'])) ? $row['option_presets'] : array();
							
							$row_item_options = array();
							
							if (isset($_POST['item_options'][$row_id]))
							{
								$row_item_options = $_POST['item_options'][$row_id];
							}
							else if (isset($_POST['item_options'][$data['entry_id'].':'.$row_id.":"]))
							{
 								$row_item_options = $_POST['item_options'][$data['entry_id'].':'.$row_id.":"];
							}
							else if (isset($_POST['item_options'][':'.$row_id]))
							{
								$row_item_options = $_POST['item_options'][':'.$row_id];
							}
							
							
							$price_modifiers = $this->EE->product_model->get_all_price_modifiers($row['entry_id']);
							
							foreach ($row_item_options as $key => $value)
							{
								//if it's not a price modifier (ie, an on-the-fly item option), add it
								//if it is a price modifier, check that it's been allowed before adding
								if ( ! isset($price_modifiers[$key]) || ! empty($row['allow_selection'][$key]))
								{
									$item['item_options'][$key] = $this->EE->security->xss_clean($value);
								}
							}
							
							$data['sub_items'][$row_id] = $item;
						}
					}
				}
				$field_id = $this->EE->cartthrob_field_model->channel_has_fieldtype($entry['channel_id'], 'cartthrob_subscriptions', TRUE);
				
				if ($field_id && ! empty($entry['field_id_'.$field_id]) && !isset($plan_id))
				{
					// it's a subscription product, let's set the price
					$item_subscription_options = _unserialize($entry['field_id_'.$field_id], TRUE);
					if((isset($item_subscription_options['subscription_trial_occurrences']) && $item_subscription_options['subscription_trial_occurrences'] > 0) && isset($item_subscription_options['subscription_trial_price']))
					{
						$data['price'] = sanitize_number($item_subscription_options['subscription_trial_price']);
					}
					else
					{
						
						$data['price'] = sanitize_number($item_subscription_options['subscription_price']);
					}
					
				}
			}
		}
		else
		{
			if (isset($data['class']))
			{
				unset($data['class']); 
			}
		}
		
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob');
		
		// if a class has been assigned to the item. 
		if ( $this->EE->input->post('CLS'))
		{
			$data['class'] = $this->EE->encrypt->decode($this->EE->input->post('CLS')); 
		}
		$original_last_row_id = ($this->EE->cartthrob->cart->items()) ? $this->EE->cartthrob->cart->last_row_id() : -1;
		
		if ( ! isset($data['quantity']) || (isset($data['quantity']) && $data['quantity'] !== '0' && $data['quantity'] !== 0))
		{
			if ($item = $this->EE->cartthrob->cart->add_item($data))
			{
				if (isset($configuration))
				{
					$item->set_meta('configuration', $configuration);
				}
				if ($item->product_id() && $field_id = $this->EE->cartthrob_field_model->channel_has_fieldtype($item->meta('channel_id'), 'cartthrob_subscriptions', TRUE))
				{
					$item_subscription_options = _unserialize($item->meta('field_id_'.$field_id), TRUE);
				}
			
				if (isset($plan_id))
				{
					if (isset($plan_data))
					{
						// this crazy thing creates a function to add a prefix to each array key. 
						$item_subscription_options = array_combine(
						    array_map(create_function('$k', 'return "subscription_".$k;'), array_keys($plan_data))
							, $plan_data
						);
					}

					$item_subscription_options['subscription_enabled'] = TRUE; 
					
					$item->set_meta('plan_id', $plan_id);
				}
				
				// set after plan so that plan permissions can be added. 
				if ($this->EE->input->post('PER') && $permissions = $this->EE->encrypt->decode($this->EE->input->post('PER')))
				{
					$item->set_meta('permissions', $permissions);
				}
				else if (isset($item_subscription_options['subscription_permissions']))
				{
					$item->set_meta('permissions', $item_subscription_options['subscription_permissions']);
				}
				
 				if ($this->EE->input->post('LIC') && bool_string($this->EE->encrypt->decode($this->EE->input->post('LIC'))))
				{
					$new_last_row_id = ($this->EE->cartthrob->cart->items()) ? $this->EE->cartthrob->cart->last_row_id() : -1;
					
					for ($i = $original_last_row_id; $i <= $new_last_row_id; $i++)
					{
						if ($i < 0 || ! $_item = $this->EE->cartthrob->cart->item($i))
						{
							continue;
						}
						
						if ($data['class'] === 'package')
						{
							foreach ($_item->sub_items() as $sub_item)
							{
								$sub_item->set_meta('license_number', TRUE);
							}
						}
						else
						{
							$_item->set_meta('license_number', TRUE);
						}
					}
				}
			
				$sub = $this->clean_sub_data((isset($item_subscription_options) ? $item_subscription_options: NULL)); 
				if ($sub !== FALSE)
				{
					// adding subscription meta. even if there's no new info, we still want the subscription meta set
					$item->set_meta('subscription_options', $sub);
					$item->set_meta('subscription', TRUE);
					
 					if (!$item->title() && isset($sub['name']))
					{
						$item->set_title($sub['name']);
					}
				}
				
			
				if ($this->EE->input->post('EXP'))
				{
					$EXP = xss_clean($this->EE->encrypt->decode($this->EE->input->post('EXP')));
		
					if ($EXP == sanitize_number($EXP))
					{
						$new_last_row_id = ($this->EE->cartthrob->cart->items()) ? $this->EE->cartthrob->cart->last_row_id() : -1;
					
						for ($i = $original_last_row_id; $i <= $new_last_row_id; $i++)
						{
							if ($i < 0 || ! $_item = $this->EE->cartthrob->cart->item($i))
							{
 								continue;
							}
						
							if ($data['class'] === 'package')
							{
 								foreach ($_item->sub_items() as $sub_item)
								{
 									$sub_item->set_meta('expires', $EXP);
									
 								}
							}
							else
							{
 								$_item->set_meta('expires', $EXP);
							}
						}
					}
				}
			
				if ($inventory_reduce = $this->EE->input->post('inventory_reduce', TRUE))
				{
					$item->set_meta('inventory_reduce', $inventory_reduce);
				}
			}
	
			// tired of adding item options, only to strip them out later and conver them to meta. 
			$meta = $this->EE->input->post('meta', TRUE);
			
			if ($this->EE->input->post('MET'))
			{
				$meta = @unserialize(base64_decode(xss_clean($this->EE->encrypt->decode($this->EE->input->post('MET')))));

				if ($meta && is_array($meta))
				{
					//don't grab numeric item_options, those are for sub_items
					foreach ($meta as $key => $value)
					{
 						if (strpos($key, ':') === FALSE)
						{
							// don't want to override any existing meta that has been set already
							if (!$item->meta($key))
							{
								$item->set_meta($key, $value);
							}
						}
					}
				}
				
			}
			
			// cartthrob_add_to_cart_end hook
			if ($this->EE->extensions->active_hook('cartthrob_add_to_cart_end') === TRUE)
			{
				//@TODO work on hook parameters
				//$edata = $EXT->universal_call_extension('cartthrob_add_to_cart_end', $this, $_SESSION['cartthrob'], $row_id);
				$this->EE->extensions->call('cartthrob_add_to_cart_end', $item);
				if ($this->EE->extensions->end_script === TRUE) return;
			}
		}
		
		//if they're using inline stuff we wanna clear the added item upon error
		if ($this->EE->input->post('error_handling') === 'inline' && $item)
		{
			$this->EE->form_builder->set_error_callback(array($this->EE->cartthrob->cart, 'remove_item', $item->row_id()));
		}
		
		$this->EE->form_builder->set_errors($this->EE->cartthrob->errors())
					->set_success_callback(array($this->EE->cartthrob, 'action_complete'))
					->action_complete();
	}
	
	/**
	 * update_cart_form
	 * 
	 * handles submissions from the update_cart_form 
	 * redirects on completion
	 * 
	 * @access protected
	 * @since 1.0
	 * @return void
	 * @author Rob Sanchez
	 */
	public function update_cart_action()
	{
		if ( ! $this->EE->input->get_post('ACT'))
		{
			return;
		}
		
		if ($this->EE->extensions->active_hook('cartthrob_update_cart_start') === TRUE)
		{
			$this->EE->extensions->call('cartthrob_update_cart_start');
			if ($this->EE->extensions->end_script === TRUE) return;
		}
		
		$this->EE->load->library('form_builder');
		
		if ( ! $this->EE->form_builder->validate())
		{
			$this->EE->cartthrob_variables->set_global_values();
			
			$this->EE->form_builder->set_value(array(
				'clear_cart',
			));
			
			return $this->EE->form_builder->action_complete();
		}

		$this->EE->cartthrob->save_customer_info();

		if ($this->EE->input->post('clear_cart'))
		{
			$this->EE->cartthrob->cart->clear();
		}
		else
		{
			foreach ($this->EE->cartthrob->cart->items() as $row_id => $item)
			{
				if (element($row_id, element('delete', $_POST)))
				{
					$_POST['quantity'][$row_id] = 0;
				}
				
				$data = array();
	
				foreach ($_POST as $key => $value)
				{
					if ($item->sub_items())
					{
						foreach ($item->sub_items() as $sub_item)
						{
							if (isset($value[$row_id.':'.$sub_item->row_id()]) && in_array($key, $sub_item->default_keys()))
							{
								$_value = $value[$row_id.':'.$sub_item->row_id()];
								
								$this->EE->load->library('api');
								
								$this->EE->api->instantiate('channel_fields');
								
								if (empty($this->EE->api_channel_fields->field_types))
								{
									$this->EE->api_channel_fields->fetch_installed_fieldtypes();
								}
								
								if ($key === 'item_options' && $this->EE->api_channel_fields->setup_handler('cartthrob_package'))
								{
									$this->EE->load->model('cartthrob_field_model');
									$field_id = $this->EE->cartthrob_field_model->channel_has_fieldtype($item->meta('channel_id'), 'cartthrob_package', TRUE);
									
									$field_data = $this->EE->api_channel_fields->apply('pre_process', array($item->meta('field_id_'.$field_id)));
									
									$this->EE->load->add_package_path(PATH_THIRD.'cartthrob');
									
									foreach ($field_data as $row)
									{
										if (isset($row['allow_selection']))
										{
											foreach ($row['allow_selection'] as $zkey => $allowed)
											{
												if ( !$allowed && isset($_value[$zkey]))
												{
													unset($_value[$zkey]);
												}
											}
										}
									}
								}
								$sub_item->update(array($key => $this->EE->security->xss_clean($_value)));
							}
						}
					}
					
					if (isset($value[$row_id]) && in_array($key, $item->default_keys()))
					{
						$item_options = array(); 
						
						if ($key == "item_options")
						{
							$configuration = array(); 
							$configuration_meta = NULL; 
							$set_configuration_data = array(); 
							
							$fields_list = array(); 
							$this->EE->load->helper("array"); 
							$arr =  $value[$row_id];
 
							foreach ($arr as $k => $v)
							{
								if (strpos($k, 'configuration:') !== FALSE)
								{
									list($a, $field, $option_group) = explode(":", $k); 
									$fields_list[] = $field; 
									$configuration[$field][$option_group] = $v; 
									unset($value[$row_id][$k]);
								}
							}

							if (!empty($configuration))
							{
								$fields_list = array_filter($fields_list); 
								$this->EE->load->model('product_model'); 
								foreach ($fields_list as $field_name)
								{
									$entry = $this->EE->product_model->get_product( $item->product_id() );
									if ($entry)
									{
										$sku = $this->EE->product_model->get_base_variation($item->product_id() , $field, $configuration[$field_name] );
										
										if ($sku)
										{
											$item_options[$field_name] = $sku;
											
											$qu = (isset($_POST['quantity'][$row_id]) ? $_POST['quantity'][$row_id] : 1); 
											$inventory = $this->EE->product_model->check_inventory($item->product_id(), $qu , array($field_name => $sku ));
											if ($inventory !== FALSE && $inventory <=0)
											{
												$title = $this->EE->input->post('title'); 
												if (!$title)
												{
													$title = lang('item_title_placeholder');
												}
												$this->EE->form_builder->set_errors(array(sprintf(lang('configuration_not_in_stock'), $title )))
															->set_success_callback(array($this->EE->cartthrob, 'action_complete'))
															->action_complete();								
											}
										}
									}
								}
							}
						}
						$data[$key] = $this->EE->security->xss_clean($value[$row_id]);
						if (isset($item_options) && !empty($item_options) && is_array($data[$key]))
						{
							$data[$key] = array_merge($data[$key], $item_options); 
							
							$configuration_meta = $item->meta('configuration'); 
							if ($configuration_meta && isset($configuration))
							{
								$set_configuration_data = array(); 
								foreach($configuration_meta as $b => $c)
								{
									if (array_key_exists($b, $configuration))
									{
										$set_configuration_data[$b] = array_merge($c,  $configuration[$b]); 
									}
								}
							}
							if (isset($set_configuration_data) && is_array($set_configuration_data))
							{
								$item->set_meta("configuration", $set_configuration_data); 
							}
						}
					}
					
					
					if (isset($value[$row_id]) && $key === 'subscription')
					{
						$item->set_meta('subscription', bool_string($value[$row_id]));
					}
				}
				
 				$sub = $this->clean_sub_data( (array) $item->meta('subscription_options'), $update = TRUE); 
				if ($sub !== FALSE)
				{
					// adding subscription meta. even if there's no new info, we still want the subscription meta set
					$item->set_meta('subscription_options', $sub);
				}
				
				if ($data)
				{
					$item->update($data);
				}
			}
		}

		if ($this->EE->extensions->active_hook('cartthrob_update_cart_end') === TRUE)
		{
			$this->EE->extensions->call('cartthrob_update_cart_end');
			if ($this->EE->extensions->end_script === TRUE) return;
		}
		
		if (trim($this->EE->input->post('coupon_code', TRUE)))
		{
			$this->EE->cartthrob->cart->add_coupon_code(trim($this->EE->input->post('coupon_code', TRUE)));
		}
		
		$this->EE->cartthrob->cart->check_inventory();
	
		$this->EE->form_builder->set_errors($this->EE->cartthrob->errors())
					->set_success_callback(array($this->EE->cartthrob, 'action_complete'))
					->action_complete();
	}
	
	public function add_coupon_action()
	{
		if ( ! $this->EE->input->get_post('ACT'))
		{
			return;
		}
		
		$this->EE->load->library('form_builder');
		
		if ( ! $this->EE->form_builder->validate())
		{
			$this->EE->cartthrob_variables->set_global_values();
			
			$this->EE->form_builder->set_value('coupon_code');
			
			return $this->EE->form_builder->action_complete();
		}
		
		$this->EE->cartthrob->save_customer_info();
		
		$this->EE->cartthrob->cart->add_coupon_code(trim($this->EE->input->post('coupon_code', TRUE)));
	
		$this->EE->form_builder->set_errors($this->EE->cartthrob->errors())
					->set_success_callback(array($this->EE->cartthrob, 'action_complete'))
					->action_complete();
	}
	
	public function cart_action()
	{
		if ( ! $this->EE->input->get_post('ACT'))
		{
			return;
		}
		
		$this->EE->cartthrob->save_customer_info();
	}
	
	public function checkout_action()
	{
		if ( ! $this->EE->input->get_post('ACT'))
		{
			return;
		}
		
		if ($this->EE->extensions->active_hook('cartthrob_checkout_action_start') === TRUE)
		{
			$this->EE->extensions->call('cartthrob_checkout_action_start');
			if ($this->EE->extensions->end_script === TRUE) return;
		}
		
		$checkout_options['create_user'] = bool_string($this->EE->input->post('create_user')); // NOTE create_member_id. This is lookin in the post 
		
		// if you're logged in as an admin, and you're creating a user, we don't want to save the info now, or your admin info will be overwritten
		if ( $checkout_options['create_user'])
		{
			if ( ! in_array($this->EE->session->userdata('group_id'), $this->EE->config->item('cartthrob:admin_checkout_groups')) )
			{
				// Save the current customer info for use after checkout
				// needed for return trip after offsite processing
				$this->EE->cartthrob->save_customer_info();
			}
			elseif ( $this->EE->cartthrob->cart->customer_info('email_address') == $this->EE->input->post('email_address'))
			{
				// admin checkout with create user turned on... but checking out with their own account
				$this->EE->cartthrob->save_customer_info();
			}
 		}
		elseif ($this->EE->input->post('member_id', TRUE) && in_array($this->EE->session->userdata('group_id'), $this->EE->config->item('cartthrob:admin_checkout_groups')))
		{
			// Save the current customer info for use after checkout
			// needed for return trip after offsite processing
			$this->EE->cartthrob->cart->set_meta('checkout_as_member', $this->EE->input->post('member_id', TRUE)); 
		}
		// we also don't want to save data if you're tring to update an order id at this point. 
		elseif(!$this->EE->input->post('order_id'))
		{
			$this->EE->cartthrob->save_customer_info();
		}


		if ($this->EE->input->post('order_id'))
		{
			$checkout_options['update_order_id'] = $this->EE->input->post('order_id');
		}

		$this->EE->cartthrob_variables->set_global_values();
		
		$this->EE->form_builder->set_value(array(
			'coupon_code',
		));
		
		$this->EE->load->library('form_validation');
		$this->EE->load->library('encrypt');
		$this->EE->load->library('form_builder');
		
		$this->EE->form_builder->set_show_errors(TRUE)
					->set_captcha($this->EE->session->userdata('member_id') == 0 && $this->EE->cartthrob->store->config('checkout_form_captcha'))
					->set_success_callback(array($this->EE->cartthrob, 'action_complete'))
					->set_error_callback(array($this->EE->cartthrob, 'action_complete'));
		
		
		if ( ! $checkout_options['create_user'] && ! $this->EE->session->userdata('member_id') && $this->EE->cartthrob->store->config('logged_in'))
		{
			return $this->EE->form_builder->add_error($this->EE->lang->line('must_be_logged_in'))
						->action_complete();
		}
		
		$this->EE->load->library('languages');
		
		$this->EE->languages->set_language($this->EE->input->post('language', TRUE));
		
		$not_required = array();

		$required = array();

		if ($this->EE->input->post('NRQ'))
		{
			$not_required = explode('|', xss_clean($this->EE->encrypt->decode($this->EE->input->post('NRQ'))));
		}
		
		$checkout_options['gateway'] = ($this->EE->cartthrob->store->config('allow_gateway_selection') && $this->EE->input->post('gateway')) ? xss_clean($this->EE->encrypt->decode($this->EE->input->post('gateway'))) : $this->EE->cartthrob->store->config('payment_gateway');
		
		if (empty($checkout_options['gateway']))
		{
			$checkout_options['gateway'] = $this->EE->cartthrob->store->config('payment_gateway');
		}
		$this->EE->load->library('cartthrob_payments');
		$this->EE->cartthrob_payments->set_gateway($checkout_options['gateway']);
		
		$checkout_options['credit_card_number'] = sanitize_credit_card_number($this->EE->input->post('credit_card_number', TRUE));
		
		$authorized_redirect = $this->EE->input->post('authorized_redirect', TRUE);

		$failed_redirect = $this->EE->input->post('failed_redirect', TRUE);

		$declined_redirect = $this->EE->input->post('declined_redirect', TRUE);
		
		if ($coupon_code = trim($this->EE->input->post('coupon_code', TRUE)))
		{
			$this->EE->cartthrob->cart->add_coupon_code($coupon_code);
		}

		$inventory_pass = $this->cart->check_inventory();
		// need to throw an error if inventory caught any
		if (!$inventory_pass && $this->cartthrob->errors())
		{
			$this->EE->form_builder->set_errors($this->cartthrob->errors())
						->set_success_callback(array($this->EE->cartthrob, 'action_complete'))
						->action_complete();
		}
		
		if ($this->EE->input->post('AUS') && bool_string($this->EE->encrypt->decode($this->EE->input->post('AUS'))) && $this->EE->input->post('shipping') !== FALSE)
		{
			$this->EE->cartthrob->cart->set_shipping( $this->EE->input->post('shipping', TRUE) );
		}
		
		$checkout_options['tax'] = $this->EE->cartthrob->cart->tax();
		$checkout_options['shipping'] = $this->EE->cartthrob->cart->shipping();
		// discount MUST be calculated before shipping to set shipping free, etc.
		$checkout_options['discount'] = $this->EE->cartthrob->cart->discount();
		$checkout_options['shipping'] = $this->EE->cartthrob->cart->shipping();
		$checkout_options['shipping_plus_tax'] = $this->EE->cartthrob->cart->shipping_plus_tax();
		$checkout_options['subtotal'] = $this->EE->cartthrob->cart->subtotal();
		$checkout_options['subtotal_plus_tax'] = $this->EE->cartthrob->cart->subtotal_with_tax();
		$checkout_options['total'] = $this->EE->cartthrob->cart->total();
		
		if ($this->EE->input->post('EXP'))
		{
			$data = xss_clean($this->EE->encrypt->decode($this->EE->input->post('EXP')));

			if ($data == sanitize_number($data)) // ignore a non-numeric input
			{
				$checkout_options['expiration_date'] = $data;
			}
		}

		if ($this->EE->input->post('TX'))
		{
			$data = xss_clean($this->EE->encrypt->decode($this->EE->input->post('TX')));

			if ($data == sanitize_number($data)) // ignore a non-numeric input
			{
				$checkout_options['total'] -= $checkout_options['tax'];
				$checkout_options['tax'] = $data;
				$checkout_options['total'] += $checkout_options['tax'];
				unset($checkout_options['subtotal_plus_tax']);
				unset($checkout_options['shipping_plus_tax']);
			}
		}

		if ($this->EE->input->post('SHP'))
		{
			$data = xss_clean($this->EE->encrypt->decode($this->EE->input->post('SHP')));

			if ($data == sanitize_number($data)) // ignore a non-numeric input
			{
				$checkout_options['total'] -= $checkout_options['shipping'];
				$checkout_options['shipping'] = $data;
				$checkout_options['total'] += $checkout_options['shipping'];
				unset($checkout_options['shipping_plus_tax']);
			}
		}
		elseif ($this->EE->input->post('AUS') && $this->EE->input->post('shipping') !== FALSE && bool_string($this->EE->encrypt->decode($this->EE->input->post('AUS'))))
		{
			$data = xss_clean($this->EE->input->post('shipping'));
			
			$checkout_options['total'] -= $checkout_options['shipping'];
			$checkout_options['shipping'] = $data;
			$checkout_options['total'] += $checkout_options['shipping'];
			unset($checkout_options['shipping_plus_tax']);
		}
		
		$checkout_options['group_id'] = 5;
		
		if ($this->EE->input->post('GI'))
		{
			$checkout_options['group_id'] = xss_clean($this->EE->encrypt->decode($this->EE->input->post('GI')));
			
			if ($checkout_options['group_id'] < 5)
			{
				$checkout_options['group_id'] = 5; 
			}
		}
		
		if ($this->EE->input->post('PR'))
		{
			$data = xss_clean($this->EE->encrypt->decode($this->EE->input->post('PR')));

			if ($data == sanitize_number($data)) // ignore a non-numeric input
			{
				$checkout_options['total'] -= $checkout_options['subtotal'];
				$checkout_options['subtotal'] = $data;
				$checkout_options['total'] += $checkout_options['subtotal'];
				unset($checkout_options['subtotal_plus_tax']);
			}
		}
		elseif ($this->EE->input->post('AUP'))
		{
			if (bool_string($this->EE->encrypt->decode($this->EE->input->post('AUP'))))
			{
				$checkout_options['total'] = sanitize_number($this->EE->input->post('price', TRUE));
			}
		}
		
		$this->EE->load->library('cartthrob_payments');

		//fetch payment_gateway's required fields
		//bypass if cart total is zero
		if ($checkout_options['total'] > 0)//@TODO REMOVE THIS
		{
			$required = array_merge($required, $this->EE->cartthrob_payments->required_fields());
		}

		
		foreach ($not_required as $key)
		{
			unset($required[array_search($key, $required)]);
		}
		
		if ( ! $this->EE->form_builder->set_required($required)->validate())
		{
			return $this->EE->form_builder->action_complete();
		}
		
		$checkout_options['subscription'] = FALSE;
		$checkout_options['subscription_options'] = array();
		
		// @TODO SUB we should change start date and end date to individual fields maybe? month, day year? 
		if (($this->EE->input->post('SUB') && bool_string($this->EE->encrypt->decode($this->EE->input->post('SUB'))))
				|| $this->EE->input->post('sub_id')
				/* || ($this->EE->input->post('subscription') && bool_string($this->EE->input->post('subscription')) ) */ 
				)
		{
			$checkout_options['subscription'] = TRUE;
			
 			// these are all of the subscription options
			$this->EE->load->model('subscription_model');

			// iterating through those options. if they're in post, we'll add them to the "subscription_options" meta	
			foreach ($this->EE->subscription_model->option_keys() as $encoded_key => $key)
			{
 				$option = NULL;
				
				if ($this->EE->input->post($encoded_key))
				{
					$option = $this->EE->encrypt->decode($this->EE->input->post($encoded_key)); 
				}
				else if ($this->EE->input->post("subscription_".$key))
				{
					if ($key == "name" || $key == "description")
					{
						$option = $this->EE->input->post("subscription_".$key); 
					}
					else
					{
						$option = $this->EE->encrypt->decode($this->EE->input->post("subscription_".$key)); 
						
					}
				}
				else if ($this->EE->input->post($key))
				{
					$option = $this->EE->input->post($key); 
				}

				
				if ( ! is_null($option))
				{
					if (in_array($encoded_key, $this->EE->subscription_model->encoded_bools()))
					{
						$option = bool_string($option);
					}
					
					if (strncmp($key, 'subscription_', 13) === 0)
					{
						$key = substr($key, 13);
					}
					
					$checkout_options['subscription_options'][$key] = $option;
				}
			}
		}
		
		if (isset($_POST['member_id']) && in_array($this->EE->session->userdata('group_id'), $this->EE->config->item('cartthrob:admin_checkout_groups')))
		{
			$this->EE->session->cache['cartthrob']['member_id'] = $checkout_options['member_id'] = $this->EE->input->post('member_id');
		}
		
		// create a user 
		if ($checkout_options['create_user'])
		{
			$checkout_options['create_username'] = $this->EE->input->post('username');
			$checkout_options['create_email'] = $this->EE->input->post('email_address') ? $this->EE->input->post('email_address'): $this->EE->cartthrob->cart->customer_info('email_address');
			$checkout_options['create_screen_name'] = $this->EE->input->post('screen_name', TRUE);
			$checkout_options['create_password'] = $this->EE->input->post('password');
			$checkout_options['create_group_id'] = $checkout_options['group_id'];
			$checkout_options['create_password_confirm'] = $this->EE->input->post('password_confirm');
			$checkout_options['create_language'] = $this->EE->cartthrob->cart->customer_info('language');
		}
		
		$checkout_options['force_vault'] = bool_string($this->EE->encrypt->decode($this->EE->input->post('VLT')));
		
		$checkout_options['force_processing'] = bool_string($this->EE->encrypt->decode($this->EE->input->post('FPR')));
		
		if ($this->EE->input->post('vault_id'))
		{
			$vault_id = xss_clean($this->EE->encrypt->decode($this->EE->input->post('vault_id')));

			if ($vault_id == sanitize_number($vault_id)) // ignore a non-numeric input
			{
				$this->EE->load->model('vault_model');
				
				if ($vault = $this->EE->vault_model->get_vault($vault_id))
				{
					$checkout_options['vault'] = $vault;
				}
			}
		}
		
		
		// if the sub_id is passed in, we're deleting the cart contents, and only updating the sub
		if ($this->EE->input->post('sub_id'))
		{
			$checkout_options['update_subscription_id'] = $this->EE->input->post('sub_id'); 
			$this->EE->cartthrob->cart->clear()->save();
		}
		
		$auth = $this->EE->cartthrob_payments->checkout_start($checkout_options); 
		
		if ($auth === FALSE || !is_array($auth))
		{
			if (!is_array($auth) && is_string($auth))
			{
				 $this->EE->form_builder->add_error($auth); 
			}
			return $this->EE->form_builder->add_error($this->EE->cartthrob_payments->errors())
						      ->action_complete();
		}
		
		$this->EE->cartthrob_payments->checkout_complete($auth);
	}

	public function multi_add_to_cart_action()
	{
		// NOTE: multi add to cart does not work with configured items
		if ( ! $this->EE->input->get_post('ACT'))
		{
			return;
		}
		
		$this->EE->load->library('form_builder');
		
		// cartthrob_multi_add_to_cart_start hook
		if ($this->EE->extensions->active_hook('cartthrob_multi_add_to_cart_start') === TRUE)
		{
			$this->EE->extensions->call('cartthrob_multi_add_to_cart_start');
			if ($this->EE->extensions->end_script === TRUE) return;
		}
		
		if ( ! $this->EE->form_builder->validate())
		{
			return $this->EE->form_builder->action_complete();
		}
		
		$this->EE->cartthrob->save_customer_info();

		$entry_ids = $this->EE->input->post('entry_id', TRUE);
		$items = array(); 
		if (is_array($entry_ids))
		{
			$this->EE->load->library('encrypt');
			
			$on_the_fly = ($this->EE->input->post('OTF') && bool_string($this->EE->encrypt->decode($this->EE->input->post('OTF'))));

			$json = ($this->EE->input->post('JSN') && bool_string($this->EE->encrypt->decode($this->EE->input->post('JSN'))));
		
			$allow_user_price = ($this->EE->input->post('AUP') && bool_string($this->EE->encrypt->decode($this->EE->input->post('AUP'))));
		
			$allow_user_shipping = ($this->EE->input->post('AUS') && bool_string($this->EE->encrypt->decode($this->EE->input->post('AUS'))));
		
			$allow_user_weight = ($this->EE->input->post('AUW') && bool_string($this->EE->encrypt->decode($this->EE->input->post('AUW'))));

			$class = NULL;
			// if a class has been assigned to the item. 
			if ( $this->EE->input->post('CLS'))
			{
				$class  = $this->EE->encrypt->decode($this->EE->input->post('CLS')); 
			}
			
			foreach ($entry_ids as $row_count => $entry_id)
			{
				$quantity = xss_clean(array_value($_POST, 'quantity', $row_count));
				
				if ( ! is_numeric($quantity) || $quantity <= 0)
				{
					continue;
				}
				
				$data = array(
					'entry_id' => xss_clean(array_value($_POST, 'entry_id', $row_count)),
					'quantity' => $quantity,
				);
				// thanks to Dion40 for catching an error related to no_shipping, no_tax
				if ($this->EE->input->post('NSH'))
				{
					$data['no_shipping'] = bool_string($this->EE->encrypt->decode($this->EE->input->post('NSH')));
				}


				if ($this->EE->input->post('NTX'))
				{
					$data['no_tax'] = bool_string($this->EE->encrypt->decode($this->EE->input->post('NTX')));
				}
				if (($allow_user_price || $on_the_fly) && ($value = array_value($_POST, 'price', $row_count)) !== FALSE)
				{
					$data['price'] = xss_clean($value);
				}

				if (($allow_user_weight || $on_the_fly) && ($value = array_value($_POST, 'weight', $row_count)) !== FALSE)
				{
					$data['weight'] = xss_clean($value);
				}
		
				if (($allow_user_shipping || $on_the_fly) && ($value = array_value($_POST, 'shipping', $row_count)) !== FALSE)
				{
					$data['shipping'] = xss_clean($value);
				}
				
				if ($value = array_value($_POST, 'title', $row_count))
				{
					$data['title'] = xss_clean($value);
				}
				
				if ( ! $on_the_fly)
				{
					$data['class'] = 'product';
				}
				
				if ( $class )
				{
					$data['class'] = $class;
				}
				
				$data['site_id'] = 1; 
				

				$this->EE->load->model('product_model');
				$this->EE->load->model('cartthrob_field_model'); 
				
				$item_options = array(); 
				if ($value = array_value($_POST, 'item_options', $row_count))
				{
					$item_options = xss_clean($value);
				}
				//don't grab numeric item_options, those are for sub_items
				foreach ($item_options as $key => $value)
				{
					if (strpos($key, ':') === FALSE)
					{
						$data['item_options'][$key] = $value;
					}
				}
				
				if ($entry = $this->EE->product_model->get_product($data['entry_id']))
				{
					if (isset($entry['site_id']))
					{
						$data['site_id'] = $entry['site_id'];
					}
					
					$field_id = $this->EE->cartthrob_field_model->channel_has_fieldtype($entry['channel_id'], 'cartthrob_package', TRUE);

					if ($field_id && ! empty($entry['field_id_'.$field_id]))
					{
						//it's a package
						$data['class'] = 'package';

						$this->EE->load->library('api');

						$this->EE->api->instantiate('channel_fields');

						if (empty($this->EE->api_channel_fields->field_types))
						{
							$this->EE->api_channel_fields->fetch_installed_fieldtypes();
						}

						$data['sub_items'] = array();

						if ($this->EE->api_channel_fields->setup_handler('cartthrob_package'))
						{
							$field_data = $this->EE->api_channel_fields->apply('pre_process', array($entry['field_id_'.$field_id]));

							foreach ($field_data as $row_id => $row)
							{
								$item = array(
									'entry_id' => $row['entry_id'],
									'product_id' => $row['entry_id'],
									'row_id' => $row_id,
									'class' => 'product',
								'site_id' => $data['site_id'], // assuming it has to be from the same site id as the parent based on EE's structure
								
								);

								$item['item_options'] = (isset($row['option_presets'])) ? $row['option_presets'] : array();

								$row_item_options = array();

									if (isset($_POST['item_options'][$row_count]))
								{
	 								$row_item_options = $_POST['item_options'][$row_count];
								}
								else if (isset($_POST['item_options'][$data['entry_id'].':'.$row_id.":".$row_count]))
								{
	 								$row_item_options = $_POST['item_options'][$data['entry_id'].':'.$row_id.":".$row_count];
								}
								else if (isset($_POST['item_options'][':'.$row_id.":".$row_count]))
								{
	 								$row_item_options = $_POST['item_options'][':'.$row_id.":".$row_count];
								}
								
								$price_modifiers = $this->EE->product_model->get_all_price_modifiers($row['entry_id']);
								
									foreach ($row_item_options as $key => $value)
								{
									//if it's not a price modifier (ie, an on-the-fly item option), add it
									//if it is a price modifier, check that it's been allowed before adding
									if ( ! isset($price_modifiers[$key]) || ! empty($row['allow_selection'][$key]))
									{
										$item['item_options'][$key] = $this->EE->security->xss_clean($value);
									}
								}

								$data['sub_items'][$row_id] = $item;
							}
						}
					}
				}
				
				$data['product_id'] = $data['entry_id'];
				
				$item = $this->EE->cartthrob->cart->add_item($data);
				
				if ($this->EE->input->post('PER'))
				{
					$this->EE->load->library('encrypt');
					
					$permissions = $this->EE->encrypt->decode($this->EE->input->post('PER'));
					if ($permissions)
					{
						$item->set_meta('permissions', $permissions);
					}
				}
				
				if ($item && $value = array_value($_POST, 'license_number', $row_count))
				{
					$item->set_meta('license_number', TRUE);
				}
			}
			$items[$entry_id] = $item; 
		}

		$this->EE->cartthrob->cart->check_inventory();
		
		// cartthrob_multi_add_to_cart_end hook
		if ($this->EE->extensions->active_hook('cartthrob_multi_add_to_cart_end') === TRUE)
		{
 			$this->EE->extensions->call('cartthrob_multi_add_to_cart_end', $entry_ids, $items);
			if ($this->EE->extensions->end_script === TRUE) return;
		}
		
		$this->EE->form_builder->set_errors($this->EE->cartthrob->errors())
					->set_success_callback(array($this->EE->cartthrob, 'action_complete'))
					->action_complete();
	}
	
	public function save_customer_info_action()
	{
		if ( ! $this->EE->input->get_post('ACT'))
		{
			return;
		}
	
		$this->EE->load->library('form_builder');
		
		if ($this->EE->extensions->active_hook('cartthrob_save_customer_info_start') === TRUE)
		{
			$this->EE->extensions->call('cartthrob_save_customer_info_start');
		}
	
		if ($this->EE->form_builder->validate())
		{
			$this->EE->cartthrob->save_customer_info();
		}
		else
		{
			$this->EE->cartthrob_variables->set_global_values();
		}
		
		if ($this->EE->extensions->active_hook('cartthrob_save_customer_info_end') === TRUE)
		{
			$this->EE->extensions->call('cartthrob_save_customer_info_end');
		}
	
		$this->EE->form_builder->set_success_callback(array($this->EE->cartthrob, 'action_complete'))
					->action_complete();
	}
	

	/**
	 * payment_return_action
	 *
	 * handles information from PayPal's IPN, offsite gateways, or other payment notification systems. 
	 * @param string $gateway the payment gateway class/file that should called
	 * @param string $method the method in the gateway class that should handle the transaction
	 * @return void
	 * @author Chris Newton
	 * @since 1.0
	 * @access public
	 */
	public function payment_return_action($gateway = NULL, $method = NULL)
	{
		if ( ! $this->EE->input->get_post('ACT'))
		{
			return;
		}
		
		$this->EE->load->library('encrypt');
		
		$gateway = xss_clean($this->EE->encrypt->decode(str_replace(' ', '+', base64_decode($this->EE->input->get_post('gateway')))));
		if (!$gateway)
		{
			$gateway = xss_clean($this->EE->encrypt->decode(str_replace(' ', '+', base64_decode($this->EE->input->get_post('G')))));
		}
		
		
		// When offsite payments are returned, they're expected to have a method
		// set to handle processing the payments. 	
		if ($this->EE->input->get_post('method'))
		{
			$method = xss_clean($this->EE->encrypt->decode(str_replace(' ', '+', base64_decode($this->EE->input->get_post('method')))));
		}
		elseif ($this->EE->input->get_post('M'))
		{
			$method = xss_clean($this->EE->encrypt->decode(str_replace(' ', '+', base64_decode($this->EE->input->get_post('M')))));
		}
		
		$this->EE->load->library('cartthrob_payments');

		$auth = array(
			'processing' => FALSE,
			'authorized' => FALSE,
			'declined' => FALSE,
			'failed' => TRUE,
			'error_message' => '',
			'transaction_id' => '',
			'expired'	=> FALSE, 
			'canceled'	=> FALSE, 
			'voided'	=> FALSE,
			'pending'	=> FALSE,
			'refunded'	=> FALSE,
			
		);
		
		if ( ! $this->EE->cartthrob_payments->set_gateway($gateway)->gateway())
		{
			$auth['error_message'] = $this->EE->lang->line('invalid_payment_gateway');
		}
		else
		{
			if ($method && method_exists($this->EE->cartthrob_payments->gateway(), $method))
			{
				$data = $this->EE->security->xss_clean($_POST);
				
				// handling get variables.
				if ($_SERVER['QUERY_STRING'])
				{
					// the following was added to convert the query string manually into an array
					// because something like &company=abercrombie&fitch&name=joe+jones was causing the return
					// data to get hosed. Stupid PayPal. You suck. URLencode your goddamned querystrings in your
					// IPN notifications. Fucking bastards.
					$_SERVER['QUERY_STRING'] = preg_replace("/&(?=[^=]*&)/", "%26", $_SERVER['QUERY_STRING']);
					
					$get = array();
					parse_str($_SERVER['QUERY_STRING'], $get);
					
					foreach($get as $key => $value) 
					{
						if ( ! isset($data[$key]))
						{
							$data[$key] = xss_clean($value);
						}
					}
				}
				
				foreach ($data as $key=> $item)
				{
					$this->EE->cartthrob->log($key.' - '.$item);
				}
				
				$auth = $this->EE->cartthrob_payments->gateway()->$method($data);
			}
			else
			{
				$auth['error_message']	= $this->EE->lang->line('gateway_function_does_not_exist');
			}
		}
		
		$this->EE->cartthrob_payments->checkout_complete($auth);
	}
	// END
 
	/* NOT READY FOR PRIME TIME!
	public function create_token_form()
	{
		if ($this->EE->session->userdata('member_id') == 0)
		{
			$this->EE->template_helper->tag_redirect($this->EE->TMPL->fetch_param('logged_out_redirect'));
		}

		$this->EE->load->library('api/api_cartthrob_payment_gateways');

		if ($this->EE->cartthrob->store->config('allow_gateway_selection'))
		{
			if ($this->EE->TMPL->fetch_param('gateway'))
			{
				$this->EE->api_cartthrob_payment_gateways->set_gateway($this->EE->TMPL->fetch_param('gateway'));
			}
		}
		else
		{
			unset($this->EE->TMPL->tagparams['gateway']);
		}
		// @TODO add token_fields method
		$data = $this->EE->cartthrob_variables->global_variables(TRUE);
		
		$data = ['token_fields'] = $this->EE->api_cartthrob_payment_gateways->token_fields();

		$this->EE->load->library('form_builder');

		$this->EE->form_builder->initialize(array(
			'form_data' => array(
				'action',
				'secure_return',
				'return',
				'language',
				'authorized_redirect',
				'failed_redirect',
			),
			'encoded_form_data' => array(
				'gateway' => 'gateway'
			),
			'classname' => 'Cartthrob',
			'method' => 'token_action',
			'params' => $this->EE->TMPL->tagparams,
			'action' => $this->EE->cartthrob->store->config('payment_system_url'),
			'content' => $this->EE->TMPL->parse_variables(array($data)),
		));

		return $this->EE->form_builder->form();
	}


	protected function token_action()
	{
		if ( ! $this->EE->input->get_post('ACT'))
		{
			return;
		}

		$this->EE->cartthrob->save_customer_info();

		$this->EE->load->library('form_validation');
		$this->EE->load->library('encrypt');
		$this->EE->load->library('form_builder');

		$this->EE->form_builder->set_show_errors(TRUE)
					->set_success_callback(array($this->EE->cartthrob, 'action_complete'))
					->set_error_callback(array($this->EE->cartthrob, 'action_complete'));

		$this->EE->load->library('languages');

		$this->EE->languages->set_language($this->EE->input->post('language', TRUE));

		$not_required = array();

		$required = array();

		if ($this->EE->input->post('REQ'))
		{
			$required_string = xss_clean($this->EE->encrypt->decode($this->EE->input->post('REQ')));

			if (preg_match('/^not (.*)/', $required_string, $matches))
			{
				$not_required = explode('|', $matches[1]);
				$required_string = '';
			}

			if ($required_string)
			{
				$required = explode('|', $required_string);
			}
			unset($required_string);
		}	
		$gateway = ($this->EE->cartthrob->store->config('allow_gateway_selection') && $this->EE->input->post('gateway')) ? xss_clean($this->EE->encrypt->decode($this->EE->input->post('gateway'))) : $this->EE->cartthrob->store->config('payment_gateway');

		$credit_card_number = sanitize_credit_card_number($this->EE->input->post('credit_card_number', TRUE));
		if ($this->EE->cartthrob->store->config('modulus_10_checking') && ! modulus_10_check($credit_card_number))
		{
			$this->EE->form_builder->add_error($this->EE->lang->line('validation_card_modulus_10'))
						->action_complete();
		}

		// Load the payment processing plugin that's stored in the extension's settings.
		$this->EE->load->library('cartthrob_payments');

		if ( ! $this->EE->cartthrob_payments->set_gateway($gateway)->gateway())
		{
			$this->EE->form_builder->add_error($this->EE->lang->line('invalid_payment_gateway'))
						->action_complete();
		}

		$authorized_redirect = $this->EE->input->post('authorized_redirect', TRUE);
		$failed_redirect = $this->EE->input->post('failed_redirect', TRUE);

		// @TODO add the required_token_fields method
	 	$required = array_merge($required, $this->EE->cartthrob_payments->required_token_fields());
		foreach ($not_required as $key)
		{
			unset($required[array_search($key, $required)]);
		}
		if ($required)
		{
			foreach ($required as $key)
			{
				if (preg_match('/^custom_data\[(.*)\]$/', $key, $match))
				{
					$message = sprintf($this->EE->lang->line('validation_custom_data'), $match[1]);
				}
				else
				{
					$message = $this->EE->lang->line('validation_'.$key);
				}

				$this->EE->form_validation->set_rules($key, $message, 'required');
			}

			if ( ! $this->EE->form_validation->run())
			{
				$this->EE->form_builder->add_error($this->EE->form_validation->_error_array)
							->action_complete();
			}
		}

		$token_data = array(
	 		'member_id'			=> $this->EE->cartthrob->cart->customer_info('member_id'),
			'last_four'			=> substr($credit_card_number,-4,4),
			'description'		=> $this->EE->input->post('description', TRUE),
			'token_id'			=> NULL,
			'id'				=> NULL,
		);


		$auth = $this->EE->cartthrob_payments->create_token($credit_card_number);

		if ($auth['authorized'])
		{
			$token_array['token_id'] = $auth['transaction_id'];

			$this->EE->db->insert('cartthrob_tokens', $token_data);

			$token_array['id'] = $this->EE->db->insert_id();
		}
		else
		{
			$this->EE->form_builder->set_return($this->EE->cartthrob->cart->order('failed_redirect'))
					       ->add_error(element('error_message', $auth));
		}

		$this->EE->form_builder->action_complete();
	}
	*/
	// @TODO add subscription updater
	
	// @TODO make this function read the gateway out of the database based on the provided entry id
	public function update_recurrent_billing_form()
	{
		if ($this->EE->session->userdata('member_id') == 0)
		{
			$this->EE->template_helper->tag_redirect($this->EE->TMPL->fetch_param('logged_out_redirect'));
		}

		$this->EE->load->library('api/api_cartthrob_payment_gateways');

		if ($this->EE->TMPL->fetch_param('gateway'))
		{
			$this->EE->api_cartthrob_payment_gateways->set_gateway($this->EE->TMPL->fetch_param('gateway'));
		}
		
		$data = $this->EE->cartthrob_variables->global_variables(TRUE);
		
		$data['recurrent_billing_fields'] = $this->EE->api_cartthrob_payment_gateways->gateway_fields(FALSE, 'recurrent_billing_update');
		$data['gateway_fields'] = $this->EE->api_cartthrob_payment_gateways->gateway_fields();

 		$this->EE->load->library('form_builder');
		
		$this->EE->form_builder->initialize(array(
			'classname' => 'Cartthrob',
			'method' => 'update_recurrent_billing_action',
			'params' => $this->EE->TMPL->tagparams,
			'content' => $this->EE->template_helper->parse_variables_row($data),
			'form_data' => array(
				'action',
				'secure_return',
				'return',
				'language',
				
			),
			'encoded_form_data' => array(
				'required' 					=> 'REQ',
				'gateway' 					=> 'gateway',
				'subscription_name'					=> 'SUN',
				'subscription_start_date'			=> 'SSD',
				'subscription_end_date'				=> 'SED',
				'subscription_interval_units'		=> 'SIU',
				'sub_id'							=> 'SD',
				'subscription_type'					=> 'SUT',
			),
			'encoded_numbers' => array(
				'subscription_total_occurrences'	=> 'SO',
				'subscription_trial_price'			=> 'ST',
				'subscription_trial_occurrences'	=> 'SP',
				'subscription_interval_length'		=> 'SI',
				'order_id'							=> 'OI',
				
			),
			'encoded_bools' => array(
				'allow_user_price' => 'AUP',
				//'show_errors' => array('ERR', TRUE),
				'json' => 'JSN',
				// 'subscription_allow_modification'		=> 'SM',
				
			),
		));
		return $this->EE->form_builder->form();
	}
	
	public function update_recurrent_billing_action()
	{
		// currently we allow the customer information stored on file with the recurrent bill to be changed. 
		// the actual details of the original order are not changed however. 
		// over time we need feedback about what needs to be added / changed in the original order
		// or purchased items when someone decides to update their subscription
		// not all systems allow the sub itself to be updated, but they all allow customer information
		// like credit card numbers to be changed. For our purposes, we're currently only using this
		// as a card data update. 
		
		// @TODO catch the sub id, and order id. 
		
		$total = 0; 
		
		if ( ! $this->EE->input->get_post('ACT'))
		{
			return;
		}

		$this->EE->cartthrob->save_customer_info();
		
		$this->EE->load->library('form_validation');
		$this->EE->load->library('encrypt');
		$this->EE->load->library('form_builder');
		
		$this->EE->form_builder->set_show_errors(TRUE)
					->set_success_callback(array($this->EE->cartthrob, 'action_complete'))
					->set_error_callback(array($this->EE->cartthrob, 'action_complete'));
		
		if (! $this->EE->cartthrob->store->config('save_orders'))
		{
			$this->EE->form_builder->action_complete();
		}
				
		$this->EE->load->library('languages');

		$this->EE->languages->set_language($this->EE->input->post('language', TRUE));

		$not_required = array();
		$required = array();

		if ($this->EE->input->post('REQ'))
		{
			$required_string = xss_clean($this->EE->encrypt->decode($this->EE->input->post('REQ')));

			if (preg_match('/^not (.*)/', $required_string, $matches))
			{
				$not_required = explode('|', $matches[1]);
				$required_string = '';
			}

			if ($required_string)
			{
				$required = explode('|', $required_string);
			}

			unset($required_string);
		}

		$gateway = ($this->EE->input->post('gateway')) ? xss_clean($this->EE->encrypt->decode($this->EE->input->post('gateway'))) : $this->EE->cartthrob->store->config('payment_gateway');

		$credit_card_number = sanitize_credit_card_number($this->EE->input->post('credit_card_number', TRUE));

		// Load the payment processing plugin that's stored in the extension's settings.
		$this->EE->load->library('cartthrob_payments');

		if ( ! $this->EE->cartthrob_payments->set_gateway($gateway)->gateway())
		{
			$this->EE->form_builder->add_error($this->EE->lang->line('invalid_payment_gateway'))
						->action_complete();
		}

		$authorized_redirect = $this->EE->input->post('authorized_redirect', TRUE);
		$failed_redirect = $this->EE->input->post('failed_redirect', TRUE);
		$declined_redirect = $this->EE->input->post('declined_redirect', TRUE);
 
		if ($this->EE->input->post('EXP'))
		{
			$data = xss_clean($this->EE->encrypt->decode($this->EE->input->post('EXP')));

			if ($data == sanitize_number($data)) // ignore a non-numeric input
			{
				$expiration_date = $data;
			}
		}
		
		if ($this->EE->input->post('PR'))
		{
			$data = xss_clean($this->EE->encrypt->decode($this->EE->input->post('PR')));

			if ($data == sanitize_number($data)) // ignore a non-numeric input
			{
				$total -= $subtotal;
				$subtotal = $data;
				$total += $subtotal;
			}
		}
		elseif ($this->EE->input->post('AUP'))
		{
			if (bool_string($this->EE->encrypt->decode($this->EE->input->post('AUP'))))
			{
				$total = sanitize_number($this->EE->input->post('price', TRUE));
			}
		}
		
		if ($this->EE->input->post('OI'))
		{
			$data = xss_clean($this->EE->encrypt->decode($this->EE->input->post('OI')));

			if ($data == sanitize_number($data)) // ignore a non-numeric input
			{
				$order_id = $data; 
			}
		}
		
		if ($this->EE->input->post('SD'))
		{
			$sub_id = xss_clean($this->EE->encrypt->decode($this->EE->input->post('SD')));
		}
		
		foreach ($not_required as $key)
		{
			unset($required[array_search($key, $required)]);
		}
		
		
		if ( ! $this->EE->form_builder->set_required($required)->validate())
		{
			$this->EE->form_builder->action_complete();
		}
	
		$this->EE->load->model('order_model');
	
		$order_data = $this->EE->order_model->order_data_array(); 
		
		$this->EE->cartthrob->cart->set_order($order_data);
		
		$this->EE->cartthrob_payments->set_total($total);
		
		$this->EE->cartthrob->cart->save();
		
		$auth = $this->EE->cartthrob_payments->update_recurrent_billing($sub_id, $credit_card_number);
		
		$auth = array_merge(
			array(
				'processing' => FALSE,
				'authorized' => FALSE,
				'declined' => FALSE,
				'failed' => TRUE,
				'error_message' => '',
				'transaction_id' => '',
			),
			$auth
		);
		
		#$this->EE->session->set_flashdata($auth);
		
		//since we use the authorized variables as tag conditionals in submitted_order_info,
		//we won't throw any errors from here on out
		$this->EE->form_builder->set_show_errors(FALSE);

 
		if ($auth['authorized'])
		{
			$this->EE->form_builder->set_return($this->EE->cartthrob->cart->order('authorized_redirect'));
		}
		else 
		{
			$this->EE->form_builder->set_return($this->EE->cartthrob->cart->order('failed_redirect'))
					       ->add_error(element('error_message', $auth));
		}
		$this->EE->cartthrob->cart->save();

		$this->EE->form_builder->action_complete();
		
	}
	// @TODO make this function read the gateway out of the database based on the provided entry id
	
	public function delete_recurrent_billing_form()
	{
		if ($this->EE->session->userdata('member_id') == 0)
		{
			$this->EE->template_helper->tag_redirect($this->EE->TMPL->fetch_param('logged_out_redirect'));
		}

		$this->EE->load->library('api/api_cartthrob_payment_gateways');

		if ($this->EE->TMPL->fetch_param('gateway'))
		{
			$this->EE->api_cartthrob_payment_gateways->set_gateway($this->EE->TMPL->fetch_param('gateway'));
		}
		
		$data = $this->EE->cartthrob_variables->global_variables(TRUE);

		$data['gateway_fields'] = $this->EE->api_cartthrob_payment_gateways->gateway_fields(FALSE, 'recurrent_billing_delete');

 		$this->EE->load->library('form_builder');
		
		$this->EE->form_builder->initialize(array(
			'classname' => 'Cartthrob',
			'method' => 'delete_recurrent_billing_action',
			'params' => $this->EE->TMPL->tagparams,
			'content' => $this->EE->template_helper->parse_variables_row($data),
			'form_data' => array(
				'action',
				'secure_return',
				'return',
				'language',
				
			),
			'encoded_form_data' => array(
				'required' 					=> 'REQ',
				'sub_id'							=> 'SD',
				'gateway' 					=> 'gateway',
			),
			'encoded_numbers' => array(
				'order_id'							=> 'OI',
			),
			'encoded_bools' => array(
				'allow_user_price' => 'AUP',
				//'show_errors' => array('ERR', TRUE),
				'json' => 'JSN',
			),
		));
		return $this->EE->form_builder->form();
	}
	public function delete_recurrent_billing_action()
	{
		if ( ! $this->EE->input->get_post('ACT'))
		{
			return;
		}

		$this->EE->cartthrob->save_customer_info();
		
		$this->EE->load->library('form_validation');
		$this->EE->load->library('encrypt');
		$this->EE->load->library('form_builder');
		
		$this->EE->form_builder->set_show_errors(TRUE)
					->set_success_callback(array($this->EE->cartthrob, 'action_complete'))
					->set_error_callback(array($this->EE->cartthrob, 'action_complete'));
		
		if (! $this->EE->cartthrob->store->config('save_orders'))
		{
			$this->EE->form_builder->action_complete();
		}
				
		$this->EE->load->library('languages');

		$this->EE->languages->set_language($this->EE->input->post('language', TRUE));

		$not_required = array();
		$required = array();

		if ($this->EE->input->post('REQ'))
		{
			$required_string = xss_clean($this->EE->encrypt->decode($this->EE->input->post('REQ')));

			if (preg_match('/^not (.*)/', $required_string, $matches))
			{
				$not_required = explode('|', $matches[1]);
				$required_string = '';
			}

			if ($required_string)
			{
				$required = explode('|', $required_string);
			}

			unset($required_string);
		}

		$gateway = ($this->EE->input->post('gateway')) ? xss_clean($this->EE->encrypt->decode($this->EE->input->post('gateway'))) : $this->EE->cartthrob->store->config('payment_gateway');

		// Load the payment processing plugin that's stored in the extension's settings.
		$this->EE->load->library('cartthrob_payments');

		if ( ! $this->EE->cartthrob_payments->set_gateway($gateway)->gateway())
		{
			$this->EE->form_builder->add_error($this->EE->lang->line('invalid_payment_gateway'))
						->action_complete();
		}

		$authorized_redirect = $this->EE->input->post('authorized_redirect', TRUE);
		$failed_redirect = $this->EE->input->post('failed_redirect', TRUE);
		$declined_redirect = $this->EE->input->post('declined_redirect', TRUE);
		
		if ($this->EE->input->post('OI'))
		{
			$data = xss_clean($this->EE->encrypt->decode($this->EE->input->post('OI')));

			if ($data == sanitize_number($data)) // ignore a non-numeric input
			{
				$order_id = $data; 
			}
		}
		
		if ($this->EE->input->post('SD'))
		{
			$sub_id = xss_clean($this->EE->encrypt->decode($this->EE->input->post('SD')));
		}
		
 		foreach ($not_required as $key)
		{
			unset($required[array_search($key, $required)]);
		}
		
		
		if ( ! $this->EE->form_builder->set_required($required)->validate())
		{
			$this->EE->form_builder->action_complete();
		}
		
		$this->EE->load->model('order_model');
	
		$order_data = $this->EE->order_model->order_data_array(); 
		
		$this->EE->cartthrob->cart->set_order($order_data);
		
		$this->EE->cartthrob->cart->save();
		
		$auth = $this->EE->cartthrob_payments->delete_recurrent_billing($sub_id);
		
		$auth = array_merge(
			array(
				'processing' => FALSE,
				'authorized' => FALSE,
				'declined' => FALSE,
				'failed' => TRUE,
				'error_message' => '',
				'transaction_id' => '',
			),
			$auth
		);
		
		$this->EE->cartthrob->cart->update_order(array_merge($auth, array('auth' => $auth)));
		
		$this->EE->session->set_flashdata($auth);
		
		//since we use the authorized variables as tag conditionals in submitted_order_info,
		//we won't throw any errors from here on out
		$this->EE->form_builder->set_show_errors(FALSE);
 
		if ($auth['authorized'])
		{
			$this->EE->form_builder->set_return($this->EE->cartthrob->cart->order('authorized_redirect'));
		}
		else 
		{
			$this->EE->form_builder->set_return($this->EE->cartthrob->cart->order('failed_redirect'))
					       ->add_error(element('error_message', $auth));
		}
		$this->EE->cartthrob->cart->save();

		$this->EE->form_builder->action_complete();
	}
 	/**
	 * Prints a coupon code form.
	 *
	 * @access public
	 * @param string $TMPL->fetch_param('action')
	 * @param string $TMPL->fetch_param('id')
	 * @param string $TMPL->fetch_param('class')
	 * @param string $TMPL->fetch_param('name')
	 * @param string $TMPL->fetch_param('onsubmit')
	 * @return string
	 * @since 1.0.0
	 * @author Rob Sanchez
	 */
	public function add_coupon_form()
	{
		if ( ! $this->EE->session->userdata('member_id'))
		{
			$this->EE->template_helper->tag_redirect($this->EE->TMPL->fetch_param('logged_out_redirect'));
		}
		
		$this->EE->load->library('form_builder');

		$data = $this->EE->cartthrob_variables->global_variables(TRUE);
		
		$data['allowed'] = 1;

		if ($this->EE->cartthrob->store->config('global_coupon_limit') && count($this->EE->cartthrob->cart->coupon_codes()) >= $this->EE->cartthrob->store->config('global_coupon_limit'))
		{
			$data['allowed'] = 0;
		}
		
		$this->EE->form_builder->initialize(array(
			'classname' => 'Cartthrob',
			'method' => 'add_coupon_action',
			'params' => $this->EE->TMPL->tagparams,
			'content' => $this->EE->template_helper->parse_variables_row($data),
			'form_data' => array(
				'action',
				'secure_return',
				'return',
				'language',
			),
			'encoded_form_data' => array(
			),
			'encoded_numbers' => array(
			),
			'encoded_bools' => array(
				//'show_errors' => array('ERR', TRUE),
				'json' => 'JSN',
			),
		));

		return $this->EE->form_builder->form();
	}

	public function add_to_cart()
	{
		// cartthrob_add_to_cart_start hook
		if ($this->EE->extensions->active_hook('cartthrob_add_to_cart_start') === TRUE)
		{
			//@TODO work on hook parameters
			//$edata = $EXT->universal_call_extension('cartthrob_add_to_cart_start', $this, $_SESSION['cartthrob']);
			$this->EE->extensions->call('cartthrob_add_to_cart_start');
			if ($this->EE->extensions->end_script === TRUE) return;
		}
		
		$data = array(
			'entry_id' => $this->EE->TMPL->fetch_param('entry_id'),
			'quantity' => ($this->EE->TMPL->fetch_param('quantity') !== FALSE) ? $this->EE->TMPL->fetch_param('quantity') : 1,
			'class' => 'product',
		);
		
		foreach ($this->EE->TMPL->tagparams as $key => $value)
		{
			if (preg_match('/^item_options?:(.*)$/', $key, $match))
			{
				if ( ! isset($data['item_options']))
				{
					$data['item_options'] = array();
				}
				
				$data['item_options'][$match[1]] = $value;
			}
		}

		if (bool_string($this->EE->TMPL->fetch_param('shipping_exempt')))
		{
			$data['no_shipping'] = TRUE;
		}
		if (bool_string($this->EE->TMPL->fetch_param('no_shipping')))
		{
			$data['no_shipping'] = TRUE;
		}

		if (bool_string($this->EE->TMPL->fetch_param('tax_exempt')))
		{
			$data['no_tax'] = TRUE;
		}
		if (bool_string($this->EE->TMPL->fetch_param('no_tax')))
		{
			$data['no_tax'] = TRUE;
		}
		
		$data['product_id'] = $data['entry_id'];
		
		if ( ! $data['entry_id'])
		{
			$this->EE->cartthrob->set_error(lang('add_to_cart_no_entry_id'));
		}
		
		if ( ! $this->EE->cartthrob->errors())
		{
			$entry = $this->EE->product_model->get_product($data['entry_id']);
			
			//it's a package
			if ($entry && $field_id = $this->EE->cartthrob_field_model->channel_has_fieldtype($entry['channel_id'], 'cartthrob_package', TRUE))
			{
				$data['class'] = 'package';
				
				$this->EE->load->library('api');
				
				$this->EE->api->instantiate('channel_fields');
				
				if (empty($this->EE->api_channel_fields->field_types))
				{
					$this->EE->api_channel_fields->fetch_installed_fieldtypes();
				}
				
				$data['sub_items'] = array();
				
				if ($this->EE->api_channel_fields->setup_handler('cartthrob_package'))
				{
					$field_data = $this->EE->api_channel_fields->apply('pre_process', array($entry['field_id_'.$field_id]));
					
					foreach ($field_data as $row_id => $row)
					{
						$item = array(
							'entry_id' => $row['entry_id'],
							'product_id' => $row['entry_id'],
							'row_id' => $row_id,
							'class' => 'product',
						);
						
						$item['item_options'] = (isset($row['option_presets'])) ? $row['option_presets'] : array();
						
						$row_item_options = array();
						
						if (isset($_POST['item_options'][$row_id]))
						{
							$row_item_options = $_POST['item_options'][$row_id];
						}
						else if (isset($_POST['item_options'][':'.$row_id]))
						{
							$row_item_options = $_POST['item_options'][':'.$row_id];
						}
						
						$price_modifiers = $this->EE->product_model->get_all_price_modifiers($row['entry_id']);
						
						foreach ($row_item_options as $key => $value)
						{
							//if it's not a price modifier (ie, an on-the-fly item option), add it
							//if it is a price modifier, check that it's been allowed before adding
							if ( ! isset($price_modifiers[$key]) || ! empty($row['allow_selection'][$key]))
							{
								$item['item_options'][$key] = $this->EE->security->xss_clean($value);
							}
						}
						
						$data['sub_items'][$row_id] = $item;
					}
				}
			}
			elseif($entry)
			{
				// it's a product... don't need to do anything extra
				// but we need to check for it... else the class gets killed and we dont' want that. 
			}
			else
			{
				// it's a dynamic product. kill the class
				if (isset($data['class']))
				{
					unset($data['class']); 
				}
			}
						
			$item = $this->EE->cartthrob->cart->add_item($data);
			
			if ($item && $this->EE->TMPL->fetch_param('permissions'))
			{
				$item->set_meta('permissions', $this->EE->TMPL->fetch_param('permissions'));
			}
			
			if ($item && bool_string($this->EE->TMPL->fetch_param('license_number')))
			{
				$item->set_meta('license_number', TRUE);
			}
	
			// cartthrob_add_to_cart_end hook
			if ($this->EE->extensions->active_hook('cartthrob_add_to_cart_end') === TRUE)
			{
				//@TODO work on hook parameters
				//$edata = $EXT->universal_call_extension('cartthrob_add_to_cart_end', $this, $_SESSION['cartthrob'], $row_id);
				$this->EE->extensions->call('cartthrob_add_to_cart_end', $item);
				if ($this->EE->extensions->end_script === TRUE) return;
			}
		}
		
		$show_errors = bool_string($this->EE->TMPL->fetch_param('show_errors'), TRUE);
		
		$this->EE->session->set_flashdata(array(
			'success' => ! (bool) $this->EE->cartthrob->errors(),
			'errors' => $this->EE->cartthrob->errors(),
			'csrf_token' => $this->EE->functions->add_form_security_hash('{CSRF_TOKEN}'),
		));
		
		if ($show_errors && $this->EE->cartthrob->errors() && ! AJAX_REQUEST)
		{
			return show_error($this->EE->cartthrob->errors());
		}
		
		$this->EE->cartthrob->cart->save();
		
		$this->EE->template_helper->tag_redirect($this->EE->TMPL->fetch_param('return'));
	}
	// --------------------------------
	//  Add to Cart Form
	// --------------------------------
	/**
	 * add_to_cart_form
	 *
	 * This tag creates a form for adding one or more products to the cart object
	 * 
	 * @return string Tagdata output
	 * @author Rob Sanchez, Chris Newton
	 * @since 1.0
	 * @access public
	 */
	public function add_to_cart_form()
	{
		if ( ! $this->EE->session->userdata('member_id') && $this->EE->TMPL->fetch_param('logged_out_redirect'))
		{
			$this->EE->template_helper->tag_redirect($this->EE->TMPL->fetch_param('logged_out_redirect'));
		}
		
		$this->EE->load->library('form_builder');
		
		$this->EE->load->model('subscription_model');
		
		$this->EE->form_builder->initialize(array(
			'form_data' => array(
				'entry_id',
				'quantity',
				'secure_return',
				'title',
				'language',
				'return'
			),
			'encoded_form_data' => array_merge(
				$this->EE->subscription_model->encoded_form_data(),
				array(
					'shipping' => 'SHP',
					'weight' => 'WGT', 
					'permissions'	=> 'PER',
					'upload_directory' => 'UPL',
					'class'		=> 'CLS',
				)
			),
			'encoded_numbers' => array_merge(
				$this->EE->subscription_model->encoded_numbers(),
				array(
					'price' => 'PR',
					'expiration_date' => 'EXP',
				)
			),
			'encoded_bools' => array_merge(
				array(
					'allow_user_price' => 'AUP',
					'allow_user_weight' => 'AUW',
					'allow_user_shipping' => 'AUS',
					'on_the_fly' => 'OTF',
					'show_errors' => array('ERR', TRUE),
					'license_number' => 'LIC',
				)
			),
			'array_form_data' => array(
				'item_options',
			),
			'encoded_array_form_data' => array(
				'meta'				=> 'MET',
			),
			'classname' => 'Cartthrob',
			'method' => 'add_to_cart_action',
			'params' => $this->EE->TMPL->tagparams,
		));
 		
		// can't just shove these in the encoded bools, or they will always be FALSE by default unless set. 
		// since the field type overrides them, we don't even want them set here unless explicitly set. 
		foreach ($this->EE->subscription_model->encoded_bools() as $key => $value)
		{
			if ($this->EE->TMPL->fetch_param($key))
			{
				$this->EE->form_builder->set_encoded_bools($key, $value)->set_params($this->EE->TMPL->tagparams);
			}
		}

		if (bool_string($this->EE->TMPL->fetch_param('no_tax')))
		{
			$this->EE->form_builder->set_encoded_bools("no_tax", 'NTX')->set_params($this->EE->TMPL->tagparams);
		}
		elseif (bool_string($this->EE->TMPL->fetch_param('tax_exempt')))
		{
			$this->EE->form_builder->set_encoded_bools("tax_exempt", 'NTX')->set_params($this->EE->TMPL->tagparams);
		}
		
		if (bool_string($this->EE->TMPL->fetch_param('no_shipping')))
		{
			$this->EE->form_builder->set_encoded_bools("no_shipping", 'NSH')->set_params($this->EE->TMPL->tagparams);
		}
		elseif (bool_string($this->EE->TMPL->fetch_param('shipping_exempt')))
		{
			$this->EE->form_builder->set_encoded_bools("shipping_exempt", 'NSH')->set_params($this->EE->TMPL->tagparams);
		}
		
		
		$data = array_merge(
			$this->EE->cartthrob_variables->item_option_vars($this->EE->TMPL->fetch_param('entry_id')),
			$this->EE->cartthrob_variables->global_variables(TRUE)
		);

		$this->EE->cartthrob_variables->add_encoded_option_vars($data);
		
		foreach ($this->EE->TMPL->var_single as $var)
		{
			if (preg_match('/^inventory:reduce(.+)$/', $var, $match))
			{
				$data[$match[0]] = '';
				
				$var_params = $this->EE->functions->assign_parameters($match[1]);
				
				if ( ! empty($var_params['entry_id']))
				{
					if (empty($var_params['quantity']))
					{
						$var_params['quantity'] = 1;
					}
					else
					{
						$var_params['quantity'] = sanitize_number($var_params['quantity']);
					}
					
					$this->EE->form_builder->set_hidden('inventory_reduce['.$var_params['entry_id'].']', $var_params['quantity']);
				}
			}
		}
		
		$this->EE->load->library('languages');
		
		$this->EE->languages->set_language($this->EE->TMPL->fetch_param('language'));
		
		$this->EE->form_builder->set_content($this->EE->template_helper->parse_variables_row($data));
		
		return $this->EE->form_builder->form();
	}

	public function add_coupon_code()
	{
		$this->EE->cartthrob->cart->add_coupon_code($this->EE->TMPL->fetch_param('coupon_code'));
		
		$this->EE->cartthrob->cart->save();
		
		$this->EE->template_helper->tag_redirect($this->EE->TMPL->fetch_param('return'));
	}
	
	public function purchased_entry_ids()
	{
		$data = array(); 
		
		$this->EE->load->model('purchased_items_model');
		
		$purchased = $this->EE->purchased_items_model->purchased_entry_ids();
		
		foreach ($purchased as  $entry_id)
		{
			$data[] = array('entry_id' => $entry_id);
 		}
		
		return $this->EE->template_helper->parse_variables($data);
	}
	/**
 	 * most_purchased
 	 *
 	 * Tag pair will print out the entry IDs of items purchased in descending order.
 	 * @return string
 	 * @param $TMPL limit
 	 * @author Jubair Saidi
 	 **/
  	public function most_purchased()
	{
 	 	$data = array();
		
  	 	$this->EE->load->model('order_management_model');
 	 	$sort = $this->EE->TMPL->fetch_param('sort') ? $this->EE->TMPL->fetch_param('sort') : "DESC";
 	 	$limit = $this->EE->TMPL->fetch_param('limit');

 	 	$purchased = $this->EE->order_management_model->get_purchased_products(array(), "total_sales", $sort, $limit);

 	 	foreach ($purchased as $row) {
 	 	 	 	$data[] = array(
 	 	 	 	 	 	'entry_id' => $row['entry_id'],
 	 	 	 	);
 	 	}
 	 	return $this->EE->template_helper->parse_variables($data);
 	}
 	
	/**
	 * also_purchased
	 *
	 * Tag pair will replace {entry_id} with entry id of related purchased items.
	 * @return string
	 * @param $TMPL entry_id
	 * @param $TMPL limit
	 * @author Chris Newton
	 * @since 1.0
	 **/
	public function also_purchased()
	{
		$data = array();
		
		if ($parent_id = $this->EE->TMPL->fetch_param('entry_id'))
		{
			$this->EE->load->model(array('purchased_items_model', 'cartthrob_entries_model'));
			
			$purchased = $this->EE->purchased_items_model->also_purchased($parent_id, $this->EE->TMPL->fetch_param('limit'));
			
			foreach ($purchased as $entry_id => $count)
			{
				if ($row = $this->EE->cartthrob_entries_model->entry_vars($entry_id))
				{
					$data[] = $row;
				}
			}
		}
		
		return $this->EE->template_helper->parse_variables($data);
	}
	// END

	/**
	 * arithmetic
	 * 
	 * This function does arithmetic calculations
	 *
	 * @return string
	 * @param string TEMPLATE PARAM operator + / - etc
	 * @author Rob Sanchez, Chris Barrett
	 * @access public
	 * @since 1.0
	 */
	public function arithmetic()
	{
		$this->EE->load->library(array('math', 'number'));
		
		if ($this->EE->TMPL->fetch_param('expression') !== FALSE)
		{
			if (bool_string($this->EE->TMPL->fetch_param('debug')))
			{
				return $this->EE->TMPL->fetch_param('expression');
			}
			
			$evaluation = ($this->EE->TMPL->fetch_param('expression')) ? $this->EE->math->evaluate($this->EE->TMPL->fetch_param('expression')) : 0;
		}
		else
		{
			$evaluation = $this->EE->math->arithmetic($this->EE->TMPL->fetch_param('num1'), $this->EE->TMPL->fetch_param('num2'), $this->EE->TMPL->fetch_param('operator'));
		}
		
		if ($evaluation === FALSE && bool_string($this->EE->TMPL->fetch_param('show_errors'), TRUE))
		{
			return $this->EE->math->last_error;
		}
		
		return $this->EE->number->format($evaluation);
	}
	// --------------------------------
	//  Cart Empty Redirect
	// --------------------------------
	/**
	 * Redirects if cart is empty.
	 * Place on your view cart page.
	 *
	 * @access public
	 * @return void
	 * @since 1.0.0
	 * @author Rob Sanchez
	*/
	public function cart_empty_redirect()
	{
 		if ($this->EE->cartthrob->cart->is_empty())
		{
			$this->EE->template_helper->tag_redirect($this->EE->TMPL->fetch_param('return'));
		}
	}
	// END
	
	public function cart_form()
	{
		$this->EE->load->library(array('number', 'form_builder'));
		
		$data = $this->EE->cartthrob_variables->global_variables(TRUE);
		
		$data['items'] = array();
		
		foreach ($this->EE->cartthrob->cart->items() as $row_id => $item)
		{
			$data['items'][$row_id] = $item->data();
			$data['items'][$row_id]['entry_id'] = $item->product_id();
			
			$row['item_price:numeric'] = 
			$row['price:numeric'] = 
			$row['item_price_numeric'] = 
			$row['price_numeric'] = 
				$item->price(); 
				
			$row['item_price_plus_tax:numeric'] =
			$row['price_numeric:plus_tax'] = 
			$row['price_plus_tax:numeric'] = 
			$row['item_price_plus_tax_numeric'] = 
			$row['price_plus_tax_numeric'] = 
				$item->taxed_price();
			
			$row['item_price'] = 
			$row['price'] = 
				$this->EE->number->format( $item->price() );
			
			$row['item_price_plus_tax'] = 
			$row['price:plus_tax'] =
			$row['item_price:plus_tax'] =  
			$row['price_plus_tax'] = 
				$this->EE->number->format( $item->taxed_price() );
			
			foreach ($this->EE->cartthrob_variables->item_option_vars($item->product_id(), $row_id) as $key => $value)
			{
				$data['items'][$row_id][$key] = $value;
			}
		}
		
		$this->EE->load->library('data_filter');
		
		$order_by = ($this->EE->TMPL->fetch_param('order_by')) ?  $this->EE->TMPL->fetch_param('order_by') : $this->EE->TMPL->fetch_param('orderby');
		
		$this->EE->data_filter->sort($data['items'], $order_by, $this->EE->TMPL->fetch_param('sort'));
		$this->EE->data_filter->limit($data['items'], $this->EE->TMPL->fetch_param('limit'), $this->EE->TMPL->fetch_param('offset'));
		
		$this->EE->form_builder->initialize(array(
			'form_data' => array(
				'action',
				'secure_return',
				'return',
				'language',
			),
			'encoded_form_data' => array(
			),
			'encoded_numbers' => array(
			),
			'encoded_bools' => array(
			),
			'classname' => 'Cartthrob',
			'method' => 'cart_action',
			'params' => $this->EE->TMPL->tagparams,
			'content' => $this->EE->template_helper->parse_variables_row($data),
		));
		
		return $this->EE->form_builder->form();
	}
	
	/**
	 * cart_entry_ids
	 * 
	 * returns a pipe delimited list of entry ids
	 *
	 * @return string
	 * @author Chris Newton
	 * @since 1.0
	 */
	public function cart_entry_ids()
	{	
		return implode('|', $this->EE->cartthrob->cart->product_ids());
 	}
	/**
	 * cart_info
	 *
	 * Template tag that outputs generic cart info & conditionals related to totals and shipping
	 * 
	 * @return string
	 * @since 1.0
	 * @author Chris Newton
	 */
	public function cart_info()
	{
		$this->EE->TMPL->tagdata = $this->EE->functions->prep_conditionals($this->EE->TMPL->tagdata, $this->EE->cartthrob->cart->info(FALSE));
		
 		return $this->EE->template_helper->parse_variables_row($this->EE->cartthrob_variables->global_variables());
 	}
	/**
	 * cart_items_info
	 * 
	 * Prints out cart contents
	 *
	 * @access public
	 * @return string
	 * @since 1.0.
	 * @author Rob Sanchez
	*/
	public function cart_items_info()
	{
		$this->EE->load->library(array('number', 'typography'));
		$this->EE->load->helper('array');
		$data = array();
		
		$global_vars = $this->EE->cartthrob_variables->global_variables();
		
		//@TODO add ability to limit to certain channels too. 
		$entry_ids = ($this->EE->TMPL->fetch_param('entry_id')) ? explode('|', $this->EE->TMPL->fetch_param('entry_id')) : FALSE;
		$row_ids = ($this->EE->TMPL->fetch_param('row_id') !== FALSE) ? explode('|', $this->EE->TMPL->fetch_param('row_id')) : FALSE;
		$plan_ids = ($this->EE->TMPL->fetch_param('plan_id') !== FALSE) ? explode('|', $this->EE->TMPL->fetch_param('plan_id')) : FALSE;
		
		$this->EE->load->library('api');
		$this->EE->api->instantiate('channel_fields');
		
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob');
		
		$this->EE->load->model(array('product_model', 'cartthrob_field_model', 'subscription_model'));
		
		$categories = (strpos($this->EE->TMPL->tagdata, '{categories') !== FALSE) ? $this->EE->product_model->get_categories() : FALSE;
		
		if ($categories)
		{
			$this->EE->cartthrob_entries_model->load_categories_by_entry_id($this->EE->cartthrob->cart->product_ids());
		}
		
		if (preg_match_all('#{packages?(.*?)}(.*?){/packages?}#s', $this->EE->TMPL->tagdata, $matches))
		{
			$package_tagdata = array();
			
			foreach ($matches[0] as $i => $full_match)
			{
				$package_tagdata[substr($full_match, 1, -1)] = $matches[2][$i];
			}
		}
		
		foreach ($this->EE->cartthrob->cart->items() as $row_id => $item)
		{
			if (($entry_ids && ! in_array($item->product_id(), $entry_ids)) 
				|| ($row_ids && ! in_array($row_id, $row_ids))
				|| ($plan_ids && ! in_array($item->meta("plan_id"), $plan_ids))
			)
			{
				continue;
			}
			
			$row = $this->EE->cartthrob_variables->item_vars($item, $global_vars);
			
			if (isset($package_tagdata))
			{
				foreach ($package_tagdata as $full_match => $_package_tagdata)
				{
					$row[$full_match] = '';
					
					foreach ($this->EE->cartthrob_variables->sub_item_vars($item, $global_vars, $_package_tagdata) as $sub_row)
					{
						$row[$full_match] .= $this->EE->TMPL->parse_variables($_package_tagdata, array($sub_row));
					}
				}
			}
			
			$row['is_subscription'] = ($item->meta("subscription")) ? 1 : 0;
			
			$keys = $this->EE->subscription_model->option_keys(); 

			foreach ($keys as $v)
			{
				if ($item->meta('subscription_options'))
				{
					$row['subscription_'.$v] = element($v, $item->meta('subscription_options') ); 
				}
				else
				{
					$row['subscription_'.$v]  = NULL; 
				}
			}
			

			$row['is_package'] = ($item->sub_items()) ? 1 : 0;
			$row['item_options'] = ($item->item_options()) ? count($item->item_options()) : 0;

 			$data[] = $row;
		}
		
		
		//alternate for nested tag
		if (preg_match('/'.LD.'if no_items'.RD.'(.*?)'.LD.'\/if'.RD.'/s', $this->EE->TMPL->tagdata, $match))
		{
			$this->EE->TMPL->tagdata = str_replace($match[0], '', $this->EE->TMPL->tagdata);
			
			$this->EE->TMPL->no_results = $match[1];
		}
		
		if ( ! $data)
		{
			return $this->EE->TMPL->no_results();
		}
		
 		$this->EE->load->library('data_filter');
		
		$order_by = ($this->EE->TMPL->fetch_param('order_by')) ? $this->EE->TMPL->fetch_param('order_by') : $this->EE->TMPL->fetch_param('orderby');
		
		$this->EE->data_filter->sort($data, $order_by, $this->EE->TMPL->fetch_param('sort'));
		$this->EE->data_filter->limit($data, $this->EE->TMPL->fetch_param('limit'), $this->EE->TMPL->fetch_param('offset'));

		$this->EE->template_helper->apply_search_filters($data);
		
		if ( ! $data)
		{
			return $this->EE->TMPL->no_results();
		}
		
		$count = 1;
		//$total_results = $this->EE->cartthrob->cart->count();
		$total_results = count($data);
		
		foreach ($data as &$row)
		{
			$row['cart_count'] = $count;
			$row['cart_total_results'] = $total_results;
			$row['first_row'] = ($count === 1) ? TRUE : FALSE;
			$row['last_row'] = ($count === $total_results) ? TRUE : FALSE;
			
			$count++;
		}
		
 		$return_data = $this->EE->template_helper->parse_variables($data);
		
		return $return_data;
	}

	public function cart_discount()
	{
		$this->EE->load->library('number');
		
		if (isset($this->EE->TMPL->tagparts[2]) && $this->EE->TMPL->tagparts[2] === 'numeric')
		{
			return $this->EE->cartthrob->cart->discount();
		}
		
		if (isset($this->EE->TMPL->tagparts[2]) && $this->EE->TMPL->tagparts[2] === 'minus_tax')
		{
			// we are ADDING the tax amount, because the discount will INCREASE due to the offset of the reduced tax applied to everything else based on this discount. 
			// technically the discount is a negative amount... flip your brain... we're representing the total negative amount applied to the cart. 
			$discount = $this->EE->cartthrob->cart->discount() + $this->EE->cartthrob->cart->discount_tax(); 

			if (isset($this->EE->TMPL->tagparts[3]) && $this->EE->TMPL->tagparts[3] === 'numeric')
			{
				return $discount;
			}
			return $this->EE->number->format($discount);
 		}
		
 		return $this->EE->number->format($this->EE->cartthrob->cart->discount());
	}
	
	
	
	/**
	 * Returns discount percentage of total
	 * Uses number format params.
	 *
	 * @access public
	 * @param int $TMPL->fetch_param('decimals')
	 * @param string $TMPL->fetch_param('dec_point')
	 * @param string $TMPL->fetch_param('thousands_sep')
	 * @param string $TMPL->fetch_param('prefix')
	 * @return string
	 * @since 1.0.0
	 * @author Chris Newton
	 */
	public function cart_discount_percent_of_total()
	{
		return $this->EE->cartthrob->cart->discount() / $this->EE->cartthrob->cart->total() * 100;
	}

	/**
	 * Returns discount percentage of subtotal
	 * Uses number format params.
	 *
	 * @access public
	 * @param int $TMPL->fetch_param('decimals')
	 * @param string $TMPL->fetch_param('dec_point')
	 * @param string $TMPL->fetch_param('thousands_sep')
	 * @param string $TMPL->fetch_param('prefix')
	 * @return string
	 * @since 1.0.0
	 * @author Chris Newton
	 */
	public function cart_discount_percent_of_subtotal()
	{
		return $this->EE->cartthrob->cart->discount() / $this->EE->cartthrob->cart->subtotal() * 100;
	}

	public function cart_subtotal()
	{
		$this->EE->load->library('number');
		
		if (isset($this->EE->TMPL->tagparts[2]) && $this->EE->TMPL->tagparts[2] === 'numeric')
		{
			return $this->EE->cartthrob->cart->subtotal();
		}
		if (isset($this->EE->TMPL->tagparts[2]) && $this->EE->TMPL->tagparts[2] === 'plus_tax')
		{
			$subtotal_plus_tax = $this->EE->cartthrob->cart->subtotal_with_tax();

			if (isset($this->EE->TMPL->tagparts[3]) && $this->EE->TMPL->tagparts[3] === 'numeric')
			{
				return $subtotal_plus_tax;
			}
			return $this->EE->number->format($subtotal_plus_tax);
 		}
		
		return $this->EE->number->format($this->EE->cartthrob->cart->subtotal());
	}

	public function cart_subtotal_plus_tax()
	{
		$subtotal_plus_tax = $this->EE->cartthrob->cart->subtotal_with_tax();
		
		if (isset($this->EE->TMPL->tagparts[2]) && $this->EE->TMPL->tagparts[2] === 'numeric')
		{
			return $subtotal_plus_tax;
		}
		
		$this->EE->load->library('number');
		return $this->EE->number->format($subtotal_plus_tax);
	}
	
	public function cart_subtotal_minus_discount()
	{
		$this->EE->load->library('number');

		if (isset($this->EE->TMPL->tagparts[2]) && $this->EE->TMPL->tagparts[2] === 'numeric')
		{
			return $this->EE->cartthrob->cart->subtotal()- $this->EE->cartthrob->cart->discount();
		}
		if (isset($this->EE->TMPL->tagparts[2]) && $this->EE->TMPL->tagparts[2] === 'plus_tax')
		{
			$subtotal_plus_tax = $this->EE->cartthrob->cart->subtotal_with_tax();

			if (isset($this->EE->TMPL->tagparts[3]) && $this->EE->TMPL->tagparts[3] === 'numeric')
			{
				return $subtotal_plus_tax- $this->EE->cartthrob->cart->discount() ;
			}
			return $this->EE->number->format($subtotal_plus_tax - $this->EE->cartthrob->cart->discount() );
 		}

		return $this->EE->number->format($this->EE->cartthrob->cart->subtotal() - $this->EE->cartthrob->cart->discount());
	}
	
	public function cart_subtotal_plus_shipping()
	{
		$this->EE->load->library('number');
		
		$subtotal_plus_shipping = $this->EE->cartthrob->cart->subtotal() + $this->EE->cartthrob->cart->shipping();
		
		if (isset($this->EE->TMPL->tagparts[2]) && $this->EE->TMPL->tagparts[2] === 'numeric')
		{
			return $subtotal_plus_shipping;
		}
		
		return $this->EE->number->format($subtotal_plus_shipping); 
	}

	public function cart_shipping()
	{
		$this->EE->load->library('number');
		
		if (isset($this->EE->TMPL->tagparts[2]) && $this->EE->TMPL->tagparts[2] === 'numeric')
		{
			return $this->EE->cartthrob->cart->shipping();
		}
		if (isset($this->EE->TMPL->tagparts[2]) && $this->EE->TMPL->tagparts[2] === 'plus_tax')
		{
			$shipping_plus_tax = $this->EE->cartthrob->cart->shipping_plus_tax();

			if (isset($this->EE->TMPL->tagparts[3]) && $this->EE->TMPL->tagparts[3] === 'numeric')
			{
				return $shipping_plus_tax;
			}

			return $this->EE->number->format($shipping_plus_tax);
		}
		return $this->EE->number->format($this->EE->cartthrob->cart->shipping());
	}
	
	public function cart_shipping_plus_tax()
	{
		$shipping_plus_tax = $this->EE->cartthrob->cart->shipping_plus_tax();
		
		if (isset($this->EE->TMPL->tagparts[2]) && $this->EE->TMPL->tagparts[2] === 'numeric')
		{
			return $shipping_plus_tax;
		}
		
		$this->EE->load->library(array('number'));
		return $this->EE->number->format($shipping_plus_tax);
	}
	
	public function cart_tax()
	{
		$this->EE->load->library('number');
		
		if (isset($this->EE->TMPL->tagparts[2]) && $this->EE->TMPL->tagparts[2] === 'numeric')
		{
			return $this->EE->cartthrob->cart->tax();
		}
		
		return $this->EE->number->format($this->EE->cartthrob->cart->tax());
	}

	public function cart_tax_rate()
	{
		return $this->EE->cartthrob->store->tax_rate();
	}

	/**
	 * Returns total price of all items in cart
	 * The formula is subtotal + tax + shipping - discount
	 * Uses number format params.
	 *
	 * @access public
	 * @param int $TMPL->fetch_param('decimals')
	 * @param string $TMPL->fetch_param('dec_point')
	 * @param string $TMPL->fetch_param('thousands_sep')
	 * @param string $TMPL->fetch_param('prefix')
	 * @return string
	 * @since 1.0.0
	 * @author Rob Sanchez
	 */
	public function cart_total()
	{
		$this->EE->load->library('number');
		
		if (isset($this->EE->TMPL->tagparts[2]) && $this->EE->TMPL->tagparts[2] === 'numeric')
		{
			return $this->EE->cartthrob->cart->total();
		}
		
		return $this->EE->number->format($this->EE->cartthrob->cart->total());
	}
	
	public function cart_weight()
	{
		return $this->EE->cartthrob->cart->weight();
	}

	public function change_quantity()
	{
		if ($item = $this->EE->cartthrob->cart->item($this->EE->TMPL->fetch_param('row_id')))
		{
			$item->set_quantity($this->EE->TMPL->fetch_param('quantity'));
		}
		
		$this->EE->cartthrob->cart->save();
		
		$this->EE->template_helper->tag_redirect($this->EE->TMPL->fetch_param('return'));
	}
	
	public function check_cc_number_errors()
	{
		$data = array(
			'errors' => '',
			'valid' => TRUE
		);
		
		if ( ! $this->EE->TMPL->fetch_param('credit_card_number'))
		{
			$data['errors'] = $this->EE->lang->line('validate_cc_number_missing');// return lang missing number. 
		}
		
		$response = validate_credit_card($this->EE->TMPL->fetch_param('credit_card_number'), $this->EE->TMPL->fetch_param('card_type')); 
		
		if ( ! $response['valid'])
		{
			$data['errors'] = $response['error_code'];
			
			$data['valid'] = FALSE;
			
			switch ($response['error_code'])
			{
				case "1": 
					$data['errors'] = $this->EE->lang->line('validate_cc_card_type_unknown');
				break;
				case "2":
					$data['errors'] = $this->EE->lang->line('validate_cc_card_type_mismatch');
				break;
				case "3": 
					$data['errors'] = $this->EE->lang->line('validate_cc_invalid_card_number');
				break;
				case "4":
					$data['errors'] = $this->EE->lang->line('validate_cc_incorrect_card_length');
				break;
				default: 
					$data['errors'] = $this->EE->lang->line('validate_cc_card_type_unknown');
			}
			
		}
		
		return $this->EE->template_helper->parse_variables_row($data);
 	}
	//END
 

	public function checkout_form()
	{
		if ($this->EE->session->userdata('member_id') == 0)
		{
			$this->EE->template_helper->tag_redirect($this->EE->TMPL->fetch_param('logged_out_redirect'));
		}

		if ($this->EE->cartthrob->cart->is_empty())
		{
			$this->EE->template_helper->tag_redirect($this->EE->TMPL->fetch_param('cart_empty_redirect'));
		}
		
		if (bool_string($this->EE->TMPL->fetch_param('live_rates')))
		{
			$shipping_content = $this->require_shipping_update(); 
			if ($shipping_content);
			{
				return $shipping_content;
			} 
		}
			
		if ( ! $this->EE->TMPL->fetch_param('id'))
		{
			$this->EE->TMPL->tagparams['id'] = 'checkout_form';
		}
		
		$this->EE->load->library('api/api_cartthrob_payment_gateways');
		
		if ($this->EE->cartthrob->store->config('allow_gateway_selection'))
		{
			if ($this->EE->TMPL->fetch_param('gateway'))
			{
				$this->EE->api_cartthrob_payment_gateways->set_gateway($this->EE->TMPL->fetch_param('gateway'));
			}
		}
		else
		{
			unset($this->EE->TMPL->tagparams['gateway']);
		}
		
		if (strpos($this->EE->TMPL->tagdata, '{gateway_fields}') !== FALSE)
		{
			$this->EE->TMPL->tagdata = str_replace('{gateway_fields}', $this->EE->api_cartthrob_payment_gateways->gateway_fields(), $this->EE->TMPL->tagdata);
		}
		
		if (isset($this->EE->TMPL->tagparams['required']) && strncmp($this->EE->TMPL->tagparams['required'], 'not ', 4) === 0)
		{
			$this->EE->TMPL->tagparams['not_required'] = substr($this->EE->TMPL->tagparams['required'], 4);
			
			unset($this->EE->TMPL->tagparams['required']);
		}
		
		$this->EE->load->library('form_builder');
		
		$this->EE->load->model('subscription_model');

		$this->EE->cartthrob_variables->add_encoded_option_vars($data);
		
		$this->EE->form_builder->initialize(array(
			'captcha' => (bool) ( ! $this->EE->session->userdata('member_id') && $this->EE->cartthrob->store->config('checkout_form_captcha')),
			'form_data' => array(
				'action',
				'secure_return',
				'return',
				'language',
				'authorized_redirect',
				'failed_redirect',
				'declined_redirect',
				'processing_redirect',
				'create_user',
				'member_id',
				'order_id',
			),
			'encoded_form_data' => array_merge(
				array(
					'file' 								=> 'FI',
					'not_required' 							=> 'NRQ',
					'gateway' 							=> 'gateway',
					'permissions'						=> 'PER',
				),
				$this->EE->subscription_model->encoded_form_data()
			),
			'encoded_numbers' => array_merge(
				array(
					'price' => 'PR',
					'shipping' => 'SHP',
					'tax' => 'TX',
					'group_id' => 'GI',
					'expiration_date' => 'EXP',
					'vault_id' => 'vault_id',
				),
				$this->EE->subscription_model->encoded_numbers()
			),
			'encoded_bools' => array_merge(
				array(
					'allow_user_price' 					=> 'AUP',
					'allow_user_shipping' 					=> 'AUS',
					'on_the_fly' 						=> 'OTF',
					'license_number' 					=> 'LIC',
					'force_vault'						=> 'VLT',
					'force_processing'					=> 'FPR',
				),
				$this->EE->subscription_model->encoded_bools()
			),
			'classname' => 'Cartthrob',
			'method' => 'checkout_action',
			'params' => $this->EE->TMPL->tagparams,
			'action' => $this->EE->cartthrob->store->config('payment_system_url'),
		));
		
		
		// setting the subscription id. if a subscription id is set the contents of the cart are removed, and only the subscription itself is updated. 
		if ($this->EE->TMPL->fetch_param('sub_id'))
		{
			$this->EE->form_builder->set_hidden('sub_id', $this->EE->TMPL->fetch_param('sub_id'));
		}
		
		if (bool_string($this->EE->TMPL->fetch_param('no_tax')))
		{
			$this->EE->form_builder->set_encoded_bools("no_tax", 'NTX')->set_params($this->EE->TMPL->tagparams);
		}
		elseif (bool_string($this->EE->TMPL->fetch_param('tax_exempt')))
		{
			$this->EE->form_builder->set_encoded_bools("tax_exempt", 'NTX')->set_params($this->EE->TMPL->tagparams);
		}
		
		if (bool_string($this->EE->TMPL->fetch_param('no_shipping')))
		{
			$this->EE->form_builder->set_encoded_bools("no_shipping", 'NSH')->set_params($this->EE->TMPL->tagparams);
		}
		elseif (bool_string($this->EE->TMPL->fetch_param('shipping_exempt')))
		{
			$this->EE->form_builder->set_encoded_bools("shipping_exempt", 'NSH')->set_params($this->EE->TMPL->tagparams);
		}
		
		
		//do this after initialize so captch vars are set
		$variables = $this->EE->cartthrob_variables->global_variables(TRUE);
		
		$this->EE->form_builder->set_content($this->EE->template_helper->parse_variables_row($variables));
		
		if ($this->EE->TMPL->fetch_param('order_id') || $this->EE->TMPL->fetch_param('member_id'))
		{
			$this->EE->form_builder->set_hidden('save_member_data', 0);
		}
		
		return $this->EE->form_builder->form().$this->EE->api_cartthrob_payment_gateways->gateway('form_extra');
	}

	/**
	 * Empties the cart
	 *
	 * @access public
	 * @return void
	 * @since 1.0.0
	 * @author Rob Sanchez
	 */
	public function clear_cart()
	{
		$this->EE->cartthrob->cart->clear()
			   ->clear_coupon_codes()
			   ->clear_shipping_info()
			   ->clear_totals();

		if (bool_string($this->EE->TMPL->fetch_param('clear_customer_info')))
		{
			$this->EE->cartthrob->cart->clear_customer_info()
				   ->clear_custom_data();
		}
		
		$this->EE->cartthrob->cart->save();
		
		$this->EE->template_helper->tag_redirect($this->EE->TMPL->fetch_param('return'));
	}

	public function clear_coupon_codes()
	{
		$this->EE->cartthrob->cart->clear_coupon_codes()->save();
		
		$this->EE->template_helper->tag_redirect($this->EE->TMPL->fetch_param('return'));
	}

	public function convert_country_code()
	{
		$this->EE->load->library('locales');
		
		$country_code = $this->EE->TMPL->fetch_param('country_code'); 
		$code = $this->EE->locales->alpha3_country_code($country_code);
		
		$countries = $this->EE->locales->all_countries();
		
		return (isset($countries[$code])) ? $countries[$code] : $country_code;
	}

	public function countries()
	{
		$this->EE->load->library('locales');
		
		$data = array();
		
		foreach ($this->EE->locales->countries(bool_string($this->EE->TMPL->fetch_param('alpha2'))) as $abbrev => $country)
		{
			$data[] = array(
				'country_code' => $abbrev,
				'countries:country_code' => $abbrev,
				'country' => $country,
				'countries:country' => $country
			);
		}
		
		return $this->EE->template_helper->parse_variables($data);
	}

	public function country_select()
	{
		$this->EE->load->library('locales');
		$this->EE->load->helper('form');
		
		$name = ($this->EE->TMPL->fetch_param('name')) ? $this->EE->TMPL->fetch_param('name') : 'country';
		
		$countries = $this->EE->locales->countries(
			bool_string($this->EE->TMPL->fetch_param('alpha2')),
			bool_string($this->EE->TMPL->fetch_param('country_codes'), TRUE)
		);
		
		if (bool_string($this->EE->TMPL->fetch_param('add_blank')))
		{
			$blank = array('' => '---'); 
			$countries = $blank + $countries;
		}
		
		$attrs = array();
		
		if ($this->EE->TMPL->fetch_param('id'))
		{
			$attrs['id'] = $this->EE->TMPL->fetch_param('id');
		}
		
		if ($this->EE->TMPL->fetch_param('class'))
		{
			$attrs['class'] = $this->EE->TMPL->fetch_param('class');
		}
		
		if ($this->EE->TMPL->fetch_param('onchange'))
		{
			$attrs['onchange'] = $this->EE->TMPL->fetch_param('onchange');
		}
		
		$extra = '';
		
		if ($attrs)
		{
			$extra .= _attributes_to_string($attrs);
		}
		
		if ($this->EE->TMPL->fetch_param('extra'))
		{
			if (substr($this->EE->TMPL->fetch_param('extra'), 0, 1) !== ' ')
			{
				$extra .= ' ';
			}
			
			$extra .= $this->EE->TMPL->fetch_param('extra');
		}
		
		$selected = ($this->EE->TMPL->fetch_param('selected')) ? $this->EE->TMPL->fetch_param('selected') : $this->EE->TMPL->fetch_param('default');
		
		return form_dropdown(
			$name,
			$countries,
			$selected,
			$extra
		);
	}

	public function coupon_count()
	{
		return count($this->EE->cartthrob->cart->coupon_codes());
	}
	
	public function coupon_info()
	{
		$this->EE->load->library('number');
		
		if ( ! $coupon_codes = $this->EE->cartthrob->cart->coupon_codes())
		{
			return $this->EE->TMPL->no_results();
		}
		
		$this->EE->load->model('coupon_code_model');
		
		foreach ($coupon_codes as $coupon_code)
		{
			$row = array_key_prefix($this->EE->coupon_code_model->get_coupon_code_data($coupon_code), 'coupon_');
 			$row['coupon_code'] = $coupon_code;
			
			$entry_id = $row['coupon_metadata']['entry_id'];
 
			$discount_price = $this->EE->cartthrob->cart->discount(TRUE, $entry_id, $coupon_code); 
			
 			$row['discount_amount'] = $row['coupon_amount'] = $row['voucher_amount'] = $this->EE->number->format($discount_price); 
			
			unset($row['coupon_metadata']);
			
			$variables[] = array_merge($this->EE->cartthrob_entries_model->entry_vars($entry_id), $row);
		}
		
		return $this->EE->template_helper->parse_variables($variables);
	}
	
	public function discount_info()
	{
		$this->EE->load->model('discount_model');
		$this->EE->load->library('number');

		if ( ! $discounts = $this->EE->discount_model->get_valid_discounts())
		{
			return $this->EE->TMPL->no_results();
		}
		
		foreach ($discounts as $discount)
		{
			$row = array(); 
			
			foreach ($discount as $key => $value)
			{
				if (strpos($key, 'discount_') !== 0)
				{
					$key = 'discount_'.$key;
				}
				
				$row[$key] = $value;
			}

			$discount_price = $this->EE->cartthrob->cart->discount(TRUE, $discount['entry_id']); 
			$row['discount_amount'] = $this->EE->number->format($discount_price); 
			
			$row = array_merge($this->EE->cartthrob_entries_model->entry_vars($row['discount_entry_id']), $row);
			
			$variables[] = $row;
		}
		return $this->EE->template_helper->parse_variables($variables);
	}

	public function customer_info()
	{
		return $this->EE->template_helper->parse_variables_row($this->EE->cartthrob_variables->global_variables());
	}
	// --------------------------------
	//  Debug Info
	// --------------------------------
	/**
	 * debug_info
	 * Outputs all data related to CartThrob
	 *
	 * @access public
	 * @since 1.0.0
	 * @return string
	 * @author Chris Newton, Rob Sanchez
	 */
	public function debug_info()
	{
		if (! $this->EE->cartthrob->store->config('show_debug'))
		{
			return; 
		}
		elseif ($this->EE->cartthrob->store->config('show_debug') == "super_admins")
		{
			if ($this->EE->session->userdata('group_id') !=="1")
			{
				return; 
			}
		}
		$debug['session'] = $this->EE->cartthrob_session->to_array();
		
		$debug = array_merge($debug, $this->EE->cartthrob->cart->to_array());

	 	uksort($debug, 'strnatcasecmp');
		
		if (bool_string($this->EE->TMPL->fetch_param('console')))
		{
			$this->EE->load->library('javascript');
			
			return '<script type="text/javascript">(function(data) { if (typeof(window.console) == "undefined") return; window.console.log(data) })('.json_encode($debug).')</script>';
		}
		
		$output = '<fieldset id="ct_debug_info" style="border:1px solid #000;padding:6px 10px 10px 10px;margin:20px 0 20px 0;background-color:#ffbc9f ">';
		$output .= '<legend style="color:#000;">&nbsp;&nbsp;'.$this->EE->lang->line('cartthrob_profiler_data').'  </legend>';
		
		$output .= $this->format_debug($debug); 

		$output .= '</table>';
		$output .= "</fieldset>";
		
		return $output;
	}
	/**
	 * format_debug
	 * Formats debug arrays into tables
	 *
	 * @access private
	 * @since 2.1
	 * @return string
	 * @author Chris Newton
	 */
	private function format_debug($data, $parent_key = NULL)
	{
		$output = ""; 
		if (is_array($data))
		{
		 	uksort($data, 'strnatcasecmp'); 
			$output = "<table style='width:100%;'>"; 
			foreach ($data as $key => $value)
			{
				$content = ""; 
				$output_key = $key; 
				if (is_numeric($key))
				{
					$output_key = "Row ID: ". $key;
				}
				if (is_array($value))
				{
					$content.= $this->format_debug($value, $key);
				}
				else
				{
					if ($key == "inventory" && $value ==PHP_INT_MAX)
					{
						$value = "unlimited"; 
					}
					if ($key == "price")
					{
						if ($value == "" && $parent_key !==NULL)
						{
							$item = $this->EE->cartthrob->cart->item($parent_key); 
							
							if ( $item )
							{
								$this->EE->load->model('cartthrob_field_model'); 
								$field_id = $this->EE->cartthrob->store->config('product_channel_fields', $item->meta('channel_id'), $key); 

								$field_name = "channel entry"; 
								if ($this->EE->cartthrob->store->config('product_channel_fields', $item->meta('channel_id'), "global_price"))
								{
									$field_name = "globally set"; 
								}
								elseif ($field_id)
								{
									$field_name = $this->EE->cartthrob_field_model->get_field_name($field_id)." field"; 
								}

								$value = $item->price(). " (uses ".$field_name." price)";
							}
						}
						else
						{
							$value = $value . " (uses customer price)";
						}
					}
					if ($key == "entry_id" && empty($value))
					{
						$value="(dynamic item)"; 
					}
					$content.= htmlspecialchars($value);
				}
				$output .= "<tr><td style='padding:5px; vertical-align: top;color:#900;background-color:#ddd;'>".$output_key."&nbsp;&nbsp;</td><td style='padding:5px; color:#000;background-color:#ddd;'>".$content."</td></tr>\n";
			}
			$output .= '</table>';
		}
		else
		{
			$output = htmlspecialchars($data); 
		}
		return $output; 
	}
	
	
	/**
	 * decrypt
	 * 
	 * Encrypts and returns a string. 
	 * @see Encrypt Class encode
	 * @access public
	 * @param string $TMPL->fetch_param('string') the data to be decrypted
	 * @param string $TMPL->fetch_param('key') the key used to encrypt the data
	 * @return string decrypted string
	 * @author Chris Newton
	 * @since 1.0.0
	 **/
	function decrypt()
	{
		$this->EE->load->library('encrypt');
		
		return xss_clean($this->EE->encrypt->decode(base64_decode(rawurldecode($this->EE->TMPL->fetch_param('string'))), $this->EE->TMPL->fetch_param('key'))); 
	}

	public function delete_from_cart()
	{
		if ($this->EE->extensions->active_hook('cartthrob_delete_from_cart_start') === TRUE)
		{
			$this->EE->extensions->call('cartthrob_delete_from_cart_start');
			if ($this->EE->extensions->end_script === TRUE) return;
		}
		
		if ($this->EE->TMPL->fetch_param('row_id') !== FALSE)
		{
			$this->EE->cartthrob->cart->remove_item($this->EE->TMPL->fetch_param('row_id'));
		}
		else if ($this->EE->TMPL->fetch_param('entry_id'))
		{
			$data = array('entry_id' => xss_clean($this->EE->TMPL->fetch_param('entry_id')));
		
			foreach ($this->EE->TMPL->tagparams as $key => $value)
			{
				if (preg_match('/^item_options?:(.*)$/', $key, $match))
				{
					$data['item_options'][$match[1]] = $value;
				}
			}
			
			if ($this->EE->input->post('item_options') && is_array($this->EE->input->post('item_options')))
			{
				$data['item_options'] = (isset($data['item_options'])) ? array_merge($data['item_options'], $this->EE->input->post('item_options', TRUE)) : $this->EE->input->post('item_options', TRUE);
			}
			
			if ($item = $this->EE->cartthrob->cart->find_item($data))
			{
				$item->remove();
			}
		}
		
		if ($this->EE->extensions->active_hook('cartthrob_delete_from_cart_end') === TRUE)
		{
			$this->EE->extensions->call('cartthrob_delete_from_cart_end');
			if ($this->EE->extensions->end_script === TRUE) return;
		}
		
		$this->EE->cartthrob->cart->save();
		
		$this->EE->template_helper->tag_redirect($this->EE->TMPL->fetch_param('return'));
	}
	
	public function delete_from_cart_form()
	{
		if ( ! $this->EE->session->userdata('member_id') && $this->EE->TMPL->fetch_param('logged_out_redirect'))
		{
			$this->EE->template_helper->tag_redirect($this->EE->TMPL->fetch_param('logged_out_redirect'));
		}
		
		$this->EE->load->library('form_builder');
		
		$data = $this->EE->cartthrob_variables->global_variables(TRUE);
		
		$this->EE->form_builder->initialize(array(
			'form_data' => array(
				'secure_return',
				'row_id',
				'return'
			),
			'classname' => 'Cartthrob',
			'method' => 'delete_from_cart_action',
			'params' => $this->EE->TMPL->tagparams,
			'content' => $this->EE->template_helper->parse_variables_row($data),
			//'secure_action' => bool_string()
		));
		
		return $this->EE->form_builder->form();
	}

	/**
	 * download_file
	 *
	 * This uses curl for URLs, or fopen for paths to download files. 
	 * 
	 * @param string $TMPL->fetch_param('file')
	 * @param string $TMPL->fetch_param('return')
	 * @access public
	 * @return void
	 * @since 1.0
	 * @param 
	 * @author Chris Newton
	 **/
	public function download_file()
	{
		$this->EE->load->library('encrypt');
		$this->EE->load->library('paths');
		
		if ($this->EE->TMPL->fetch_param('field') && $this->EE->TMPL->fetch_param('entry_id'))
		{
			$this->EE->load->model(array('cartthrob_field_model', 'cartthrob_entries_model', 'tools_model'));
			
			$entry = $this->EE->cartthrob_entries_model->entry($this->EE->TMPL->fetch_param('entry_id'));
			
			$this->EE->load->helper('array');
			
			if ($path = element($this->EE->TMPL->fetch_param('field'), $entry))
			{
				$this->EE->load->library('paths');
				
				$path = $this->EE->paths->parse_file_server_paths($path);
				
				$this->EE->TMPL->tagparams['file'] = $path;
			}
		}
		
		if ($this->EE->TMPL->fetch_param('member_id') !== FALSE)
		{
			if ( ! $this->EE->TMPL->fetch_param('member_id'))
			{
				return show_error( $this->EE->lang->line('download_file_not_authorized'));
				
 			}
			
			if (bool_string($this->EE->TMPL->fetch_param('encrypted')))
			{
				if (xss_clean($this->EE->encrypt->decode(base64_encode(rawurldecode($this->EE->TMPL->fetch_param('member_id'))))) != $this->EE->session->userdata('member_id'))
				{
					return show_error($this->EE->lang->line('download_file_not_authorized'));
				}
			}
			else 
			{
				if ($this->EE->TMPL->fetch_param('member_id') != $this->EE->session->userdata['member_id'])
				{
					return show_error($this->EE->lang->line('download_file_not_authorized'));
				}
			}
		}
		if ( ! $this->EE->TMPL->fetch_param('file'))
		{
			return show_error($this->EE->lang->line('download_url_not_specified'));
		}
		else
		{
			$post_url = $this->EE->TMPL->fetch_param('file');
		}
		
		if (bool_string($this->EE->TMPL->fetch_param('encrypted')))
		{
			$post_url = xss_clean($this->EE->encrypt->decode(base64_decode(rawurldecode($post_url))));
		}
		
		$this->EE->load->library('cartthrob_file');
		
		$this->EE->cartthrob_file->force_download($post_url);
		
		if ($this->EE->cartthrob_file->errors())
		{
			return show_error($this->EE->cartthrob_file->errors());
		}
	}
 	
	public function download_file_form()
	{
		if ($this->EE->TMPL->fetch_param('member_id'))
		{
			if (in_array($this->EE->TMPL->fetch_param('member_id'), array('CURRENT_USER', '{logged_in_member_id}', '{member_id}')))
			{
				$this->EE->TMPL->tagparams['member_id'] = $this->EE->session->userdata('member_id');
			}
			else
			{
				$this->EE->TMPL->tagparams['member_id'] = sanitize_number($this->EE->TMPL->fetch_param('member_id'));
			}
		}
 		
		if ($this->EE->TMPL->fetch_param('group_id'))
		{
			if (in_array($this->EE->TMPL->fetch_param('group_id'), array('{logged_in_group_id}', '{group_id}')))
			{
				$this->EE->TMPL->tagparams['group_id'] = $this->EE->session->userdata('group_id');
			}
			else
			{
				$this->EE->TMPL->tagparams['group_id'] = sanitize_number($this->EE->TMPL->fetch_param('group_id'));
			}
		}
		
		
		if ($this->EE->TMPL->fetch_param('field') && $this->EE->TMPL->fetch_param('entry_id'))
		{
			$this->EE->load->model(array('cartthrob_field_model', 'cartthrob_entries_model', 'tools_model'));
			
			$entry = $this->EE->cartthrob_entries_model->entry($this->EE->TMPL->fetch_param('entry_id'));
			
			$this->EE->load->helper('array');
			// @NOTE if the developer has assigned an entry id and a field, but there's nothing IN the field,  then the path doesn't get set, and no debug information is output, because path, below would be set to NULL
			if ($path = element($this->EE->TMPL->fetch_param('field'), $entry))
			{
				$this->EE->load->library('paths');
				
				$path = $this->EE->paths->parse_file_server_paths($path);
				
				$this->EE->TMPL->tagparams['file'] = $path;
			}
		}
		
		if (bool_string($this->EE->TMPL->fetch_param('debug')) && $this->EE->TMPL->fetch_param('file') )
		{
			$this->EE->load->library('cartthrob_file');
			$this->EE->TMPL->tagdata.= $this->EE->cartthrob_file->file_debug($this->EE->TMPL->fetch_param('file')); 
		}
		
		$this->EE->load->library('form_builder');

		$data = $this->EE->cartthrob_variables->global_variables(TRUE);
		
		if (in_array($this->EE->TMPL->fetch_param('member_id'), array('CURRENT_USER', '{member_id}', '{logged_in_member_id}')))
		{
			$this->EE->TMPL->tagparams['member_id'] = $this->EE->session->userdata('member_id');
		}

		if (in_array($this->EE->TMPL->fetch_param('group_id'), array('{group_id}', '{logged_in_group_id}')))
		{
			$this->EE->TMPL->tagparams['group_id'] = $this->EE->session->userdata('group_id');
		}

		if ($this->EE->TMPL->fetch_param('free_file'))
		{
			$this->EE->TMPL->tagparams['free_file'] = 'FI'.$this->EE->TMPL->fetch_param('free_file');
		}
		else
		{
			if ($this->EE->TMPL->fetch_param('file') && (! $this->EE->TMPL->fetch_param('member_id') && ! $this->EE->TMPL->fetch_param('group_id') ) )
			{
				$this->EE->TMPL->tagparams['free_file'] = 'FI'.$this->EE->TMPL->fetch_param('file');
			}
			elseif ($this->EE->TMPL->fetch_param('file'))
			{
				$this->EE->TMPL->tagparams['file'] = 'FP'.$this->EE->TMPL->fetch_param('file');
			}
 		}
		
		
		$this->EE->form_builder->initialize(array(
			'form_data' => array(
				'secure_return',
				'language'
			),
			'encoded_form_data' => array(
				'file' => 'FP',
				'free_file' => 'FI',
			),
			'encoded_numbers' => array(
				'member_id' => 'MI',
				'group_id'	=> 'GI'
			),
			'classname' => 'Cartthrob',
			'method' => 'download_file_action',
			'params' => $this->EE->TMPL->tagparams,
			'content' => $this->EE->template_helper->parse_variables_row($data),
		));
		
		return $this->EE->form_builder->form();
 	}
	
	public function duplicate_item()
	{
		$this->EE->cartthrob->cart->duplicate_item($this->EE->TMPL->fetch_param('row_id'));
		
		$this->EE->cartthrob->cart->save();
		
		$this->EE->template_helper->tag_redirect($this->EE->TMPL->fetch_param('return'));
	}
	
	/**
	 * encrypt
	 * 
	 * Encrypts and returns a string. 
	 * @see Encrypt Class encode
	 * @access public
	 * @param string $string | $TMPL->fetch_param('string') the data to be encrypted
	 * @param string $key | $TMPL->fetch_param('key') the text string key that will be used to encrypt the data
	 * @return string encrypted string
	 * @author Chris Newton
	 * @since 1.0.0
	 **/
	function encrypt()
	{
		$this->EE->load->library('encrypt');
		
		return rawurlencode(base64_encode($this->EE->encrypt->encode($this->EE->TMPL->fetch_param('string'), $this->EE->TMPL->fetch_param('key')))); 
	}

	//deprecated
	public function https_redirect()
	{
		$this->EE->load->helper('https');

		force_https($this->EE->TMPL->fetch_param('domain'), ($this->EE->config->item('send_headers') === 'y'));
		
		if (bool_string($this->EE->TMPL->fetch_param('secure_site_url')))
		{
			$this->EE->config->config['site_url'] = str_replace('http://', 'https://', $this->EE->config->item('site_url'));
		}
		
		return $this->EE->TMPL->tagdata;
	}
	
	/*
	public function gateway_info()
	{
		$this->EE->load->library('encrypt');
		
		$this->EE->load->library('api');
		
		$this->EE->load->library('api/api_cartthrob_payment_gateways');
		
		$gateways = array();
		
		if ($this->EE->TMPL->fetch_param('gateway'))
		{
			$gateways = explode('|', $this->EE->TMPL->fetch_param('gateway'));
		}
		else if ($this->EE->cartthrob->store->config('payment_gateway'))
		{
			$gateways[] = Cartthrob_core::get_class($this->EE->cartthrob->store->config('payment_gateway'));
		}
		
		if ( ! $gateways)
		{
			return '';
		}
		
		$data = array();
		
		foreach ($this->EE->api_cartthrob_payment_gateways->gateways() as $gateway_info)
		{
			//remove Cartthrob_ from classname
			$gateway = Cartthrob_core::get_class($gateway_info['classname']);
			
			if ( ! in_array($gateway, $gateways))
			{
				continue;
			}
			
			$row = array(
				'option_name' => lang($gateway_info['title']),
				'option_value' => $this->EE->encrypt->encode($gateway),
			);
			
			$data[] = $row;
		}
		
		return $this->EE->template_helper->parse_variables($data);
	}
	*/
	
	/**
	 * get_card_type
	 *
	 * @access public
	 * @param string $ccn | $this->EE->fetch_param('credit_card_number')
	 * @return string credit card type, ex. Amex, Visa, Mc, Discover
	 * @author Chris Newton
	 * @since 1.0.0
	 */
	public function get_card_type()
	{
		return card_type($this->EE->TMPL->fetch_param('credit_card_number')); 
	}

	public function get_cartthrob_logo()
	{
		$this->EE->load->helper(array('html', 'url'));
		
		return anchor(
			'http://livecart.com',
			img(array('src' => 'http://livecart.com/images/powered_by_logos/powered_by_livecart.png', 'alt' => $this->EE->lang->line('powered_by_title'))),
			array('title' => $this->EE->lang->line('powered_by_title'), 'onclick' => "javascript:window.open('http://livecart.com','livecart');return false;")
		);
	}
	/**
	 * Returns string of entry_id's separated by | for use in weblog:entries
	 *
	 * @access public
	 * @param $IN->GBL('price_min')
	 * @param $IN->GBL('price_max')
	 * @return string
	 */

	public function get_items_in_range()
	{
		$price_min = ($this->EE->TMPL->fetch_param('price_min') !== FALSE) ? xss_clean($this->EE->TMPL->fetch_param('price_min')) : $this->EE->input->get_post('price_min', TRUE);

		$price_max = ($this->EE->TMPL->fetch_param('price_max') !== FALSE) ? xss_clean($this->EE->TMPL->fetch_param('price_max')) : $this->EE->input->get_post('price_max', TRUE);

		if ( ! is_numeric($price_min))
		{
			$price_min = '';
		}
		if ( ! is_numeric($price_max))
		{
			$price_max = '';
		}

		if ($price_min == '' && $price_max == '')
		{
			return '';
		}
		
		$this->EE->load->model('product_model');
		
		$entry_ids = $this->EE->product_model->get_products_in_price_range($price_min, $price_max);

		if (count($entry_ids))
		{
			return implode('|', $entry_ids);
		}
		else
		{
			return NULL; 
		}	
	}
	
	// does not show content if shipping rates require update
	public function require_shipping_update()
	{
		// @TODO language
		$this->EE->load->library('api/api_cartthrob_shipping_plugins');
		$this->EE->load->library('template_helper'); 
		$this->EE->cartthrob->cart->shipping();
		
		if ($this->EE->TMPL->fetch_param('shipping_plugin'))
		{
			$this->EE->api_cartthrob_shipping_plugins->set_plugin($this->EE->TMPL->fetch_param('shipping_plugin')); 
		}
  		if ( ! $this->EE->api_cartthrob_shipping_plugins->shipping_options() )
		{
			$error = $this->EE->cartthrob->cart->custom_data('shipping_error'); 
		}
		else
		{
			$error = NULL; 
		}
		
		if ( $this->EE->cartthrob->cart->custom_data("shipping_requires_update") != NULL || $error != NULL)
		{
				if ($error)
				{
					$content = "<span class='error_message'>Shipping Error: ". $this->EE->cartthrob->cart->custom_data("shipping_error")."</span>"; 
				}
				else
				{
					$content = ""; 
				}
				// @TODO... this would be great if it was configurable
				$content .= '
				{exp:cartthrob:customer_info}
					{exp:cartthrob:update_cart_form return="" id="shipping_update_required"}
						<div>
							<h2>'.$this->EE->lang->line("shipping_update_required").'</h2>

							<fieldset class="shipping" id="shipping">
								<legend>Shipping</legend>
					
								<label for="shipping_address">Shipping Address
								<input type="text" value="{customer_shipping_address}" name="shipping_address" id="shipping_address" />
								</label>

								<label for="shipping_address2">Shipping Address (apartment/suite number)
								<input type="text" value="{customer_shipping_address2}" name="shipping_address2" id="shipping_address2" />
								</label>

								<label for="shipping_city">Shipping City
								<input type="text" value="{customer_shipping_city}" name="shipping_city" id="shipping_city" />
								</label>

								<label for="shipping_state">Shipping State
								{exp:cartthrob:state_select  id="shipping_state" name="shipping_state" selected="{customer_shipping_state}" add_blank="yes" }
								</label>

								<label for="shipping_zip">Shipping Zip/Postal Code
								<input type="text" value="{customer_shipping_zip}" name="shipping_zip" id="shipping_zip" />
								</label>
					 
								<label for="shipping_country">Shipping Country
								    {exp:cartthrob:country_select name="shipping_country_code" id="shipping_country" selected="{customer_shipping_country_code}"}
								</label>
					
								<label for="shipping_country">Shipping Option
									<select name="shipping_option">
									    {exp:cartthrob:get_shipping_options shipping_plugin="'.$this->EE->TMPL->fetch_param('shipping_plugin').'"}
									        <option value="{rate_short_name}" {selected}>{rate_title} - {price}</option>
									    {/exp:cartthrob:get_shipping_options}
									</select>
								</label>
					
							</fieldset>

							<input type="submit" value="Submit" name="submit"/> 
						</div>
					{/exp:cartthrob:update_cart_form}
				{/exp:cartthrob:customer_info}
				';
			return $this->EE->TMPL->tagdata = $content; 
		}
 		return NULL; 
	}
	/**
	 * Returns the options from the selected shipping plugin
	 *
	 * @access public
	 * @return string
	 * @since 1.0.0
	 * @author Rob Sanchez
	 */
	function get_shipping_options()
	{
		$this->EE->load->library('api/api_cartthrob_shipping_plugins');
		
		if ($this->EE->TMPL->fetch_param('shipping_plugin'))
		{
			$this->EE->api_cartthrob_shipping_plugins->set_plugin($this->EE->TMPL->fetch_param('shipping_plugin')); 
		}
		$options = $this->EE->api_cartthrob_shipping_plugins->shipping_options(); 
 
 		if ( ! $options  && ! trim($this->EE->TMPL->tagdata) )
		{
			if ($this->EE->cartthrob->cart->custom_data("shipping_error")) 
			{
	 			$option['price'] = ""; 
				$option['option_value'] = ""; 
				$option['option_name'] = ""; 
				$option['checked'] = '';
				$option['selected'] = '';
				$option['count'] = 0;
				$option['first_row'] = FALSE;
				$option['last_row'] = FALSE;
				$option['total_results'] = 0; 
				
				$options['error_message'] = $this->EE->cartthrob->cart->custom_data("shipping_error"); 
				
				// by the way, the next time someone asks why no_results doesn't work in this tag, it's because its' put in an update_cart_form or something... and basically that killed no results, because there's content there. So... you have to use the custom_data:shipping_error instead. 
	 			return $this->EE->template_helper->parse_variables_row($options);
			}
		}
	
		$selected = ($this->EE->cartthrob->cart->shipping_info('shipping_option')) ? $this->EE->cartthrob->cart->shipping_info('shipping_option') : $this->EE->api_cartthrob_shipping_plugins->default_shipping_option();
	
		if ( ! trim($this->EE->TMPL->tagdata))
		{
			if (!$options)
			{
				return NULL; 
			}
			$attrs = array();
			
			if ($this->EE->TMPL->fetch_param('id'))
			{
				$attrs['id'] = $this->EE->TMPL->fetch_param('id');
			}
			
			if ($this->EE->TMPL->fetch_param('class'))
			{
				$attrs['class'] = $this->EE->TMPL->fetch_param('class');
			}
			
			if ($this->EE->TMPL->fetch_param('onchange'))
			{
				$attrs['onchange'] = $this->EE->TMPL->fetch_param('onchange');
			}
			
			$extra = '';
			
			if ($attrs)
			{
				$extra .= _attributes_to_string($attrs);
			}
			
			if ($this->EE->TMPL->fetch_param('extra'))
			{
				if (substr($this->EE->TMPL->fetch_param('extra'), 0, 1) != ' ')
				{
					$extra .= ' ';
				}
				
				$extra .= $this->EE->TMPL->fetch_param('extra');
			}
			
			$select_options = array();
			
			foreach ($options as $row)
			{
				if (bool_string($this->EE->TMPL->fetch_param('hide_price')))
				{
					$select_options[$row['rate_short_name']] = $row['rate_title'];
				}
				else
				{
					$select_options[$row['rate_short_name']] = $row['rate_title']. " - ". $row['price'];
				}
			}
			
			if (!empty($select_options))
			{
				return form_dropdown(
				'shipping_option',
				$select_options,
				$selected,
				$extra
				);
			}
			return null; 
		}
		
		$this->EE->load->library('number');
		
		$new_options = array(); 
		foreach ($options as $key => $option)
		{
			if (empty($option['rate_short_name']) || empty($option['rate_title']))
			{
 				continue; 
			}
			!isset($count)?$count=1: $count++; 
 			$option['price'] = $this->EE->number->format($option['price']);
			$option['option_value'] = $option['rate_short_name'];
			$option['option_name'] = $option['rate_title'];
			$option['checked'] = ($option['rate_short_name'] == $selected) ? ' checked="checked"' : '';
			$option['selected'] = ($option['rate_short_name'] == $selected) ? ' selected="selected"' : '';
			$option['count'] = $count;
			$option['first_row'] = ($count === 1) ? TRUE : FALSE;
			$option['last_row'] = ($count === count($options)) ? TRUE : FALSE;
			$option['total_results'] = count($options);
			$option['error_message'] = NULL; 
			if ($this->EE->cartthrob->cart->custom_data("shipping_error")) 
			{
				$option['error_message'] = $this->EE->cartthrob->cart->custom_data("shipping_error"); 
			}
			$new_options[] = $option; 
		}

		return $this->EE->template_helper->parse_variables($new_options);
	}
	
	//@TODO this needs some serious work, it relies on a field specifically called product_shippable and also assumes it's value is "Yes"
	public function has_shippable_items()
	{
		foreach ($this->EE->cartthrob->cart->items() as $row_id => $item)
		{        
			$product = ($item->product_id()) ? $this->EE->product_model->get_product($item->product_id()) : FALSE;
			
			if ($product)
			{
				$data = $this->EE->cartthrob_entries_model->entry_vars($product);
				
				if ($data && isset($data['product_shippable']) && $data['product_shippable'] == 'Yes')
				{
					return TRUE;
				}
			}
		}
		
		return FALSE; 
	}
	
	
	public function in_array()
	{
		$needle = $this->EE->TMPL->fetch_param('needle');
		
		$haystack = ($this->EE->TMPL->fetch_param('haystack')) ? explode('|', $this->EE->TMPL->fetch_param('haystack')) : array();
		
		return (in_array($needle, $haystack)) ? '1' : 0;
	}
	
	/**
	 * Returns a conditional whether item has been purchased
	 *
	 * @access public
	 * @param string $TMPL->fetch_param('entry_id')
	 * @return string (int)
	 * @since 1.0.0
	 * @author Rob Sanchez, Chris Newton
	 */
	public function is_purchased_item()
	{
		// @TODO add in the ability to pull up items with a particular status
		// or recognize only completed itms. 
		
		$entry_id = $this->EE->TMPL->fetch_param('entry_id');
		
		$this->EE->load->model('purchased_items_model');
		
		$data['is_purchased_item'] = $this->EE->purchased_items_model->has_purchased($this->EE->TMPL->fetch_param('entry_id'));
		
		//single tag
		if ( ! $this->EE->TMPL->tagdata)
		{
			return (int) $data['is_purchased_item'];
		}
		
		return $this->EE->template_helper->parse_variables_row($data);
	}
	// END

	public function is_in_cart()
	{
		$data['is_in_cart'] = (int) ($this->EE->TMPL->fetch_param('entry_id') && $this->EE->cartthrob->cart->find_item(array('entry_id' => $this->EE->TMPL->fetch_param('entry_id'))));
		
		//single tag
		if ( ! $this->EE->TMPL->tagdata)
		{
			return $data['is_in_cart'];
		}
		
		$data['item_in_cart'] = $data['is_in_cart'];
		
		return $this->EE->template_helper->parse_variables_row($data);
	}

	public function cart_has_subscription()
	{
		foreach ($this->EE->cartthrob->cart->items() as $item)
		{
			if ($item->meta('subscription'))
			{
				return '1';
			}
		}

		return 0;
	}
	
	/**
	 * For use in a conditional, returns whether or not customer_info has been saved
	 *
	 * @access public
	 * @return string
	 * @since 1.0.0
	 * @author Rob Sanchez
	 */
	public function is_saved()
	{
		foreach ($this->EE->cartthrob->cart->customer_info() as $key => $value)
		{
			if ( ! empty($value))
			{
				return '1';
			}
		}
		
		return 0;
	}
	
	public function item_options()
	{
		$this->EE->load->helper('inflector');
		$this->EE->load->helper('array'); 
		
		$entry_id = $this->EE->TMPL->fetch_param('entry_id');
		
		$row_id = $this->EE->TMPL->fetch_param('row_id');
		
 		if ( ! $entry_id && $row_id === FALSE)
		{
			return $this->EE->TMPL->no_results();
		}
		
		$item = FALSE;
		$parent_id = FALSE; 
		$item_row_id = FALSE; 
		$option_value = FALSE; 
		$selected = FALSE;
		if (strpos($row_id, "configurator:") !== FALSE)
		{
			$item = $this->EE->cartthrob->cart->item($row_id);
		}
		elseif (strpos($row_id, ':') !== FALSE)
		{
			list($parent_id, $item_row_id) = explode(':', $row_id);
			if ($parent_item = $this->EE->cartthrob->cart->item($parent_id))
			{
				$item = $parent_item->sub_item($item_row_id);
			}
		}
		else
		{
			$item = $this->EE->cartthrob->cart->item($row_id);
		}
		if ($item && $item->product_id())
		{
			$entry_id = $item->product_id();
		}
		
		$price_modifiers = $this->EE->product_model->get_all_price_modifiers($entry_id);
		
		if ($row_id === FALSE)
		{
 			if ($this->EE->cartthrob->cart->meta("all_item_options"))
			{
				$all_keys = $this->EE->cartthrob->cart->meta("all_item_options"); 
				foreach ($price_modifiers as $key => $value)
				{
					if (in_array($key, $all_keys))
					{
						unset($price_modifiers[$key]); 
					}
				}
			}
		}
		//this will be an array of option field name => bool is dynamic
		$item_options = array();
		
		foreach (array_keys($price_modifiers) as $key)
		{
			// not dyanmic
			$item_options[$key] = FALSE;
		}
		
		if ($item)
		{
			$conf = $item->meta('configuration'); 
			
			if (is_array($item->item_options()))
			{
			foreach (array_keys($item->item_options()) as $key)
			{
				if ( ! isset($item_options[$key]))
				{
					// dynamic
					$item_options[$key] = TRUE;
				}
				
				if ($conf && is_array($conf))
				{
					foreach ($conf as $k => $v)
					{
						if (array_key_exists($k, $item_options))
						{
							unset($item_options[$k]); 
							continue; 
						}
					}
				}
			}
		}
		}
		
		$return_data = '';
		
		//if I leave {selected} in there, assign_variables output is wrong
		$this->EE->TMPL->tagdata = str_replace('{selected}', '8bdb34edd2d86eff7aa60be77e3002f5', $this->EE->TMPL->tagdata);
		$variables = $this->EE->functions->assign_variables($this->EE->TMPL->tagdata);
		$this->EE->TMPL->var_single = $variables['var_single'];
		$this->EE->TMPL->var_pair = $variables['var_pair'];
		$this->EE->TMPL->tagdata = str_replace('8bdb34edd2d86eff7aa60be77e3002f5', '{selected}', $this->EE->TMPL->tagdata);
		
		$tagdata = $this->EE->TMPL->tagdata;
		
		//only use one field instead of all fields
		$fields = $this->EE->TMPL->fetch_param('field') ? explode('|', $this->EE->TMPL->fetch_param('field')) : FALSE;
		
		$count = 0; 
		foreach ($item_options as $field_name => $dynamic)
		{
			if ($fields && ! in_array($field_name, $fields))
			{
				continue;
			}
			$count +=1; 
			$this->EE->TMPL->tagdata = $tagdata;
			
			//for early parsing
			$this->EE->TMPL->tagdata = $this->EE->TMPL->swap_var_single('option_field', $field_name, $this->EE->TMPL->tagdata);
			// add this line for dynamic options
			$option_value = ($item) ? $item->item_options($field_name) : '';
 			if ($item && $item->is_sub_item() && $entry = $this->EE->cartthrob_entries_model->entry($item->parent_item()->product_id()))
			{
	 			// already in the cart
				$item_row_id = $item->row_id(); 
				$option_value = ($item) ? $item->item_options($field_name) : '';
			}
			elseif ($parent_id &&  $entry = $this->EE->cartthrob_entries_model->entry($parent_id))
			{
				// just getting the entry
			}
			
			$vars = array(); 
			$vars['allow_selection'] = 1;
			
			if ($item_row_id !== FALSE)
			{
				if ($field_id = $this->EE->cartthrob_field_model->channel_has_fieldtype($entry['channel_id'], 'cartthrob_package', TRUE))
				{
					$this->EE->load->library('api');
					
					$this->EE->api->instantiate('channel_fields');
					
					if (empty($this->EE->api_channel_fields->field_types))
					{
						$this->EE->api_channel_fields->fetch_installed_fieldtypes();
					}
					
					if ($this->EE->api_channel_fields->setup_handler('cartthrob_package'))
					{
						if ( ! isset($this->EE->session->cache['cartthrob']['cartthrob_package'][$entry['entry_id']][$field_id]))
						{
							$this->EE->session->cache['cartthrob']['cartthrob_package'][$entry['entry_id']][$field_id] = $this->EE->api_channel_fields->apply('pre_process', array($entry['field_id_'.$field_id]));
						}
						
						$field_data = $this->EE->session->cache['cartthrob']['cartthrob_package'][$entry['entry_id']][$field_id];
						
 						if (isset($field_data[$item_row_id]) && empty($field_data[$item_row_id]['allow_selection'][$field_name]))
						{
							$vars['allow_selection'] = 0;
 						}
						
						if (!$item)
						{
							if (isset($field_data[$item_row_id]) &&  isset($field_data[$item_row_id]['option_presets'][$field_name]))
							{
								if (!in_array($field_data[$item_row_id]['option_presets'][$field_name], array(NULL, "")))
								{
 									$option_value = $field_data[$item_row_id]['option_presets'][$field_name]; 
									$selected = $option_value; 
 								}
							}
						}
					}
				}
			}
 
			$vars = array_merge($this->EE->cartthrob_variables->item_option_vars($entry_id, $row_id, $field_name, $selected), $vars); 
			
			$vars['option_field'] = $field_name;
			$vars['option_label'] = $vars['item_options:option_label'] = $this->EE->cartthrob_field_model->get_field_label($this->EE->cartthrob_field_model->get_field_id($field_name));
			$vars['field_type'] = $this->EE->cartthrob_field_model->get_field_type($this->EE->cartthrob_field_model->get_field_id($field_name));
			$vars['configuration_label'] = NULL; 
 			if ($vars['field_type'] == "cartthrob_price_modifiers_configurator" && strpos($vars['option_field'], ":") === FALSE) 
			{
				$vars['configuration_label'] = $vars['option_label']; 
   			}
 
			$vars['item_options_total_results'] = count($item_options);
			$vars['item_options_count'] = $count;
			$vars['dynamic'] = $dynamic;
			$vars['option_value'] = $option_value; 
			$vars['options_exist'] = (isset($price_modifiers[$field_name]) && count($price_modifiers[$field_name]) > 0) ? (int) (count($price_modifiers[$field_name])) : FALSE;
			
 			if (empty($vars['option_label']))
			{
				$labels = $this->EE->cartthrob->cart->meta('item_option_labels'); 
				
 				if (isset($labels[$vars['option_field']]))
				{
					$vars['option_label'] = $vars['item_options:option_label'] = $labels[$vars['option_field']]; 
				}
				else
				{
					$vars['option_label'] = $vars['item_options:option_label'] = humanize($field_name);
				}
			}
			
			$return_data .= $this->EE->template_helper->parse_variables_row($vars);
		}
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob');
		
		return $return_data;
	}
	
	public function member_downloads()
	{
		if ( ! $this->EE->session->userdata('member_id'))
		{
			return $this->EE->TMPL->no_results();
		}
		
		$this->EE->load->model('cartthrob_entries_model');
		
		return $this->EE->cartthrob_entries_model->channel_entries(array(
			'dynamic' => 'no',
			'author_id' => $this->EE->session->userdata('member_id'),
			'channel_id' => $this->EE->cartthrob->store->config('purchased_items_channel'),
		));
	}

	public function multi_add_to_cart_form()
	{
		if ( ! $this->EE->session->userdata('member_id') && $this->EE->TMPL->fetch_param('logged_out_redirect'))
		{
			$this->EE->template_helper->tag_redirect($this->EE->TMPL->fetch_param('logged_out_redirect'));
		}
		
		$this->EE->load->library('languages');
		
		$this->EE->languages->set_language($this->EE->TMPL->fetch_param('language'));
		
		$TMPL = array(
			'tagdata' => $this->EE->TMPL->tagdata,
			'var_single' => $this->EE->TMPL->var_single,
			'var_pair' => $this->EE->TMPL->var_pair,
			'tagparams' => $this->EE->TMPL->tagparams,
		);
		
		/*
		// deprecated
		if (preg_match_all('/'. LD.'products\s*(.*?)'.RD.'(.*)'.LD.'\/products'.RD.'/s', $TMPL['tagdata'], $matches))
		{
			$this->EE->load->helper('form');
			
			require_once PATH_MOD.'channel/mod.channel'.EXT;
			
			$channel = new Channel;
			
			foreach ($matches[0] as $i => $match)
			{
				$this->EE->TMPL->tagparams = $this->EE->functions->assign_parameters($matches[1][$i]);
				
				$row_id_field = ($this->EE->TMPL->fetch_param('row_id_field')) ? LD.$this->EE->TMPL->fetch_param('row_id_field').RD : '{count}';
				
				$this->EE->TMPL->tagdata = form_hidden('entry_id['.$row_id_field.']', '{entry_id}').$matches[2][$i];
				
				if (preg_match_all('/'.LD.'(item_options?:)(select|input)(:[^\s]+\s*)(.*?)'.RD.'/s', $this->EE->TMPL->tagdata, $_matches))
				{
					foreach ($_matches[0] as $i => $_match)
					{
						$this->EE->TMPL->tagdata = str_replace($_match, LD.$_matches[1][$i].$_matches[2][$i].$_matches[3][$i].' entry_id="{entry_id}" row_id="'.$row_id_field.'"'.$_matches[4][$i].RD, $this->EE->TMPL->tagdata);
					}
				}
				
				$variables = $this->EE->functions->assign_variables($this->EE->TMPL->tagdata);
				
				$this->EE->TMPL->var_single = $variables['var_single'];
				
				$this->EE->TMPL->var_pair = $variables['var_pair'];
				
				$TMPL['tagdata'] = str_replace($match, $channel->entries(), $TMPL['tagdata']);
			}
			
			$variables = $this->EE->functions->assign_variables($TMPL['tagdata']);
			
			$TMPL['var_single'] = $variables['var_single'];
			
			$TMPL['var_pair'] = $variables['var_pair'];
		}
		*/ 
		
		foreach ($TMPL as $key => $value)
		{
			$this->EE->TMPL->{$key} = $value;
		}
		
		$this->EE->load->library('form_builder');
		
		$data = array_merge(
			$this->EE->cartthrob_variables->item_option_vars(),
			$this->EE->cartthrob_variables->global_variables(TRUE)
		);
		
		$this->EE->form_builder->initialize(array(
			'classname' => 'Cartthrob',
			'method' => 'multi_add_to_cart_action',
			'params' => $this->EE->TMPL->tagparams,
			'content' => $this->EE->template_helper->parse_variables_row($data),
			'form_data' => array(
				'secure_return',
				'language',
				'return'
			),
			'encoded_form_data' => 	array(
				'shipping' => 'SHP',
				'weight' => 'WGT', 
				'permissions'	=> 'PER',
				'upload_directory' => 'UPL',
				'class'			=> 'CLS',
			),
			'encoded_bools' => array(
				'allow_user_price' => 'AUP',
				'allow_user_shipping' => 'AUS',
				'allow_user_weight' => 'AUW',
				//'show_errors' => array('ERR', TRUE),
				'on_the_fly' => 'OTF',
				'json' => 'JSN',
				'tax_exempt' => 'TXE',
				'shipping_exempt' => 'SHX',
				
			),
		));
		
		if (bool_string($this->EE->TMPL->fetch_param('no_tax')))
		{
			$this->EE->form_builder->set_encoded_bools("no_tax", 'NTX')->set_params($this->EE->TMPL->tagparams);
		}
		elseif (bool_string($this->EE->TMPL->fetch_param('tax_exempt')))
		{
			$this->EE->form_builder->set_encoded_bools("tax_exempt", 'NTX')->set_params($this->EE->TMPL->tagparams);
		}
		
		if (bool_string($this->EE->TMPL->fetch_param('no_shipping')))
		{
			$this->EE->form_builder->set_encoded_bools("no_shipping", 'NSH')->set_params($this->EE->TMPL->tagparams);
		}
		elseif (bool_string($this->EE->TMPL->fetch_param('shipping_exempt')))
		{
			$this->EE->form_builder->set_encoded_bools("shipping_exempt", 'NSH')->set_params($this->EE->TMPL->tagparams);
		}
		
		
		return $this->EE->form_builder->form();
	}

	public function new_cart()
	{
		$this->EE->cartthrob->cart->initialize()->save();
		
		$this->EE->template_helper->tag_redirect($this->EE->TMPL->fetch_param('return'));
	}
	
	public function order_items()
	{
		$order_ids = ($this->EE->TMPL->fetch_param('order_id')) ? explode('|', $this->EE->TMPL->fetch_param('order_id')) : FALSE;
		$entry_ids = ($this->EE->TMPL->fetch_param('entry_id')) ? explode('|', $this->EE->TMPL->fetch_param('entry_id')) : FALSE;
		$member_ids = ($this->EE->TMPL->fetch_param('member_id')) ? explode('|', str_replace(array('CURRENT_USER', '{logged_in_member_id}', '{member_id}'), $this->EE->session->userdata('member_id'), $this->EE->TMPL->fetch_param('member_id'))) : FALSE;
		
		$this->EE->load->model(array('order_model', 'product_model'));
		
		$this->EE->load->library('number');
		
		$data = $this->EE->order_model->get_order_items($order_ids, $entry_ids, $member_ids);
	
		if ( ! $data)
		{
			return $this->EE->TMPL->no_results();
		}
		
		$this->EE->load->library('api');
		
		$this->EE->api->instantiate('channel_fields');
		
		$this->EE->api_channel_fields->include_handler('cartthrob_order_items');
		
		$this->EE->load->model('cartthrob_entries_model');
		
		if ( ! $this->EE->api_channel_fields->setup_handler('cartthrob_order_items'))
		{
			return '';
		}
		
		if ($this->EE->TMPL->fetch_param('variable_prefix'))
		{
			$this->EE->api_channel_fields->field_types['cartthrob_order_items']->variable_prefix = $this->EE->TMPL->fetch_param('variable_prefix');
		}
		
		$this->EE->api_channel_fields->apply('pre_process', array($data));
		
		$return_data = $this->EE->api_channel_fields->apply('replace_tag', array($data, $this->EE->TMPL->tagparams, $this->EE->TMPL->tagdata));
		
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob'); 
		
		return $return_data; 
	}
	
	public function order_totals()
	{
		$this->EE->load->library('number');
		
		$data = array(
			'total' => 0,
			'subtotal' => 0,
			'tax' => 0,
			'shipping' => 0,
			'discount' => 0,
			'count' => 0,
		);
		
		if ($this->EE->cartthrob->store->config('orders_channel'))
		{
			$this->EE->load->model('cartthrob_entries_model');
			
			if ($query = $this->EE->cartthrob_entries_model->channel_entries(array('channel_id' => $this->EE->cartthrob->store->config('orders_channel')), TRUE))
			{
				$data['count'] = $query->num_rows();
				
				foreach ($query->result_array() as $row)
				{
					if ($this->EE->cartthrob->store->config('orders_total_field') && isset($row['field_id_'.$this->EE->cartthrob->store->config('orders_total_field')]))
					{
						$data['total'] += sanitize_number($row['field_id_'.$this->EE->cartthrob->store->config('orders_total_field')]);
					}
					
					if ($this->EE->cartthrob->store->config('orders_subtotal_field') && isset($row['field_id_'.$this->EE->cartthrob->store->config('orders_subtotal_field')]))
					{
						$data['subtotal'] += sanitize_number($row['field_id_'.$this->EE->cartthrob->store->config('orders_subtotal_field')]);
					}
					
					if ($this->EE->cartthrob->store->config('orders_tax_field') && isset($row['field_id_'.$this->EE->cartthrob->store->config('orders_tax_field')]))
					{
						$data['tax'] += sanitize_number($row['field_id_'.$this->EE->cartthrob->store->config('orders_tax_field')]);
					}
					
					if ($this->EE->cartthrob->store->config('orders_shipping_field') && isset($row['field_id_'.$this->EE->cartthrob->store->config('orders_shipping_field')]))
					{
						$data['shipping'] += sanitize_number($row['field_id_'.$this->EE->cartthrob->store->config('orders_shipping_field')]);
					}
					
					if ($this->EE->cartthrob->store->config('orders_discount_field') && isset($row['field_id_'.$this->EE->cartthrob->store->config('orders_discount_field')]))
					{
						$data['discount'] += sanitize_number($row['field_id_'.$this->EE->cartthrob->store->config('orders_discount_field')]);
					}
				}
			}
		}
		
		foreach ($data as $key => $value)
		{
			if ($key === 'count')
			{
				continue;
			}
			
			$data[$key] = $this->EE->number->format($value);
		}
		
		if ( ! $this->EE->TMPL->tagdata)
		{
			return $data['total'];
		}
		
		return $this->EE->template_helper->parse_variables_row($data);
	}
	
	//@TODO test
	public function package()
	{
		if ($this->EE->TMPL->fetch_param('row_id', '') !== '')
		{
			$item = $this->EE->cartthrob->cart->item($this->EE->TMPL->fetch_param('row_id'));
		}
		
		$data = array();
		
		if (empty($item))
		{
			if ($this->EE->TMPL->fetch_param('entry_id', '') !== '')
			{
				$product = $this->EE->product_model->get_product($this->EE->TMPL->fetch_param('entry_id'));
				
				$this->EE->load->library('api');
				
				$this->EE->api->instantiate('channel_fields');
				
				if ($product && $this->EE->api_channel_fields->setup_handler('cartthrob_package'))
				{
					if ($this->EE->TMPL->fetch_param('variable_prefix'))
					{
						$this->EE->api_channel_fields->field_types['cartthrob_package']->variable_prefix = $this->EE->TMPL->fetch_param('variable_prefix');
					}
					
					$field_id = $this->EE->cartthrob_field_model->channel_has_fieldtype($product['channel_id'], 'cartthrob_package', TRUE);
					
					if ($field_id && isset($product['field_id_'.$field_id]))
					{
						$data = $this->EE->api_channel_fields->apply('pre_process', array($product['field_id_'.$field_id])); 
						
						return $this->EE->api_channel_fields->apply('replace_tag', array($data, $this->EE->TMPL->tagparams, $this->EE->TMPL->tagdata));
					}
				}
			}
		}
		else if ($item->sub_items())
		{
			$data = $this->EE->cartthrob_variables->sub_item_vars($item);
		}
		
		if (count($data) === 0)
		{
			return $this->EE->TMPL->no_results();
		}
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob'); 
		return $this->EE->template_helper->parse_variables($data);
	}

	public function save_customer_info()
	{
		$this->EE->load->library('form_builder');
		
		$_POST = array_merge($_POST, $this->EE->TMPL->tagparams);
		
		$customer_fields = array_keys($this->EE->cartthrob->cart->customer_info());
		
		$required = $this->EE->TMPL->fetch_param('required');

		$save_shipping = bool_string($this->EE->TMPL->fetch_param('save_shipping'), TRUE);

		if ($required == 'all')
		{
			$required = $customer_fields;
			
			if ($save_shipping)
			{
				$required[] = 'shipping_option';
			}
		}
		elseif (preg_match('/^not\s/', $required))
		{
			$not_required = explode('|', substr($required, 4));
			
			$required = $customer_fields;
			
			if ($save_shipping)
			{
				$required[] = 'shipping_option';
			}
			
			foreach ($required as $key => $value)
			{
				if (in_array($value, $not_required))
				{
					unset($required[$key]);
				}
			}
		}
		elseif ($required)
		{
			$required = explode('|', $required);
		}

		if ( ! $required)
		{
			$required = array();
		}

		if ($this->EE->form_builder
							->set_require_rules(FALSE)
		                    ->set_require_errors(FALSE)
		                    ->set_require_form_hash(FALSE)
		                    ->set_required($required)->validate($required))
		{
            $this->EE->cartthrob->save_customer_info();
        }

		$this->EE->template_helper->tag_redirect($this->EE->TMPL->fetch_param('return'));
	}

	public function save_customer_info_form()
	{
		if ($this->EE->session->userdata('member_id') && $this->EE->TMPL->fetch_param('logged_out_redirect'))
		{
			$this->EE->template_helper->tag_redirect($this->EE->TMPL->fetch_param('logged_out_redirect'));
		}
		
		$this->EE->load->library('form_builder');
		
		$variables = $this->EE->cartthrob_variables->global_variables(TRUE);
		
		$this->EE->form_builder->initialize(array(
			'form_data' => array(
				'return',
				'secure_return',
				'derive_country_code',
				'error_handling',
			),
			'encoded_form_data' => array(
			),
			'classname' => 'Cartthrob',
			'method' => 'save_customer_info_action',
			'params' => $this->EE->TMPL->tagparams,
			'content' => $this->EE->template_helper->parse_variables_row($variables),
		));
		
		return $this->EE->form_builder->form();
	}

	/**
	 * Saves chosen shipping option to SESSION
	 *
	 * @access public
	 * @return string
	 * @since 1.0.0
	 * @author Rob Sanchez
	 */
	public function save_shipping_option()
	{
		$shipping_option = set($this->EE->TMPL->fetch_param('shipping_option'), $this->EE->input->post('shipping_option', TRUE));
		
		$this->EE->cartthrob->cart->set_shipping_info('shipping_option', $shipping_option);
		
		$this->EE->cartthrob->cart->save();
		
		$this->EE->template_helper->tag_redirect($this->EE->TMPL->fetch_param('return'));
	}
	
	public function gateway_select()
	{
		$this->EE->load->helper('form');
		$this->EE->load->library('encrypt');

		$attrs = array();

		if ($this->EE->TMPL->fetch_param('encrypt') && bool_string($this->EE->TMPL->fetch_param('encrypt'))==FALSE)
		{
 			$encrypt=FALSE; 
		}
		else
		{
			$encrypt=TRUE; 
		}
 
		if ($this->EE->TMPL->fetch_param('id'))
		{
			$attrs['id'] = $this->EE->TMPL->fetch_param('id');
		}

		if ($this->EE->TMPL->fetch_param('class'))
		{
			$attrs['class'] = $this->EE->TMPL->fetch_param('class');
		}

		if ($this->EE->TMPL->fetch_param('onchange'))
		{
			$attrs['onchange'] = $this->EE->TMPL->fetch_param('onchange');
		}

		$extra = '';

		if ($attrs)
		{
			$extra .= _attributes_to_string($attrs);
		}

		if ($this->EE->TMPL->fetch_param('extra'))
		{
			if (substr($this->EE->TMPL->fetch_param('extra'), 0, 1) != ' ')
			{
				$extra .= ' ';
			}

			$extra .= $this->EE->TMPL->fetch_param('extra');
		}
		
		$selectable_gateways = $this->EE->cartthrob->store->config('available_gateways'); 
 		
		$name = ($this->EE->TMPL->fetch_param('name')?  $this->EE->TMPL->fetch_param('name') : "gateway");
		$selected = ($this->EE->TMPL->fetch_param('selected') ? $this->EE->TMPL->fetch_param('selected'): $this->EE->cartthrob->store->config('payment_gateway') ); 

 		// get the gateways that the user wants to output
		if ($this->EE->TMPL->fetch_param('gateways'))
		{
 			foreach (explode("|", $this->EE->TMPL->fetch_param('gateways')) as $my_gateways)
			{
				$final_g["Cartthrob_".$my_gateways] = "1"; 
			}
			// Making it so that it's possible to add the default gateway in this parameter without it having been selected as a choosable gateway. 
			// if its the default then it's choosable in my book. 
			if (isset($final_g[$this->EE->cartthrob->store->config('payment_gateway')]) && !isset($selectable_gateways[$this->EE->cartthrob->store->config('payment_gateway')]))
			{
				$selectable_gateways[$this->EE->cartthrob->store->config('payment_gateway')] = 1; 
			}
			$selectable_gateways = array_intersect_key($final_g, $selectable_gateways ); 
		}
  		// if the users selected gateways is not an option, then we'll use the default
		if (!isset($selectable_gateways[$selected]) && is_array($selectable_gateways) )
		{
			if (isset($selectable_gateways["Cartthrob_". $selected]))
			{
				$selected = "Cartthrob_".$selected; 
			}
			elseif (isset($selectable_gateways["Cartthrob_".$this->EE->encrypt->decode($selected)]))
			{
				$selected = "Cartthrob_".$this->EE->encrypt->decode($selected); 
			}
			// make sure this isn't an encoded value.
			elseif (!isset($selectable_gateways[$this->EE->encrypt->decode($selected)]))
			{
				$selected =  $this->EE->cartthrob->store->config('payment_gateway'); 
				$selectable_gateways = array_merge(array($this->EE->cartthrob->store->config('payment_gateway') => '1'), (array) $selectable_gateways);
			}
			else
			{
				$selected = $this->EE->encrypt->decode($selected); 
			}
		}

		$this->EE->load->library('api');
		$this->EE->load->library('api/api_cartthrob_payment_gateways');

		if ($this->cart_has_subscription())
		{
			$subscription_gateways = array();

			foreach ($this->EE->api_cartthrob_payment_gateways->subscription_gateways() as $plugin_data)
			{
				$subscription_gateways[] = $plugin_data['classname'];
			}

			$selectable_gateways = array_intersect_key($selectable_gateways, array_flip($subscription_gateways));
		}
			
 		// if none have been selected, OR if you're not allowed to select, then the default is shown
		if (!$this->EE->cartthrob->store->config('allow_gateway_selection') || count($selectable_gateways) == 0)
		{
			$selectable_gateways = array($this->EE->cartthrob->store->config('payment_gateway') => '1'); 
			$selected = $this->EE->cartthrob->store->config('payment_gateway'); 
 		}
 	
		$gateways = $this->EE->api_cartthrob_payment_gateways->gateways();

  		$data = array(); 
		foreach ($gateways as $plugin_data)
		{
 			if (isset($selectable_gateways[$plugin_data['classname']]) )
			{
				$this->EE->lang->loadfile(strtolower($plugin_data['classname']), 'cartthrob', FALSE);

				if (isset($plugin_data["title"]))
				{
					$title = $this->EE->lang->line($plugin_data['title']);
				}
				else
				{
					$title = $plugin_data['classname']; 
				}	
				if ($encrypt)
				{
 					// have to create a variable here, because it'll be used in a spot
					// where it needs to match. each time we encode, the values change. 
 					$encoded = $this->EE->encrypt->encode($plugin_data['classname']); 
					$data[$encoded] = $title; 
 
					if ($plugin_data['classname'] == $selected)
					{
 						$selected = $encoded; 
					}	
				}
				else
				{
					$data[$plugin_data['classname']] = $title; 
				}
			}
		}
 
		asort($data); 
		
		if (bool_string($this->EE->TMPL->fetch_param('add_blank')))
		{
			$data = array_merge(array('' => '---'), $data);
		}
		
 		return form_dropdown(
			$name, 
			$data,
			$selected,
			$extra
		);
	}

	/**
	 * gateway_fields_url
	 *
	 * outputs an action URL so that you can post requests for gateway fields to a URL instead of a template
	 * this will use the change_gateway_fields action to get selected gateway fiedls with an CSRF_TOKEN hash
	 * 
	 * @return string action url
	 * @author Chris Newton
	 */
	public function gateway_fields_url()
	{
		return  $this->EE->functions->fetch_site_index(0, 0).QUERY_MARKER.'ACT='.$this->EE->functions->insert_action_ids($this->EE->functions->fetch_action_id('Cartthrob', 'change_gateway_fields_action')); 
	}
	/**
	 * change_gateway_fields_action
	 * 
	 * gets gateway fields of selected gateway
	 *
	 * @return void 
	 * if hit via an ajax request, EE will return a json object:  data.success data.errors data.gateway_fields data.CSRF_TOKEN
	 * @author Chris Newton
	 */
	public function change_gateway_fields_action()
	{
		if ( ! AJAX_REQUEST)
		{
			exit;
		}
		
		$data = $this->EE->cartthrob_variables->global_variables(TRUE);

 		$html = $this->EE->template_helper->parse_template($this->selected_gateway_fields(), $data);
			
		$this->EE->session->set_flashdata(array(
			'success' => ! (bool) $this->EE->cartthrob->errors(),
			'errors' => $this->EE->cartthrob->errors(),
			'gateway_fields'	=> $html,
			'csrf_token' => $this->EE->functions->add_form_security_hash('{CSRF_TOKEN}'),
		));
	}
	/**
	 * selected_gateway_fields
	 *
	 * returns data from the 'html' field of the currently selected gateway
	 * 
	 * @param bool $gateway 
	 * @return string
	 * @since 1.0
	 * @author Chris Newton
	 */
	public function selected_gateway_fields()
	{
		$this->EE->load->library('encrypt');

		$selectable_gateways = $this->EE->cartthrob->store->config('available_gateways'); 

		if ($this->EE->input->post('gateway'))
		{
			$selected = $this->EE->input->post('gateway'); 
		}
		else
		{
			$selected = ($this->EE->TMPL->fetch_param('gateway') ? $this->EE->TMPL->fetch_param('gateway'): $this->EE->cartthrob->store->config('payment_gateway') ); 
		}


		if (!isset($selectable_gateways[$selected])  )
		{
			if (isset($selectable_gateways["Cartthrob_". $selected]))
			{
				$selected = "Cartthrob_".$selected; 
			}
			elseif (isset($selectable_gateways["Cartthrob_".$this->EE->encrypt->decode($selected)]))
			{
				$selected = "Cartthrob_".$this->EE->encrypt->decode($selected); 
			}
			// make sure this isn't an encoded value.
			elseif (!isset($selectable_gateways[$this->EE->encrypt->decode($selected)]))
			{
				$selected =  $this->EE->cartthrob->store->config('payment_gateway'); 
				$selectable_gateways = array_merge(array($this->EE->cartthrob->store->config('payment_gateway') => '1'),$selectable_gateways);
			}
			else
			{
				$selected = $this->EE->encrypt->decode($selected); 
			}
		}	

 		// if none have been selected, OR if you're not allowed to select, then the default is shown
		if (!$this->EE->cartthrob->store->config('allow_gateway_selection') || count($selectable_gateways) == 0)
		{
			$selectable_gateways = array($this->EE->cartthrob->store->config('payment_gateway') => '1'); 
			$selected = $this->EE->cartthrob->store->config('payment_gateway'); 
 		}

 		$this->EE->load->library('api');

		$this->EE->load->library('api/api_cartthrob_payment_gateways');
 		#$selected = str_replace("Cartthrob_","",$selected); 
		$this->EE->api_cartthrob_payment_gateways->set_gateway($selected);

		if ($this->EE->api_cartthrob_payment_gateways->template())
		{
			$return_data = '{embed="'.$this->EE->api_cartthrob_payment_gateways->template().'"}';
		}
		else
		{
			$return_data = $this->EE->api_cartthrob_payment_gateways->gateway_fields();
		}

		$this->EE->api_cartthrob_payment_gateways->reset_gateway();

		return $return_data;
	}
	
	/**
	 * selected_shipping_fields
	 *
	 * returns data from the 'html' field of the currently selected shipping plugin
	 * 
	 * @param bool $plugin 
	 * @return string
	 * @since 1.0
	 * @author Chris Newton
	 */
	function selected_shipping_fields()
	{
		$this->EE->load->library('api');
		
		$this->EE->load->library('api/api_cartthrob_shipping_plugins');
		
		return $this->EE->api_cartthrob_shipping_plugins->set_plugin($this->EE->TMPL->fetch_param('shipping_plugin'))->html();
	}

	/**
	 * selected_shipping_option
	 *
	 * outputs the description of the shipping item selected in the backend
	 * 
	 * @return string
	 * @author Rob Sanchez
	 * @since 1.0
	 */
	public function selected_shipping_option()
	{
		$this->EE->load->library('api');
		
		$this->EE->load->library('api/api_cartthrob_shipping_plugins');
		
		return ($this->EE->cartthrob->cart->shipping_info('shipping_option')) ? $this->EE->cartthrob->cart->shipping_info('shipping_option') : $this->EE->api_cartthrob_shipping_plugins->default_shipping_option();
	}

	public function set_config()
	{
		$this->EE->load->helper('array');
		
		$data = array_merge($this->EE->cartthrob->cart->customer_info(), array_key_prefix($this->EE->cartthrob->cart->customer_info(), 'customer_'), $this->EE->cartthrob->cart->info(), $this->EE->TMPL->segment_vars, $this->EE->config->_global_vars);
		
		$this->EE->TMPL->tagdata = $this->EE->functions->prep_conditionals($this->EE->TMPL->tagdata, $data);
		
		$this->EE->TMPL->tagdata = $this->EE->TMPL->advanced_conditionals($this->EE->TMPL->tagdata);
		
		$hash = md5($this->EE->TMPL->tagdata);
		
		if ($this->EE->cartthrob->cart->meta('set_config_hash') === $hash)
		{
			//maybe we shouldn't reset it? leaving it for now @TODO
			$this->EE->cartthrob->cart->set_meta('set_config_hash', FALSE)->save();
			
			return '';
		}
		
		$this->EE->cartthrob->cart->set_meta('set_config_hash', $hash);
		
		$vars = $this->EE->functions->assign_variables($this->EE->TMPL->tagdata);
		
		foreach ($vars['var_single'] as $var_single)
		{
			$params = $this->EE->functions->assign_parameters($var_single);
			
			$method = (preg_match('/^set_(config_)?([^\s]+)\s*.*$/', $var_single, $match)) ? 'set_config_'.$match[2] : FALSE;
			
			if ($method && method_exists($this->EE->cartthrob, $method))
			{
				$this->EE->cartthrob->$method($params);
			}
			else if (isset($params['value']))
			{
				$this->EE->cartthrob->cart->set_config($match[2], $params['value']);
			}
			
			if ($method)
			{
				$this->EE->TMPL->tagdata = $this->EE->TMPL->swap_var_single($var_single, '', $this->EE->TMPL->tagdata);
			}
		}
		
		$this->EE->cartthrob->cart->save();
		
		$this->EE->functions->redirect($this->EE->functions->create_url($this->EE->uri->uri_string()));
		
		return $this->EE->TMPL->tagdata; 
		
	}

	/**
	 * get_live_rates_form
	 * Outputs a quote request form
	 * 
	 * @since 1.0
	 * @param $TMPL->shipping_plugin
	 * @return string
	 * @author Chris Newton
	 **/
	public function get_live_rates_form()
	{
		$this->EE->load->library('form_builder');
		
		$data = $this->EE->cartthrob_variables->global_variables(TRUE);

		$data['shipping_fields'] = $this->selected_shipping_fields();
		
		$this->EE->form_builder->initialize(array(
			'classname' => 'Cartthrob',
			'method' => 'update_live_rates_action',
			'params' => $this->EE->TMPL->tagparams,
			'content' => $this->EE->template_helper->parse_variables_row($data),
			'form_data' => array(
				'return',
				'secure_return',
				'derive_country_code',
				'shipping_plugin',
				'shipping_option',
				'activate_plugin'
			),
			'encoded_form_data' => array(
			),
		));
		
		return $this->EE->form_builder->form();
	}
	
	/**
	 * update_live_rates_action
	 * Gets a quoted shipping value from the default shipping method, and applies that value as the shipping value
	 * 
	 * @since 1.0
	 * @param $this->EE->TMPL->shipping_plugin
	 * @param $this->EE->TMPL->validate (checks required fields)
	 * @return string
	 * @author Chris Newton
	 **/
	function update_live_rates_action()
	{
		// save_shipping (if set in post...will automatically save the cheapest option)
		
		if ( ! $this->EE->input->get_post('ACT'))
		{
			return;
		}
		
		if ($this->EE->extensions->active_hook('cartthrob_update_live_rates_start') === TRUE)
		{
			$this->EE->extensions->call('cartthrob_update_live_rates_start');
			if ($this->EE->extensions->end_script === TRUE) return;
		}
		$this->EE->cartthrob->save_customer_info();
		$this->EE->cartthrob->cart->save();
		
		$this->EE->load->library('form_validation');
		$this->EE->load->library('form_builder');
		$this->EE->load->library('encrypt');
		$this->EE->load->library('api/api_cartthrob_shipping_plugins');
		$this->EE->load->library('languages');
		
 		if ($this->EE->cartthrob->cart->count() <= 0)
		{
			return $this->EE->form_builder->add_error($this->EE->lang->line('empty_cart'))->action_complete(); 
		}
		
		if ($this->EE->cartthrob->cart->shippable_subtotal() <= 0)
		{
			$this->EE->form_builder->set_errors($this->EE->cartthrob->errors())
						->set_success_callback(array($this->EE->cartthrob, 'action_complete'))
							->action_complete();
		}
		
		
		$this->EE->languages->set_language($this->EE->input->post('language', TRUE));
	
		$not_required = array();
		
		$required = array();
		
		if ($this->EE->input->post('REQ'))
		{
			$required_string = xss_clean($this->EE->encrypt->decode($this->EE->input->post('REQ')));
			
			if (preg_match('/^not (.*)/', $required_string, $matches))
			{
				$not_required = explode('|', $matches[1]);
				$required_string = '';
			}
			
			if ($required_string)
			{
				$required = explode('|', $required_string);
			}

			unset($required_string);
		}
		
		if ($this->EE->input->post('shipping_plugin'))
		{
			$selected_plugin =xss_clean( $this->EE->input->post('shipping_plugin')); 
			$this->EE->api_cartthrob_shipping_plugins->set_plugin($selected_plugin);
			if (bool_string(xss_clean($this->EE->input->post('activate_plugin')), TRUE))
			{
				$this->EE->cartthrob->cart->set_config("shipping_plugin", $selected_plugin);
			}
		}
		
		$shipping_name = $this->EE->api_cartthrob_shipping_plugins->title();
		
		$required = array_unique(array_merge($required, $this->EE->api_cartthrob_shipping_plugins->required_fields()));
		foreach ($not_required as $key)
		{
			unset($required[array_search($key, $required)]);
		}
		if ( ! $this->EE->form_builder->set_required($required)->validate())
		{
			return $this->EE->form_builder->action_complete();
		}
		
		$product_id = $this->EE->input->post('shipping_option') ? $this->EE->input->post('shipping_option') : 'ALL';
		
		$shipping_info= array(
			'error_message'	=> NULL, 
			'option_value'	=> array(),
			'option_name'	=> array(),
			'price'			=> array(),
			);
		
		$shipping_info = array_merge($shipping_info, $this->EE->api_cartthrob_shipping_plugins->get_live_rates($product_id));
		
		$this->EE->load->library('cartthrob_shipping_plugins');
		
  		// OUTPUTS ERROR IN STANDARD EE WAY
		if (!$shipping_info || (empty($shipping_info['error_message']) && empty($shipping_info['option_value']) ))
		{
			return $this->EE->form_builder->add_error($this->EE->lang->line('no_shipping_returned'))->action_complete();
 			
		}
 		if (!empty($shipping_info['error_message']) )
		{

 			return $this->EE->form_builder->add_error($shipping_info['error_message'])->action_complete();
		}
		else
		{
			// SAVE THE CHEAPEST OPTION AS SELECTED
			if (bool_string($this->EE->input->post('save_shipping'), TRUE))
			{
				if (!in_array($this->selected_shipping_option(), $shipping_info['option_value']))
				{
					$lowest_amount_key = array_pop(array_keys($shipping_info['price'], min($shipping_info['price'])));
					if (!empty( $shipping_info['option_value'][$lowest_amount_key]))
					{
						$this->EE->cartthrob->cart->set_shipping( $shipping_info['price'][$lowest_amount_key]);
						$this->EE->cartthrob->cart->set_shipping_info('shipping_option', $shipping_info['option_value'][$lowest_amount_key]);
						$this->EE->cartthrob->cart->save();
					}
				}
			}

		}

		$this->EE->form_builder->set_errors($this->EE->cartthrob->errors())
					->set_success_callback(array($this->EE->cartthrob, 'action_complete'))
						->action_complete();
		}

	/**
	 * states
	 *
	 * swaps abbrev, and state from list in templates 
	 * @param $TMPL country_code 3 character country code (Default USA)
	 * @return string 
	 * @author Rob Sanchez, Chris Newton 
	 * @since 1.0
	 */
	public function states()
	{
		$this->EE->load->library('locales');
		
		$country_code = ($this->EE->TMPL->fetch_param('country_code')) ? $this->EE->TMPL->fetch_param('country_code') : FALSE;
		
 		$data = array();
		
 		foreach ($this->EE->locales->states($country_code) as $abbrev => $state)
		{
			$data[] = array('abbrev' => $abbrev, 'state' => $state);
		}
		
 		return $this->EE->template_helper->parse_variables($data);

	}
	//alias for state_select()
	public function states_select()
	{
		return $this->state_select();
	}
	
	public function state_select()
	{
		$this->EE->load->library('locales');
		$this->EE->load->helper('form');
		
		$name = ($this->EE->TMPL->fetch_param('name')) ? $this->EE->TMPL->fetch_param('name') : 'state';
		$selected = ($this->EE->TMPL->fetch_param('selected')) ? $this->EE->TMPL->fetch_param('selected') : $this->EE->TMPL->fetch_param('default');
		$abbrev_label = bool_string($this->EE->TMPL->fetch_param('abbrev_label'));
		$abbrev_value = bool_string($this->EE->TMPL->fetch_param('abbrev_value'), TRUE);
		
		$states = $this->EE->locales->states($this->EE->TMPL->fetch_param('country_code'));
		
		if (bool_string($this->EE->TMPL->fetch_param('add_blank')))
		{
			$blank = array('' => '---'); 
			$states = $blank + $states;
		}
		
		$states_converted= array(); 
		foreach ($states as $abbrev => $state)
		{
			$value = ($abbrev_value) ? $abbrev : $state;
			$states_converted[$value] = ($abbrev_label) ? $abbrev : $state;
		}
		
		$attrs = array();
		
		if ($this->EE->TMPL->fetch_param('id'))
		{
			$attrs['id'] = $this->EE->TMPL->fetch_param('id');
		}
		
		if ($this->EE->TMPL->fetch_param('class'))
		{
			$attrs['class'] = $this->EE->TMPL->fetch_param('class');
		}
		
		if ($this->EE->TMPL->fetch_param('onchange'))
		{
			$attrs['onchange'] = $this->EE->TMPL->fetch_param('onchange');
		}
		
		$extra = '';
		
		if ($attrs)
		{
			$extra .= _attributes_to_string($attrs);
		}
		
		if ($this->EE->TMPL->fetch_param('extra'))
		{
			if (substr($this->EE->TMPL->fetch_param('extra'), 0, 1) != ' ')
			{
				$extra .= ' ';
			}
			
			$extra .= $this->EE->TMPL->fetch_param('extra');
		}
		$this->EE->load->helper('form');
		
		return form_dropdown(
			$name,
			$states_converted,
			$this->EE->TMPL->fetch_param('selected'),
			$extra
		);
 	}

	public function subscription_select()
	{
		$this->EE->load->helper('form');
		$this->EE->load->library('encrypt'); 
		$this->EE->load->model('subscription_model');
		
		$name = ($this->EE->TMPL->fetch_param('name')) ? $this->EE->TMPL->fetch_param('name') : 'subscription_interval_units';
		
		if ($name =="subscription")
		{
			$name = "SUB"; 
		}
		$selected = ($this->EE->TMPL->fetch_param('selected')) ? $this->EE->TMPL->fetch_param('selected') : $this->EE->TMPL->fetch_param('default');
		$temp_name = str_replace("subscription_", "", $name); 

 		if (!$selected && $item = $this->EE->cartthrob->cart->item($this->EE->TMPL->fetch_param('row_id')))
		{
			$this->EE->load->helper('array'); 
			$opt = element($temp_name, $item->meta('subscription_options')); 
			if ($item->meta('subscription_options') && $opt !== FALSE)
			{
				$selected = $opt; 
			}
		}
 		
		$options = ($this->EE->TMPL->fetch_param('options') ? $this->EE->TMPL->fetch_param('options') : "days|weeks|months|years"); 

		// need to get the encoded key SUB
		$keys = $this->EE->subscription_model->option_keys(); 
		$encoded_key = array_search($temp_name, $keys); 
		if ($encoded_key)
		{
			$name = $encoded_key; 
		}
		$options = explode("|", $options);
		if($name == "PI")
		{
			if(in_array("days", $options))
			{
				// either no options were passed in or someone screwed up. Clear the options if it has days in the option array
				$options = NULL;
			}
				$temp_options = array();
				$sub_params['order_by'][] = "name";
				$sub_params['sort'][] = "asc";
				$all_plans= $this->EE->subscription_model->get_plans($sub_params, 100);
				
				if (is_array($all_plans) && count($all_plans))
				{
					if(! empty($options))
					{
						foreach ($all_plans as $p)
						{
							if(in_array($p['id'],$options))
							{
								$temp_options[]=$p['id'].":".$p['name'];
							}
						}
					}
					else
					{
						foreach ($all_plans as $p)
						{
							$temp_options[]=$p['id'].":".$p['name'];
						}
					}
					
					$options = $temp_options;
				}
		}
		
		$revised_options = array(); 
		foreach ($options as $option_value => $option_name)
		{
			// format: options="12:1 year|24:2 years|36:3 years"
			if (strpos($option_name, ":")!== FALSE)
			{
				list($option_value, $option_name) = explode(":", $option_name); 
			}
			else
			{
				$option_value = $option_name; 
			}
			
			$encoded_value =  $this->EE->encrypt->encode(trim($option_value));
	
			if ($option_value == $selected) // have to do this because an unencoded value won't = the encoded version of same
			{
				$selected = $encoded_value; 
			}
			$revised_options[$encoded_value] = $option_name; 
		}
		if (bool_string($this->EE->TMPL->fetch_param('add_blank')))
		{
			$blank = array('' => '---'); 
			$revised_options = array_merge($blank, $revised_options);
		}
		
		$attrs = array();
		
		if ($this->EE->TMPL->fetch_param('id'))
		{
			$attrs['id'] = $this->EE->TMPL->fetch_param('id');
		}
		
		if ($this->EE->TMPL->fetch_param('class'))
		{
			$attrs['class'] = $this->EE->TMPL->fetch_param('class');
		}
		
		if ($this->EE->TMPL->fetch_param('onchange'))
		{
			$attrs['onchange'] = $this->EE->TMPL->fetch_param('onchange');
		}
		
		$extra = '';
		
		if ($attrs)
		{
			$extra .= _attributes_to_string($attrs);
		}
		
		if ($this->EE->TMPL->fetch_param('extra'))
		{
			if (substr($this->EE->TMPL->fetch_param('extra'), 0, 1) != ' ')
			{
				$extra .= ' ';
			}
			
			$extra .= $this->EE->TMPL->fetch_param('extra');
		}
 		
		return form_dropdown(
			$name,
			$revised_options,
			$selected,
			$extra
		);
 	}


 
	public function submitted_order_info()
	{
		$data = $this->EE->cartthrob->cart->order();

 		$this->EE->load->model(array('cartthrob_entries_model', 'order_model'));
		
		if ( ! $data)
		{
			return $this->EE->template_helper->parse_variables();
		}
		
		foreach ($data as $i => $row)
		{
			//what's happening here:
			//not all of the data from cart->order() is suitable to be passed to parse_variables
			//particularly arrays of data that don't contain arrays
			//remove them.
			if (is_array($row) && count($row) > 0 && ! is_array(current($row)))
			{
				if ($i === 'custom_data')
				{
					foreach ($row as $key => $value)
					{
						$data['custom_data:'.$key] = $value;
					}
				}
				
				unset($data[$i]);
			}
		}
 
 		$auth = array(
			'processing' => (isset($data['auth']['processing'])) ? $data['auth']['processing'] : '',
			'authorized' => (isset($data['auth']['authorized'])) ? $data['auth']['authorized'] : '',
			'declined' => (isset($data['auth']['declined'])) ? $data['auth']['declined'] : '',
			'failed' => (isset($data['auth']['failed'])) ? $data['auth']['failed'] : '',
			'error_message' => (isset($data['auth']['error_message'])) ? $data['auth']['error_message'] : '',
			'transaction_id' => (isset($data['auth']['transaction_id'])) ? $data['auth']['transaction_id'] : '',
			'no_order' => ! (bool) $data //deprecated, use no_results
		);
		
 		$data = array_merge($data, $auth, array_key_prefix($data, 'cart_'));
		
		if ( ! empty($data['order_id']))
		{
			if ($order = $this->EE->order_model->get_order($data['order_id']))
			{
				$status = $this->EE->order_model->get_order_status($data['order_id']); 
				switch($status)
				{
					case "authorized": 
					case "completed": 
						$data['authorized'] = TRUE; 
						break; 
					case "declined": 
						$data['declined'] = TRUE; 
						break;
					case "failed": 
					case "refunded": 
					case "expired": 
					case "reversed": 
					case "canceled": 
					case "voided": 
						$data['failed'] = TRUE; 
						break; 
					default: 
						$data['processing'] = TRUE; 
					
				}
				$data['transaction_id'] = (!empty($data['auth']['transaction_id']) ? $data['auth']['transaction_id'] : $this->EE->order_model->get_order_transaction_id($data['order_id'])); 
				$data['error_message'] = (!empty($data['auth']['error_message']) ? $data['auth']['error_message'] : $this->EE->order_model->get_order_error_message($data['order_id']) ); 
				$data = array_merge($this->EE->cartthrob_entries_model->entry_vars($order), $data);
			}
		}
		
		// this needs to remain just before variable parsing so that any scripts above are not affected by removing data keys
		foreach ($data as $i => $row)
		{
			//what's happening here:
			//not all of the data from cart->order() is suitable to be passed to parse_variables
			//particularly arrays of data that don't contain arrays
			//remove them.
			if (is_array($row) && count($row) > 0 && ! is_array(current($row)))
			{
				if ($i === 'custom_data')
				{
					foreach ($row as $key => $value)
					{
						$data['custom_data:'.$key] = $value;
					}
				}
				
				unset($data[$i]);
			}
		}
		
  		return $this->EE->template_helper->parse_variables_row($data);
	}
	
	// --------------------------------
	//  Total Items Count
	// --------------------------------
	/**
	 * Returns total number of ALL items (including indexes) in cart
	 * If you have 4 of product A, and 5 of product B, this would return 9. 
	 * To get total individual items, use total unique items
	 *
	 * @access public
	 * @return string
	 * @since 1.0.0
	 * @author Rob Sanchez
	 */ 
	public function total_items_count()
	{
		return $this->EE->cartthrob->cart->count_all();
	}

	public function unique_items_count()
	{
		return $this->EE->cartthrob->cart->count();
	}

	/**
	 * update_cart_form
	 * 
	 * outputs a form for updating data in the cart
	 * 
	 * @return string
	 * @access public 
	 * @param $this->EE->TMPL->id
	 * @param $this->EE->TMPL->name
	 * @param $this->EE->TMPL->onsubmit
	 * @param $this->EE->TMPL->show_errors
	 * @param $this->EE->TMPL->json
	 * @param $this->EE->TMPL->redirect deprecated
	 * @param $this->EE->TMPL->return
	 * @param $this->EE->TMPL->class
	 * @author Rob Sanchez, Chris Newton
	 * @since 1.0
	 */
	public function update_cart_form()
	{
		if ( ! $this->EE->session->userdata('member_id') && $this->EE->TMPL->fetch_param('logged_out_redirect'))
		{
			$this->EE->template_helper->tag_redirect($this->EE->TMPL->fetch_param('logged_out_redirect'));
		}
		
		$this->EE->load->library('form_builder');
		
		$variables = $this->EE->cartthrob_variables->global_variables(TRUE);
		
		foreach ($this->EE->TMPL->var_single as $key)
		{
			if ( ! isset($variables[$key]) && strpos($key, 'custom_data:') === 0)
			{
				$variables[$key] = '';
			}
		}
		
		$this->EE->load->model('subscription_model');
		
		$this->EE->form_builder->initialize(array(
			'form_data' => array(
				'secure_return',
				'return'
			),
			'encoded_form_data' => $this->EE->subscription_model->encoded_form_data(),
			'encoded_numbers' => $this->EE->subscription_model->encoded_numbers(),
			'encoded_bools' => $this->EE->subscription_model->encoded_bools(),
			'classname' => 'Cartthrob',
			'method' => 'update_cart_action',
			'params' => $this->EE->TMPL->tagparams,
			'content' => $this->EE->template_helper->parse_variables_row($variables),
		));
		
		return $this->EE->form_builder->form();
	}
	
	/**
	 * Updates an item's quantity and item_options
	 *
	 * @access public
	 * @param string $this->EE->TMPL->fetch_param('entry_id')
	 * @return string
	 * @since 1.0.0
	 * @author Rob Sanchez
	 */
	public function update_item()
	{
		foreach ($this->EE->TMPL->tagparams as $key => $value)
		{
			if (preg_match('/^item_options?:(.*)$/', $key, $match))
			{
				unset($this->EE->TMPL->tagparams[$key]);
				
				$this->EE->TMPL->tagparams['item_options'][$match[1]] = $value;
			}
		}
		
		$data = $this->EE->TMPL->tagparams;
		
		//should I?
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$data = array_merge($data, xss_clean($_POST));
		}

		if ($item = $this->EE->cartthrob->cart->item($this->EE->TMPL->fetch_param('row_id')))
		{
			$item->update($this->EE->TMPL->tagparams);
		
			$this->EE->cartthrob->cart->save();
		}

		$this->EE->template_helper->tag_redirect($this->EE->TMPL->fetch_param('return'));
	}

	public function update_item_form()
	{
		if ($this->EE->session->userdata('member_id') && $this->EE->TMPL->fetch_param('logged_out_redirect'))
		{
			$this->EE->template_helper->tag_redirect($this->EE->TMPL->fetch_param('logged_out_redirect'));
		}
		
		$item = $this->EE->cartthrob->cart->item($this->EE->TMPL->fetch_param('row_id'));
		
		$entry_id = ($item && $item->product_id()) ? $item->product_id() : $this->EE->TMPL->fetch_param('entry_id');
		
		$this->EE->load->library('form_builder');
		
		$data = array_merge(
			$this->EE->cartthrob_variables->global_variables(TRUE),
			$this->EE->cartthrob_variables->item_option_vars($entry_id, $this->EE->TMPL->fetch_param('row_id'))
		);
		
		$this->EE->form_builder->initialize(array(
			'form_data' => array(
				'secure_return',
				'entry_id',
				'row_id',
				'quantity',
				'title',
				'language',
				'return',
				'delete',
				'delete_all'
			),
			'encoded_form_data' => array_merge(
				array(
					'shipping' => 'SHP',
					'weight' => 'WGT', 
					'permissions'	=> 'PER',
				),
				$this->EE->subscription_model->encoded_form_data()
			),
			'encoded_numbers' =>array_merge(
				array(
					'price' => 'PR',
					'expiration_date' => 'EXP',
				),
				$this->EE->subscription_model->encoded_numbers()
			),
			'encoded_bools' => array_merge(
				array(
					'allow_user_price' => 'AUP',
					'allow_user_weight' => 'AUW',
					'allow_user_shipping' => 'AUS',
					'on_the_fly' => 'OTF',
					'license_number' => 'LIC',
				),
				$this->EE->subscription_model->encoded_bools()
			),
			'array_form_data' => array(
				'item_option'
			),
			'classname' => 'Cartthrob',
			'method' => 'update_item_action',
			'params' => $this->EE->TMPL->tagparams,
			'content' => $this->EE->template_helper->parse_variables_row($data),
		));
		
		if (bool_string($this->EE->TMPL->fetch_param('no_tax')))
		{
			$this->EE->form_builder->set_encoded_bools("no_tax", 'NTX')->set_params($this->EE->TMPL->tagparams);
		}
		elseif (bool_string($this->EE->TMPL->fetch_param('tax_exempt')))
		{
			$this->EE->form_builder->set_encoded_bools("tax_exempt", 'NTX')->set_params($this->EE->TMPL->tagparams);
		}
		
		if (bool_string($this->EE->TMPL->fetch_param('no_shipping')))
		{
			$this->EE->form_builder->set_encoded_bools("no_shipping", 'NSH')->set_params($this->EE->TMPL->tagparams);
		}
		elseif (bool_string($this->EE->TMPL->fetch_param('shipping_exempt')))
		{
			$this->EE->form_builder->set_encoded_bools("shipping_exempt", 'NSH')->set_params($this->EE->TMPL->tagparams);
		}
		
		
		return $this->EE->form_builder->form();
	}
	public function update_item_action()
	{
		if ( ! $this->EE->input->get_post('ACT'))
		{
			return;
		}
 
		$row_id =  $this->EE->input->post('row_id') ? $this->EE->input->post('row_id') : 0 ; 
		$post = $this->EE->security->xss_clean($_POST);
		
		$item = $this->EE->cartthrob->cart->item($row_id); 
		if ( $item )
		{
			if (element($row_id, element('delete', $post)))
			{
				$this->EE->cartthrob->cart->remove_item($row_id);
			}
			else
			{
				foreach ($post as $key => $value)
				{
 					if (in_array($key, $item->default_keys()))
					{
						$data[$key] = $value;
					}
				}

				if (!empty($data))
				{
					$item->update($data);
				}
			}
			
		}
		if ($this->EE->input->post('delete_all'))
		{
			$this->EE->cartthrob->cart->clear();
		}
		$this->EE->cartthrob->cart->check_inventory();

		$this->EE->load->library('form_builder');

		$this->EE->form_builder->set_errors($this->EE->cartthrob->errors())
					->set_success_callback(array($this->EE->cartthrob, 'action_complete'))
					->action_complete(TRUE);
 
	}
	
	// aliases the checkout form. sub_id MUST be supplied, and must be valid. 
	public function update_subscription_form()
	{
		$this->EE->load->library('form_builder');
		$id = $this->EE->TMPL->fetch_param('sub_id');
		$subs = NULL; 
		
		if (!$id)
		{
			$this->EE->cartthrob->set_error(lang('you_must_supply_a_subscription_id'));
		}
		else
		{
			$this->EE->load->model('subscription_model');

			$subs = $this->EE->subscription_model->get_subscription($id); 
		}
			
		if ( ! $subs)
		{
			$this->EE->cartthrob->set_error(lang('no_subscription_by_that_id'));
		}
		
		if ( $this->EE->form_builder->errors())
		{
			return $this->EE->form_builder->action_complete();
		}
	
		
		return $this->checkout_form(); 
		
	}
	public function update_subscription_details()
	{
		$id = $this->EE->TMPL->fetch_param('sub_id');
		
		$this->EE->load->model('subscription_model');
		
		// this should NOT be a db search parameter of: sub_id, which is assigned by the payment provider. 
		$params = array('id' => $id);
		
		if ($this->EE->session->userdata('group_id') != 1)
		{
			$params['member_id'] = $this->EE->session->userdata('member_id');
		}
		
		//@TODO for now only the member can update his own sub
		$subs = $this->EE->subscription_model->get_subscriptions($params);
		
		if ( ! $subs)
		{
 			return $this->EE->TMPL->no_results();
		}
		
		$sub = array_shift($subs);
		
		$this->EE->load->library('form_builder');
		
		$data = array_merge(
			$sub,
			$this->EE->cartthrob_variables->global_variables(TRUE)
		);

		$this->EE->cartthrob_variables->add_encoded_option_vars($data);

		$encoded_form_data = array(); 
		$form_data = array('sub_id'); 
	
 		foreach ($this->EE->subscription_model->columns as $key => $default)
		{
			if (in_array($key, array('name', 'description')))
			{
				$form_data[] = "subscription_". $key; 
			}
			else
			{
				if ($this->EE->TMPL->fetch_param('subscription_'. $key))
				{
					$encoded_form_data[ "subscription_". $key ]= "subscription_". $key; 
				}
			}
		}
		
 		$this->EE->form_builder->initialize(array(
			'form_data' => $form_data, 
			'encoded_form_data' => $encoded_form_data,
			'classname' => 'Cartthrob',
			'method' => 'update_subscription_action',
			'params' => $this->EE->TMPL->tagparams,
			'content' => $this->EE->template_helper->parse_variables_row($data),
		));

		$this->EE->form_builder->set_hidden('sub_id', $this->EE->TMPL->fetch_param('sub_id', $this->EE->TMPL->fetch_param('sub_id')));

		return $this->EE->form_builder->form();
	}
	
	public function update_subscription_action()
	{
		$this->EE->load->library(array('form_builder', 'encrypt'));

		$this->EE->load->model('subscription_model');

		$data = array('id' => $this->EE->input->post('sub_id'));

		$this->EE->cartthrob->save_customer_info();

		if ( ! $data['id'])
		{
			$this->EE->form_builder->add_error(lang('no_subscription_id'))
				->action_complete();
		}

		foreach ($this->EE->subscription_model->columns as $key => $default)
		{
			$value = NULL; 
			
			$encoded_bools = $this->EE->subscription_model->encoded_bools(); 
			$encoded_form_data = $this->EE->subscription_model->encoded_form_data(); 
			$encoded_numbers = $this->EE->subscription_model->encoded_numbers(); 
			if (($value = $this->EE->input->post('subscription_'.$key)) !== FALSE)
			{
				$data[$key] = in_array($key, array('name', 'description')) ? $value : $this->EE->encrypt->decode($value);
			}
			elseif  (isset($encoded_form_data['subscription_'.$key]) && ($value =  $this->EE->input->post($encoded_form_data['subscription_'.$key])) !== FALSE)
			{
				$data[$key] =  $this->EE->encrypt->decode($value);
			}
			elseif  (isset($encoded_bools['subscription_'.$key]) && ($value =  $this->EE->input->post($encoded_bools['subscription_'.$key])) !== FALSE)
			{
				$data[$key] =  $this->EE->encrypt->decode($value);
			}
			elseif  (isset($encoded_numbers['subscription_'.$key]) && ($value =  $this->EE->input->post($encoded_numbers['subscription_'.$key])) !== FALSE)
			{
				$data[$key] =  $this->EE->encrypt->decode($value);
			}
 		}
		if ( ! $this->EE->subscription_model->validate($data))
		{
			return $this->EE->form_builder->add_error($this->EE->subscription_model->errors)
										->action_complete();
		}
		
		// check to see if the plan is changing
		$current_subscription = $this->EE->subscription_model->get_subscription($data['id']);
		if(isset($data['plan_id']) && ($current_subscription['plan_id'] != $data['plan_id']))
		{
			$permissions_id = array();
			
			// update permissions when a plan changes
			$this->EE->load->model("permissions_model");
			// get the current permissions for this id
			$current_permissions_data = (array) $this->EE->permissions_model->get(array('sub_id' => $data['id']),NULL,0,TRUE);
			// get the permissions for the new plan
			$new_plan = $this->EE->subscription_model->get_plan($data['plan_id']);
			$new_plan_permissions = unserialize(base64_decode($new_plan['permissions']));
	
			
			#let's go ahead and delete the permissions that we no longer want
			if(!empty($current_permissions_data))
			{
				foreach ($current_permissions_data as $current_perm)	
				{
					if($new_plan_permissions)
					{
						if(($key = array_search($current_perm['permission'],$new_plan_permissions)) !== FALSE) {
							
							# it was in the array, so I'm removing it from the new permissions array
							# when I'm done deleting unwanted permissions, I'll use what's left in the new permissions data to create new permissions
						    unset($new_plan_permissions[$key]);
							# push the id to the permissions_id array
							$permissions_id[]=$current_perm['id'];
						}
						else
						{
							# we don't want this permission anymore, delete it
							$this->EE->permissions_model->delete($current_perm['id']);
						}
					}
					else
					{
						# apparently we don't want any permissions anymore, deleting them all
						$this->EE->permissions_model->delete($current_perm['id']);
					}
					
				}
			}
			
			//var_dump($current_subscription);
			if(!empty($new_plan_permissions))
			{
				foreach ($new_plan_permissions as $perm)
				{
					$perm_id = $this->EE->permissions_model->update(array(
						'member_id'       => $current_subscription['member_id'],
						'permission'      => $perm,
						'item_id'         => (isset($current_subscription['entry_id'])) ? $current_subscription['entry_id'] : 000,
						'order_id'        => $current_subscription['order_id'],
						'sub_id'		=> $data['id']
					));
				
					# the permission ids need to get updated in the subscription table
					$permissions_id[] = $perm_id;
				}
			
			}
			
			# going to add the permission ids into the subscription
			# should have the serialized item from the current subscription, unserialize it, add the permissions_id, then reserialize it
			if(isset($current_subscription['serialized_item']))
			{
				$unserialized_item = unserialize(base64_decode($current_subscription['serialized_item']));
				$unserialized_item['permissions_id'] = $permissions_id;
				$data['serialized_item'] = base64_encode(serialize($unserialized_item));
			}
		}
		
		
		$this->EE->subscription_model->update($data);
		
		// cartthrob_add_to_cart_start hook
		if ($this->EE->extensions->active_hook('cartthrob_update_subscription_details') === TRUE)
		{
			//$edata = $EXT->universal_call_extension('cartthrob_update_subscription_details', $data);
			$this->EE->extensions->call('cartthrob_update_subscription_details', $data);
			if ($this->EE->extensions->end_script === TRUE) return;
		}
		
		$this->EE->form_builder->set_success_callback(array($this->EE->cartthrob->cart, 'save'))
			->action_complete();
	}

	/**
	 * view_converted_currency
	 *
	 * @param $number bool
	 * @return string
	 * @author Chris Newton
	 * @param string $TMPL->fetch_param('price')
	 * @param string $TMPL->fetch_param('currency_code')
	 * @param string $TMPL->fetch_param('new_currency_code')
	 * @param string $TMPL->fetch_param('decimals')
	 * @param string $TMPL->fetch_param('dec_point')
	 * @param string $TMPL->fetch_param('thousands_sep')
	 * @param string $TMPL->fetch_param('prefix')
	 * @param string $TMPL->fetch_param('new_prefix')
	 **/
	public function view_converted_currency()
	{
		$this->EE->load->library('number');
		$this->EE->load->library('curl');
 		
		// Check to see if this value is being passed in or not. 
		$number = $this->EE->TMPL->fetch_param('price');
		
		if ($number === FALSE)
		{
			return '';
		}
		
		// clean the number
		$number = sanitize_number($number);
		
		// -------------------------------------------
		// 'cartthrob_view_converted_currency' hook.
		//
		if ($this->EE->extensions->active_hook('cartthrob_view_converted_currency') === TRUE)
		{
			return $this->EE->extensions->call('cartthrob_view_converted_currency', $number);
		}

		// set defaults
		$currency = ($this->EE->TMPL->fetch_param('currency_code') !== FALSE) ? $this->EE->TMPL->fetch_param('currency_code') : $this->EE->cartthrob->store->config('number_format_default_currency_code');
		$new_currency = ($this->EE->TMPL->fetch_param('new_currency_code') !== FALSE) ? $this->EE->TMPL->fetch_param('new_currency_code') : $this->EE->cartthrob->store->config('number_format_default_currency_code');
		
		$currency = strtolower($currency);
		$new_currency = strtolower($new_currency);

		$new_prefix = bool_string($this->EE->TMPL->fetch_param('use_prefix')); 

		$prefix = ""; 

		if ($new_prefix)
		{
			switch ($new_currency)
			{
				case "eur":
					$prefix = "&#8364;";
					break;
				case "usd":
					$prefix = "$";
					break;
				case "gbp":
					$prefix = "&#163;";
					break;
				case "aud":
					$prefix = "$";
					break;
				case "brl":
					$prefix = "R$";
					break;
				case "nzd":
					$prefix = "$";
					break;
				case "cad":
					$prefix = "$";
					break;
				case "chf":
					$prefix = "CHF";
					break;
				case "cny":
					$prefix = "&#165;";
					break;
				case "dkk":
					$prefix = "kr";
					break;
				case "hkd":
					$prefix = "$";
					break;
				case "inr":
					$prefix = "&#8360;";
					break;
				case "jpy":
					$prefix = "&#165;";
					break;
				case "krw":
					$prefix = "&#8361;";
					break;
				case "mxn":
					$prefix = "$";
					break;
				case "myr":
					$prefix = "RM";
					break;
				case "nok":
					$prefix = "kr";
					break;
				case "sek":
					$prefix = "kr";
					break;
				case "sgd":
					$prefix = "$";
					break;
				case "thb":
					$prefix = "&#3647;";
					break;
				case "zar":
					$prefix = "R";
					break;
				case "bgn":
					$prefix = "&#1083;&#1074;";
					break;
				case "czk":
					$prefix = "&#75;&#269;";
					break;
				case "eek":
					$prefix = "kr";
					break;
				case "huf":
					$prefix = "Ft";
					break;
				case "ltl":
					$prefix = "Lt";
					break;
				case "lvl":
					$prefix = "&#8364;";
					break;
				case "pln":
					$prefix = "z&#322;";
					break;
				case "ron":
					$prefix = "kr";
					break;
				case "hrk":
					$prefix = "kn";
					break;
				case "rub":
					$prefix = "&#1088;&#1091;&#1073;";
					break;
				case "try":
					$prefix = "TL";
					break;
				case "php":
					$prefix = "Php";
					break;
				case "cop":
					$prefix = "$";
					break;
				case "ars":
					$prefix = "$";
					break;
				default: $prefix = "$"; 
			}
		}
		
		$this->EE->number->set_prefix($prefix);
		
		$this->EE->load->library('services_json');
		$this->EE->load->library('curl');
		
		$api_key = ($this->EE->TMPL->fetch_param('api_key')) ? '?key='.$this->EE->TMPL->fetch_param('api_key') : '';

		if ($json = $this->EE->curl->simple_get("http://xurrency.com/api/".$currency."/".$new_currency."/".$number.$api_key))
		{
			$obj = json_decode($json);

			if (is_object($obj) 
				&& isset($obj->{'result'}) 
				&& isset($obj->{'status'}) 
				&& $obj->{'status'} =="ok" 
				&& isset($obj->{'result'}->{'value'})
				)
			{
				return $this->EE->number->format($obj->{'result'}->{'value'});
			}
		}
		
		return $this->EE->number->format($number); 
	}

	public function view_download_link()
	{
		$this->EE->load->library('encrypt');
		
		$link = $this->EE->TMPL->fetch_param('template');
		
		if ( ! $this->EE->TMPL->fetch_param('file'))
		{
			return show_error($this->EE->lang->line('download_url_not_specified'));
		}
		else
		{
			$link .= rawurlencode(base64_encode($this->EE->encrypt->encode($this->EE->TMPL->fetch_param('file'))));
		}
		
		if ($member_id = $this->EE->TMPL->fetch_param('member_id'))
		{
			if (in_array($member_id, array('{logged_in_member_id}', '{member_id}', 'CURRENT_USER')))
			{
				$member_id = $this->EE->session->userdata('member_id');
			}
			
			$link .= '/'.rawurlencode(base64_encode($this->EE->encrypt->encode($member_id)));
		}
		
		return $link; 
	}

	public function get_download_link()
	{
		$file = NULL; 
		
		$path = NULL;
		
		if ($this->EE->TMPL->fetch_param('field') && $this->EE->TMPL->fetch_param('entry_id'))
		{
			$this->EE->load->model(array('cartthrob_field_model', 'cartthrob_entries_model', 'tools_model'));
			
			$entry = $this->EE->cartthrob_entries_model->entry($this->EE->TMPL->fetch_param('entry_id'));
			
			$this->EE->load->helper('array');
			// @NOTE if the developer has assigned an entry id and a field, but there's nothing IN the field,  then the path doesn't get set, and no debug information is output, because path, below would be set to NULL
			if ($path = element($this->EE->TMPL->fetch_param('field'), $entry))
			{
				$this->EE->load->library('paths');
				
				$path = $this->EE->paths->parse_file_server_paths($path);
				
				$this->EE->TMPL->tagparams['file'] = $path;
				$this->EE->TMPL->tagparams['free_file'] = $path;
				
			}
		}
		
		if (bool_string($this->EE->TMPL->fetch_param('debug')) && $this->EE->TMPL->fetch_param('file') )
		{
			$this->EE->load->library('cartthrob_file');
			return $this->EE->cartthrob_file->file_debug($this->EE->TMPL->fetch_param('file')); 
		}
		
		
		foreach ($this->EE->TMPL->tagparams as $key => $value)
		{
			if ($value !== '' || $value !== FALSE)
			{
				$this->EE->load->library('encrypt');
				
				switch ($key)
				{
					case 'member_id':
						if (in_array($value, array('{logged_in_member_id}', '{member_id}', 'CURRENT_USER')))
						{
							$value = $this->EE->session->userdata('member_id');
						}
						$member_id = rawurlencode(base64_encode($this->EE->encrypt->encode(sanitize_number($value))));
						if (isset($this->EE->TMPL->tagparams['free_file']))
						{
							unset($this->EE->TMPL->tagparams['free_file']); 
						}
						break;
					case 'group_id':
						if (in_array($value, array('{logged_in_group_id}', '{group_id}')))
						{
							$value = $this->EE->session->userdata('group_id');
						}
						$group_id = rawurlencode(base64_encode($this->EE->encrypt->encode(sanitize_number($value))));
						if (isset($this->EE->TMPL->tagparams['free_file']))
						{
							unset($this->EE->TMPL->tagparams['free_file']); 
						}
						break;
					case 'language':
						$language = $value;
						break;
					case 'free_file':
						
						$file = '&FI='. rawurlencode(base64_encode($this->EE->encrypt->encode('FI'.$value)));
						break;
					case 'file':
						$file = '&FP='. rawurlencode(base64_encode($this->EE->encrypt->encode('FP'.$value)));
						break;
				}
			}
		}
		
		if (bool_string($this->EE->TMPL->fetch_param('debug')))
		{
			$this->EE->load->library('cartthrob_file');
			$this->EE->cartthrob_file->file_debug($file);
		}
 
		
		$download_url = $this->EE->functions->fetch_site_index(0, 0).QUERY_MARKER.'ACT='.$this->EE->functions->insert_action_ids($this->EE->functions->fetch_action_id('Cartthrob', 'download_file_action')).$file; 

		if (isset($member_id))
		{
			$download_url .="&MI=". $member_id; 
		}
		if (isset($group_id))
		{
			$download_url .="&GI=". $group_id; 
		}
		if (isset($language))
		{
			$download_url .="&L=".$language; 
		}
		return $download_url; 
	}
	/**
	 * Formats a number
	 *
	 * @access public
	 * @param int $this->EE->TMPL->fetch_param('number')
	 * @param int $this->EE->TMPL->fetch_param('decimals')
	 * @param string $this->EE->TMPL->fetch_param('dec_point')
	 * @param string $this->EE->TMPL->fetch_param('thousands_sep')
	 * @param string $this->EE->TMPL->fetch_param('prefix')
	 * @return string
	 * @since 1.0.0
	 * @author Rob Sanchez, Chris Newton, Chris Barrett
	**/
	public function view_formatted_number()
	{
		$this->EE->load->library('number');
		
		return $this->EE->number->format($this->EE->TMPL->fetch_param('number'));
	}

	public function view_country_name()
	{
		$this->EE->load->library('locales');
		
		$countries = $this->EE->locales->all_countries();
		
		return ($this->EE->TMPL->fetch_param('country_code') && isset($countries[$this->EE->TMPL->fetch_param('country_code')])) ? $countries[$this->EE->TMPL->fetch_param('country_code')] : '';
	}

	public function view_decrypted_string()
	{
		$this->EE->load->library('encrypt');
		
		if ( ! $this->EE->TMPL->fetch_param('string'))
		{
			return ''; 
		}
		
		return $this->EE->encrypt->decode(base64_decode(rawurldecode($this->EE->TMPL->fetch_param('string'))), $this->EE->TMPL->fetch_param('key'));
	}

	public function view_encrypted_string()
	{
		$this->EE->load->library('encrypt');
		
		if ( ! $this->EE->TMPL->fetch_param('string'))
		{
			return ''; 
		}
		
		return rawurlencode(base64_encode($this->EE->encrypt->encode($this->EE->TMPL->fetch_param('string'), $this->EE->TMPL->fetch_param('key')))); 
	}

	/**
	 * format_phone
	 *
	 * returns an array of phone parts
	 * @param string $phone 
	 * @return string formatted string | array of number parts
	 * @author Chris Newton
	 * @since 1.0
	 * @access protected
	 */
	public function view_formatted_phone_number() 
	{
		if ( ! $this->EE->TMPL->fetch_param('number'))
		{
			return ''; 
		}
		
		$return = get_formatted_phone($this->EE->TMPL->fetch_param('number'));

		$output = '';
		
		if ($return['international'])
		{
			$output .= $return['international'].'-';
		}
		
		if ($return['area_code'])
		{
			$output .= $return['area_code'].'-';
		}
		
		if ($return['prefix'])
		{
			$output .= $return['prefix'].'-';
		}
		
		if ($return['suffix'])
		{
			$output .= $return['suffix'];
		}
		
		return $output; 
		
  	}

	/**
	 * view_setting
	 *
	 * returns selected settings from the backend. 
	 *
	 * @return string
	 * @author Chris Newton
	 * @since 1.0
	 * @access public
	 **/
	function view_setting()
	{
		foreach ($this->EE->TMPL->tagparams as $key => $value)
		{
			switch ($key)
			{
				case ! $key:
				case ! bool_string($value);
					break;
				case 'prefix':
				case 'number_prefix':
					return $this->EE->cartthrob->store->config('number_format_defaults_prefix');
				case 'country':
					return $this->EE->cartthrob->store->config('default_location', 'country_code');
				case 'country_code':
				case 'state':
				case 'region':
				case 'zip':
					return $this->EE->cartthrob->store->config('default_location', $key);
				case 'member_id':
					return $this->EE->cartthrob->store->config('default_member_id');			
				case 'thousands_sep':
				case 'thousands_separator':
					return $this->EE->cartthrob->store->config('number_format_defaults_thousands_sep');
				case 'prefix_position':
					return $this->EE->cartthrob->store->config('number_format_defaults_prefix_position');
				case 'decimal':
				case 'decimal_point':
					return $this->EE->cartthrob->store->config('number_format_defaults_dec_point');
				case 'decimal_precision':
					return $this->EE->cartthrob->store->config('number_format_defaults_decimals');
				case 'currency_code':
					return $this->EE->cartthrob->store->config('number_format_defaults_currency_code');
				case 'shipping_option':
				case 'selected_shipping_option':
					return $this->EE->cartthrob->cart->shipping_info('shipping_option');
				default:
					return $this->EE->cartthrob->store->config($key);
			}
		}
		
		return '';
	}
	
	public function vaults()
	{
		$this->EE->load->model('vault_model');
		
		$variables = array();
		
		$params = array();
		
		if ($this->EE->TMPL->fetch_param('id'))
		{
			$params['id'] = (strstr($this->EE->TMPL->fetch_param('id'), '|') !== FALSE) ? explode('|', $this->EE->TMPL->fetch_param('id')) : $this->EE->TMPL->fetch_param('id');
		}
		
		if ($this->EE->TMPL->fetch_param('order_id'))
		{
			$params['order_id'] = (strstr($this->EE->TMPL->fetch_param('order_id'), '|') !== FALSE) ? explode('|', $this->EE->TMPL->fetch_param('order_id')) : $this->EE->TMPL->fetch_param('order_id');
		}
		
		if ($this->EE->TMPL->fetch_param('member_id'))
		{
			if (in_array($this->EE->TMPL->fetch_param('member_id'), array('CURRENT_USER', '{member_id}', '{logged_in_member_id}')))
			{
				$params['member_id'] = $this->EE->session->userdata('member_id');
			}
			else
			{
				$params['member_id'] = (strstr($this->EE->TMPL->fetch_param('member_id'), '|') !== FALSE) ? explode('|', $this->EE->TMPL->fetch_param('member_id')) : $this->EE->TMPL->fetch_param('member_id');
			}
		}
		
		//default to current member's vaults if no other params are specified
		if ( ! $params)
		{
			$params = array('member_id' => $this->EE->session->userdata('member_id'));
		}
		
		//@TODO add pagination
		
		$params['limit'] = ($this->EE->TMPL->fetch_param('limit')) ? $this->EE->TMPL->fetch_param('limit') : 100;
		
		$variables = $this->EE->vault_model->get_vaults($params);
		
		if ( ! $variables)
		{
			return $this->EE->TMPL->no_results();
		}
		
		return $this->EE->template_helper->parse_variables($variables);
	}
	
	public function subscriptions()
	{
		$this->EE->load->model('subscription_model');
		$this->EE->load->library('number');
		
		$variables = array();
		
		$params = array();
		
		if ($this->EE->TMPL->fetch_param('id'))
		{
			$params['id'] = (strstr($this->EE->TMPL->fetch_param('id'), '|') !== FALSE) ? explode('|', $this->EE->TMPL->fetch_param('id')) : $this->EE->TMPL->fetch_param('id');
		}
		
		if ($this->EE->TMPL->fetch_param('order_id'))
		{
			$params['order_id'] = (strstr($this->EE->TMPL->fetch_param('order_id'), '|') !== FALSE) ? explode('|', $this->EE->TMPL->fetch_param('order_id')) : $this->EE->TMPL->fetch_param('order_id');
		}
		
		if ($this->EE->TMPL->fetch_param('member_id'))
		{
			if (in_array($this->EE->TMPL->fetch_param('member_id'), array('CURRENT_USER', '{member_id}', '{logged_in_member_id}')))
			{
				$params['member_id'] = $this->EE->session->userdata('member_id');
			}
			else
			{
				$params['member_id'] = (strstr($this->EE->TMPL->fetch_param('member_id'), '|') !== FALSE) ? explode('|', $this->EE->TMPL->fetch_param('member_id')) : $this->EE->TMPL->fetch_param('member_id');
			}
		}
		
		//default to current member's vaults if no other params are specified
		if ( ! $params && $this->EE->session->userdata('group_id') != 1)
		{
			$params = array('member_id' => $this->EE->session->userdata('member_id'));
		}
		
		//@TODO add pagination
		
		$params['limit'] = ($this->EE->TMPL->fetch_param('limit')) ? $this->EE->TMPL->fetch_param('limit') : 100;
		
		if (! $this->EE->TMPL->fetch_param('status'))
		{
			$this->EE->db->where("status !=", 'closed');
		}
		else
		{
			$params['status'] = $this->EE->TMPL->fetch_param('status'); 
			
		}
		
		if (is_array($this->EE->TMPL->tagparams))
		{
			$params = array_merge($this->EE->TMPL->tagparams, $params); 
		}
		$data = $this->EE->subscription_model->get_subscriptions($params);
		
 		if ( empty($params['member_id']) && $this->EE->session->userdata('group_id') != 1)
		{
			return $this->EE->TMPL->no_results();
		}
		
		$this->EE->load->model('order_model'); 
		foreach ($data as $k => &$row)
		{
			$item = _unserialize($row['serialized_item'], TRUE);
			
			if (!empty($row['plan_id']))
			{
 				$plan = $this->EE->subscription_model->get_plan($row['plan_id']);
				if (!$plan)
				{
					// plan doesn't exist anymore
					unset($data[$k]); 
					continue; 
				}
				else
				{
 
					if (isset($plan['id']))
					{
						unset($plan['id']); 
					}
					if (array_key_exists('used_total_occurrences', $plan))
					{
						unset($plan['used_total_occurrences']);
					}
					if (array_key_exists('used_trial_occurrences', $plan))
					{
						unset($plan['used_trial_occurrences']);
					}
					if (isset($plan['status']))
					{
						unset($plan['status']);
					}
 
					if ( empty($row['name']))
					{
						if (!empty( $plan['name'] ))
						{
		 				 $row['name'] =  $plan['name'];
						}					
					}
					
					$row = array_merge($row, $plan); 
					
				}

 			}
			$row['entry_id'] = isset($item['product_id']) ? $item['product_id'] : '';

			if (isset($row['permissions']))
			{
				$perms =  @unserialize(base64_decode($row['permissions']));
				if (is_array($perms))
				{
					$row['permissions'] = implode("|", $perms); 
				} 
				else
				{
					$row['permissions'] = $perms; 
				}
			}
			
			if ($row['start_date'] === "0")
			{
				$row['start_date'] = NULL; 
 			}
			if ($row['end_date'] === "0")
			{
				$row['end_date'] = NULL; 
 			}
			if ($row['last_bill_date'] === "0")
			{
				$row['last_bill_date'] = NULL;
 			}

			$last_billing = ($row['last_bill_date']) ? $row['last_bill_date'] : $row['start_date'];
			$last_billing = ($last_billing) ? $last_billing : strtotime("now");
				
			$date_string = @date('Y-m-d', $last_billing)  . "+  ". $row['interval_length'].  "  ".$row['interval_units']; 

			$row['next_billing_date'] = strtotime($date_string);
			
			if (array_key_exists('price', $row))
			{
				$row['price_numeric'] = $row['price']; 
				$row['price'] = $this->EE->number->format($row['price']); 
				//$row['last_bill_date'] = NULL; 
 			}

			unset($row['serialized_item']);
			//@TODO need to delete rows if there's no order to back it up'
		}

		$this->EE->load->library('data_filter');

		$order_by = ($this->EE->TMPL->fetch_param('order_by')) ? $this->EE->TMPL->fetch_param('order_by') : $this->EE->TMPL->fetch_param('orderby');

 		$this->EE->data_filter->sort($data, $order_by, $this->EE->TMPL->fetch_param('sort'));
		$this->EE->data_filter->limit($data, $this->EE->TMPL->fetch_param('limit'), $this->EE->TMPL->fetch_param('offset'));

		$this->EE->template_helper->apply_search_filters($data);

		if ( ! $data)
		{
			return $this->EE->TMPL->no_results();
		}
		return $this->EE->template_helper->parse_variables($data);
	}

	public function has_subscription_permission()
	{
		if ( ! $this->EE->session->userdata('member_id'))
		{
			return $this->EE->TMPL->no_results();
		}
		
		$params = array();
 		
		if (in_array($this->EE->TMPL->fetch_param('member_id'), array('CURRENT_USER', '{member_id}', '{logged_in_member_id}')))
		{
			$params['member_id'] = $this->EE->session->userdata('member_id');
		}
		else
		{
			$params['member_id'] = (strstr($this->EE->TMPL->fetch_param('member_id'), '|') !== FALSE) ? explode('|', $this->EE->TMPL->fetch_param('member_id')) : $this->EE->TMPL->fetch_param('member_id');
		}
		
		if (empty($params['member_id']))
		{
			$params['member_id'] = $this->EE->session->userdata('member_id');
		}
		$this->EE->load->model('subscription_model');
		$this->EE->db->where("status", 'open');
		$this->EE->db->or_where("end_date >", $this->EE->localize->now);
		
		$data = NULL; 
		
		$data = $this->EE->subscription_model->get_subscriptions($params);
		
		if ( ! $data)
		{
			return $this->EE->TMPL->no_results();
		}
		else
		{
			foreach ($data as $key => $value)
			{
				$params['sub_id'][] = $value['id']; 
			}
			
			$this->EE->load->model('permissions_model');

			if ($this->EE->TMPL->fetch_param('permissions'))
			{
				$permissions = explode('|',$this->EE->TMPL->fetch_param('permissions'));
				$params['permission'] = $permissions; 
				$query = $this->EE->permissions_model->get($params, 1);
			}
			else
			{
				$query = $this->EE->permissions_model->get($params, 1);
			}
			
			if ( ! empty($query))
			{
				//single tag
				if ( ! $this->EE->TMPL->tagdata)
				{
					return 1; 
				}
				return $this->EE->TMPL->tagdata;
			}
			else
			{
				return $this->EE->TMPL->no_results();
			}
		}
	
	
		return $this->EE->TMPL->no_results();
	}
	public function has_permission()
	{
		if ( ! $this->EE->session->userdata('member_id'))
		{
			return $this->EE->TMPL->no_results();
		}
		
		$params = array();
 		
		if (in_array($this->EE->TMPL->fetch_param('member_id'), array('CURRENT_USER', '{member_id}', '{logged_in_member_id}')))
		{
			$params['member_id'] = $this->EE->session->userdata('member_id');
		}
		else
		{
			$params['member_id'] = (strstr($this->EE->TMPL->fetch_param('member_id'), '|') !== FALSE) ? explode('|', $this->EE->TMPL->fetch_param('member_id')) : $this->EE->TMPL->fetch_param('member_id');
		}
		
		if (empty($params['member_id']))
		{
			$params['member_id'] = $this->EE->session->userdata('member_id');
		}
		
		// checking to see if there's a sub id. if the sub is inactive, the permission is irrelevant.
 		if ($this->EE->TMPL->fetch_param('sub_id'))
		{
			$params['sub_id'] = (strstr($this->EE->TMPL->fetch_param('sub_id'), '|') !== FALSE) ? explode('|', $this->EE->TMPL->fetch_param('sub_id')) : $this->EE->TMPL->fetch_param('sub_id');

 			$this->EE->load->model('subscription_model');
			$this->EE->db->where("status", 'open');
			$data = NULL; 
			if (!is_array($params['sub_id']) && strtolower($params['sub_id']) == "any")
			{
				unset($params['sub_id']); 
				$data = $this->EE->subscription_model->get_subscriptions($params);
				unset($params['member_id']); 
  			}
			else
			{
				$data = $this->EE->subscription_model->get_subscriptions($params);
			}
			if ( ! $data)
			{
				return $this->EE->TMPL->no_results();
			}
			else
			{
					
				foreach ($data as $key => $value)
				{
					$params['sub_id'][] = $value['id']; 
				}

				$this->EE->load->model('permissions_model');

				if ($this->EE->TMPL->fetch_param('permissions'))
				{
					$permissions = explode('|',$this->EE->TMPL->fetch_param('permissions'));
					$params['permission'] = $permissions; 
					$query = $this->EE->permissions_model->get($params, 1);
				}
				else
				{
					$query = $this->EE->permissions_model->get($params, 1);
				}

				if ( ! empty($query))
				{
					//single tag
					if ( ! $this->EE->TMPL->tagdata)
					{
						return 1; 
					}
					return $this->EE->TMPL->tagdata;
				}
				else
				{
					return $this->EE->TMPL->no_results();
				}
			}
		}
		
		if ($this->EE->TMPL->fetch_param('permissions'))
		{
			$permissions = explode('|',$this->EE->TMPL->fetch_param('permissions'));
			
 			foreach($permissions as $key => $value)
			{
				$params['permission'] = $value; 
				$this->EE->load->model('permissions_model');
				
				$query = $this->EE->permissions_model->get($params, 1);
				
				if ( ! empty($query))
				{
					//single tag
					if ( ! $this->EE->TMPL->tagdata)
					{
						return 1; 
					}
					return $this->EE->TMPL->tagdata;
				}
			}
 		}
		return $this->EE->TMPL->no_results();
	}
	
	public function permissions()
	{
		$this->EE->load->model('permissions_model');
		
		$variables = array();
		
		$params = array();
		
		if ($this->EE->TMPL->fetch_param('id'))
		{
			$params['id'] = (strstr($this->EE->TMPL->fetch_param('id'), '|') !== FALSE) ? explode('|', $this->EE->TMPL->fetch_param('id')) : $this->EE->TMPL->fetch_param('id');
		}
		

		if ($this->EE->TMPL->fetch_param('item_id'))
		{
			$params['item_id'] = (strstr($this->EE->TMPL->fetch_param('item_id'), '|') !== FALSE) ? explode('|', $this->EE->TMPL->fetch_param('item_id')) : $this->EE->TMPL->fetch_param('item_id');
		}
		
		if ($this->EE->TMPL->fetch_param('order_id'))
		{
			$params['order_id'] = (strstr($this->EE->TMPL->fetch_param('order_id'), '|') !== FALSE) ? explode('|', $this->EE->TMPL->fetch_param('order_id')) : $this->EE->TMPL->fetch_param('order_id');
		}
		
		if ($this->EE->TMPL->fetch_param('member_id'))
		{
			if (in_array($this->EE->TMPL->fetch_param('member_id'), array('CURRENT_USER', '{member_id}', '{logged_in_member_id}')))
			{
				$params['member_id'] = $this->EE->session->userdata('member_id');
			}
			else
			{
				$params['member_id'] = (strstr($this->EE->TMPL->fetch_param('member_id'), '|') !== FALSE) ? explode('|', $this->EE->TMPL->fetch_param('member_id')) : $this->EE->TMPL->fetch_param('member_id');
			}
		}
		
		if ($this->EE->TMPL->fetch_param('sub_id'))
		{
			$sub_param = (strstr($this->EE->TMPL->fetch_param('sub_id'), '|') !== FALSE) ? explode('|', $this->EE->TMPL->fetch_param('sub_id')) : $this->EE->TMPL->fetch_param('sub_id');
			
			if (!is_array($sub_param))
			{
				$subs[]=$sub_param; 
			}
			else
			{
				$subs = $sub_param;
			}

			$use = array(); 
			// look through the subs model to make sure this subscription is open. 
			// if it's not open, then don't return anything
			$this->EE->load->model('subscription_model'); 
			foreach ($subs as $id)
			{
				$s = $this->EE->subscription_model->get_subscription($id); 
				if (isset($s['status']) && $s['status'] == "open")
				{
					$use[] = $id; 
				}
			}
			if (empty($use))
			{
				return $this->EE->TMPL->no_results();
			}
			$params['sub_id'] = $use; 
		}
		
		//default to current member's permissions if no other params are specified
		if ( ! $params)
		{
			$params = array('member_id' => $this->EE->session->userdata('member_id'));
		}
		
		$params['limit'] = ($this->EE->TMPL->fetch_param('limit')) ? $this->EE->TMPL->fetch_param('limit') : 100;
		
		$variables = $this->EE->permissions_model->get($params);
		
		if (empty($variables))
		{
			return $this->EE->TMPL->no_results();
		}
		
		return $this->EE->template_helper->parse_variables($variables);
	}
	
	public function years()
	{
		$years = (is_numeric($this->EE->TMPL->fetch_param('years'))) ? $this->EE->TMPL->fetch_param('years') : 5;
		
		$start_year = (is_numeric($this->EE->TMPL->fetch_param('start_year'))) ? $this->EE->TMPL->fetch_param('start_year') : date('Y');
		
		$final_year = $start_year + $years;
		
		$data = array();
		
		for ($year = $start_year; $year < $final_year; $year++)
		{
			$data[] = array('year' => $year);
		}
		
		return $this->EE->template_helper->parse_variables($data);
	}
	public function month_select()
	{
		$selected = NULL; 
		
		$attrs = array(); 
		$this->EE->load->helper('form');
 		$data = array(
			"01"	=> $this->EE->lang->line('january'),
			"02"	=> $this->EE->lang->line('february'),
			"03"	=> $this->EE->lang->line('march'),
			"04"	=> $this->EE->lang->line('april'),
			"05"	=> $this->EE->lang->line('may'),
			"06"	=> $this->EE->lang->line('june'),
			"07"	=> $this->EE->lang->line('july'),
			"08"	=> $this->EE->lang->line('august'),
			"09"	=> $this->EE->lang->line('september'),
			"10"	=> $this->EE->lang->line('october'),
			"11"	=> $this->EE->lang->line('november'),
			"12"	=> $this->EE->lang->line('december'),
			);
 		
 		
		if ($this->EE->TMPL->fetch_param('id'))
		{
			$attrs['id'] = $this->EE->TMPL->fetch_param('id');
		}
		
		if ($this->EE->TMPL->fetch_param('class'))
		{
			$attrs['class'] = $this->EE->TMPL->fetch_param('class');
		}
		
		if ($this->EE->TMPL->fetch_param('onchange'))
		{
			$attrs['onchange'] = $this->EE->TMPL->fetch_param('onchange');
		}
		
		$extra = '';
		
		if ($attrs)
		{
			$extra .= _attributes_to_string($attrs);
		}
		
		if ($this->EE->TMPL->fetch_param('extra'))
		{
			if (substr($this->EE->TMPL->fetch_param('extra'), 0, 1) != ' ')
			{
				$extra .= ' ';
			}
			
			$extra .= $this->EE->TMPL->fetch_param('extra');
		}
		
		$name = ($this->EE->TMPL->fetch_param('name')?  $this->EE->TMPL->fetch_param('name') : "expiration_month"); 
		
		if ($this->EE->TMPL->fetch_param('selected'))
		{
			$selected = $this->EE->TMPL->fetch_param('selected'); 
		}
		
		if (!$selected || !array_key_exists($selected, $data))
		{
			$selected = @date("m"); 
		}
		
		return form_dropdown(
			$name, 
			$data,
			$selected,
			$extra
		);
	}
	public function year_select()
	{
		$selected= NULL; 
		
		$this->EE->load->helper('form');
		
		$years = (is_numeric($this->EE->TMPL->fetch_param('years'))) ? $this->EE->TMPL->fetch_param('years') : 5;
		
		$start_year = (is_numeric($this->EE->TMPL->fetch_param('start_year'))) ? $this->EE->TMPL->fetch_param('start_year') : date('Y');
		
		$final_year = $start_year + $years;
		
		$data = array();
		
		for ($year = $start_year; $year < $final_year; $year++)
		{
			$data[$year] = $year;
		}
		
		$attrs = array();
		
		if ($this->EE->TMPL->fetch_param('id'))
		{
			$attrs['id'] = $this->EE->TMPL->fetch_param('id');
		}
		
		if ($this->EE->TMPL->fetch_param('class'))
		{
			$attrs['class'] = $this->EE->TMPL->fetch_param('class');
		}
		
		if ($this->EE->TMPL->fetch_param('onchange'))
		{
			$attrs['onchange'] = $this->EE->TMPL->fetch_param('onchange');
		}
		
		$extra = '';
		
		if ($attrs)
		{
			$extra .= _attributes_to_string($attrs);
		}
		
		if ($this->EE->TMPL->fetch_param('extra'))
		{
			if (substr($this->EE->TMPL->fetch_param('extra'), 0, 1) != ' ')
			{
				$extra .= ' ';
			}
			
			$extra .= $this->EE->TMPL->fetch_param('extra');
		}
		
		$name = ($this->EE->TMPL->fetch_param('name')?  $this->EE->TMPL->fetch_param('name') : "expiration_year"); 
 		
		if ($this->EE->TMPL->fetch_param('selected'))
		{
			$selected = $this->EE->TMPL->fetch_param('selected'); 
		}
		
		if ( ! $selected )
		{
			$selected = @date("Y"); 
		}
		
		return form_dropdown(
			$name,
			$data,
			$selected,
			$extra
		);
	}

	public function years_select()
	{
		return $this->year_select();
	}
	
	
	/* protected methods */
}

/* End of file mod.cartthrob.php */
/* Location: ./system/expressionengine/third_party/cartthrob/mod.cartthrob.php */