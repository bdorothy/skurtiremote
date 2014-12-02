<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Customer_model extends CI_Model
{
	public function __construct()
	{
		$this->load->model('cartthrob_settings_model');
	}
	
	public function load_profile_edit()
	{
		static $loaded;
		
		// config says don't use P:E
		if ( ! $this->config->item('cartthrob:use_profile_edit'))
		{
			// not setting the static value, because wwho knows... maybe we change it dynamically. 
			return FALSE; 
		}
		
		if ( ! is_null($loaded))
		{
			return $loaded;
		}
		
		if ( ! isset($this->extensions->extensions['channel_form_submit_entry_start'][10]['Profile_ext']))
		{
			return $loaded = FALSE;
		}
		
		$this->load->add_package_path(PATH_THIRD.'profile/');
		
		$this->load->model('profile_model');
		
		$this->load->remove_package_path(PATH_THIRD.'profile/');
		
		$site_id = $this->profile_model->site_id(); 

		if ($site_id)
		{
			// need to load fields related to profile edit, otherwise it won't work across site on MSM installs
			$this->load->model('cartthrob_field_model'); 
			$this->cartthrob_field_model->load_fields($site_id);
		}
		
		return $loaded = $this->profile_model->settings('channel_id');
	}
	
	public function get_customer_info($existing_customer_info = NULL, $member_id = NULL)
	{
		if (is_array($this->config->item('cartthrob:default_location')))
		{
			$customer_info_defaults = $this->config->item('cartthrob:customer_info_defaults');
			
			foreach ($this->config->item('cartthrob:default_location') as $key => $value)
			{
				$customer_info_defaults[$key] = $value;
			}
			
			$this->cartthrob_settings_model->set_item('customer_info_defaults', $customer_info_defaults);
		}
		
		if (is_null($existing_customer_info))
		{
			$customer_info = $this->config->item('cartthrob:customer_info_defaults');
		}
		else 
		{
			$customer_info = $existing_customer_info;
		}
		
		if (is_null($member_id))
		{
			$member_id = $this->session->userdata('member_id');

			$userdata = $this->session->userdata;
		}
		else
		{
			$query = $this->db->select('username, screen_name, email')
						->where('member_id', $member_id)
						->get('members');

			$userdata = $query->row_array();

			$query->free_result();
		}
		
		//auto-set the customer ip address
		$customer_info['ip_address'] = $this->input->ip_address();
		
		if (empty($customer_info['currency_code']))
		{
			$customer_info['currency_code'] = (string) $this->config->item('cartthrob:number_format_defaults_currency_code');
		}
		
		if ($member_id && $this->config->item('cartthrob:save_member_data'))
		{
			$member_data_loaded = FALSE;
			
			if ($profile_edit_channel_id = $this->load_profile_edit())
			{
				$profile_edit_entry_id = $this->profile_model->get_profile_id($member_id);
				
				$this->load->model('cartthrob_entries_model');
				
				if ($member_data = $this->cartthrob_entries_model->entry($profile_edit_entry_id))
				{
					foreach ($this->config->item('cartthrob:customer_info_defaults') as $key => $value)
					{
						if ($member_field = $this->config->item('cartthrob:member_'.$key.'_field'))
						{
							if (isset($member_data['field_id_'.$member_field]))
							{
								$customer_info[$key] = $member_data['field_id_'.$member_field];
							}
							else if ( ! is_numeric($member_field) && isset($userdata[$member_field]))
							{
								$customer_info[$key] = $userdata[$member_field];
							}
						}
						
					}
					
					// going to load in all of the P:E fields into the customer info
					$this->load->model('cartthrob_field_model'); 
					$fields = $this->cartthrob_field_model->get_fields_by_channel($profile_edit_channel_id);
					foreach ($fields as $key => $field)
					{
						// dont' want to overwrite anything that's already there
						if (!isset($customer_info[$field['field_name']]))
						{
							if (array_key_exists('field_id_'. $field['field_id'], $member_data))
							{
								// adding something like my_occupation is now possible in standard variable output.
								$customer_info[$field['field_name']] = $member_data['field_id_'. $field['field_id']]; 
							}
						}
					}
					$member_data_loaded = TRUE;
				}
			}
			
			if ($member_data_loaded === FALSE)
			{
				$this->load->model('member_model');
				
				$member_data = $this->member_model->get_all_member_data($member_id)->row_array();
				
				foreach ($this->config->item('cartthrob:customer_info_defaults') as $key => $value)
				{
					if ($member_field = $this->config->item('cartthrob:member_'.$key.'_field'))
					{
						if (isset($member_data['m_field_id_'.$member_field]))
						{
							$customer_info[$key] = $member_data['m_field_id_'.$member_field];
						}
						else if ( ! is_numeric($member_field) && isset($userdata[$member_field]))
						{
							$customer_info[$key] = $userdata[$member_field];
						}
					}
				}
			}
		}
		
		foreach ($this->config->item('cartthrob:customer_info_defaults') as $key => $value)
		{
			if ( ! isset($customer_info[$key]))
			{
				$customer_info[$key] = $value;
			}
		}
		
		return $customer_info;
	}
}
