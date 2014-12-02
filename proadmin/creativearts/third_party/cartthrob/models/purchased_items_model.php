<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Purchased_items_model extends CI_Model
{

	public function __construct()
	{
		$this->load->model('cartthrob_settings_model');
		$this->load->model('cartthrob_entries_model');
	}
	
	public function get_purchased_items($order_id)
	{
		if ( ! $this->config->item('cartthrob:purchased_items_order_id_field'))
		{
			return array();
		}
		
		return $this->cartthrob_entries_model->find_entries(array(
			'field_id_'.$this->config->item('cartthrob:purchased_items_order_id_field') => $order_id,
		));
	}
	
	public function purchased_entry_ids()
	{
		$query = $this->db->select('field_id_'.$this->config->item('cartthrob:purchased_items_id_field')." AS entry_id")
				  	->distinct()
	    			->where('channel_id', $this->config->item('cartthrob:purchased_items_channel'))
				  	->get('channel_data');
		
		$entry_ids = array(); 
		foreach ($query->result() as $row)
		{
			$entry_ids[] = $row->entry_id; 
		}
		return $entry_ids; 
	}
	
	//returns an array
	//  entry_id => count
	public function also_purchased($entry_id, $limit = FALSE)
	{
		static $cache;
		
		if (isset($cache[$entry_id]))
		{
			return $cache[$entry_id];
		}
		
		if ( ! $entry_id || ! $this->config->item('cartthrob:orders_channel'))
		{
			return array();
		}
		
		$purchased = array();
		
		$query = $this->db->select('order_id')
				  ->distinct()
				  ->from('cartthrob_order_items')
				  ->where('entry_id', $entry_id)
				  ->get();
		
		if ($query->num_rows() === 0)
		{
			return array();
		}
		
		$order_ids = array();
		
		foreach ($query->result() as $row)
		{
			$order_ids[] = $row->order_id;
		}
		
		$query->free_result();
		
		$query = $this->db->select('entry_id')
				  ->distinct()
				  ->from('cartthrob_order_items')
				  ->where_in('order_id', $order_ids)
				  ->where('entry_id !=', $entry_id)
				  ->get();
		
		if ($query->num_rows() === 0)
		{
			return array();
		}
		
		foreach ($query->result() as $row)
		{
			if (isset($purchased[$row->entry_id]))
			{
				$purchased[$row->entry_id]++;
			}
			else
			{
				$purchased[$row->entry_id] = 1;
			}
		}
		
		$query->free_result();
		
		if ( ! $limit)
		{
			$limit = 20;
		}
		
		arsort($purchased);
		
		$purchased = array_slice($purchased, 0, $limit, TRUE);
		
		$cache[$entry_id] = $purchased;
		
		return $purchased;
	}
	
	public function has_purchased($entry_id)
	{
		if ( ! $this->config->item('cartthrob:orders_channel'))
		{
			return FALSE;
		}
		
		$status = $this->config->item('cartthrob:orders_default_status') ? $this->config->item('cartthrob:orders_default_status') : 'open';
		

		$site_id = $this->db->select('site_id')->where('channel_id', $this->config->item('cartthrob:orders_channel'))->get('channels')->row('site_id');
		
		$this->db->from('cartthrob_order_items')
			 ->join('channel_titles', 'channel_titles.entry_id = cartthrob_order_items.order_id')
			 ->where('cartthrob_order_items.entry_id', $entry_id)
			 ->where('channel_titles.author_id', $this->session->userdata('member_id'))
			 ->where('channel_titles.site_id', $site_id)
			 ->where('channel_titles.status', $status)
			 ->where('channel_titles.channel_id', $this->config->item('cartthrob:orders_channel'));
		
		return ($this->db->count_all_results() > 0);
	}
	
	public function update_purchased_item($entry_id, $data)
	{
		return $this->cartthrob_entries_model->update_entry($entry_id, $data);
	}
	/**
	 * create_purchased_item
	 *
	 * creates ONE purchased item. Item data array should contain: 
	 * 
	 * product_id (basically entry id)
	 * 
	 * 
	 * @param array $item_data 
	 * @param string $order_id 
	 * @param string $status 
	 * @return string entry_id
	 * @author Chris Newton
	 */
	public function create_purchased_item($item_data, $order_id, $status)
	{
 		$this->load->model('cartthrob_members_model');
		$this->load->model('order_model');
		$this->load->helper('url');
		
		if ( ! $channel_id = $this->config->item('cartthrob:purchased_items_channel'))
		{
			return 0;
		}
		
		$product = $this->cartthrob->store->product($item_data['product_id']);
		
		$title = element('title', $item_data);
		
		if ($product && ! $title)
		{
			$title = $product->title();
		}
		
		$word_separator = "_"; 
		switch ($this->config->item('word_separator'))
		{
			case "dash":
				$word_separator = "-"; 
				break;
			default: 
				$word_separator = "_"; 
		}
		
		$order_entry = $this->order_model->get_order($order_id); 
		
		$data = array(
			'title' => $this->config->item('cartthrob:purchased_items_title_prefix').$title,
			'url_title' => url_title(substr($title, 0, 35), $word_separator, TRUE).$word_separator.uniqid(NULL, TRUE),
			'author_id' =>  $order_entry['author_id'],  // $this->cartthrob_members_model->get_member_id(),
			'channel_id' => $channel_id,
			'status' => ($status) ? $status : 'closed',
		);
		
		$purchasedItemChannel = $this->config->item('cartthrob:purchased_items_channel');
                //double check that the purchased item channel
                //is a valid EE channel before proceding
                $queryResults = $this->db->select('site_id')->where('channel_id', $purchasedItemChannel)->get('channels');
                if($queryResults->num_rows() == 0){
                    //channel did not exist, impossible to purchase item
                    log_message('error creating a purchased item!', 'Invalid channel for purchased item. Make sure the channel exists in both the CartThrob configuration and ExpressionEngine.');
                    return false; //no entry id to return!
                }else{
                    //channel exists!
                    $data['site_id'] = $queryResults->row('site_id');
                }
		
		if ( ! empty($item_data['meta']['expires']))
		{
			$data['expiration_date'] = $this->localize->now + ($item_data['meta']['expires']*24*60*60);
		}

		if ($this->config->item('cartthrob:purchased_items_id_field') && isset($item_data['product_id']))
		{
			$data['field_id_'.$this->config->item('cartthrob:purchased_items_id_field')] = $item_data['product_id'];
		}
		
		if ($this->config->item('cartthrob:purchased_items_quantity_field') && isset($item_data['quantity']))
		{
			$data['field_id_'.$this->config->item('cartthrob:purchased_items_quantity_field')] = $item_data['quantity'];
		}
		
		if ($this->config->item('cartthrob:purchased_items_price_field'))
		{
			if ( ! empty($item_data['price']))
			{
				$data['field_id_'.$this->config->item('cartthrob:purchased_items_price_field')] = $item_data['price'];
			}
			else if ($product)
			{
				$data['field_id_'.$this->config->item('cartthrob:purchased_items_price_field')] = $product->price();
			}
		}
		
		if ($this->config->item('cartthrob:purchased_items_order_id_field') && $order_id)
		{
			$data['field_id_'.$this->config->item('cartthrob:purchased_items_order_id_field')] = $order_id;
		}
		
		if ($this->config->item('cartthrob:purchased_items_package_id_field') && $order_id)
		{
			if ( ! empty($item_data['package_id']))
			{
				$data['field_id_'.$this->config->item('cartthrob:purchased_items_package_id_field')] = $item_data['package_id'];
			}
 		}
		
		if ($this->config->item('cartthrob:purchased_items_license_number_field') && ! empty($item_data['meta']['license_number']))
		{
			$limit = 25;

			$license_number = '';

			$this->load->helper('license_number');
			
			do
			{
				$license_number = generate_license_number($this->config->item('cartthrob:purchased_items_license_number_type'));

				$this->db->from('channel_data')
						->where('field_id_'.$this->config->item('cartthrob:purchased_items_license_number_field'), $license_number);

				$limit --;

			} while($this->db->count_all_results() > 0 && $limit >= 0);

			if ($limit >= 0 && $license_number)
			{
				$data['field_id_'.$this->config->item('cartthrob:purchased_items_license_number_field')] = $license_number;
			}
		}
		
		if ($this->config->item('cartthrob:purchased_items_discount_field'))
		{
			if ( ! empty($item_data['discount']))
			{
				$data['field_id_'.$this->config->item('cartthrob:purchased_items_discount_field')] = $item_data['discount'];
			}
		}
		
		// @NOTE: this should not be operating on fucking POST data. All of this stuff should be passed in shouldn't it? 
		// a controller should be setting up this stuff, not the model. 
		foreach ($this->cartthrob_field_model->get_fields_by_channel($channel_id) as $field)
		{
			if ($this->input->post($field['field_name']) !== FALSE)
			{
				$field_data = $this->input->post($field['field_name'], TRUE); 
				if (is_array($field_data))
				{
					$field_data = implode('|', $field_data);
				}
				$data['field_id_'.$field['field_id']] = $field_data; 
			}

			if (isset($item_data['item_options'][$field['field_name']]))
			{
				$field_data = $item_data['item_options'][$field['field_name']]; 
				if (is_array($field_data))
				{
					$field_data = implode('|', $field_data);
				}
				$data['field_id_'.$field['field_id']] = $field_data;
			}
			
			// this looks for a field like "purchased_seller" and an item option called "seller"
			if (preg_match('/^purchased_(.*)/', $field['field_name'], $match) && isset($item_data['item_options'][$match[1]]))
			{
				$field_data =  $item_data['item_options'][$match[1]];
				if (is_array($field_data))
				{
					$field_data = implode('|', $field_data);
				}
				$data['field_id_'.$field['field_id']] = $field_data; 
			}
			
			// this looks for fields like "seller" where you have a purchased items channel field called purchased_seller
			if (preg_match('/^purchased_(.*)/', $field['field_name'], $match) && ($this->input->post($match[1]) !== FALSE) )
			{
				$field_data =  $this->input->post($match[1], TRUE); 
				if (is_array($field_data))
				{
					$field_data = implode('|', $field_data);
				}
				$data['field_id_'.$field['field_id']] = $field_data; 
			}
			
			
		}
		
		return $this->cartthrob_entries_model->create_entry($data);
	}
}
