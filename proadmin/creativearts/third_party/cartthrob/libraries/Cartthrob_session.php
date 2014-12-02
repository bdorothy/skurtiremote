<?php

/**
 * The CartThrob Session class
 *
 * This is designed to allow members and guests to be associated with a cart in the database, with persistence
 */
class Cartthrob_session
{
	/**
	 * @var string|false The user's unique fingerprint, based on the session_fingerprint_method config, FALSE when fingerprinting turned off
	 */
	protected $fingerprint;
	
	/**
	 * @var int see Cartthrob_session::generate_fingerprint()
	 */
	protected $fingerprint_method = 0;
	
	/**
	 * @var string The user's unique session ID
	 */
	protected $session_id;
	
	/**
	 * @var int The cart associated with this session
	 */
	protected $cart_id;
	
	/**
	 * @var int The percent chance that garbage collection will occur
	 */
	protected $garbage_collection_probability = 5;
	
	/**
	 * @var int The length of time of the session cookie
	 */
	protected $expires = 7200;
	
	/**
	 * @var EE The EE controller/CI super object
	 */
	protected $EE;
	
	/**
	 * Constructor
	 * 
	 * @param array $params core (required), use_fingerprint, use_regenerate_id, 
	 * 
	 * @return void
	 */
	public function __construct($params = array())
	{
		$this->EE =& get_instance();
		
		$this->fingerprint_method = $this->EE->config->item('cartthrob:session_fingerprint_method');
		$this->fingerprint = $this->EE->config->item('cartthrob:session_use_fingerprint') ? $this->generate_fingerprint() : FALSE;
		
		if ($this->EE->config->item('cartthrob:session_expire') || $this->EE->config->item('cartthrob:session_expire') === '0')
		{
			$this->expires = $this->EE->config->item('cartthrob:session_expire');
		}
		else if ($this->EE->config->item('sess_expiration'))
		{
			$this->expires = $this->EE->config->item('sess_expiration');
		}
		
		
		if ( ! is_numeric($this->expires))
		{
			$this->expires = ini_get('sesssion.cookie_lifetime');
		}
		
		$this->session_id = (isset($params['session_id'])) ? $params['session_id'] : $this->EE->input->cookie('cartthrob_session_id');
		
		if ($this->session_id)
		{
			//if they're logged in we can just pull up the session by member id, since EE has already verified this user
			if ($this->EE->session->userdata('member_id'))
			{
				$this->EE->db->where('member_id', $this->EE->session->userdata('member_id'))
					     ->or_where('session_id', $this->session_id);
			}
			//otherwise we just pull from the session id and fingerprint if fingerprinting is active
			else
			{
				$this->EE->db->where('session_id', $this->session_id);
				
				if ($this->fingerprint !== FALSE)
				{
					$this->EE->db->where('fingerprint', $this->fingerprint);
				}
			}
			
			$query = $this->EE->db->order_by('expires', 'desc')
					      ->limit(1)
					      ->get('cartthrob_sessions');			
			
			if ($query->num_rows() === 0 || $query->row('expires') < @time())
			{
				//$this->garbage_collection();
				
				$this->generate_session();
			}
			else
			{
				$this->cart_id = $query->row('cart_id');
				
				//this is a roundabout way to make true the default
				if ( ! isset($params['use_regenerate_id']) || $params['use_regenerate_id'])
				{
					$this->regenerate_session_id();
				}
			}
		}
		//there's no session id, move on
		else
		{
			//let's see if there's a member based session for this user
			if ($this->EE->session->userdata('member_id'))
			{
				$this->EE->db->where('member_id', $this->EE->session->userdata('member_id'));
				
				$query = $this->EE->db->limit(1)->get('cartthrob_sessions');
				
				if ($query->num_rows() === 0 || $query->row('expires') < @time())
				{
					//$this->garbage_collection();
					
					$this->generate_session();
				}
				else
				{
					$this->cart_id = $query->row('cart_id');
					
					$this->session_id = $query->row('session_id');
					
					//don't regenerate on an ajax request
					if (( ! isset($params['use_regenerate_id']) || $params['use_regenerate_id']) && ! $this->EE->input->is_ajax_request())
					{
						$this->regenerate_session_id();
					}
				}
			}
			else
			{
				$this->generate_session();
			}
		}
		
		if ( ! $this->EE->config->item('cartthrob:garbage_collection_cron') && rand(1, 100) <= $this->garbage_collection_probability)
		{
			$this->garbage_collection();
		}
	}
	
	/**
	 * Destroy the session from database and destroys the session cookie
	 * 
	 * @return Cartthrob_session
	 */
	public function destroy()
	{
		$this->EE->db->delete('cartthrob_sessions', array('session_id' => $this->session_id));
		
		$this->EE->db->delete('cartthrob_cart', array('id' => $this->cart_id));
		
		$this->EE->input->set_cookie('cartthrob_session_id', '', -3600);
		
		return $this;
	}
	
	/**
	 * Create a new session in the database, and set the session id cookie
	 * 
	 * @return Cartthrob_session
	 */
	public function generate_session()
	{
		$this->session_id = $this->generate_session_id();
		
		$this->EE->load->model('cart_model');
		
		$this->cart_id = $this->EE->cart_model->create_cart();
		
		$this->EE->db->insert('cartthrob_sessions', array(
			'session_id' => $this->session_id,
			'member_id' => $this->EE->session->userdata('member_id'),
			'fingerprint' => $this->fingerprint,
			'cart_id' => $this->cart_id,
			'expires' => @time() + $this->expires,
		));
		// why the fuck is the expiry in EE's set_cookie function an optional parameter? 
		// if it's not added to this EE function, then the cookie automatically expires as soon as its created. how is that useful? 
		$this->EE->input->set_cookie('cartthrob_session_id', $this->session_id, $this->expires);
 		
		return $this;
	}
	
	/**
	 * Generate a unique fingerprint for the user
	 * 
	 * @return Type    Description
	 */
	public function generate_fingerprint()
	{
		switch($this->fingerprint_method)
		{
			//@TODO clear existing sessions when changing this setting
			case 1:
				$fingerprint = $this->EE->input->ip_address();
				break;
			case 2:
				$fingerprint = substr($this->EE->input->user_agent(), 0, 120);
				break;
			case 3:
				$fingerprint = $this->EE->input->ip_address().substr($this->EE->input->user_agent(), 0, 120);
				break;
			case 4:
				//rackspace ip
				$fingerprint = $this->EE->input->server('HTTP_X_FORWARDED_FOR');
				break;
			case 5:
				//rackspace ip + useragent
				$fingerprint = $this->EE->input->server('HTTP_X_FORWARDED_FOR').substr($this->EE->input->user_agent(), 0, 120);
				break;
			case 0:
			default:
				$fingerprint = $this->EE->input->server('HTTP_ACCEPT_LANGUAGE').$this->EE->input->server('HTTP_ACCEPT_CHARSET').$this->EE->input->server('HTTP_ACCEPT_ENCODING');
		}
		
		return sha1($this->EE->config->item('encryption_key').$fingerprint);
	}
	
	/**
	 * Generate a unique session id
	 * 
	 * @return string
	 */
	public function generate_session_id()
	{
		return md5(uniqid(rand(), TRUE));
	}
	
	/**
	 * Generate a new session id, or set one, for an existing session
	 *
	 * //@TODO this is wonky for some reason, something to do with the timing of the cookie setting, and kills logged out carts intermittently
	 * 
	 * @param unknown $session_id Description
	 * 
	 * @return Type    Description
	 */
	public function regenerate_session_id($session_id = NULL)
	{
		//@TODO
		return $this;
		
		if (is_null($session_id))
		{
			$session_id = $this->generate_session_id();
		}
		
		$old_session_id = $this->session_id;
		
		$this->session_id = $session_id;
		
		$this->update(array('session_id' => $this->session_id), $old_session_id);
		
		$this->EE->functions->set_cookie('cartthrob_session_id', $this->session_id, $this->expires);
 		
		return $this;
	}
	
	/**
	 * Removes expired sessions from the database
	 * 
	 * @return Cartthrob_session
	 */
	public function garbage_collection()
	{
		$this->EE->db->where('expires <', @time())->delete('cartthrob_sessions');
		
		return $this;
	}
	
	/**
	 * Get the current users's session_id
	 * 
	 * @return string
	 */
	public function session_id()
	{
		return $this->session_id;
	}
	
	/**
	 * Cart ID
	 * 
	 * @return int
	 */
	public function cart_id()
	{
		return $this->cart_id;
	}
	
	/**
	 * Visitor's fingerprint
	 *
	 * USE FOR DEBUG ONLY
	 * 
	 * @return string
	 */
	public function fingerprint()
	{
		return $this->fingerprint;
	}
	
	/**
	 * Expires
	 *
	 * Number of ms before session expires
	 * 
	 * @return int
	 */
	public function expires()
	{
		return $this->expires;
	}
	
	/**
	 * to array
	 * 
	 * @return array
	 */
	public function to_array()
	{
		return array(
			'session_id' => $this->session_id,
			'cart_id' => $this->cart_id,
			'fingerprint' => $this->fingerprint,
			'expires' => $this->expires,
		);
	}
	
	/**
	 * set cart id
	 * 
	 * @param int $cart_id
	 * 
	 * @return Cartthrob_session
	 */
	public function set_cart_id($cart_id)
	{
		$this->cart_id = $cart_id;
		
		$this->update(array('cart_id' => $this->cart_id));
		
		return $this;
	}
	
	/**
	 * set member ID
	 *
	 * Use this in a login hook, or when creating a member, to associate a guest cart with the member
	 * 
	 * @return Cartthrob_session
	 */
	public function set_member_id()
	{
		$this->update(array('member_id' => $this->EE->session->userdata('member_id')));
		
		return $this;
	}
	
	/**
	 * Update session in database
	 *
	 * Automatically updates the expires field
	 * 
	 * @param array $data fields to update in the database: cart_id, member_id, session_id
	 * @param string|null $session_id The session id to update, if null, it will use the current session id
	 * 
	 * @return Cartthrob_session
	 */
	public function update(array $data, $session_id = NULL)
	{
		$valid = array('cart_id', 'member_id', 'session_id');
		
		if (is_null($session_id))
		{
			$session_id = $this->session_id;
		}
		
		foreach ($data as $key => $value)
		{
			if ( ! in_array($key, $valid))
			{
				unset($data[$key]);
			}
		}
		
		$data['expires'] = @time() + $this->expires;
		
		$this->EE->db->update('cartthrob_sessions', $data, array('session_id' => $session_id));
		
		return $this;
	}
}