<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Order_model extends CI_Model
{
	public function __construct()
	{
		$this->load->model('cartthrob_field_model');
		$this->load->model('cartthrob_entries_model');
		$this->load->model('cartthrob_settings_model');
		$this->load->helper('data_formatting');
	}
	
	public function create_order($order_data)
	{
		$this->load->library('cartthrob_loader');
		
		if ( ! $this->config->item('cartthrob:orders_channel'))
		{
			return FALSE;
		}
		
		$this->load->model('cartthrob_members_model');
		
		$order_data['channel_id'] = $this->config->item('cartthrob:orders_channel');
		
		$data = $this->convert_order_data($order_data);
		
		$data['status'] = ($this->config->item('cartthrob:orders_processing_status')) ? $this->config->item('cartthrob:orders_processing_status') : 'closed';
		$data['author_id'] = (!empty($order_data['member_id']) ? $order_data['member_id'] : $this->cartthrob_members_model->get_member_id());
		
		if ( ! empty($order_data['expiration_date']))
		{
			$data['expiration_date'] = $this->localize->now + ($order_data['expiration_date']*24*60*60);
		}
		
		$reserved_keys = array('items');
		
		foreach ($reserved_keys as $key)
		{
			unset($data[$key]);
		}
		
		// where the order will be saved
		$site_id = $this->db->select('site_id')->where('channel_id', $order_data['channel_id'])->get('channels')->row('site_id');
		$data['site_id']  = $site_id; 
		
		if ($this->config->item('cartthrob:orders_sequential_order_numbers'))
		{
			$last_order_number = $this->config->item('cartthrob:last_order_number');
			
			if ($this->config->item('cartthrob:msm_show_all'))
			{
 				$this->db->where(array(
					'`key`' => 'last_order_number',
					'site_id' => $site_id,
				));
				$query = $this->db->get('cartthrob_settings'); 
				if ($query->num_rows())
				{
					$last_order_number = $query->row('value');
				}
			}
			
			//fallback to the old way if the setting isn't present
			if ($last_order_number === FALSE)
			{
				$order_number = 1;
				
				$query = $this->db->select('title')
						->from('channel_titles')
						->where('channel_id', $data['channel_id'])
						->where('site_id', $site_id)
						->like('title', $this->config->item('cartthrob:orders_title_prefix'), 'after')
						->like('title', $this->config->item('cartthrob:orders_title_suffix'), 'before')
						->order_by('entry_date', 'desc')
						->limit(1)
						->get();
				
				if ($query->num_rows())
				{
					$order_number = (int) str_replace(array($this->config->item('cartthrob:orders_title_prefix'), $this->config->item('cartthrob:orders_title_suffix')), '', $query->row('title')) + 1;
				}
			
				$this->db->insert('cartthrob_settings', array(
					'`key`' => 'last_order_number',
					'value' => $order_number,
					'site_id' => $site_id,
				));
			}
			else
			{
				if ($last_order_number == 0)
				{
					$this->db->where(array(
						'`key`' => 'last_order_number',
						'site_id' => $site_id,
					));
					
					if ($this->db->count_all_results('cartthrob_settings') === 0)
					{
						$this->db->insert('cartthrob_settings', array(
							'`key`' => 'last_order_number',
							'value' => 0,
							'site_id' => $site_id,
							'serialized' => 0,
						));
					}
				}
				
				$order_number = $last_order_number + 1;
				
				$this->db->where('`key`', 'last_order_number');
				$this->db->where('site_id', $site_id); 
 				$this->db->set('value', 'value+1', FALSE);
				$this->db->update('cartthrob_settings');
				
				/*
				$this->db->update('cartthrob_settings', array(
					'value' => $order_number,
				), array(
					'`key`' => 'last_order_number',
					'site_id' => $site_id,
				));
				*/ 
			}
			
			$data['title'] = $this->config->item('cartthrob:orders_title_prefix').$order_number.$this->config->item('cartthrob:orders_title_suffix');
			$data['url_title'] = $this->config->item('cartthrob:orders_url_title_prefix').$order_number.$this->config->item('cartthrob:orders_url_title_suffix');
			
			$entry_id = $this->cartthrob_entries_model->create_entry($data);
		}
		else if ($entry_id = $this->cartthrob_entries_model->create_entry($data))
		{
			$data = array(
				'title' => $this->config->item('cartthrob:orders_title_prefix').$entry_id.$this->config->item('cartthrob:orders_title_suffix'),
				'url_title' => $this->config->item('cartthrob:orders_url_title_prefix').$entry_id.$this->config->item('cartthrob:orders_url_title_suffix'),
			);
			
			$this->cartthrob_entries_model->update_entry($entry_id, $data);
		}
		
		if ($this->config->item('cartthrob:orders_items_field'))
		{
			// adding items to the order items, even if there's no field assigned. 
			$items = array();

			foreach ($this->cartthrob->cart->items() as $item)
			{
				$items[] = $this->create_order_item_row($item);
			}
			
			$this->update_order_items($entry_id, $items);

			$field_type = $this->cartthrob_field_model->get_field_type($this->config->item('cartthrob:orders_items_field'));

			if ($field_type === 'cartthrob_order_items')
			{
				if ($this->db->field_exists('field_id_'.$this->config->item('cartthrob:orders_items_field'), 'channel_data'))
				{
					$this->cartthrob_entries_model->update_entry($entry_id, array('field_id_'.$this->config->item('cartthrob:orders_items_field') => 1));
				}
			}
		}
		if (!empty($entry_id))
		{
			$data['entry_id'] = $entry_id; 
		}
		return $data;
	}
	
	protected function create_order_item_row(Cartthrob_item $item)
	{
		$row = array(
			'entry_id' => 		$item->product_id(),
			'title' => 			$item->title(),
			'site_id'	=> 		$item->site_id(),
			'quantity' => 		(float) $item->quantity(),
			'price' => 			(float) $item->price(),
			'price_plus_tax' => (float) $item->taxed_price(),
			'weight' => 		(float) $item->weight(),
			'shipping' => 		(float) $item->shipping(),
			'discount'	=> 		$item->discount(),
			'no_tax' => 		! $item->is_taxable(),
			'no_shipping' => 	! $item->is_shippable(),
			'entry_date'	=>	$this->localize->now,
		);

 		
		if (is_array($item->item_options()))
		{
			$configurator = array(); 
			if (is_array($item->meta('configuration')))
			{
				foreach ($item->meta('configuration') as $key => $value)
				{
					foreach ($value as $k => $v)
					{
						$configurator[$key.":".$k] = $v; 
					}
				}
			}
			$row = array_merge($row, $configurator); 
			$row = array_merge($row, $item->item_options());	
		}
		
		if ($item->sub_items())
		{
			foreach ($item->sub_items() as $i => $sub_item)
			{
				$sub_row = array(
					'entry_id' => 		$sub_item->product_id(),
					'title' => 			$sub_item->title(),
					'site_id'	=> 		$sub_item->site_id(),
					'quantity' => 		(float) $sub_item->quantity(),
					'price' => 			(float) $sub_item->price(),
					'price_plus_tax' => (float) $sub_item->taxed_price(),
					'weight' => 		(float) $sub_item->weight(),
					'shipping' => 		(float) $sub_item->shipping(),
					'discount'	=> 		$sub_item->discount(),
					'no_tax' => 		! $sub_item->is_taxable(),
					'no_shipping' => 	! $sub_item->is_shippable(),
				);

				if (is_array($sub_item->item_options()))
				{
					$sub_row = array_merge($sub_row, $sub_item->item_options());
				}
				
				$row['sub_items'][$i] = $this->create_order_item_row($sub_item);
			}
		}
		
		return $row;
	}
	
	public function update_order($entry_id, $order_data)
	{
		if ( ! $this->config->item('cartthrob:orders_channel'))
		{
			return FALSE;
		}
		
		return $this->cartthrob_entries_model->update_entry($entry_id, $this->convert_order_data($order_data));
	}
	
	public function get_order_items($order_ids, $entry_ids = array(), $member_ids = array(), $keep_extra = FALSE)
	{
		$this->load->helper('data_formatting');
		
		if ($order_ids)
		{
			if ( ! is_array($order_ids))
			{
				$this->db->where('order_id', $order_ids);
			}
			else
			{
				$this->db->where_in('order_id', $order_ids);
			}
		}
		
		if ($entry_ids)
		{
			if ( ! is_array($entry_ids))
			{
				$this->db->where('cartthrob_order_items.entry_id', $entry_ids);
			}
			else
			{
				$this->db->where_in('cartthrob_order_items.entry_id', $entry_ids);
			}
		}
		
		if ($member_ids)
		{
			$this->db->select('cartthrob_order_items.*')
				 ->join('channel_titles', 'channel_titles.entry_id = cartthrob_order_items.order_id');
			
			if ( ! is_array($member_ids))
			{
				$this->db->where('channel_titles.author_id', $member_ids);
			}
			else
			{
				$this->db->where_in('channel_titles.author_id', $member_ids);
			}
		}
		
		$query = $this->db->order_by('order_id, row_order', 'asc')
				  ->get('cartthrob_order_items');
		
		$order_items = $query->result_array();
		
		$query->free_result();
		
		foreach ($order_items as &$row)
		{
			$extra = _unserialize($row['extra'], TRUE);
			
			if ($keep_extra)
			{
				$row['extra'] = $extra;
			}
			else
			{
				foreach ($extra as $key => $value)
				{
					if ( ! isset($row[$key]))
					{
						$row[$key] = $value;
					}
				}
				
				unset($row['extra']);
			}
		}
		
		return $order_items;
	}
	public function update_order_items($entry_id, $data)
	{
		$original_data = array();

		foreach ($this->get_order_items($entry_id) as $_row)
		{
			$original_data[$_row['row_id']] = $_row;
		}

		$rows_to_keep = array();

		$default_keys = array('entry_id', 'title', 'quantity', 'price', 'price_plus_tax', 'weight', 'shipping', 'no_tax', 'no_shipping', 'site_id', 'entry_date');
		
		$special_keys = array('row_id', 'order_id', 'row_order');

		foreach ($data as $row_order => $row)
		{
			$insert = array(
				'order_id' => $entry_id,
				'row_order' => $row_order,
			);

			//get array values that are not default order item columns
			$extra = array_diff_key($row, array_flip(array_merge($default_keys, $special_keys)));
			
			foreach ($default_keys as $key)
			{
				$insert[$key] = (isset($row[$key])) ? $row[$key] : '';
			}
			
			$insert['extra'] = (count($extra) > 0) ? base64_encode(serialize($extra)) : '';
			
			if ( ! empty($row['row_id']))
			{
				if ($this->config->item('cartthrob:update_inventory_when_editing_order') && isset($original_data[$row['row_id']]))
				{
					$this->update_product_inventory($row, $original_data[$row['row_id']]);
				}

				$this->db->update('cartthrob_order_items', $insert, array('row_id' => $row['row_id']));
				
				$rows_to_keep[] = $row['row_id'];
			}
			else
			{
				$this->db->insert('cartthrob_order_items', $insert);
				
 				$id =$this->db->insert_id();
				$rows_to_keep[] = $id;

				/* 
				// shouldn't update the inventory here. it's a new item. the system should process inventory elsewhere since this is new. 
				// if we ever create orders outside of the regular flow... commenting this out is going to be a problem. 
				// we're not really "updating inventory" in this case. 
				// if we just adjust inventory here, which kind of makes sense, the problem is that there's some meta iventory admustments 
				// that happen elsewhere and emails that are sent for inventory modifications that aren't sent here. 
				// might want to create an inventory model or something
				if ($this->config->item('cartthrob:update_inventory_when_editing_order'))
				{
					$this->db->where('row_id', $id); 
					$this->db->limit('1'); 
					$query = $this->db->get('cartthrob_order_items');
				
					if ($query->result() and $query->num_rows() > 0)
					{
						$item = $query->row_array();
						$this->update_product_inventory($row, $item, $new_item = TRUE);
					}
					$query->free_result();
 					
				}
				*/ 
			}
		}

		foreach ($original_data as $row_id => $row)
		{
			if ( ! in_array($row_id, $rows_to_keep))
			{
				if ($this->config->item('cartthrob:update_inventory_when_editing_order'))
				{
					$new_row = $row;

					$new_row['quantity'] = 0;

					$this->update_product_inventory($new_row, $row);
				}

				$this->delete_order_item($row_id);
			}
		}
	}

	public function delete_order_item($row_id)
	{
		$this->db->delete('cartthrob_order_items', array('row_id' => $row_id));
	}
	
	public function delete_order_items($entry_ids)
	{
		if ( ! is_array($entry_ids))
		{
			$entry_ids = array($entry_ids);
		}

		$order_items = $this->get_order_items($entry_ids);
		
		foreach ($order_items as $row)
		{
			if ($this->config->item('cartthrob:update_inventory_when_editing_order'))
			{
				$new_row = $row;

				$new_row['quantity'] = 0;

				$this->update_product_inventory($new_row, $row);
			}

			$this->delete_order_item($row['row_id']);
		}
	}

	protected function update_product_inventory($row, $original_row, $new_item = FALSE)
	{
		if (empty($row['entry_id']))
		{
			return;
		}

		$difference = $row['quantity'] - $original_row['quantity'];

		if ($new_item)
		{
			$difference = $row['quantity']; 
			if (!empty($original_row['extra']))
			{
				$opts = _unserialize($original_row['extra']); 
				$original_row = array_merge($row, $opts); 
			}
			
		}
		if ($difference === 0)
		{
			return;
		}

		$default_keys = array('row_id', 'row_order', 'order_id', 'entry_id', 'title', 'quantity', 'price', 'price_plus_tax', 'weight', 'shipping', 'no_tax', 'no_shipping', 'site_id');

		$item_options = array_diff_key($original_row, array_flip($default_keys));

		$this->load->model('product_model');

		$this->product_model->reduce_inventory($row['entry_id'], $difference, $item_options);
	}
	
	private function convert_order_data($order_data)
	{
		$this->load->library('cartthrob_loader');
		
		$this->load->library('locales');
	
		$data = $order_data;
		
		$custom_data = $this->cartthrob->cart->custom_data();
		
		$fields = $this->cartthrob_field_model->get_fields_by_channel($this->config->item('cartthrob:orders_channel'));

		foreach ($fields as $field)
		{
			if ($this->input->post($field['field_name']) !== FALSE)
			{
				$data['field_id_'.$field['field_id']] = $this->input->post($field['field_name'], TRUE);
			}
			
			if (isset($custom_data[$field['field_name']]))
			{
				$data['field_id_'.$field['field_id']] = $custom_data[$field['field_name']];
			}
		}

		if ($this->config->item('cartthrob:orders_subtotal_field') && isset($order_data['subtotal']))
		{
			$data['field_id_'.$this->config->item('cartthrob:orders_subtotal_field')] = $order_data['subtotal'];
		}
		if ($this->config->item('cartthrob:orders_subtotal_plus_tax_field') && isset($order_data['subtotal_plus_tax']))
		{
			$data['field_id_'.$this->config->item('cartthrob:orders_subtotal_plus_tax_field')] = $order_data['subtotal_plus_tax'];
		}
		if ($this->config->item('cartthrob:orders_tax_field') && isset($order_data['tax']))
		{
			$data['field_id_'.$this->config->item('cartthrob:orders_tax_field')] = $order_data['tax'];
		}
		if ($this->config->item('cartthrob:orders_shipping_field') && isset($order_data['shipping']))
		{
			$data['field_id_'.$this->config->item('cartthrob:orders_shipping_field')] = $order_data['shipping'];
		}
		if ($this->config->item('cartthrob:orders_shipping_plus_tax_field') && isset($order_data['shipping_plus_tax']))
		{
			$data['field_id_'.$this->config->item('cartthrob:orders_shipping_plus_tax_field')] = $order_data['shipping_plus_tax'];
		}
		if ($this->config->item('cartthrob:orders_total_field') && isset($order_data['total']))
		{
			$data['field_id_'.$this->config->item('cartthrob:orders_total_field')] = $order_data['total'];
		}
		if ($this->config->item('cartthrob:orders_discount_field') && isset($order_data['discount']))
		{
			$data['field_id_'.$this->config->item('cartthrob:orders_discount_field')] = $order_data['discount'];
		}
		if ($this->config->item('cartthrob:orders_coupon_codes') && isset($order_data['coupon_codes']))
		{
			$data['field_id_'.$this->config->item('cartthrob:orders_coupon_codes')] = $order_data['coupon_codes'];
		}
		if ($this->config->item('cartthrob:orders_last_four_digits') && isset($order_data['last_four_digits']))
		{
			$data['field_id_'.$this->config->item('cartthrob:orders_last_four_digits')] = $order_data['last_four_digits'];
		}
		if ($this->config->item('cartthrob:orders_transaction_id') && isset($order_data['transaction_id']))
		{
			$data['field_id_'.$this->config->item('cartthrob:orders_transaction_id')] = $order_data['transaction_id'];
		}
		if ($this->config->item('cartthrob:orders_customer_name') && isset($order_data['customer_name']))
		{
			$data['field_id_'.$this->config->item('cartthrob:orders_customer_name')] = $order_data['customer_name'];
		}
		if ($this->config->item('cartthrob:orders_customer_email') && isset($order_data['customer_email']))
		{
			$data['field_id_'.$this->config->item('cartthrob:orders_customer_email')] = $order_data['customer_email'];
		}
		if ($this->config->item('cartthrob:orders_customer_ip_address') && isset($order_data['customer_ip_address']))
		{
			$data['field_id_'.$this->config->item('cartthrob:orders_customer_ip_address')] = $order_data['customer_ip_address'];
		}
		if ($this->config->item('cartthrob:orders_customer_phone') && isset($order_data['customer_phone']))
		{
			$data['field_id_'.$this->config->item('cartthrob:orders_customer_phone')] = $order_data['customer_phone'];
		}
		if ($this->config->item('cartthrob:orders_full_billing_address') && isset($order_data['full_billing_address']))
		{
			$data['field_id_'.$this->config->item('cartthrob:orders_full_billing_address')] = $order_data['full_billing_address'];
		}
		if ($this->config->item('cartthrob:orders_billing_first_name') && isset($order_data['billing_first_name']))
		{
			$data['field_id_'.$this->config->item('cartthrob:orders_billing_first_name')] = $order_data['billing_first_name'];
		}
		if ($this->config->item('cartthrob:orders_billing_last_name') && isset($order_data['billing_last_name']))
		{
			$data['field_id_'.$this->config->item('cartthrob:orders_billing_last_name')] = $order_data['billing_last_name'];
		}
		if ($this->config->item('cartthrob:orders_billing_company') && isset($order_data['billing_company']))
		{
			$data['field_id_'.$this->config->item('cartthrob:orders_billing_company')] = $order_data['billing_company'];
		}
		if ($this->config->item('cartthrob:orders_billing_address') && isset($order_data['billing_address']))
		{
			$data['field_id_'.$this->config->item('cartthrob:orders_billing_address')] = $order_data['billing_address'];
		}
		if ($this->config->item('cartthrob:orders_billing_address2') && isset($order_data['billing_address2']))
		{
			$data['field_id_'.$this->config->item('cartthrob:orders_billing_address2')] = $order_data['billing_address2'];
		}
		if ($this->config->item('cartthrob:orders_billing_city') && isset($order_data['billing_city']))
		{
			$data['field_id_'.$this->config->item('cartthrob:orders_billing_city')] = $order_data['billing_city'];
		}
		if ($this->config->item('cartthrob:orders_billing_state') && isset($order_data['billing_state']))
		{
			$data['field_id_'.$this->config->item('cartthrob:orders_billing_state')] = $order_data['billing_state'];
		}
		if ($this->config->item('cartthrob:orders_billing_zip') && isset($order_data['billing_zip']))
		{
			$data['field_id_'.$this->config->item('cartthrob:orders_billing_zip')] = $order_data['billing_zip'];
		}
		if ($this->config->item('cartthrob:orders_billing_country'))
		{
			if ($this->config->item('cartthrob:orders_convert_country_code'))
			{
				if (isset($order_data['billing_country_code']))
				{
					$order_data['billing_country'] = $this->locales->country_from_country_code($order_data['billing_country_code']);
				}
			}
			
			if (isset($order_data['billing_country']))
			{
				$data['field_id_'.$this->config->item('cartthrob:orders_billing_country')] = $order_data['billing_country'];
			}
		}
		if ($this->config->item('cartthrob:orders_country_code') && isset($order_data['country_code']))
		{
			$data['field_id_'.$this->config->item('cartthrob:orders_country_code')] = $order_data['country_code'];
		}
		if ($this->config->item('cartthrob:orders_full_shipping_address') && isset($order_data['full_shipping_address']))
		{
			$data['field_id_'.$this->config->item('cartthrob:orders_full_shipping_address')] = $order_data['full_shipping_address'];
		}
		if ($this->config->item('cartthrob:orders_shipping_first_name') && isset($order_data['shipping_first_name']))
		{
			$data['field_id_'.$this->config->item('cartthrob:orders_shipping_first_name')] = $order_data['shipping_first_name'];
		}
		if ($this->config->item('cartthrob:orders_shipping_last_name') && isset($order_data['shipping_last_name']))
		{
			$data['field_id_'.$this->config->item('cartthrob:orders_shipping_last_name')] = $order_data['shipping_last_name'];
		}
		if ($this->config->item('cartthrob:orders_shipping_company') && isset($order_data['shipping_company']))
		{
			$data['field_id_'.$this->config->item('cartthrob:orders_shipping_company')] = $order_data['shipping_company'];
		}
		if ($this->config->item('cartthrob:orders_shipping_address') && isset($order_data['shipping_address']))
		{
			$data['field_id_'.$this->config->item('cartthrob:orders_shipping_address')] = $order_data['shipping_address'];
		}
		if ($this->config->item('cartthrob:orders_shipping_address2') && isset($order_data['shipping_address2']))
		{
			$data['field_id_'.$this->config->item('cartthrob:orders_shipping_address2')] = $order_data['shipping_address2'];
		}
		if ($this->config->item('cartthrob:orders_shipping_city') && isset($order_data['shipping_city']))
		{
			$data['field_id_'.$this->config->item('cartthrob:orders_shipping_city')] = $order_data['shipping_city'];
		}
		if ($this->config->item('cartthrob:orders_shipping_state') && isset($order_data['shipping_state']))
		{
			$data['field_id_'.$this->config->item('cartthrob:orders_shipping_state')] = $order_data['shipping_state'];
		}
		if ($this->config->item('cartthrob:orders_shipping_zip') && isset($order_data['shipping_zip']))
		{
			$data['field_id_'.$this->config->item('cartthrob:orders_shipping_zip')] = $order_data['shipping_zip'];
		}
		if ($this->config->item('cartthrob:orders_shipping_country'))
		{
			if ($this->config->item('cartthrob:orders_convert_country_code'))
			{
				if (isset($order_data['shipping_country_code']))
				{
					$order_data['shipping_country'] = $this->locales->country_from_country_code($order_data['shipping_country_code']);
				}
			}
			
			if (isset($order_data['shipping_country']))
			{
				$data['field_id_'.$this->config->item('cartthrob:orders_shipping_country')] = $order_data['shipping_country'];
			}

		}
		if ($this->config->item('cartthrob:orders_shipping_country_code') && isset($order_data['shipping_country_code']))
		{
			$data['field_id_'.$this->config->item('cartthrob:orders_shipping_country_code')] = $order_data['shipping_country_code'];
		}
		if ($this->config->item('cartthrob:orders_shipping_option') && isset($order_data['shipping_option']))
		{
			$data['field_id_'.$this->config->item('cartthrob:orders_shipping_option')] = $order_data['shipping_option'];
		}
		if ($this->config->item('cartthrob:orders_error_message_field') && isset($order_data['error_message']))
		{
			$data['field_id_'.$this->config->item('cartthrob:orders_error_message_field')] = $order_data['error_message'];
		}
		if ($this->config->item('cartthrob:orders_language_field'))
		{
			$data['field_id_'.$this->config->item('cartthrob:orders_language_field')] = ($this->input->cookie('language')) ? $this->input->cookie('language', TRUE) : $this->session->userdata('language');
		}
		if ($this->config->item('cartthrob:orders_payment_gateway') && isset($order_data['payment_gateway']))
		{
			$data['field_id_'.$this->config->item('cartthrob:orders_payment_gateway')] = $order_data['payment_gateway'];
		}
		if ($this->config->item('cartthrob:orders_site_id') && isset($order_data['site_id']))
		{
			$data['field_id_'.$this->config->item('cartthrob:orders_site_id')] = $order_data['site_id'];
		}
		
		if ($this->config->item('cartthrob:orders_subscription_id') && isset($order_data['subscription_id']))
		{
			$data['field_id_'.$this->config->item('cartthrob:orders_subscription_id')] = $order_data['subscription_id'];
		}
		if ($this->config->item('cartthrob:orders_vault_id') && isset($order_data['vault_id']))
		{
			$data['field_id_'.$this->config->item('cartthrob:orders_vault_id')] = $order_data['vault_id'];
		}
		
		$data['channel_id'] = $this->config->item('cartthrob:orders_channel');
		
		return $data;
	}
	public function get_sub_id($id)
	{
		$query = $this->db->select('sub_id')
				->from('cartthrob_subscriptions')
				->where('id', $id)
				->limit(1)
				->get();
		
		if ($query->num_rows())
		{
			return $query->row('sub_id');
		}
		return NULL;
	}
	public function get_order_cart_id($entry_id)
	{
		$query = $this->db->select('cart_id')
				->from('cartthrob_status')
				->where('entry_id', $entry_id)
				->limit(1)
				->get();
		
		if ($query->num_rows())
		{
			return $query->row('cart_id');
		}
		return NULL; 
		
	} 
	public function get_order_id_from_session($session_id)
	{
		$query = $this->db->select('entry_id')
				->from('cartthrob_status')
				->where('session_id', $session_id)
				->limit(1)
				->get();
		
		if ($query->num_rows())
		{
			return $query->row('entry_id');
		}
		return NULL; 
		
	}

	public function can_update_order($entry_id)
	{
		$order = $this->get_order($entry_id);

		if ($order === FALSE)
		{
			return FALSE;
		}

		if (in_array($this->session->userdata('group_id'), $this->config->item('cartthrob:admin_checkout_groups')))
		{
			return TRUE;
		}

		return $order['author_id'] == $this->session->userdata('member_id');
	}
	
	public function get_status($entry_id, $key = FALSE, $default = FALSE)
	{
		if ( ! isset($this->session->cache['cartthrob'][__CLASS__][__FUNCTION__][$entry_id]))
		{
			$query = $this->db->where('entry_id', $entry_id)
					  ->limit(1)
					  ->get('cartthrob_status');
			
			if ($query->num_rows() === 0)
			{
				return FALSE;
			}
			
			$this->session->cache['cartthrob'][__CLASS__][__FUNCTION__][$entry_id] = $query->row_array();
			
			$query->free_result();
		}
		
		$cache =& $this->session->cache['cartthrob'][__CLASS__][__FUNCTION__][$entry_id];
		
		if ($key !== FALSE)
		{
			return (isset($cache[$key])) ? $cache[$key] : $default;
		}
		
		return ($cache) ? $cache : $default;
	}
	
	public function get_order_status($entry_id)
	{
		// this always needs to be direct from the database.
		// getting cached data... man, it really screws us up when the get_status function is used more than once to check, then set, then check again somewhere else. 
		$query = $this->db->where('entry_id', $entry_id)
				  ->limit(1)
				  ->get('cartthrob_status');
		if ($query->num_rows())
		{
			$data = $query->row_array(); 
			if (isset($data['status']))
			{
				return $data['status']; 
			}
		}
		
		return $this->get_status($entry_id, 'status', NULL);
		
	}
	
	public function get_order_transaction_id($entry_id)
	{
		// this always needs to be direct from the database.
		
		$query = $this->db->where('entry_id', $entry_id)
				  ->limit(1)
				  ->get('cartthrob_status');
		if ($query->num_rows())
		{
			$data = $query->row_array(); 
			if (isset($data['transaction_id']))
			{
				return $data['transaction_id']; 
			}
		}
 		return $this->get_status($entry_id, 'transaction_id', NULL);
	}
	
	public function get_order_error_message($entry_id)
	{
		return $this->get_status($entry_id, 'error_message', NULL);
	}
	
	public function set_order_status($entry_id, $status="processing")
	{
		$statuses = array(
			'authorized', 
			'completed',
			'processing', 
			'reversed',
			'refunded',
			'voided',
			'expired',
			'canceled',
			'failed',
			'declined', 
			'offsite'
		);     
		if (!in_array($status, $statuses))
		{
			$status =  "processing"; 
		}
		if ($this->get_order_status($entry_id) !="authorized" && $this->get_order_status($entry_id) != "completed")
		{
			// set order status.
			if ( $this->get_order_status($entry_id) != NULL)  
			{     
				$this->db->update('cartthrob_status', array('status' => $status), array('entry_id' => $entry_id));
			} 
			else
			{
			   	$this->db->insert('cartthrob_status', array('entry_id' => $entry_id, 'status' => $status));
			}
		} 
		elseif ($status == "refunded" || $status== "reversed" || $status == "voided")
		{
			if ( $this->get_order_status($entry_id) != NULL)  
			{     
				$this->db->update('cartthrob_status', array('status' => $status), array('entry_id' => $entry_id));
			} 
			else
			{
			   	$this->db->insert('cartthrob_status', array('entry_id' => $entry_id, 'status' => $status));
			}
		}  
		return $status; 
	}
	public function set_order_transaction_id($entry_id, $transaction_id= NULL)
	{
		if (!$transaction_id)
		{
			return; 
		}

		if ($this->get_status($entry_id))  
		{
			$this->db->update('cartthrob_status', array('transaction_id' => $transaction_id), array('entry_id' => $entry_id));
		}
		else
		{
		   	$this->db->insert('cartthrob_status', array('entry_id' => $entry_id, 'transaction_id' => $transaction_id));
		}
 		return $transaction_id; 
	}
	public function set_order_error_message($entry_id, $error_message= NULL)
	{
		if (!$error_message)
		{
			return NULL; 
		}

		if ($this->get_status($entry_id))  
 		{     
			$this->db->update('cartthrob_status', array('error_message' => $error_message), array('entry_id' => $entry_id));
		} 
		else
		{
		   	$this->db->insert('cartthrob_status', array('entry_id' => $entry_id, 'error_message' => $error_message));
		}
 		return $error_message; 
	}
	public function get_cart_from_order($entry_id)
	{
		$query = $this->db->select('cart')
				->from('cartthrob_status')
				->where('entry_id', $entry_id)
				->limit(1)
				->get();
					
		if ($query->row('cart'))
		{
			$this->load->library('encrypt');
 			return _unserialize($this->encrypt->decode($query->row('cart')));
 		}

		return NULL; 
	}
	public function save_cart_snapshot($entry_id, $inventory_processed=FALSE, $discounts_processed=FALSE, $cart = NULL, $cart_id=NULL, $session_id = NULL)
	{
		$data = array(); 
		if ( $inventory_processed !==FALSE)
		{
			$data['inventory_processed'] = $inventory_processed; 
		}
		if ( $discounts_processed !==FALSE)
		{
			$data['discounts_processed'] = $discounts_processed; 
			
		}
		if ($cart)
		{
			$this->load->library('encrypt');
			$data['cart']	= $this->encrypt->encode(serialize($cart));
			
		}	
		if ($cart_id)
		{
			$data['cart_id'] = $cart_id; 
		}
		if ($session_id)
		{
			$data['session_id']  = $session_id; 
		}	
		
		$query = $this->db->select('inventory_processed')
				->select('discounts_processed')
				->select('cart')
				->select('cart_id')
				->from('cartthrob_status')
				->where('entry_id', $entry_id)
				->limit(1)
				->get();
					
		if ($query->num_rows())
		{
			$this->db->update('cartthrob_status', $data, array('entry_id' => $entry_id));
		}
		else
		{
			$data['entry_id'] = $entry_id; 
			$this->db->insert('cartthrob_status', $data);
		}
	}
	public function get_order($entry_id)
	{
		return $this->cartthrob_entries_model->entry($entry_id);
	}
	
	public function get_orders($where)
	{
		$where['channel_titles.channel_id'] = $this->config->item('cartthrob:orders_channel');
		
		return $this->cartthrob_entries_model->find_entries($where);
	}
	
	public function get_member_orders($member_id, $where = array())
	{
		$where['author_id'] = $member_id;
		
		return $this->get_orders($where);
	}
	
	public function get_member_last_order($member_id)
	{
		return current($this->get_member_orders($member_id));
	}
	
	/**
	 * Get a CartThrob compatible order array from a saved order
	 * 
	 * @param int $entry_id the entry id of the order
	 * 
	 * @return array   use in conjunction with $this->EE->cartthrob->cart->set_order($data);
	 */
	public function get_order_from_entry($entry_id)
	{
		$this->load->helper('array');

		$this->load->model('purchased_items_model');
		
		$entry = $this->get_order($entry_id);
		
		$order_data = array(
			'title' => element('title', $entry_id),
			'invoice_number' => element('title', $entry_id),
			'items' => array(),
			'transaction_id' => element('field_id_'.$this->config->item('cartthrob:orders_transaction_id'), $entry),
			'card_type' => '',//not saved in order
			'shipping'	=>element('field_id_'.$this->config->item('cartthrob:orders_shipping_field'), $entry),
			'shipping_plus_tax'	=>element('field_id_'.$this->config->item('cartthrob:orders_shipping_plus_tax_field'), $entry),
			'tax' => element('field_id_'.$this->config->item('cartthrob:orders_tax_field'), $entry),
			'subtotal' => element('field_id_'.$this->config->item('cartthrob:orders_subtotal_field'), $entry),
			'subtotal_plus_tax' => element('field_id_'.$this->config->item('cartthrob:orders_subtotal_plus_tax_field'), $entry),
			'discount' => element('field_id_'.$this->config->item('cartthrob:orders_discount_field'), $entry),
			'total' => element('field_id_'.$this->config->item('cartthrob:orders_total_field'), $entry),
			'customer_name' => element('field_id_'.$this->config->item('cartthrob:orders_customer_name'), $entry),
			'email_address' => element('field_id_'.$this->config->item('cartthrob:orders_customer_email'), $entry),
			'customer_email' => element('field_id_'.$this->config->item('cartthrob:orders_customer_email'), $entry),
			'customer_ip_address' => element('field_id_'.$this->config->item('cartthrob:orders_customer_ip_address'), $entry),
			'ip_address' => element('field_id_'.$this->config->item('cartthrob:orders_customer_ip_address'), $entry),
			'customer_phone' => element('field_id_'.$this->config->item('cartthrob:orders_customer_phone'), $entry),
			'coupon_codes' => element('field_id_'.$this->config->item('cartthrob:orders_coupon_codes'), $entry),
			'coupon_codes_array' => ! empty($entry['field_id_'.$this->config->item('cartthrob:orders_transaction_id')]) ? explode(',', $entry['field_id_'.$this->config->item('cartthrob:orders_transaction_id')]) : array(),
			'last_four_digits' => element('field_id_'.$this->config->item('cartthrob:orders_transaction_id'), $entry),
			'full_billing_address' => element('field_id_'.$this->config->item('cartthrob:orders_full_billing_address'), $entry),
			'full_shipping_address' => element('field_id_'.$this->config->item('cartthrob:orders_full_shipping_address'), $entry),
			'billing_first_name' => element('field_id_'.$this->config->item('cartthrob:orders_billing_first_name'), $entry),
			'billing_last_name' => element('field_id_'.$this->config->item('cartthrob:orders_billing_last_name'), $entry),
			'billing_company' => element('field_id_'.$this->config->item('cartthrob:orders_billing_company'), $entry),
			'billing_address' => element('field_id_'.$this->config->item('cartthrob:orders_billing_address'), $entry),
			'billing_address2' => element('field_id_'.$this->config->item('cartthrob:orders_billing_address2'), $entry),
			'billing_city' => element('field_id_'.$this->config->item('cartthrob:orders_billing_city'), $entry),
			'billing_state' => element('field_id_'.$this->config->item('cartthrob:orders_billing_state'), $entry),
			'billing_zip' => element('field_id_'.$this->config->item('cartthrob:orders_billing_zip'), $entry),
			'billing_country' => element('field_id_'.$this->config->item('cartthrob:orders_billing_country'), $entry),
			'billing_country_code' => element('field_id_'.$this->config->item('cartthrob:orders_country_code'), $entry),
			'first_name' => element('field_id_'.$this->config->item('cartthrob:orders_billing_first_name'), $entry),
			'last_name' => element('field_id_'.$this->config->item('cartthrob:orders_billing_last_name'), $entry),
			'company' => element('field_id_'.$this->config->item('cartthrob:orders_billing_company'), $entry),
			'address' => element('field_id_'.$this->config->item('cartthrob:orders_billing_address'), $entry),
			'address2' => element('field_id_'.$this->config->item('cartthrob:orders_billing_address2'), $entry),
			'city' => element('field_id_'.$this->config->item('cartthrob:orders_billing_city'), $entry),
			'state' => element('field_id_'.$this->config->item('cartthrob:orders_billing_state'), $entry),
			'zip' => element('field_id_'.$this->config->item('cartthrob:orders_billing_zip'), $entry),
			'country' => element('field_id_'.$this->config->item('cartthrob:orders_billing_country'), $entry),
			'country_code' => element('field_id_'.$this->config->item('cartthrob:orders_country_code'), $entry),
			'entry_id' => $entry_id,
			'order_id' => $entry_id,
			'total_cart' => element('field_id_'.$this->config->item('cartthrob:orders_total_field'), $entry),
			'auth' => array(
				'authorized' => element('status', $entry) === $this->config->item('cartthrob:orders_default_status'),
				'failed' => element('status', $entry) === $this->config->item('cartthrob:orders_failed_status'),
				'declined' => element('status', $entry) === $this->config->item('cartthrob:orders_declined_status'),
				'processing' => element('status', $entry) === $this->config->item('cartthrob:orders_processing_status'),
				'error_message' => element('field_id_'.$this->config->item('cartthrob:orders_error_message_field'), $entry),
				'transaction_id' => element('field_id_'.$this->config->item('cartthrob:orders_transaction_id'), $entry),
			),
			'purchased_items' => $this->purchased_items_model->get_purchased_items($entry_id),
			'create_user' => NULL,
			'member_id' => element('author_id', $entry),
			'group_id' => NULL,
			'authorized_redirect' => '',
			'failed_redirect' => '',
			'declined_redirect' => '',
			'return' => $this->functions->fetch_site_index(1),
			'site_name' => $this->config->item('site_name'),
			'custom_data' => array(),
			'subscription' => '',
			'subscription_options' => NULL,
			'payment_gateway' => element('field_id_'.$this->config->item('cartthrob:orders_payment_gateway'), $entry),
		);
		
		$fields = array();
		
		foreach ($this->cartthrob_settings_model->get_settings() as $key => $value)
		{
			if (strncmp('orders_', $key, 7) === 0)
			{
				$fields[] = $value;
			}
		}
		
		foreach ($this->get_order_items($entry_id, array(), array(), TRUE) as $row)
		{
			unset($row['exrta']['row_id']);
			$row['product_id'] = $row['entry_id'];
			$row['item_options'] = $row['extra'];
			unset($row['extra'], $row['row_order'], $row['order_id']);
			$order_data['items'][$row['row_id']] = $row;
		}
		
		foreach ($this->cartthrob_field_model->get_fields_by_channel($this->config->item('cartthrob:orders_channel')) as $field)
		{
			if ( ! in_array($field['field_id'], $fields))
			{
				$order_data['custom_data'][$field['field_name']] = element($field['field_name'], $entry);
			}
		}
		
		return $order_data;
	}
	
	public function get_member_first_order($member_id)
	{
		return current(reset($this->get_member_orders($member_id)));
	}
	public function order_totals($where = FALSE, $just_total = FALSE)
	{
		$defaults = array(
			'total' => 0,
			'subtotal' => 0,
			'tax' => 0,
			'shipping' => 0,
			'discount' => 0,
			'orders' => 0
		);
		
		if ( ! $this->config->item('cartthrob:orders_total_field') || ! $this->config->item('cartthrob:orders_channel'))
		{
			return ($just_total) ? 0 : $defaults;
		}
		
		if ($this->db->field_exists('field_id_'.$this->config->item('cartthrob:orders_total_field'), 'channel_data'))
		{
			$this->db->select('SUM(field_id_'.$this->config->item('cartthrob:orders_total_field').') AS total', TRUE);
		}
		if ($this->db->field_exists('field_id_'.$this->config->item('cartthrob:orders_total_field'), 'channel_data'))
		{
			$this->db->select('AVG(field_id_'.$this->config->item('cartthrob:orders_total_field').') AS average_total', TRUE);
		}
		
				
		if (is_array($where))
		{
			foreach ($where as $key => $value)
			{
				if (is_array($value))
				{
					$this->db->where_in($key, $value); 
				}
				else
				{
					if ( $value == "IS NOT NULL")
					{
 		 				$this->db->where($key." <> ''", NULL, FALSE); 
						$this->db->where($key." IS NOT NULL", NULL, FALSE); 
					}
					else
					{
						$this->db->where($key, $value);
					}
				}
			}
		}
		
		if ($this->config->item('cartthrob:orders_subtotal_field'))
		{
			if ($this->db->field_exists('field_id_'.$this->config->item('cartthrob:orders_subtotal_field'), 'channel_data'))
			{
				$this->db->select('SUM(field_id_'.$this->config->item('cartthrob:orders_subtotal_field').') AS subtotal', TRUE);
			}
		}
		
		if ($this->config->item('cartthrob:orders_subtotal_plus_tax_field'))
		{
			if ($this->db->field_exists('field_id_'.$this->config->item('cartthrob:orders_subtotal_plus_tax_field'), 'channel_data'))
			{
				$this->db->select('SUM(field_id_'.$this->config->item('cartthrob:orders_subtotal_plus_tax_field').') AS subtotal_plus_tax', TRUE);
				
			}
		}
		
		if ($this->config->item('cartthrob:orders_tax_field'))
		{
			if ($this->db->field_exists('field_id_'.$this->config->item('cartthrob:orders_tax_field'), 'channel_data'))
			{
				$this->db->select('SUM(field_id_'.$this->config->item('cartthrob:orders_tax_field').') AS tax', TRUE);
			}
		}
		
		if ($this->config->item('cartthrob:orders_shipping_field'))
		{
			if ($this->db->field_exists('field_id_'.$this->config->item('cartthrob:orders_shipping_field'), 'channel_data'))
			{
				$this->db->select('SUM(field_id_'.$this->config->item('cartthrob:orders_shipping_field').') AS shipping', TRUE);
			}
		}
		if ($this->config->item('cartthrob:orders_shipping_plus_tax_field'))
		{
			if ($this->db->field_exists('field_id_'.$this->config->item('cartthrob:orders_shipping_plus_tax_field'), 'channel_data'))
			{
				$this->db->select('SUM(field_id_'.$this->config->item('cartthrob:orders_shipping_plus_tax_field').') AS shipping_plus_tax', TRUE);
			}
		}
		
		if ($this->config->item('cartthrob:orders_discount_field'))
		{
			if ($this->db->field_exists('field_id_'.$this->config->item('cartthrob:orders_discount_field'), 'channel_data'))
			{
				$this->db->select('SUM(field_id_'.$this->config->item('cartthrob:orders_discount_field').') AS discount', TRUE);
			}
		}
		
		$this->db->select('COUNT(*) AS orders');
		
		$this->db->from('channel_data')
			->join('channel_titles', 'channel_titles.entry_id = channel_data.entry_id')
			->where('channel_titles.channel_id', $this->config->item('cartthrob:orders_channel'));
			
		$data = $this->db->get();
		
		$dat = array(); 
		if ($data->result() && $data->num_rows() > 0)
		{
			$dat = $data->row_array();
			$data->free_result(); 
		}
		
		
		if (array_key_exists("shipping_plus_tax", $dat))
		{
			$dat['shipping:plus_tax'] = $dat['shipping_plus_tax']; 
		}
		if (array_key_exists("subtotal_plus_tax", $dat))
		{
			$dat['subtotal:plus_tax'] = $dat['subtotal_plus_tax']; 
		}
		
		foreach ($defaults as $key => $value)
		{
			if (empty($dat[$key]))
			{
				$dat[$key] = $value;
			}
		}
		
		if ($just_total)
		{
			return $dat['total'];
		}
		return $dat;
	}
	
	/**
	 * Creates an order from a sub_id
	 *
	 * This'll kill an existing cart "session", so only use in a cron where there is no session
	 * //@TODO but this won't happen in the transaction-object branch
	 * 
	 * @param int|string $sub_id
	 * 
	 * @return
	 */
	public function create_order_from_subscription($sub_id)
	{
		$query = $this->db->where('sub_id', $sub_id)
				  ->get('cartthrob_permissions');
		
		if ($query->num_rows() === 0)
		{
			return FALSE;
		}
		
		$item = _unserialize($query->row('serialized_item'));
		
		//add some stuff to EE session
		$member_query = $this->db->select('member_id, group_id, email')
				  ->where('member_id', $query->row('member_id'))
				  ->get('members');
		
		$cache = array();
		
		foreach ($member_query->row_array() as $key => $value)
		{
			$cache[$key] = $this->session->userdata[$key];
			
			$this->session->userdata[$key] = $value;
		}
		
		$this->load->model('customer_model');
		
		$customer_info = $this->customer_model->get_customer_info(NULL, $query->row('member_id'));
		
		//relaunch the cart
		$this->cartthrob = Cartthrob_core::instance('ee', array(
			'cart' => array(
				'items' => array($item),
				'customer_info' => $customer_info,
			),
		));
		
		$return = $this->create_order($this->order_data_array());
		
		foreach ($cache as $key => $value)
		{
			$this->session->userdata[$key] = $value;
		}
		
		return $return;
	}
	
	/**
	 * order_data_array
	 *
	 * formats post data and merges it with customer session data 
	 * 
	 * @param array $vars 
	 * @return array
	 * @author Chris Newton
	 */
	public function order_data_array($vars = array())
	{
		$this->load->library('cartthrob_loader');
		
		$shipping = NULL; 
		$shipping_plus_tax = NULL; 
		$tax = NULL; 
		$subtotal = NULL; 
		$subtotal_plus_tax = NULL; 
		$discount = NULL; 
		$total = NULL; 
		$credit_card_number = NULL; 
		$create_member_id = NULL; 
		$group_id = NULL; 
		$subscription = array(); 
		$subscription_options = array();
		$payment_gateway = NULL;
		$create_user = FALSE;
		$subscription_id = NULL;
		
		extract($vars, EXTR_IF_EXISTS);	
		
		$this->cartthrob->cart->set_calculation_caching(FALSE);
		
		if (empty($total))
		{
			$total = $this->cartthrob->cart->total();
		}
		if (empty($tax))
		{
			$tax = $this->cartthrob->cart->tax();
		}
		if (empty($discount))
		{
			$discount = $this->cartthrob->cart->discount();
		}
		if (empty($shipping_plus_tax))
		{
			$shipping_plus_tax = $this->cartthrob->cart->shipping_plus_tax(); 
		}
		if (empty($shipping))
		{
			$shipping = $this->cartthrob->cart->shipping();
		}
		if (empty($subtotal))
		{
			$subtotal = $this->cartthrob->cart->subtotal();
		}
		if (empty($subtotal_plus_tax))
		{
			$subtotal_plus_tax = $this->cartthrob->cart->subtotal_with_tax(); 
		}
    
		$this->load->library('api/api_cartthrob_tax_plugins');
		
		$this->load->helper('credit_card');
		
		$use_billing_info = bool_string($this->input->post('use_billing_info')) ? TRUE :bool_string($this->cartthrob->cart->customer_info('use_billing_info')) ; 
		// all of this extra mess is here to deal with admin checkouts where the data hasn't necessarily been saved. 
		$first_name = ($this->input->post("first_name") ? $this->input->post('first_name') :$this->cartthrob->cart->customer_info('first_name') ); 
		$last_name = ($this->input->post("last_name") ? $this->input->post('last_name') :$this->cartthrob->cart->customer_info('last_name') ); 
		$company = ($this->input->post("company") ? $this->input->post('company') :$this->cartthrob->cart->customer_info('company') ); 

		$shipping_first_name = ($this->input->post("shipping_first_name") ? $this->input->post('shipping_first_name') :$this->cartthrob->cart->customer_info('shipping_first_name') ); 
		$shipping_last_name = ($this->input->post("shipping_last_name") ? $this->input->post('shipping_last_name') :$this->cartthrob->cart->customer_info('shipping_last_name') ); 
		$shipping_company = ($this->input->post("shipping_company") ? $this->input->post('shipping_company') :$this->cartthrob->cart->customer_info('shipping_company') ); 
		
		$address = ($this->input->post("address") ? $this->input->post('address') :$this->cartthrob->cart->customer_info('address') ); 
		$address2 = ($this->input->post("address") ? $this->input->post('address2') :$this->cartthrob->cart->customer_info('address2')); 
		$city = ($this->input->post("city") ? $this->input->post('city') :$this->cartthrob->cart->customer_info('city') ); 
		$state = ($this->input->post("state") ? $this->input->post('state') :$this->cartthrob->cart->customer_info('state') ); 
		$zip = ($this->input->post("zip") ? $this->input->post('zip') :$this->cartthrob->cart->customer_info('zip') );
		$country =  $this->cartthrob->cart->customer_info('country');
		$country_code = ($this->input->post("country_code") ? $this->input->post('country_code') :$this->cartthrob->cart->customer_info('country_code') );
				
		$shipping_address = ($this->input->post("shipping_address") ? $this->input->post('shipping_address') :$this->cartthrob->cart->customer_info('shipping_address') ); 
		$shipping_address2 = ($this->input->post("shipping_address") ? $this->input->post('shipping_address2') :$this->cartthrob->cart->customer_info('shipping_address2') ); 
		$shipping_city = ($this->input->post("shipping_city") ? $this->input->post('shipping_city') :$this->cartthrob->cart->customer_info('shipping_city') ); 
		$shipping_state = ($this->input->post("shipping_state") ? $this->input->post('shipping_state') :$this->cartthrob->cart->customer_info('shipping_state') ); 
		$shipping_zip = ($this->input->post("shipping_zip") ? $this->input->post('shipping_zip') :$this->cartthrob->cart->customer_info('shipping_zip') ); 
		$shipping_country =  $this->cartthrob->cart->customer_info('shipping_country');
		$shipping_country_code = ($this->input->post("shipping_country_code") ? $this->input->post('shipping_country_code') :$this->cartthrob->cart->customer_info('shipping_country_code') );
		
		$email_address = ($this->input->post("email_address") ? $this->input->post('email_address') :$this->cartthrob->cart->customer_info('email_address') ); 
		$currency_code = ($this->input->post("currency_code") ? $this->input->post('currency_code') :$this->cartthrob->cart->customer_info('currency_code') ); 
		
		$expiration_month = ($this->input->post("expiration_month") ? $this->input->post('expiration_month') :$this->cartthrob->cart->customer_info('expiration_month') ); 
		$expiration_year = ($this->input->post("expiration_year") ? $this->input->post('expiration_year') :$this->cartthrob->cart->customer_info('expiration_year') ); 
		
		$coupon_codes = $this->cartthrob->cart->coupon_codes() ? implode(',', $this->cartthrob->cart->coupon_codes()) : ""; 
		$CVV2 = ($this->input->post("CVV2") ? $this->input->post('CVV2') :$this->cartthrob->cart->customer_info('CVV2') ); 
		
		$RET = ($this->input->post('RET') ? $this->input->post('RET', TRUE) : $this->functions->fetch_site_index(1) ); 
		
		$return = ($this->input->post('return')) ? $this->input->post('return', TRUE) : $RET; 
		
		$order_data = array(
			'CVV2'					=> $CVV2,
			'expiration_month'			=> $expiration_month,
			'expiration_year'			=> $expiration_year,
			'items' => array(),
			'transaction_id' => 		'',
			'card_type' => 				($this->input->post('card_type')) ? $this->input->post('card_type', TRUE) : card_type($credit_card_number),
			'shipping' => 				$this->cartthrob->round($shipping),
			'shipping_plus_tax'	=>  	$this->cartthrob->round($shipping_plus_tax),
			'tax' => 					$this->cartthrob->round($tax),
			'subtotal' => 				$this->cartthrob->round($subtotal),
			'subtotal_plus_tax' => 		$this->cartthrob->round($subtotal_plus_tax),
			'discount' => 				$this->cartthrob->round($discount),
			'total' => 					$this->cartthrob->round($total),
			'customer_name' => 			$first_name.' '.$last_name,
			'customer_email' => 		$email_address, // what the hell is the distinction between customer_email and email_address
			'email_address' => 			$email_address, // what the hell is the distinction between customer_email and email_address
			'customer_ip_address' => 	$this->input->ip_address(),
			'ip_address' => 			$this->input->ip_address(),
			'customer_phone' => 		($this->input->post("phone") ? $this->input->post('phone') :$this->cartthrob->cart->customer_info('phone') ),
			'coupon_codes' => 			$coupon_codes,
			'coupon_codes_array' => 	$this->cartthrob->cart->coupon_codes(),
			'last_four_digits' => 		substr($credit_card_number,-4,4),
			'full_billing_address' => 	$address."\r\n".
										( $address2 ? $address2."\r\n" : '').
										$city.', '.$state.' '.$zip,
			'full_shipping_address' => 	($use_billing_info) ? $address."\r\n".
										( $address2 ? $address2."\r\n" : '').
										$city.', '.$state.' '.$zip : $shipping_address."\r\n".
										($shipping_address2 ? $shipping_address2."\r\n" : '').
										$shipping_city.', '.$shipping_state.' '.$shipping_zip,
			'billing_first_name' => 	$first_name,
			'billing_last_name' => 		$last_name,
			'billing_company' => 		$company,
			'billing_address' => 		$address,
			'billing_address2' => 		$address2,
			'billing_city' => 			$city,
			'billing_state' => 			$state,
			'billing_zip' => 			$zip,
			'billing_country' => 		$country,
			'billing_country_code' => 	$country_code,
			
			'first_name' => 			$first_name,
			'last_name' => 				$last_name,
			'company' => 				$company,
			'address' => 				$address,
			'address2' => 				$address2,
			'city' => 					$city,
			'state' => 					$state,
			'zip' => 					$zip,
			'country' => 				$country,
			'country_code' => 			$country_code,
			
			'shipping_first_name' => 	($use_billing_info) ? $first_name  : $shipping_first_name,
			'shipping_last_name' => 	($use_billing_info) ? $last_name   : $shipping_last_name,
			'shipping_company' => 		($use_billing_info) ? $company     : $shipping_company,
			'shipping_address' => 		($use_billing_info) ? $address     : $shipping_address,
			'shipping_address2' => 		($use_billing_info) ? $address2    : $shipping_address2,
			'shipping_city' => 			($use_billing_info) ? $city        : $shipping_city,
			'shipping_state' => 		($use_billing_info) ? $state       : $shipping_state,
			'shipping_zip' => 			($use_billing_info) ? $zip         : $shipping_zip,
			'shipping_country' => 		($use_billing_info) ? $country     : $shipping_country,
			'shipping_country_code' => 	($use_billing_info) ? $country_code: $shipping_country_code,
			
			'currency_code'	=> 			$currency_code,
			'entry_id' => 				'',
			'order_id' => 				'',
			'total_cart' => 			$this->cartthrob->round($total),
			'auth' => 					array(),
			'purchased_items' => 		array(),
			'create_user' => 			( ! empty($create_user)) ? $create_user : FALSE,
			'member_id' => 				( ! empty($create_member_id)) ? $create_member_id :  $this->session->userdata('member_id'),
			'group_id' => 				( ! empty($group_id)) ? $group_id :  $this->session->userdata('group_id'),
			'return' => 				$return,
			'site_name' => 				$this->config->item('site_name'),
			'custom_data' => 			$this->cartthrob->cart->custom_data(),
			'subscription'	=> 			$subscription,
			'subscription_options'	=> 	$subscription_options,
			'payment_gateway' => 		(strncmp($payment_gateway, 'Cartthrob_', 10) === 0) ? substr($payment_gateway, 10) : $payment_gateway,
			'subscription_id' =>			$subscription_id,
			'site_id'	=> 				$this->config->item('site_id'),
		);
		
		$order_data['authorized_redirect']	= ($this->input->post('authorized_redirect')) ? $this->input->post('authorized_redirect', TRUE) 	: $order_data['return'];
		$order_data['failed_redirect']		= ($this->input->post('failed_redirect')    ) ? $this->input->post('failed_redirect', TRUE) 		: $order_data['return'];
		$order_data['declined_redirect']	= ($this->input->post('declined_redirect')  ) ? $this->input->post('declined_redirect', TRUE) 		: $order_data['return'];
		
		// overwriting the default member data here, because otherwise it's not accessible when coming back from a payment gateway
		// when using create_user.
		// save_customer_info uses POST data. If it's coming back from offsite, there's no post data to work with
		// so it defaults to customer data. 
		foreach ($order_data as $key => $value)
		{
			if (strpos($key, "billing_") === 0)
			{
				$new_key = str_replace("billing_", "", $key); 
				$order_data[$new_key] = $value; 
			}
		}
		foreach ($this->cartthrob->cart->items() as $row_id => $item)
		{
			$row = $item->to_array();
			
			$row['price'] = $item->price();
			$row['price_plus_tax'] = $item->taxed_price();
			$row['weight'] = $item->weight();
			$row['shipping'] = $item->shipping();
			$row['title'] = $item->title();
			$row['discount'] = $item->discount(); 
			$order_data['items'][$row_id] = $row;
		}
		
		$order_data = array_merge( $this->cartthrob->cart->customer_info(), $order_data);
		
		return $order_data;
	}
}
