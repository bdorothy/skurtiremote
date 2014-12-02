<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cartthrob_members_model extends CI_Model
{
	public $errors = array();
	
	public function __construct()
	{
		parent::__construct();
		
		$this->load->model('cartthrob_settings_model');
	}
	
	/**
	 * generate_random_member_data
	 *
	 * this creates a member with email address like sub_11519801580118@example.com
	 * in the case where a user needs to be created, but no data is available to create a member
	 *
	 * @return array
	 * @author Chris Newton
	 */
	public function generate_random_member_data()
	{
		$random = uniqid("sub_"). "_@example.com"; 
		return $this->validate_member(NULL, $random);
	}	
	public function validate_member($username = FALSE, $email, $screen_name = FALSE, $password = FALSE, $password_confirm = FALSE, $group_id = 4, $language = FALSE)
	{
 		if ($group_id < 4)
		{
			$this->errors = array($this->lang->line('validation_group_id_is_too_low')); 
			return FALSE; 
		}
		$this->load->helper('security');
		
		$data['username'] = ($username) ? $username : $email;
		$data['email'] = $email;
		
		// GENERATING A PASSWORD IF NONE IS PROVIDED
		if (empty($password))
		{
			$password = $this->functions->random('alpha');
			$password_confirm = $password;
		}

		// if it's NULL then it needs to be checked
		if ($password_confirm === FALSE)
		{
			$password_confirm = $password;
		}

		$data['screen_name'] = (empty($screen_name)) ? $data['username'] : $screen_name;

		if ($language)
		{
			$data['language'] = $language; 
		}
		else
		{
			$data['language'] = $this->config->item('deft_lang');
		}
		
		/** -------------------------------------
		/**  Instantiate validation class
		/** -------------------------------------*/
		if ( ! class_exists('EE_Validate'))
		{
			require APPPATH.'libraries/Validate'.EXT;
		}

		$VAL = new EE_Validate(array(
			'member_id' => '',
			'val_type' => 'new', // new or update
			'fetch_lang' => TRUE,
			'require_cpw' => FALSE,
			'enable_log' => FALSE,
			'username' => $data['username'],
			'cur_username' => '',
			'screen_name' => $data['screen_name'],
			'cur_screen_name' => '',
			'password' => $password,
			'password_confirm' => $password_confirm,
			'cur_password' => '',
			'email' => $data['email'],
			'cur_email' => ''
		));
		
		// if the email doesn't validate, the rest are irrelevant
		$VAL->validate_email();
		if (count($VAL->errors) > 0)
		{
			// return the array of errors. 
			$this->errors = $VAL->errors;
			return FALSE;
		}
		$VAL->validate_username();
		$VAL->validate_screen_name();
		$VAL->validate_password();

		if (count($VAL->errors) > 0)
		{
			// return the array of errors. 
			$this->errors = $VAL->errors;
			return FALSE;
		}
		
		$data['password'] = $password; // this used to be sha1 encrypted. now that's handled elsewhere
		
		$data['group_id'] = $group_id;
		return $data;
	}
	
	/**
	 * create_member
	 *
	 * @param array $data must contain: username, email, screen_name, password (hashed!), group_id, language 
	 * @return int|false If successful will return member_id, if unsuccessful, will return FALSE
	 * @author Chris Newton 
	 * @access public
	 * @since 1.0
	 */
	public function create_member($data)
	{
		$this->load->helper(array('security', 'string', 'text'));
		$this->load->library('cartthrob_emails'); 

		$default_group_id = ($this->config->item('default_member_group')) ? $this->config->item('default_member_group') : 4;
		
		// we always want this to be pending, unless explicitly set. We also don't want it to be any of the default member groups that have too much power or special status. 
		if ( ! empty($data['group_id']) && $data['group_id'] < 4)
		{
			$data['group_id'] = $default_group_id; 
		}
		else
		{
			$this->db->select('group_id')
					->from('member_groups')
					->where('site_id', $this->config->item('site_id'))
					->where('group_id', $data['group_id']);
			
			$data['group_id'] = ( ! $this->db->count_all_results()) ? $default_group_id : $data['group_id']; 
		}
		
		if ($this->config->item('req_mbr_activation') === 'manual' || $this->config->item('req_mbr_activation') === 'email')
		{
			$data['group_id'] = 4;
		}
		
		if ($this->config->item('req_mbr_activation') === 'email')
		{
			$data['authcode'] = $this->functions->random('alnum', 10);
		}

		$this->load->library('auth');
		$hashed_password = $this->auth->hash_password($data['password']);
		
		//$data['username'] = $username; 
		//$data['screen_name'] = $screenname;
 		$data['password']	= $hashed_password['password'];
		$data['salt']		= $hashed_password['salt'];
		$data['unique_id']	= random_string('encrypt');
		$data['crypt_key']	= $this->functions->random('encrypt', 16);
		//$data['email'] = $email_address;
		$data['ip_address'] = $this->input->ip_address();
		$data['join_date'] = $this->localize->now;
		if (!isset($data['language']))
		{
			$data['language'] 	= $this->config->item('deft_lang');
		}
		$data['timezone'] 	= ($this->config->item('default_site_timezone') && $this->config->item('default_site_timezone') != '') ? $this->config->item('default_site_timezone') : $this->config->item('default_site_timezone');
		$data['time_format'] = ($this->config->item('time_format') && $this->config->item('time_format') != '') ? $this->config->item('time_format') : 'us';
		
		$this->load->model('member_model');
		
		if ($this->config->item('req_mbr_activation') == 'email')
		{
			$data['authcode'] = $this->functions->random('alnum', 10);
		}
		
	 	$member_id = $this->member_model->create_member($data, array());
	
		/**************** admin notification emails ************/

		if ($this->config->item('new_member_notification') == 'y' && $this->config->item('mbr_notification_emails') != '')
		{
			$vars = array(
							'name'					=> $data['screen_name'],
							'site_name'				=> stripslashes($this->config->item('site_name')),
							'control_panel_url'		=> $this->config->item('cp_url'),
							'username'				=> $data['username'],
							'email'					=> $data['email']
						 );

			$template = $this->functions->fetch_email_template('admin_notify_reg');

			foreach ($vars as $key => $val)
			{
				$template['title'] = str_replace('{'.$key.'}', $val, $template['title']);
				$template['data'] = str_replace('{'.$key.'}', $val, $template['data']);
			}
			$email_to = reduce_multiples($this->config->item('mbr_notification_emails'), ',', TRUE);
			
			$this->cartthrob_emails->send_email($this->config->item('webmaster_email'), $this->config->item('webmaster_name'), $email_to, $template['title'], $template['data'], $plaintext = FALSE); 
		}
			
		//// NOTE this does not display any warning to the user when account activation is required
		/**************** send emails *****************************/
		if ($this->config->item('req_mbr_activation') == 'none')
		{
			$this->stats->update_member_stats();
		}
		elseif ($this->config->item('req_mbr_activation') == 'email')
		{
			$action_id  = $this->functions->fetch_action_id('Member', 'activate_member');
 
			$vars = array(
				'activation_url'	=> $this->functions->fetch_site_index(0, 0).QUERY_MARKER.'ACT='.$action_id.'&id='.$data['authcode'],
				'site_name'			=> stripslashes($this->config->item('site_name')),
				'site_url'			=> $this->config->item('site_url'),
				'username'			=> $data['username'],
				'email'				=> $data['email']
 			 );

			$template = $this->functions->fetch_email_template('mbr_activation_instructions');
			
			foreach ($vars as $key => $val)
			{
				$template['title'] = str_replace('{'.$key.'}', $val, $template['title']);
				$template['data'] = str_replace('{'.$key.'}', $val, $template['data']);
			}
			
			// plaintext was changed from False to TRUE because as far as I can tell, activation instructions are always sent plain text by the system. 
			$this->cartthrob_emails->send_email($this->config->item('webmaster_email'), $this->config->item('webmaster_name'), $data['email'], $template['title'], $template['data'], $plaintext = TRUE); 
			
 		}
		/**************** end send emails *****************************/
		
		// -------------------------------------------
		// 'cartthrob_create_member' hook.
		//  - Developers, if you want to modify the $this object remember
		//	to use a reference on function call.
		//
		if ($this->extensions->active_hook('cartthrob_create_member') === TRUE)
		{
			$edata = $this->extensions->call('cartthrob_create_member', array_merge($data, array('member_id' => $member_id)), $this);
			if ($this->extensions->end_script === TRUE) return;
		}

		return $member_id;
	}
	public function validate_email_address($email_address, $member_id)
	{
		$query = $this->db->select('username, screen_name, email, member_id')
					->where('email', $email_address)
					->get('members');

		if ($query->result() && $query->num_rows())
		{
			foreach ($query->result() as $row)
			{
				// someone with that email address already exists don't update the email address
				if ($row->member_id != $member_id)
				{
					return FALSE; 
				}
			}
			$query->free_result();
		}
		else
		{
			// nobody with that email address exists.
			return TRUE; 
		}
		
		return TRUE; 
	}
	/**
	 * update_member
	 *
	 * @param string $member_id member id where data needs to be saved to
	 * @param array $customer_info
	 * @param bool $manually_save_customer_info Normally this function looks to see if the configuration is set to allow the saving of customer information. If that configuration option is set to false, under normal operation it would not be possible to save customer information. This flag overrides that configuration option. 
	 * @return void
	 * @author Chris Newton
	 */
	public function update_member($member_id = FALSE, $customer_info = array(), $manually_save_customer_info = FALSE)
	{
		// should not be NULL, 0, FALSE, ""
		if (! $member_id )
		{
			return $customer_info; 
		}
		
		$member = array();
		$member_data = array();
		
 		$this->load->model(array('customer_model', 'cartthrob_members_model', 'member_model', 'cartthrob_field_model', 'cartthrob_entries_model'));
		$this->load->helper('data_formatting');
		$this->load->helper('array');
	
		$profile_channel_id = $this->customer_model->load_profile_edit(); 
		
		foreach (array_keys($this->cartthrob->cart->customer_info()) as $field)
		{
			// setting an alternate variable because we may be changing where the data's going in a second.
			$orig_field = $field; 
			
			if (bool_string($this->cartthrob->cart->customer_info('use_billing_info')) && strpos($field, 'shipping_') !== FALSE)
			{
				// we're going to get the data from the billing field
				$field = str_replace('shipping_', '', $field); 
			}
			
			// saving the data.
			if (($this->cartthrob->store->config('save_member_data') || $manually_save_customer_info) && $field_id = $this->cartthrob->store->config('member_'.$orig_field.'_field'))
			{
				if (is_numeric($field_id))
				{
					if ($profile_channel_id)
					{
						if ($d = element($field, $customer_info, NULL))
						{
							$member_data['field_id_'.$field_id] = $d; 
						}
					}
					else
					{
						$member_data['m_field_id_'.$field_id] = element($field, $customer_info, NULL);
					}
				}
				else
				{
					if ($field == "email_address")
					{
						if ( $email_address = element($field, $customer_info, NULL))
						{
							if ($this->validate_email_address($email_address, $member_id))
							{
								$member[$field_id] = element($field, $customer_info, NULL);
							}
							else
							{
								/*
								// @TODO consider throwing an error. 
								$this->errors[] = $this->lang->line('please_choose_another_email_address'); // @TODO add this lang line to validation errors
								*/ 
							}
						}
					}
					else
					{
						$member[$field_id] = element($field, $customer_info, NULL);
					}
				}
			}
		}
		///////////////////////////////////////////////////////////
		// incorporating custom data into the newly created member
		///////////////////////////////////////////////////////////
		// custom data won't override standard customer fields though... 
		// need to get the field names from PE
		$pe_fields = $this->cartthrob_field_model->get_fields_by_channel($profile_channel_id);
		// going to convert the custom data array to a local array so we can unset each... potentially cutting down on loop time. 
		$custom_data = $this->cartthrob->cart->custom_data(); 
		foreach ($pe_fields as $pe_field)
		{
			$pe_field_name = element('field_name', $pe_field); 
		 	if (array_key_exists($pe_field_name, $custom_data) || array_key_exists("profile_".$pe_field_name, $custom_data))
		 	{
				// profile_field_name takes precedence over standard field_name
				if (array_key_exists("profile_".$pe_field_name, $custom_data))
				{
					$pe_field_name = 'profile_'.$pe_field_name; 
				}
				$pe_field_id = element('field_id', $pe_field); 
				
				if (!isset($member_data['field_id_'.$pe_field_id ]))
				{
 					$field_data = $custom_data[$pe_field_name]; 
					if (is_array($field_data))
					{
						$field_data = implode('|', $field_data);
					}
					
					$member_data['field_id_'. $pe_field_id] = $field_data; 
					// don't want to keep checking it if it's already been used
					unset($custom_data[$pe_field_name]); 
				}
			}
		}
		// custom data for custom member fields
		// the code above only works with Profile:Edit installed. Below is for custom EE member fields
		if($custom_data && empty($pe_fields))
		{
			// get the custom member fields
			$custom_m_fields = $this->member_model->get_custom_member_fields()->result_array();
			
			foreach($custom_m_fields as $custom_m_field)
			{
				if(array_key_exists($custom_m_field['m_field_name'],$custom_data))
				{
					$custom_m_id = $custom_m_field['m_field_id'];
					$member_data['m_field_id_'.$custom_m_id] = $custom_data[$custom_m_field['m_field_name']];
					unset($custom_data[$custom_m_field['m_field_name']]);
				}
			}
		}
		
		if ( ! empty($member_data))
		{
			if ($profile_channel_id)
			{
				$member_data['channel_id'] = $profile_channel_id;
				
				$this->cartthrob_entries_model->update_entry($this->profile_model->get_profile_id($member_id), $member_data);
			}
			else
			{
				$this->member_model->update_member_data($member_id, $member_data);
			}
		}
		
		if ( ! empty($member))
		{
			$this->member_model->update_member($member_id, $member);
		}
	}
	
	// END
	/**
	 * get_member_id
	 *
	 * Returns the member id of the current user
	 * If logged out, it will return the member id of the oldest superadmin
	 * 
	 * @access public
	 * @author Chris Newton
	 * @return int
	 * @since 1.0
	 */
	public function get_member_id()
	{
		//get cached created member id if newly created member
		//or if creating an order on behalf of another member
		if (isset($this->session->cache['cartthrob']['member_id']))
		{
			return $this->session->cache['cartthrob']['member_id'];
		}
		
		//get logged in member id if logged in 
		if ($this->session->userdata('member_id'))
		{
			return $this->session->userdata('member_id');
		}
		
		//get the default logged out member id if set in the settings and valid
		if ($this->config->item('cartthrob:default_member_id') && (ctype_digit($this->config->item('cartthrob:default_member_id')) || is_int($this->cartthrob->store->config('default_member_id'))))
		{
			return $this->config->item('cartthrob:default_member_id');
		}
		
		static $oldest_superadmin;
		
		if (is_null($oldest_superadmin))
		{
			//get the oldest superadmin
			$oldest_superadmin = $this->db->select('member_id')
						      ->where('group_id', 1)
						      ->order_by('member_id', 'asc')
						      ->limit(1)
						      ->get('members')
						      ->row('member_id');
		}
		
		return $oldest_superadmin;
	}
	// END
	/**
	 * login_member
	 *
	 * @param string $member_id 
	 * @param string $username 
	 * @param string $password 
	 * @param string $unique_id
	 * @return void
	 * @author Chris Newton
	 * @since 2.0
	 * does not execute multi-logins 
	 */
	public function login_member($member_id)
	{
		$query = $this->db->from('members')
				  ->select('password, unique_id')
				  ->where('member_id', $member_id)
				  ->get();

		if ($query->num_rows() === 0)
		{
			$this->errors[] = $this->lang->line('unauthorized_access');
			
			return FALSE;
		}

		$this->lang->loadfile('login');

		if ($this->config->item('user_session_type') != 's')
		{
			$this->input->set_cookie($this->session->c_expire, time(), 0);
			$this->input->set_cookie($this->session->c_anon, 1, 0);
			
			if (version_compare(APP_VER, '2.1.5', '<'))
			{
				$this->functions->set_cookie($this->session->c_uniqueid, $query->row('unique_id'), 0);
				$this->functions->set_cookie($this->session->c_password, $query->row('password') , 0);
			}
		}

		$this->session->create_new_session($member_id, TRUE);
		/*
		// this function is not callable within EE, as it's a CI function
		if (! $this->session->userdata['screen_name'])
		{
			if (version_compare(APP_VER, '2.5.1', '>'))
			{
				$query = $this->db->where('member_id', $member_id)->get('members');
				if ($query->num_rows() !== 0)
				{
					$this->session->set_userdata( array_shift($query->result_array()) ); 
				}
			}
		}
		*/ 
		
		$this->session->delete_password_lockout();
		
		//we have to do this because the CSRF_TOKEN hash was already cleared by generating a new session with the new member id and needs to be restored,
		//CSRF_TOKEN should have already kicked in, in the case of a new member registration, so we should be good arbitrarily setting it here to get around secure forms. 
		if ($this->config->item('secure_forms') === 'y' && $this->input->post('csrf_token'))
		{
			if (version_compare(APP_VER, '2.5.5', '<'))
			{
				$this->db->insert('security_hashes', array('date' => time() - 60, 'ip_address' => $this->input->ip_address(), 'hash' => $this->input->post('csrf_token')));
			}
			else
			{
				$this->db->insert('security_hashes', array('date' => time() - 60, 'session_id' => $this->session->userdata('session_id'), 'hash' => $this->input->post('csrf_token')));
			}
		}
		
		
	}
	// END
	public function set_member_group($member_id, $group_id)
	{
		$this->load->model('member_model');
		$this->member_model->update_member($member_id, array('group_id' => $group_id)); 
	}
	public function activate_member($member_id, $group_id = NULL)
	{
		$this->load->model('member_model');

		$admin = in_array($this->session->userdata('group_id'), $this->config->item('cartthrob:admin_checkout_groups'));

		if ($this->config->item('req_mbr_activation') !== 'manual' && $this->config->item('req_mbr_activation') !== 'email')
		{
			if ($group_id)
			{
				$this->member_model->update_member($member_id, array('group_id' => $group_id)); 
			}
		}

		if ($this->cartthrob->store->config('checkout_registration_options') === 'auto-login' ||  
				($this->config->item('req_mbr_activation') !== 'manual' && $this->config->item('req_mbr_activation') !== 'email'))
		{
			$this->login_member($member_id);
		}
	}
	
	public function simulate_member($member_id, $callback, $args = NULL)
	{
		$query = $this->db->select('member_id, group_id, email')
				  ->where('member_id', $member_id)
				  ->get('members');
		
		$cache = array();
		
		foreach ($query->row_array() as $key => $value)
		{
			$cache[$key] = $this->session->userdata[$key];
			
			$this->session->userdata[$key] = $value;
		}
		
		if (is_null($args))
		{
			$return = call_user_func($callback);
		}
		else
		{
			$return = call_user_func_array($callback, $args);
		}
		
		foreach ($cache as $key => $value)
		{
			$this->session->userdata[$key] = $value;
		}
		
		return $return;
	}
}
// END CLASS
