<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2003 - 2011, EllisLab, Inc.
 * @license		http://expressionengine.com/user_guide/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.0
 * @filesource
 */
 
// ------------------------------------------------------------------------

/**
 * Siteconfig Module Control Panel File
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Liveshop
 * @link		http://www.sixth.co.in
 */

class Siteconfig_mcp {
	
	public $return_data;
	
	private $_base_url;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
	$this->EE =& get_instance();		
	$this->_base_url = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=siteconfig';
		
	
	
	
	if(ee()->session->userdata('group_id') == 1){
    $this->EE->cp->set_right_nav(array(
		'module_name_home'	=> $this->_base_url,
		'module_settings'	=> $this->_base_url.AMP.'method=settings',
		// Add more right nav items here.
	));
	}else{
	$this->EE->cp->set_right_nav(array(
		'module_home'	=> $this->_base_url,
		// Add more right nav items here.
	));
	}
		
	
	$this->EE->load->add_package_path(PATH_THIRD.'siteconfig/');
    $this->EE->load->library('siteconfig');
	
	
	
	}
	
	// ----------------------------------------------------------------

	/**
	 * Index Function
	 *
	 * @return 	void
	 */
	 
	 
	 

	
	// SETTINGS
	public function index()
	{	
	
	ee()->load->add_package_path(PATH_MOD.'rte/');
	ee()->load->library(array('javascript', 'rte_lib'));
	ee()->javascript->output(
    ee()->rte_lib->build_js(0, '.WysiHat-field', NULL, TRUE)
	);
	
	$settings['form_action'] = form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=siteconfig'.AMP.'method=save_settings');
	
	$settings['helpline'] = $this->EE->siteconfig->item('helpline');
	$settings['facebook_url'] = $this->EE->siteconfig->item('facebook_url');
	$settings['twitter_url'] = $this->EE->siteconfig->item('twitter_url');
	$settings['linkedin_url'] = $this->EE->siteconfig->item('linkedin_url');
	$settings['pinterest_url'] = $this->EE->siteconfig->item('pinterest_url');	
	$settings['picasa_url'] = $this->EE->siteconfig->item('picasa_url');
	$settings['youtube_url'] = $this->EE->siteconfig->item('youtube_url');
	$settings['vimeo_url'] = $this->EE->siteconfig->item('vimeo_url');
	$settings['zopim_api'] = $this->EE->siteconfig->item('zopim_api');
	$settings['google_analytics_api'] = $this->EE->siteconfig->item('google_analytics_api');	
	$settings['about_us'] = $this->EE->siteconfig->item('about_us');
	$settings['privacy_policy'] = $this->EE->siteconfig->item('privacy_policy');
	$settings['terms_conditions'] = $this->EE->siteconfig->item('terms_conditions');
	$settings['delivery_information'] = $this->EE->siteconfig->item('about_us');
	$settings['contact_us'] = $this->EE->siteconfig->item('delivery_information');
	$settings['faqs'] = $this->EE->siteconfig->item('faqs');
	$settings['return_policy'] = $this->EE->siteconfig->item('return_policy');
	$settings['shipping_policy'] = $this->EE->siteconfig->item('shipping_policy');
	$settings['default_shipping_duration'] = $this->EE->siteconfig->item('default_shipping_duration');
	$settings['register_benefits'] = $this->EE->siteconfig->item('register_benefits');
	$settings['usd_inr'] = $this->EE->siteconfig->item('usd_inr');
	$settings['gbp_inr'] = $this->EE->siteconfig->item('gbp_inr');
	$settings['eur_inr'] = $this->EE->siteconfig->item('eur_inr');
	$settings['aud_inr'] = $this->EE->siteconfig->item('aud_inr');
	$settings['cad_inr'] = $this->EE->siteconfig->item('cad_inr');
		
	return $this->EE->load->view('config_settings',$settings,TRUE);
	}
	
	
	
	// SAVE SETTINGS
	public function save_settings(){
	if(isset($_POST)){
	$other_settings = array('products_channel','orders_channel','skin_url','allow_wholesale');
	foreach($other_settings as $item){
	$settings[$item] = $this->EE->siteconfig->item($item);
	}
	
	$settings['helpline'] =  ee()->input->post('helpline');
	$settings['facebook_url'] =  ee()->input->post('facebook_url');
	$settings['twitter_url'] =  ee()->input->post('twitter_url');
	$settings['linkedin_url'] =  ee()->input->post('linkedin_url');
	$settings['pinterest_url'] =  ee()->input->post('pinterest_url');
	$settings['picasa_url'] =  ee()->input->post('picasa_url');
	$settings['youtube_url'] =  ee()->input->post('youtube_url');
	$settings['vimeo_url'] =  ee()->input->post('vimeo_url');
	$settings['zopim_api'] =  ee()->input->post('zopim_api');
	$settings['google_analytics_api'] =  ee()->input->post('google_analytics_api');
	$settings['about_us'] =  ee()->input->post('about_us');
	$settings['privacy_policy'] =  ee()->input->post('privacy_policy');
	$settings['terms_conditions'] =  ee()->input->post('terms_conditions');
	$settings['delivery_information'] =  ee()->input->post('delivery_information');
	$settings['contact_us'] =  ee()->input->post('contact_us');
	$settings['faqs'] =  ee()->input->post('faqs');
	$settings['return_policy'] =  ee()->input->post('return_policy');
	$settings['shipping_policy'] =  ee()->input->post('shipping_policy');
	$settings['default_shipping_duration'] =  ee()->input->post('default_shipping_duration');
	$settings['register_benefits'] =  ee()->input->post('register_benefits');
	$settings['usd_inr'] = ee()->input->post('usd_inr');
	$settings['gbp_inr'] = ee()->input->post('gbp_inr');
	$settings['eur_inr'] = ee()->input->post('eur_inr');
	$settings['aud_inr'] = ee()->input->post('aud_inr');
	$settings['cad_inr'] = ee()->input->post('cad_inr');
	
	
	
	$settings = base64_encode(serialize($settings));
		$data = array(
  		'settings' => $settings
			);		
	$this->EE->db->where('site_id', $this->EE->config->item('site_id'));
	$this->EE->db->update('siteconfig', $data); 	
	ee()->functions->redirect($this->_base_url);
	}
	}
	
	
	public function settings(){
	$settings['form_action'] = form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=siteconfig'.AMP.'method=save_site_settings');	
	ee()->db->select('channel_id,channel_title');
	$query = ee()->db->get('exp_channels');
	if ($query->num_rows() > 0){
		foreach ($query->result_array() as $row){
		 $data[$row['channel_id']] = $row['channel_title'];
		}
	}
	$settings['all_channels']	   =	$data;
	$settings['products_channel']  =    $this->EE->siteconfig->item('products_channel');
	$settings['orders_channel']    =   	$this->EE->siteconfig->item('orders_channel');
	$settings['skin_url'] 		   = 	$this->EE->siteconfig->item('skin_url');
	$settings['allow_wholesale']   = 	$this->EE->siteconfig->item('allow_wholesale');
	return $this->EE->load->view('site_settings',$settings,TRUE);
	}
	
	
	
	public function save_site_settings(){
	if(isset($_POST)){
	$other_settings = array('helpline','facebook_url','twitter_url','linkedin_url','pinterest_url','picasa_url','youtube_url','vimeo_url','zopim_api','google_analytics_api','about_us','privacy_policy','terms_conditions','delivery_information','contact_us','faqs','return_policy','shipping_policy','default_shipping_duration','register_benefits','usd_inr','gbp_inr','eur_inr','aud_inr','cad_inr');
	foreach($other_settings as $item){
	$settings[$item] = $this->EE->siteconfig->item($item);
	}
	$settings['products_channel'] =  ee()->input->post('products_channel');
	$settings['orders_channel'] =  ee()->input->post('orders_channel');
	$settings['skin_url'] =  		ee()->input->post('skin_url');
	$settings['allow_wholesale'] =  ee()->input->post('allow_wholesale');
	$settings = base64_encode(serialize($settings));
		$data = array(
  		'settings' => $settings
			);		
	$this->EE->db->where('site_id', $this->EE->config->item('site_id'));
	$this->EE->db->update('siteconfig', $data); 	
	ee()->functions->redirect($this->_base_url.AMP.'method=settings');
	}
	}
	
	
	
	
	


		
	
}
/* End of file mcp.siteconfig.php */
/* Location: /system/expressionengine/third_party/siteconfig/mcp.siteconfig.php */