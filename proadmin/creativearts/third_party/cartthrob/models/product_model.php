<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Product_model extends CI_Model
{
	private $category_posts = array();
	
	public function __construct()
	{
		$this->load->model('cartthrob_settings_model');
		$this->load->model('cartthrob_entries_model');
		$this->load->helper('data_formatting');
		$this->load->helper('array');
	}
	
	/**
	 * Returns an array of product entry_id's of products within the specified price range
	 * In order to get this to work, you need to change the MySQL field type of your price field to INT or FLOAT
	 * 
	 * @access private
	 * @param float $price_min
	 * @param float $price_max
	 * @return array
	 */
	public function get_products_in_price_range($price_min, $price_max)
	{
		$this->load->model('cartthrob_field_model');
		
		$entry_ids = array();
		
		$channel_ids = ($this->config->item('cartthrob:product_channels')) ? $this->config->item('cartthrob:product_channels') : array();

		foreach ($channel_ids as $channel_id)
		{
			if ($field_id = array_value($this->config->item('cartthrob:product_channel_fields'), $channel_id, 'price'))
			{
				$this->db->select('entry_id')->where('field_id_'.$field_id.' !=', '');
				
				if ($price_min !== '' && $price_min !== FALSE)
				{
					$this->db->where('field_id_'.$field_id.' >=', $price_min);
				}
				
				if ($price_max !== '' && $price_max !== FALSE)
				{
					$this->db->where('field_id_'.$field_id.' <=', $price_max);
				}

				$query = $this->db->get('channel_data');

				if ($query->num_rows())
				{
					foreach ($query->result() as $row)
					{
						$entry_ids[] = $row->entry_id;
					}
				}
			}	
		}

		return $entry_ids;
	}
	
	public function get_product($entry_id)
	{
		if ( ! $product = $this->cartthrob_entries_model->entry($entry_id))
		{
			return array();
		}
		
		$field_ids = array();
		
		foreach ($this->cartthrob_field_model->get_fields_by_channel($product['channel_id']) as $field_id => $row)
		{
			$field_ids[] = $field_id;
		}
		
		foreach ($product as $key => $value)
		{
			if (substr($key, 0, 6) !== 'field_')
			{
				continue;
			}
			
			$field_id = substr($key, 9);
			
			if ( ! in_array($field_id, $field_ids))
			{
				unset($product[$key]);
			}
		}
		
		foreach (array('inventory', 'price', 'weight', 'shipping') as $key)
		{
			$field_id = array_value($this->config->item('cartthrob:product_channel_fields'), $product['channel_id'], $key);
			
			if ($field_id && isset($product['field_id_'.$field_id]))
			{
				/*
				if ($key == 'inventory' && $this->cartthrob_field_model->get_field_type($field_id) == 'cartthrob_price_modifiers')
				{
					$product[$key] = _unserialize($product['field_id_'.$field_id]);
				}
				else if ($key == 'inventory' && $this->cartthrob_field_model->get_field_type($field_id) == 'matrix')
				{
					$product[$key] = $product['field_id_'.$field_id];
				}
				else
				{
					$product[$key] = $product['field_id_'.$field_id];
				}
				*/
				$product[$key] = $product['field_id_'.$field_id];
			}
		}
		
		$product['product_id'] = $entry_id;
		
		if ( ! isset($this->category_posts[$entry_id]))
		{
			$query = $this->db->select('cat_id')
					  ->where('entry_id', $entry_id)
					  ->get('category_posts');
			
			$this->category_posts[$entry_id] = array();
			
			foreach ($query->result() as $row)
			{
				$this->category_posts[$entry_id][] = $row->cat_id;
			}
		}
		
		$product['categories'] = $this->category_posts[$entry_id];
		
		return $product;
	}
	public function get_base_price($entry_id)
	{
		$price = FALSE; 
		$data = $this->get_product($entry_id);
		
		if ( ! $channel_id = element('channel_id', $data))
		{
 			return FALSE;
		}
		
		if (! $field_id = array_value($this->config->item('cartthrob:product_channel_fields'), $channel_id, 'price'))
		{
 			return FALSE;
		}
 		$this->load->model('cartthrob_field_model');
		
		if ( $field_type = $this->cartthrob_field_model->get_field_type($field_id))
		{
			$this->load->library('api');
			$this->api->instantiate('channel_fields');
			$this->api_channel_fields->include_handler($field_type);
			
			if ($this->api_channel_fields->setup_handler($field_type) && $this->api_channel_fields->check_method_exists('cartthrob_price'))
			{
				return $this->api_channel_fields->apply('cartthrob_price', array($data['field_id_'.$field_id]));
			}
			else
			{
				if (array_key_exists('field_id_'.$field_id, $data))
				{
					return $data['field_id_'.$field_id]; 
				}
				else
				{
					return FALSE;
				}
			}
		}
		
	}
	
	public function get_price_modifier_value($entry_id, $field_name, $option_value)
	{
		$this->load->model('cartthrob_field_model');
		$modifier = $this->get_price_modifiers($entry_id, $this->cartthrob_field_model->get_field_id($field_name)); 
		
 		foreach ($modifier as $mod)
		{
 			$current_option_value = element('option_value', $mod); 
 			if ($current_option_value !== FALSE && $current_option_value == $option_value)
			{
 				return $mod; 
			}
		}
		return FALSE;
	}
	public function get_price_modifiers($entry_id, $field_id)
	{
		$price_modifiers = $this->get_all_price_modifiers($entry_id);
		
		$field_name = $this->cartthrob_field_model->get_field_name($field_id);
		
		return (isset($price_modifiers[$field_name])) ? $price_modifiers[$field_name] : array();
	}
	
	public function check_inventory($entry_id, $quantity = 1, $item_options = array())
	{
		$inventory = FALSE; 
		$data = $this->get_product($entry_id);
		
		if ( ! $channel_id = element('channel_id', $data))
		{
 			return FALSE;
		}
		
		if ( ! $field_id = array_value($this->config->item('cartthrob:product_channel_fields'), $channel_id, 'inventory'))
		{
 			return FALSE;
		}
 		$this->load->model('cartthrob_field_model');
		
		$field_type = $this->cartthrob_field_model->get_field_type($field_id);
		
		if (in_array($field_type, array('cartthrob_price_modifiers', 'matrix')) || strncmp($field_type, 'cartthrob_price_modifiers', 25) === 0)
		{
			$field_name = $this->cartthrob_field_model->get_field_name($field_id);
			
			// getting the prcie modifiers
			$price_modifiers = $this->get_all_price_modifiers($entry_id, $get_configurations = FALSE);
			foreach ($price_modifiers as $index => $price_modifier)
			{
				if (array_key_exists("inventory", $price_modifier) && $price_modifier['inventory'] !== '' && isset($item_options[$field_name]) && $item_options[$field_name] == $price_modifier['option_value'])
				{
					$inventory = sanitize_number($price_modifier['inventory'], TRUE) - sanitize_number($quantity, TRUE);
 					return $inventory; 
				}
			}
			return FALSE; 
		}	
		return FALSE; 
	}			

	public function get_base_variation($entry_id, $field_name, $configuration = array() )
	{
		$this->load->library('api');
		$this->load->model('cartthrob_field_model');
		$this->api->instantiate('channel_fields');
		
		$field_id = $this->cartthrob_field_model->get_field_id($field_name); 
		$field_type= $this->cartthrob_field_model->get_field_type($field_id); 
		
		$this->api_channel_fields->include_handler($field_type);
		
		if ($this->api_channel_fields->setup_handler($field_type) && $this->api_channel_fields->check_method_exists('compare') && $this->api_channel_fields->check_method_exists('item_option_groups'))
		{
			$product = $this->get_product($entry_id);
			$field_ids = array(); 

			if ( empty($product['channel_id']))
			{
				return NULL;
			}
 
			if (!array_key_exists('field_id_'.$field_id, $product))
			{
				return NULL; 
			}
			$data = _unserialize($product['field_id_'.$field_id], TRUE);
			
			$sku = $this->api_channel_fields->apply('compare', array($data, $configuration)); 
			
			$this->load->add_package_path(PATH_THIRD.'cartthrob'); 
			if ($sku !== NULL && $sku !== FALSE && $sku != "")
			{
				return $sku; 
			}
			else
			{
				return NULL; 
			}
 
		}
		else
		{
			return NULL; 
		}
	}
	public function get_all_price_modifiers($entry_id, $configurations = TRUE)
	{
		if (isset($this->session->cache['cartthrob']['product_model']['all_price_modifiers'][$entry_id]))
		{
			return $this->session->cache['cartthrob']['product_model']['all_price_modifiers'][$entry_id];
		}
		
		$price_modifiers = array();
		
		if ($this->extensions->active_hook('cartthrob_get_all_price_modifiers') === TRUE)
		{
			//@TODO hook params
			$additional_price_modifiers = $this->extensions->call('cartthrob_get_all_price_modifiers', $entry_id);
			
			if ($this->extensions->end_script === TRUE) 
			{
				// we need to turn this back on, otherwise this hook can't get called again, and that's not really the point. 
				// if it gets called again, and can't be called, we get a white screen of death
				$this->extensions->end_script = FALSE; 
				$this->session->cache['cartthrob']['product_model']['all_price_modifiers'][$entry_id] = $additional_price_modifiers;
				return $additional_price_modifiers; 
			}
						
			if (is_array($additional_price_modifiers))
			{
				$price_modifiers = $additional_price_modifiers;
			}
		}
		
		$product = $this->get_product($entry_id);
		
		$field_ids = array();
		
		if ( ! empty($product['channel_id']))
		{
			foreach ($this->cartthrob_field_model->get_fields_by_channel($product['channel_id']) as $field)
			{
				if (strncmp($field['field_type'], 'cartthrob_price_modifiers', 25) === 0)
				{
					$field_ids[] = $field['field_id'];
				}
				else if ($field['field_type'] === 'matrix')
				{
					$cols = $this->cartthrob_field_model->get_matrix_cols($field['field_id']);
					
					$is_price_modifier = FALSE;
					
					foreach ($cols as $col)
					{
						if ($col['col_name'] === 'option_value')
						{
							$is_price_modifier = TRUE;
							break;
						}
					}
					
					if ($is_price_modifier)
					{
						$field_ids[] = $field['field_id'];
					}
				}
			}
		}
		
		//$field_ids = array_value($this->config->item('cartthrob:product_channel_fields'), $product['channel_id'], 'price_modifiers');
		
		if ($field_ids && $product)
		{
			foreach ($field_ids as $field_id)
			{
				if ( ! isset($product['field_id_'.$field_id]))
				{
					continue;
				}
				
				$field_type= $this->cartthrob_field_model->get_field_type($field_id); 
				
				if ($field_type == 'matrix')
				{
					$cols = $this->cartthrob_field_model->get_matrix_cols($field_id);
					$rows = $this->cartthrob_field_model->get_matrix_rows($entry_id, $field_id);
					$data = array();
					
					foreach ($rows as $row)
					{
						$_row = array(
							'option_name' => '',
							'option_value' => '',
							'price' => 0,
							'inventory' => ''
						);
						
						foreach ($cols as $col)
						{
							switch($col['col_name'])
							{
								case 'option':
									$_row['option_value'] = $row['col_id_'.$col['col_id']];
									break;
								default:
									$_row[$col['col_name']] = $row['col_id_'.$col['col_id']];
							}
						}
						
						$data[] = $_row; 
					}
					
					$price_modifiers[$this->cartthrob_field_model->get_field_name($field_id)] = $data;
				}
				else
				{
					$this->load->library('api');
					$this->api->instantiate('channel_fields');
					$this->api_channel_fields->include_handler($field_type);
					
					if ($this->api_channel_fields->setup_handler($field_type) && $this->api_channel_fields->check_method_exists('item_option_groups') && $configurations == TRUE)
					{
						$field_short_name = $this->cartthrob_field_model->get_field_name($field_id); 
						
						$groups =  $this->api_channel_fields->apply('item_option_groups', array(_unserialize($product['field_id_'.$field_id], TRUE),$field_short_name));
						$price_modifiers[$this->cartthrob_field_model->get_field_name($field_id)] = $this->api_channel_fields->apply('item_options', array(_unserialize($product['field_id_'.$field_id], TRUE)));
			
						foreach ($groups as $config => $group)
						{
 							$price_modifiers["configuration:".$field_short_name.":".$config] = $group; 
						}
					}
					elseif ($this->api_channel_fields->setup_handler($field_type) && $this->api_channel_fields->check_method_exists('item_options'))
					{
						$price_modifiers[$this->cartthrob_field_model->get_field_name($field_id)] = $this->api_channel_fields->apply('item_options', array(_unserialize($product['field_id_'.$field_id], TRUE)));
					}
					else
					{
						$price_modifiers[$this->cartthrob_field_model->get_field_name($field_id)] = _unserialize($product['field_id_'.$field_id], TRUE);
					}
				}
			}
		}
		if ($this->extensions->active_hook('cartthrob_get_all_price_modifiers_end') === TRUE)
		{
 			$updated_price_modifiers = $this->extensions->call('cartthrob_get_all_price_modifiers_end', $price_modifiers);
		
			if (is_array($updated_price_modifiers))
			{
				$price_modifiers = $updated_price_modifiers;
			}
			if ($this->extensions->end_script === TRUE) 
			{
				// we need to turn this back on, otherwise this hook can't get called again, and that's not really the point. 
				// if it gets called again, and can't be called, we get a white screen of death
				$this->extensions->end_script = FALSE; 
 			}
		}
		$this->session->cache['cartthrob']['product_model']['all_price_modifiers'][$entry_id] = $price_modifiers;
		$this->load->add_package_path(PATH_THIRD.'cartthrob');
		
		return $price_modifiers;
	}
	
	public function adjust_inventory($entry_id, $quantity = 1, $item_options = array(), $reduce = TRUE)
	{
		$inventory = FALSE; 
		$data = $this->get_product($entry_id);
		
		if ( ! $channel_id = element('channel_id', $data))
		{
			return;
		}
		
		if ( ! $field_id = array_value($this->config->item('cartthrob:product_channel_fields'), $channel_id, 'inventory'))
		{
			return;
		}
		
		$this->load->model('cartthrob_field_model');
		
		$field_type = $this->cartthrob_field_model->get_field_type($field_id);
		
		if (in_array($field_type, array('cartthrob_price_modifiers', 'matrix')) || strncmp($field_type, 'cartthrob_price_modifiers', 25) === 0)
		{
			$field_name = $this->cartthrob_field_model->get_field_name($field_id);
			
			$price_modifiers = $this->get_price_modifiers($entry_id, $field_id);
			
			foreach ($price_modifiers as $index => $price_modifier)
			{
				if (isset($price_modifier['inventory']) && $price_modifier['inventory'] !== '' && isset($item_options[$field_name]) && $item_options[$field_name] == $price_modifier['option_value'])
				{
					if ($reduce)
					{
						$inventory = sanitize_number($price_modifier['inventory'], TRUE) - sanitize_number($quantity, TRUE);
					}
					else
					{
						$inventory = sanitize_number($price_modifier['inventory'], TRUE) + sanitize_number($quantity, TRUE);
					}
					
					if ($field_type == 'matrix')
					{
						if (empty($data['field_id_'.$field_id]))
						{
							return;
						}
						
						if ( ! $field_settings = $this->cartthrob_field_model->get_field_settings($field_id))
						{
							return;
						}
						
						$query = $this->db->select('col_id, col_name')
								->from('matrix_cols')
								->where_in('col_id', $field_settings['col_ids'])
								->where_in('col_name', array('inventory', 'option_value'))
								->get();
						
						if ($query->num_rows() != 2)
						{
							return;
						}
						
						foreach ($query->result_array() as $row)
						{
							switch($row['col_name'])
							{
								case 'inventory':
									$inventory_col_id = $row['col_id'];
									break;
								case 'option_value':
									$option_col_id = $row['col_id'];
									break;
							}
						}
						
						//update ze cache
						$this->session->cache['cartthrob']['product_model']['all_price_modifiers'][$entry_id][$field_name][$index]['inventory'] = $inventory;
						
						$this->db->update(
							'matrix_data',
							array(
								'col_id_'.$inventory_col_id => $inventory
							),
							array(
								'field_id' => $field_id,
								'col_id_'.$option_col_id => $item_options[$field_name],
								'entry_id' => $entry_id
							)
						);
					}
					else
					{
						$price_modifiers[$index]['inventory'] = $inventory;
						
						//update ze cache
						$this->session->cache['cartthrob']['product_model']['all_price_modifiers'][$entry_id][$field_name][$index]['inventory'] = $inventory;
						
						$field_data = $price_modifiers;
						
						$this->db->update(
							'channel_data',
							array('field_id_'.$field_id => base64_encode(serialize($field_data))),
							array('entry_id' => $entry_id)
						);
						
						$this->load->model('cartthrob_entries_model');
						
						$this->cartthrob_entries_model->clear_cache($entry_id);
					}
				}
			}
		}
		elseif (isset($data['field_id_'.$field_id]) && $data['field_id_'.$field_id] !== '')
		{
			if ($reduce)
			{
				$inventory = sanitize_number($data['field_id_'.$field_id], TRUE) - sanitize_number($quantity, TRUE);
			}
			else
			{
				$inventory = sanitize_number($data['field_id_'.$field_id], TRUE) + sanitize_number($quantity, TRUE);
			}
			
			$this->db->update(
				'channel_data',
				array('field_id_'.$field_id => $inventory),
				array('entry_id' => $entry_id)
			);
			
			$this->load->model('cartthrob_entries_model');
			
			$this->cartthrob_entries_model->clear_cache($entry_id);
		}
		
		return $inventory; 
	}
	public function increase_inventory($entry_id, $quantity = 1, $item_options = array())
	{
		return $this->adjust_inventory($entry_id, $quantity, $item_options, FALSE); 
	}
	public function reduce_inventory($entry_id, $quantity = 1, $item_options = array())
	{
		$inventory = $this->adjust_inventory($entry_id, $quantity, $item_options); 
		
		if ($this->extensions->active_hook('cartthrob_product_reduce_inventory'))
		{
			$this->extensions->call('cartthrob_product_reduce_inventory', $entry_id, $this->get_product($entry_id), $quantity, $item_options, $inventory);
		}
		
		return $inventory;
	}
	
	public function get_categories()
	{
		if ( ! $this->config->item('cartthrob:product_channels'))
		{
			return array();
		}
		
		$this->load->model('channel_model');
		
		$channels = $this->channel_model->get_channels(NULL, array(), array(array('channel_id' => $this->config->item('cartthrob:product_channels'))));
		
		$cat_group = array();
		
		foreach ($channels->result() as $row)
		{
			if ($row->cat_group)
			{
				$cat_group = array_merge($cat_group, explode('|', $row->cat_group));
			}
		}
		
		if ( ! $cat_group)
		{
			return array();
		}
		
		return $this->db->select('cat_id AS category_id, cat_name AS category_name, cat_url_title AS category_url_title, cat_description AS category_description, cat_image AS category_image, cat_order AS category_order, group_id, parent_id')
				->where('site_id', $this->config->item('site_id'))
				->where_in('group_id', $cat_group)
				->order_by('cat_order, cat_name')
				->get('categories')
				->result_array();
	}
	
	public function load_products($entry_ids)
	{
		$this->cartthrob_entries_model->load_entries_by_entry_id($entry_ids);
		
		foreach ($entry_ids as $i => $entry_id)
		{
			if ( ! isset($this->category_posts[$entry_id]))
			{
				$this->category_posts[$entry_id] = array();
			}
			else
			{
				unset($entry_ids[$i]);
			}
		}
		
		if (count($entry_ids) > 0)
		{
			$query = $this->db->select('cat_id, entry_id')
					  ->where_in('entry_id', $entry_ids)
					  ->get('category_posts');
			
			foreach ($query->result() as $row)
			{
				$this->category_posts[$row->entry_id][] = $row->cat_id;
			}
		}
		
		return $this;
	}
}
