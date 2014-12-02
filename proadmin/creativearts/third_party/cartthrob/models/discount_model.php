<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Discount_model extends CI_Model
{
	private $discount_data;
	
	public function __construct()
	{
		$this->load->model('cartthrob_settings_model');
		
		$this->load->model('cartthrob_entries_model');
	}
	
	public function process_discounts()
	{
		foreach ($this->get_valid_discounts() as $entry_id => $data)
		{
			// discount_type is a reference to the field that stores the discounts. 
			if ($this->config->item('cartthrob:discount_type'))
			{
				$data['used_by'] = ( ! empty($data['used_by'])) ? $data['used_by'].'|'.$this->session->userdata('member_id') : $this->session->userdata('member_id');

				$data['discount_limit'] = (isset($data['discount_limit']) && strlen($data['discount_limit']) > 0) ? $data['discount_limit'] - 1 : '';
				
				$this->db->update('channel_data', array('field_id_'.$this->config->item('cartthrob:discount_type') => base64_encode(serialize($data))), array('entry_id' => $entry_id));
			}
		}
	}
	
	public function get_valid_discounts()
	{
		if (is_null($this->discount_data))
		{
			$this->discount_data = array();
			
			$this->load->library('api/api_cartthrob_discount_plugins');
	
			if ($this->config->item('cartthrob:discount_channel') && $this->config->item('cartthrob:discount_type'))
			{
				$filter = array(
					'channel_titles.status !=' => 'closed',
					'channel_titles.channel_id' => $this->config->item('cartthrob:discount_channel'),
					'channel_titles.entry_date <=' => $this->localize->now,
					//'channel_titles.expiration_date >=' => $this->localize->now,
				);
				
				// cartthrob_discount_filter hook
				if ($this->extensions->active_hook('cartthrob_discount_filter') === TRUE)
				{
					$filter = $this->extensions->call('cartthrob_discount_filter', $filter);
				}
				
				$entries = $this->cartthrob_entries_model->find_entries($filter);
				
				$this->load->helper('data_formatting');
				
				foreach ($entries as &$entry)
				{
					if ($entry['expiration_date'] && $entry['expiration_date'] <= $this->localize->now)
					{
						continue;
					}
					
					$data = _unserialize(element('field_id_'.$this->config->item('cartthrob:discount_type'), $entry), TRUE);
	
					if ( ! isset($data['type']))
					{
						continue; 
					}
			
					$used_by = ( ! empty($data['used_by'])) ? array_count_values(preg_split('#\s*[,|]\s*#', trim($data['used_by']))) : array();
	
					if ( ! empty($data['per_user_limit']) && isset($used_by[$this->session->userdata('member_id')]) && ($used_by[$this->session->userdata('member_id')] >= $data['per_user_limit']))
					{
						continue;
					}
	
					if (isset($data['discount_limit']) && $data['discount_limit'] !== '' && $data['discount_limit'] <= 0)
					{
						continue;
					}
					
					if ( ! empty($data['member_groups']) && ! in_array($this->session->userdata('group_id'), preg_split('#\s*[,|]\s*#', trim($data['member_groups']))))
					{
						continue;
					}
					
					if ( ! empty($data['member_ids']) && ! in_array($this->session->userdata('member_id'), preg_split('#\s*[,|]\s*#', trim($data['member_ids']))))
					{
						continue;
					}
					
					$data['entry_id'] = $entry['entry_id'];
					
					$plugin = $this->api_cartthrob_discount_plugins->set_plugin($data['type'])->plugin();
	
					if (method_exists($plugin, 'validate') && is_callable(array($plugin, 'validate')))
					{
						if ( ! $plugin->set_plugin_settings($data)->validate())
						{
							continue;
						}
					}
					
					$this->discount_data[$entry['entry_id']] = $data;
				}
			}
			
			if ($this->extensions->active_hook('cartthrob_get_valid_discounts_end') === TRUE)
			{
				$this->discount_data = $this->extensions->call('cartthrob_get_valid_discounts_end', $this->discount_data);
			}
		}
		
		return $this->discount_data;
	}
}
