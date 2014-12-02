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
*
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Extension
 * @author		Chris Newton (MIghtyBigRobot)
 * @link		http://cartthrob.com
 */

class Cartthrob_order_manager_ext {
	
	public $settings 		= array();
	public $description		= "Generic Description";
	public $docs_url		= 'http://cartthrob.com';
	public $name			= "Cartthrob Addon";
	public $settings_exist	= 'n';
	public $version; 
	public $required_by 	= array('module');
	public $testing 		= FALSE; // either FALSE, or 2 char country code.  
 	private $module_name; 
	private $remove_keys = array(
		'name',
		'submit',
		'x',
		'y',
		'templates',
		'XID',
		'CSRF_TOKEN'
	);
	
	private $EE;
	
	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	public function __construct($settings = '')
	{
		$this->EE =& get_instance();
		$this->module_name = strtolower(str_replace(array('_ext', '_mcp', '_upd'), "", __CLASS__));
		
		$this->EE->lang->loadfile($this->module_name);
		
		include PATH_THIRD.$this->module_name.'/config'.EXT;
		$this->name= lang($this->module_name. "_module_name"); 
		$this->version = $config['version'];
		$this->description = lang($this->module_name. "_description"); 
		
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		$this->EE->load->add_package_path(PATH_THIRD.$this->module_name."/"); 
		$this->EE->load->library('cartthrob_loader');

		$this->params = array(
			'module_name'	=> $this->module_name,
			); 
 		$this->EE->load->library('mbr_addon_builder');
		$this->EE->mbr_addon_builder->initialize($this->params);
		
		$this->EE->load->library('get_settings');
		$this->settings = $this->EE->get_settings->settings($this->module_name);
		
 		
	}
	

	// ----------------------------------------------------------------------
	
	/**
	 * Activate Extension
	 *
	 * This function enters the extension into the exp_extensions table
	 *
	 * @see http://codeigniter.com/user_guide/database/index.html for
	 * more information on the db class.
	 *
	 * @return void
	 */
	public function activate_extension()
	{
		return $this->EE->mbr_addon_builder->activate_extension(); 
	}	
 
	// ----------------------------------------------------------------------

	public function update_extension($current='')
	{
		return $this->EE->mbr_addon_builder->update_extension($current); 
	}
	public function disable_extension()
	{
		return $this->EE->mbr_addon_builder->disable_extension(); 
	}
	public function settings()
	{
		return array(); 
	}
 
	public function cp_menu_array($menu)
	{
		if ($this->EE->extensions->last_call !== FALSE)
		{
			$menu = $this->EE->extensions->last_call;
		}
		
		$this->EE->load->library('addons');
		
		$modules = $this->EE->addons->get_installed();
		
		if ( ! isset($modules["cartthrob"]['module_version']))
		{
			return $menu;
		}
		
		//if the user has uploaded a new version, but hasn't run the updater yet
		//this hook can cause some pretty bad errors if it tries to access database tables/fields
		//that aren't yet created
		//we're gonna kill this feature if we detect that they need an update
		//so they don't get any errors trying to get to the modules page to do the update
		if (REQ === 'CP')
		{
			//i'm not worried about the overhead from this since a) we're in the CP and b) Accessories lib calls this on every CP page
			$this->EE->load->library('addons');
			
			$modules = $this->EE->addons->get_installed();
			
			if ( ! isset($modules[$this->module_name]['module_version']) || version_compare($this->version, $modules[$this->module_name]['module_version'], '>'))
			{
				return $menu;
			}
		}
		
		$channels = array();

		$this->EE->lang->loadfile($this->module_name, $this->module_name);
		
		$this->EE->load->add_package_path(PATH_THIRD.$this->module_name.'/');
		
		$this->EE->load->library('get_settings');
		#$label = lang($this->module_name. "_cp_menu_label"); 
		$label= $this->module_name; 
		$this->EE->lang->language['nav_'.$this->module_name."_cp_menu_label"] =$label; 
				
		$menu[$label] = array();
		
		$menu[$label]['overview'] = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=om_sales_dashboard';
		$menu[$label]['orders'] = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=view';
		$menu[$label]['products'] = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=view_products';
		$menu[$label]['order_report'] = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=order_report';
		$menu[$label]['customer_report'] = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=customer_report';
		$menu[$label]['product_report'] = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=product_report';
		$menu[$label]['system_settings'] = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=system_settings';
		$menu[$label][] = "----";
				
		// if we don't have admin driven products, then hide the menu for this. 
		if (! $this->EE->cartthrob->store->config('show_product_admin'))
		{
			unset(
				$menu[$label]['products']
			); 
		}
				
		$has_channel = FALSE;
		
		if (isset($menu['content']['publish']) && is_array($menu['content']['publish']))
		{
			//we've got a perfectly good list of channels right here in menu, let's grab it
			foreach ($menu['content']['publish'] as $channel_name => $url)
			{
				if (preg_match('/channel_id=(\d+)$/', $url, $match))
				{
					$channels[$match[1]] = $channel_name;
				}
			}
		}
		else if (isset($menu['content']['publish'])  && is_string($menu['content']['publish']) && preg_match('/channel_id=(\d+)$/', $menu['content']['publish'], $match))
		{
			$channels[$match[1]] = '';
		}
		
		// need to load CT first, otherwise, the following stuff craps out
		$this->EE->load->library('cartthrob_loader'); 

 		if ($this->EE->cartthrob->store->config('product_channels'))
		{
			if (count($this->EE->cartthrob->store->config('product_channels')) > 1)
			{
				foreach ($this->EE->cartthrob->store->config('product_channels') as $channel_id)
				{
					if (isset($channels[$channel_id]))
					{
						$has_channel = TRUE;
						
						$this->EE->lang->language['nav_'.$channels[$channel_id]] = $channels[$channel_id];
						$menu[$label]['products'][$channels[$channel_id]] = BASE.AMP.'C=content_edit'.AMP.'channel_id='.$channel_id;
 					}
				}
			}
			else
			{
				$channel_id = current($this->EE->cartthrob->store->config('product_channels'));
				
				if (isset($channels[$channel_id]))
				{
					$has_channel = TRUE;
					
					$menu[$label]['products'] = BASE.AMP.'C=content_edit'.AMP.'channel_id='.$channel_id;
				}
			}
		}
		
		if ($this->EE->cartthrob->store->config('save_purchased_items') && $this->EE->cartthrob->store->config('purchased_items_channel'))
		{
			if (isset($channels[$this->EE->cartthrob->store->config('purchased_items_channel')]))
			{
				$has_channel = TRUE;
				
				$menu[$label]['purchased_items'] = BASE.AMP.'C=content_edit'.AMP.'channel_id='.$this->EE->cartthrob->store->config('purchased_items_channel');
			}
		}
		
		if ($this->EE->cartthrob->store->config('discount_channel'))
		{
			if (isset($channels[$this->EE->cartthrob->store->config('discount_channel')]))
			{
				$has_channel = TRUE;
				
				$menu[$label]['discounts'] = BASE.AMP.'C=content_edit'.AMP.'channel_id='.$this->EE->cartthrob->store->config('discount_channel');
			}
		}
		
		if ($this->EE->cartthrob->store->config('coupon_code_channel'))
		{
			if (isset($channels[$this->EE->cartthrob->store->config('coupon_code_channel')]))
			{
				$has_channel = TRUE;
				
				$menu[$label]['coupon_codes'] = BASE.AMP.'C=content_edit'.AMP.'channel_id='.$this->EE->cartthrob->store->config('coupon_code_channel');
			}
		}
		
		/*
		// this doesn't work because EE adds "nav_ " to the front of everythign
		$this->EE->load->model('generic_model');
		$reports_model = new Generic_model("cartthrob_order_manager_reports");
 					
		$order_reports = $reports_model->read(NULL,$order_by=NULL,$order_direction='asc',$field_name="type",$string="order" );

		if ($order_reports)
		{
			$menu[$label][] = "----";
			
			foreach ($order_reports as $rep)
			{
				$menu[$label][$rep['report_title']] = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=run_report'.AMP.'id='.$rep['id'];
 			}
		}
		*/
		
		
		$add_settings_menu = TRUE;
		
		if ($this->EE->session->userdata('group_id') != 1)
		{
			if ( ! $this->EE->session->userdata('assigned_modules') || ! $this->EE->cp->allowed_group('can_access_addons', 'can_access_modules'))
			{
				$add_settings_menu = FALSE;
			}
			else
			{
				$module_id = $this->EE->db->select('module_id')
							  ->where('module_name', 'Cartthrob_order_manager')
							  ->get('modules')
							  ->row('module_id');
				
				$assigned_modules = $this->EE->session->userdata('assigned_modules') ? $this->EE->session->userdata('assigned_modules') : array();
				
				if ( ! $module_id || ! array_key_exists($module_id, $assigned_modules))
				{
					$add_settings_menu = FALSE;
				}
			}
		}
		
		if ($add_settings_menu === TRUE)
		{
			/*
			require_once PATH_THIRD.'cartthrob/mcp.cartthrob_order_manager.php';
			
			$settings = array();
			
			foreach (array_keys(Cartthrob_order_manager_mcp::$nav) as $nav)
			{
				if (!in_array($nav, Cartthrob_order_manager_mcp::$no_nav))
				{
					$settings[$nav] = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob_order_manager_mcp'.AMP.'method='.$nav;
				}
			}
			
			$menu[$label][] = '----';
			
			$menu[$label]['settings'] = $settings;
			*/
		}
		#var_dump($menu[$label]); 
		if (empty($menu[$label]))
		{
			unset($menu[$label]);
		}
		
		return $menu;
	}
	
	public function cartthrob_addon_register($valid_addons)
    {
        if ($this->EE->extensions->last_call !== FALSE)
        {
            $valid_addons = $this->EE->extensions->last_call;
        }

        $addon_name = $this->module_name;
        if (strpos($this->module_name, "cartthrob_") !== FALSE)
        {
            $addon_name = str_replace("cartthrob_", "", $addon_name);
        }
        $valid_addons[] = $addon_name;

        return $valid_addons;
    }
}

/* End of file ext.price_field_changer_for_cartthrob.php */
/* Location: /system/expressionengine/third_party/price_field_changer_for_cartthrob/ext.price_field_changer_for_cartthrob.php */