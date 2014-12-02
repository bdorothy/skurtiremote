<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @property CI_Controller $EE
 * @property Cartthrob_core_ee $cartthrob;
 */
class Cartthrob_validate_ext
{
	public $settings = array();
	public $name = 'CartThrob Validate';
	public $version = '1.0.0';
	public $description = 'Use this extension to set up special validation rules when updating the cart or customer data';
	public $settings_exist = 'n';
	public $docs_url = 'http://cartthrob.com';
	
	
	/**
	 * constructor
	 * 
	 * @access	public
	 * @param	mixed $settings = ''
	 * @return	void
	 */
	public function __construct($settings = '')
	{
		$this->EE =& get_instance();
		
		$this->settings = $settings;
	}
	
	/**
	 * activate_extension
	 * 
	 * @access	public
	 * @return	void
	 */
	public function activate_extension()
	{
		$data = array(
			'class'		=> __CLASS__,
			'method'	=> 'cartthrob_save_customer_info_start',
			'hook'		=> 'cartthrob_save_customer_info_start',
			'settings'	=> serialize($this->settings),
			'version'	=> $this->version,
			'enabled'	=> 'y'
		);
		$this->EE->db->insert('extensions', $data);			
		
		$data = array(
			'class'		=> __CLASS__,
			'method'	=> 'cartthrob_pre_process',
			'hook'		=> 'cartthrob_pre_process',
			'settings'	=> serialize($this->settings),
			'version'	=> $this->version,
			'enabled'	=> 'y'
		);

		$this->EE->db->insert('extensions', $data);
		
		$data = array(
			'class'		=> __CLASS__,
			'method'	=> 'cartthrob_update_cart_start',
			'hook'		=> 'cartthrob_update_cart_start',
			'settings'	=> serialize($this->settings),
			'version'	=> $this->version,
			'enabled'	=> 'y'
		);

		$this->EE->db->insert('extensions', $data);
	}
	
	/**
	 * update_extension
	 * 
	 * @access	public
	 * @param	mixed $current = ''
	 * @return	void
	 */
	public function update_extension($current = '')
	{
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}
		
		$this->EE->db->update('extensions', array('version' => $this->version), array('class' => __CLASS__));
	}
	
	/**
	 * disable_extension
	 * 
	 * @access	public
	 * @return	void
	 */
	public function disable_extension()
	{
		$this->EE->db->delete('extensions', array('class' => __CLASS__));
	}
	
	/**
	 * settings
	 * 
	 * @access	public
	 * @return	void
	 */
	public function settings()
	{
		$settings = array();
		
		return $settings;
	}
	
	private function validate()
	{
		$states = array("IL", "IA", "GA"); 
		if ($this->EE->input->post("state"))
		{
			if (in_array($this->EE->input->post("state"), $states))
			{
				$custom_data_post = $this->EE->input->post("custom_data"); 
				$county = NULL; 
				
				if (is_array($custom_data_post) && !empty($custom_data_post['county']))
				{
					$county = $custom_data_post['county']; 
				}
				if ( ! $this->EE->cartthrob->cart->custom_data('county') || !$county )
				{
					$this->EE->output->show_user_error('general', "You must select a county");
				}
			}
		}
	}
 	public function cartthrob_update_cart_start()
	{
		$this->validate(); 
	}
	public function cartthrob_pre_process()
	{
		$this->validate(); 
	}
	public function cartthrob_save_customer_info_start()
	{
		$this->validate(); 
	}
}

/* End of file ext.extension.php */
/* Location: ./system/expressionengine/third_party/extension/ext.extension.php */