<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once PATH_THIRD.'cartthrob/config.php';

require_once PATH_THIRD.'cartthrob/fieldtypes/ft.cartthrob_settings.php';

class Cartthrob_discount_ft extends Cartthrob_settings_ft
{
	public $info = array(
		'name' => 'CartThrob Discount Settings',
		'version' => CARTTHROB_VERSION,
	);
	
	public $prefix_only = FALSE;
    
	public $variable_prefix = '';

	public function __construct()
	{
		parent::__construct();
	}
	/**
	 * Display Field on Publish
	 *
	 * @access	public
	 * @param	$data
	 * @return	field html
	 *
	 */
	public function display_field($data)
	{
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		if (empty($this->EE->session->cache[__CLASS__]['display_field']))
		{
			$options = array();
			
			$this->fields = array();
			$this->EE->load->library('api/api_cartthrob_discount_plugins');
			
			foreach ($this->EE->api_cartthrob_discount_plugins->get_plugins() as $type => $plugin)
			{
				$options[$type] = lang($plugin['title']);
				
				foreach ($plugin['settings'] as $setting)
				{
					$setting['plugin_type'] = $type;
					
					$this->fields[] = $setting;
				}
			}
			
			foreach ($this->EE->api_cartthrob_discount_plugins->global_settings() as $setting)
			{
				$this->fields[] = $setting;
			}
			
			array_unshift($this->fields, array(
				'type' => 'select',
				'name' => 'Type',
				'short_name' => 'type',
				'extra' => ' class="cartthrob_discount_plugin"',
				'options' => $options
			));
		
			$this->EE->load->library('javascript');
			
			$this->EE->javascript->output('
				$(".cartthrob_discount_plugin").bind("change", function() {
					$(this).parents("table").eq(0).find("tbody tr").not(".global").hide().find(":input").attr("disabled", true);
					$(this).parents("table").eq(0).find("tbody tr."+$(this).val()).show().find(":input").attr("disabled", false);
				}).change();
			');
			
			$this->EE->session->cache[__CLASS__]['display_field'] = TRUE;
		}
		$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
		
		return parent::display_field($data);
	}
	
	/**
	 * replace_tag
	 *
	 * converts the discount settings to something that can be output
	 * 
	 * @param array $data 
	 * @param array $params 
	 * @param string|bool $tagdata 
	 * @return string
	 * @author Chris Newton
	 * 
	
	{exp:channel:entries
		channel='coupon_codes'
		url_title='test'}

		{coupon_code_type}
			{amount_off}
		{/coupon_code_type}

	{/exp:channel:entries}
	
	
	 */
	public function replace_tag($data, $params = array(), $tagdata = FALSE)
	{
		if (count($data) === 0 && preg_match('/'.LD.'if '.$this->variable_prefix.'no_results'.RD.'(.*?)'.LD.'\/if'.RD.'/s', $tagdata, $match))
		{
			$this->EE->TMPL->tagdata = str_replace($match[0], '', $this->EE->TMPL->tagdata);
			
			$this->EE->TMPL->no_results = $match[1];
		}
		
		if ( ! $data)
		{
			return $this->EE->TMPL->no_results();
		}
		
		// needs to be formatted as an array. should be, but just in case. 
		if (!is_array($data))
		{
			$data = array(); 
		}
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');

		$this->EE->load->helper('data_formatting');
		
 		$data = $this->prefix_only ? array_key_prefix($data, $this->variable_prefix) : array_merge($data, array_key_prefix($data, $this->variable_prefix));
		
		// data for this needs to be in rows
		$row[] = $data; 
		$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
		
 		return $this->EE->TMPL->parse_variables($tagdata, $row);
	}
}

/* End of file ft.cartthrob_discount.php */
/* Location: ./system/expressionengine/third_party/cartthrob_discount/ft.cartthrob_discount.php */