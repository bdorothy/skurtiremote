<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @property CI_Controller $EE
 * @property Cartthrob_core_ee $cartthrob;
 */
class Cartthrob_set_currency_country_ext
{
	public $settings = array();
	public $name = 'CartThrob Set Country based on currency changed';
	public $version = '3.0';
	public $description = 'This extension will automatically set the default country based on currency selector';
	public $settings_exist = 'n';
	public $docs_url = 'http://sixth.co.in';
	
	
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
	
	
 	
	public function cartthrob_save_customer_info_start()
	{
		
	}
}

/* End of file ext.extension.php */
/* Location: ./system/expressionengine/third_party/extension/ext.extension.php */