<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Siteconfig_upd {
	
	public $version = '3.0';
	
	private $EE;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();		
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Installation Method
	 *
	 * @return 	boolean 	TRUE
	 */
	public function install()
	{	
		$skin = $this->EE->config->item('skin');
		include_once PATH_THIRD.'siteconfig/config.php';
		$mod_data = array(
			'module_name'			=> 'Siteconfig',
			'module_version'		=> $this->version,
			'has_cp_backend'		=> "y",
			'has_publish_fields'	=> 'n'
		);
		
		$this->EE->db->insert('modules', $mod_data);
		
		ee()->load->dbforge();
		
		$fields = array(
		'site_id'    				=> array('type' => 'int', 'constraint'  => '5'),
		'settings' 					=> array('type' => 'text'),	
		);
		
		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->create_table('siteconfig');
		unset($fields);
		
		
			
		// set the default settings
		
		// DEFAULT VALUES
		$settings['helpline'] = '';
		$settings['facebook_url'] = '';
		$settings['twitter_url'] = '';
		$settings['linkedin_url'] = '';
		$settings['pinterest_url'] = '';
		$settings['youtube_url'] = '';
		$settings['picasa_url'] = '';
		$settings['vimeo_url'] = '';
		$settings['zopim_api'] = '';
		$settings['google_analytics_api'] = '';
		$settings['about_us'] = '';		
		$settings['terms_conditions'] = $config['default_settings']['terms_conditions'];
		$settings['delivery_information'] = '';
		$settings['contact_us'] = '';
		$settings['faqs'] = '';
		$settings['return_policy'] = '';
		$settings['shipping_policy'] = '';
		$settings['default_shipping_duration'] = $config['default_settings']['default_shipping_duration'];
		$settings['privacy_policy'] = $config['default_settings']['privacy_policy'];
		$settings['products_channel'] = 1;
		$settings['orders_channel'] = 3;
		$settings['skin_url'] = '/theme/THEME_NAME/skin';
		$settings['allow_wholesale'] = 'no';
		$settings['register_benefits'] = '';
		$settings['usd_inr'] = '63';
		$settings['gbp_inr'] = '98';
		$settings['eur_inr'] = '78';
		$settings['aud_inr'] = '55';
		$settings['cad_inr'] = '56';
		
		
		
		$settings = base64_encode(serialize($settings));		
		$data = array(
		'site_id'	=> $this->EE->config->item('site_id'),
  		'settings' => $settings
			);		
		$this->EE->db->insert('siteconfig', $data); 	
	
		return TRUE;
		
	}

	// ----------------------------------------------------------------
	
	/**
	 * Uninstall
	 *
	 * @return 	boolean 	TRUE
	 */	
	public function uninstall()
	{
		$mod_id = $this->EE->db->select('module_id')
								->get_where('modules', array(
									'module_name'	=> 'Siteconfig'
								))->row('module_id');
		
		$this->EE->db->where('module_id', $mod_id)
					 ->delete('module_member_groups');
		
		$this->EE->db->where('module_name', 'Siteconfig')
					 ->delete('modules');
		
		$this->EE->load->dbforge();	
		$this->EE->dbforge->drop_table('siteconfig');
		
	
		return TRUE;
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Module Updater
	 *
	 * @return 	boolean 	TRUE
	 */	
	public function update($current = '')
	{
		// If you have updates, drop 'em in here.
		return TRUE;
	}
	
}
/* End of file upd.siteconfig.php */
/* Location: /system/expressionengine/third_party/siteconfig/upd.siteconfig.php */