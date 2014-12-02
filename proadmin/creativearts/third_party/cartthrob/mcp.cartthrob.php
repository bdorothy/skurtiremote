<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @property CI_Controller $EE
 * @property Cartthrob_cart $cart
 * @property Cartthrob_store $store
 */
class Cartthrob_mcp
{
	private $module_name; 
	public $required_settings = array();
	public $template_errors = array();
	public $templates_installed = array();
	public $extension_enabled = 0;
	public $module_enabled = 0;
	public $version;
	
	private $initialized = FALSE;
	
	public static $nav = array(
		'global_settings' => array(
			'general_settings' => 'nav_general_settings',
			'number_format_defaults' => 'nav_number_format_defaults',
			'default_location' => 'nav_default_location',
			'locales' => 'nav_locales',
			'set_license_number' => 'nav_set_license_number',
		),
		'product_settings' => array(
			'product_channels' => 'nav_product_channels',
			'product_options' => 'nav_product_options',
		),
		'order_settings' => array(
			'order_channel_configuration' => 'nav_order_channel_configuration',
			'purchased_items' => 'nav_purchased_items'
		),
		'shipping' => array(
			'shipping' => 'nav_shipping',
		),
		'taxes' => array(
			'tax' => 'nav_tax',
		),
		'coupons_discounts' => array(
			'coupon_options' => 'nav_coupon_options',
			'discount_options' => 'nav_discount_options',
		),
 		'notifications' => array(
			'notifications' => 'notifications',
		),
		'members' => array(
			'member_configuration' => 'nav_member_configuration',
		),
		'payment_gateways' => array(
			'payment_gateways' => 'nav_payment_gateways',
			'payment_security' => 'nav_payment_security',
		),
		'reports' => array(
			'reports' => 'reports',	
		),
		'installation' => array(
			'install_channels' => 'nav_install_channels',
			'template_variables' => 'nav_template_variables',
		),
		'import_export' => array(
			'import_settings' => 'nav_import_settings',
			'export_settings' => 'nav_export_settings',
		),
		'support' => array(
			'helpspot'	=> 'nav_support_request',
			'get_started' => 'nav_get_started',
			'support' => '',
		),
		'add_tax'	=> array(
			'add_tax'	=> '',
		),
		'edit_tax' => array(
			'edit_tax' => '',
		),
		'delete_tax' => array(
			'delete_tax' => '',
		),
		'update_skus_action' => array(
			'update_skus_action' => '',
		),

	);
	
	public static $subnav = array(); 
	/* public static $subnav = array(
		array(
			'subscriptions_settings',
			'subscriptions_list',
			'subscriptions_vaults',
			'subscriptions_permissions',
		),
	);
	*/
	
	public static $no_nav = array(
		'edit_tax',
		'add_tax',
		'delete_tax',
		'update_skus_action',
	); 
	private $remove_keys = array(
		'name',
		'submit',
		'x',
		'y',
		'templates',
		'XID',
		'CSRF_TOKEN'
	);
	
	public $no_form = array(
		'support',
		'import_export',
		'reports',
		'add_tax',
		'taxes',
		'edit_tax',
		'delete_tax',
		'subscriptions_list',
		'subscriptions_vaults',
		'subscriptions_permissions',
		'update_skus_action',
	);
	
	public function __construct()
	{
		$this->EE =& get_instance();
		$this->module_name = strtolower(str_replace(array('_ext', '_mcp', '_upd'), "", __CLASS__));
		
		$this->EE->load->helper('debug');
		
		include_once PATH_THIRD.'cartthrob/config.php';
		
		$this->helpspot_url = CARTTHROB_HELPSPOT_URL; 
		
	}
	
	private function initialize()
	{
		if ($this->initialized === TRUE)
		{
			return;
		}
		
		$this->initialized = TRUE;
		
		$this->EE->load->model('cartthrob_settings_model');
		
		$this->EE->load->model(array('field_model', 'channel_model', 'product_model'));
		
		//$this->EE->product_model->load_products($this->EE->cartthrob->cart->product_ids());
		
		$this->EE->load->library('locales');
		$this->EE->load->library('encrypt');
		$this->EE->load->library('languages');
		$this->EE->load->helper(array('security', 'data_formatting', 'form', 'file', 'string', 'inflector'));
		
		
		$this->EE->lang->loadfile('cartthrob', 'cartthrob');
		$this->EE->lang->loadfile('cartthrob_errors', 'cartthrob');
		
		$this->module_enabled = (bool) $this->EE->db->where('module_name', 'Cartthrob')->count_all_results('modules');
		$this->extension_enabled = (bool) $this->EE->db->where(array('class' => 'Cartthrob_ext', 'enabled' => 'y'))->count_all_results('extensions');
		
		$this->EE->load->library('cartthrob_payments');
		
		$this->EE->load->library('mbr_addon_builder'); 
		// someoday... someday we'll use this whole thing to build all of this.
		$this->EE->mbr_addon_builder->initialize(array(
				'module_name' => $this->module_name,
 		));
		
		
	}
	
	/**
	 * module_enabled
	 *
	 * use this to check if one or more modules are installed. If any one of the modules are not installed, false will be returned. 
	 * 
	 * @param string|array $modules list of modules to check
	 * @param string $prefix. If not set, Cartthrob_ will be used. 
	 * @return boolean
	 * @author Chris Newton
	 */
	public function module_enabled($modules, $prefix="Cartthrob_")
	{
		$EE =& get_instance();
		
		$classes = array(); 
		if (!is_array($modules))
		{
 			$query = $EE->db->select('module_name')
					->where_in('module_name', $prefix. $modules)
					->get('modules');
			
			if ($query->result())
			{
				$query->free_result();
				
				return TRUE; 
			}
		}
		else
		{
			foreach($modules as $module)
			{
				$query = $EE->db->select('module_name')
						->where_in('module_name', $prefix. $module)
						->get('modules');
				
				if (! $query->result())
				{
					return FALSE; 
				}	
				$query->free_result();
						
 			}
			return TRUE;
		}
		
		return FALSE; 
	}

	public static function nav()
	{
		static $built = FALSE;
		
		if ($built === TRUE)
		{
			return self::$nav;
		}
		
		$built = TRUE;
		
		$EE =& get_instance();
		
		/*
		// EE chokes in template publishing with any CT tag on _validate when we're loading ct payments lib
		$EE->load->library('cartthrob_payments');
		
		if ($EE->cartthrob_payments->module_enabled('subscriptions'))
		{
			self::$nav['subscriptions_settings'] = array('subscriptions_settings' => '');
		}
		*/
		
		if (self::module_enabled('subscriptions'))
		{
			self::$nav['subscriptions_settings'] = array('subscriptions_settings' => '');
		}		
		
		$modules = array(
			'Cartthrob_order_manager'	=> 'om_sales_dashboard',
			'Ct_admin' => 'order_admin',
			'Cartthrob_item_options' => 'global_item_options',
		);
		
		foreach ($modules as $key => $module)
		{
			if (self::module_enabled($key, NULL))
			{
				self::$nav['modules'][$key] = array( $key => $modules[$key]);
			}
		}
 		return self::$nav;
	}
	
	public function index()
	{
		$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob'.AMP.'method=global_settings');
	}
	
	public function update()
	{
		require_once PATH_THIRD.'cartthrob/upd.cartthrob.php';
		
		$upd = new Cartthrob_upd;
		
		$upd->sync();
		
		$this->index();
	}
	
	/* CP Controller */
	// pass the request on to the cartthrob_cp library
	public function global_settings()
	{
		return $this->load_view(__FUNCTION__);
	}
	
	public function order_admin()
	{
		$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=ct_admin');
	}
	
	public function global_item_options()
	{
		$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob_item_options');
	}
	
	public function product_settings()
	{
		return $this->load_view(__FUNCTION__);
	}
	
	public function order_settings()
	{
		return $this->load_view(__FUNCTION__);
	}
	
	public function shipping()
	{
		return $this->load_view(__FUNCTION__);
	}
	public function coupons_discounts()
	{
		return $this->load_view(__FUNCTION__);
	}
	
	public function email_notifications()
	{
		return $this->load_view(__FUNCTION__);
	}
	
	public function subscriptions_settings()
	{
		$this->EE->load->library('cartthrob_payments');
		
		if (! $this->module_enabled('subscriptions'))
		{
			return show_error('subscriptions_not_enabled');//@TODO LANG
		}
		
		$this->EE->load->library('api/api_cartthrob_payment_gateways');
		
		$vars = array();
		
		array();
		
		$this->EE->load->library('paths');
		
		$vars['crontabulous_url'] = $this->EE->paths->build_action_url('Cartthrob_mcp', 'crontabulous_get_pending_subscriptions');
		
		$vars['gateways'] = $this->EE->api_cartthrob_payment_gateways->subscription_gateways();
		
		$this->EE->cp->add_js_script(array(
			'ui' => array('core', 'widget', 'progressbar')
		));
		
		return $this->load_view(__FUNCTION__, $vars, array());
	}
	
	//@TODO
	/**
	 * "Edit"-style list of subscriptions
	 * 
	 * @return string    the view
	 */
	public function subscriptions_list()
	{
		return $this->load_view(__FUNCTION__);
	}
	
	//@TODO
	/**
	 * "Edit"-style list of vaults
	 * 
	 * @return string    the view
	 */
	public function subscriptions_vaults()
	{
		return $this->load_view(__FUNCTION__);
	}
	
	//@TODO
	/**
	 * "Edit"-style list of permissions
	 * 
	 * @return string    the view
	 */
	public function subscriptions_permissions()
	{
		return $this->load_view(__FUNCTION__);
	}
	
	public function notifications()
	{
		$this->EE->load->model(array('field_model', 'channel_model', 'product_model'));

		$channels = $this->EE->channel_model->get_channels()->result_array();
		
		$external_app_events = array(); 
		foreach ($this->EE->db->get('cartthrob_notification_events')->result() as $row)
		{
 			$external_app_events[$row->application."_".$row->notification_event] = lang($row->application).": ".lang($row->notification_event);
 		}
 		if (!empty($external_app_events))
		{
				$email_events = array(
					'payment_triggers' => array(
						'completed' 	=> 'ct_completed',
						'declined' 		=> 'ct_declined',
						'failed' 		=> 'ct_failed',
						'offsite' 		=> 'ct_offsite',
						'processing'	=> 'ct_processing',
						'refunded' 		=> 'ct_refunded',
						'expired' 		=> 'ct_expired',
						'canceled' 		=> 'ct_canceled',
						'pending'		=> 'ct_pending',
						),
					// why is status change set to blank? 
					'other_events'	=> array(
						'low_stock'		=> 'ct_low_stock',
						''	=> 'status_change'
						),
					'application_events' =>  $external_app_events
					);
		}
		else
		{
			$email_events = array(
				'payment_triggers' => array(
					'completed' 	=> 'ct_completed',
					'declined' 		=> 'ct_declined',
					'failed' 		=> 'ct_failed',
					'offsite' 		=> 'ct_offsite',
					'processing'	=> 'ct_processing',
					'refunded' 		=> 'ct_refunded',
					'expired' 		=> 'ct_expired',
					'canceled' 		=> 'ct_canceled',
					'pending'		=> 'ct_pending',
					),
				// why is status change set to blank? 
				'other_events'	=> array(
					'low_stock'		=> 'ct_low_stock',
					''	=> 'status_change'
					)
				);
		}
		$structure['class']	= 'notifications'; 
		$structure['stacked'] = TRUE; 
		$structure['description']	=''; 
		$structure['caption']	=''; 
		$structure['title']	= "notifications"; 
		
		
	 	$structure['settings'] = array(
			array(
				'name' => 'log_email',
				'note'	=> 'log_email_note',
				'short_name' => 'log_email',
				'default'	=> 'no',
				'type' => 'select',
				'options' => array(
				     'no'	=> 'no',
					'log_only'	=> 'log_only',
					'log_and_send'	=> 'log_and_send',
				),
			), 
			array(
				'name' => 'notifications',
				'short_name' => 'notifications',
				'type' => 'matrix',
				'settings' => array(
					array(
						'name' => 'email_subject',
						'short_name' => 'email_subject',
						'type' => 'text', 
					),
					array(
						'name'			=>	'email_from_name',
						'short_name'	=>	'email_from_name',
						'type'			=>	'text',
					),
					array(
						'name'			=>	'email_from',
						'short_name'	=>	'email_from',
						'type'			=>	'text',
					),
					array(
						'name'			=>	'email_reply_to_name',
						'note'			=>	'email_reply_to_note',
						'short_name'	=>	'email_reply_to_name',
						'type'			=>	'text',
					),
					array(
						'name'			=>	'email_reply_to',
						'short_name'	=>	'email_reply_to',
						'type'			=>	'text',
					),
					array(
						'name'			=>	'email_to',
						'short_name'	=>	'email_to',
						'type'			=>	'text',
						'default'		=> '{customer_email}'
					),
					array(
						'name'=>'email_template',
						'short_name'=>'email_template',
						'type'=>'select',
						'attributes' => array(
							'class' 	=> 'templates',
							),
					),
					array(
						'name' => "cartthrob_initiated_event", 
						'short_name' => 'email_event', 
						'type' => 'select',
						'default' => '',
						'options' => $email_events
					),
					array(
						'name'=>'starting_status',
						'short_name'=>'status_start',
						'type'=>'select',
						'default'	=> '---',
						'attributes' => array(
							'class' 	=> 'statuses_blank',
							),
					),
					array(
						'name'=>'ending_status',
						'short_name'=>'status_end',
						'type'=>'select',
						'default'	=> '---',
						'attributes' => array(
							'class' 	=> 'statuses_blank',
							),
					),
					array(
						'name' => "email_type", 
						'short_name' => 'email_type', 
						'type' => 'select',
						'default' => 'html',
						'options' => array(
							'html' => 'send_html_email',
							'text' => 'send_text_email', 
							) 
					),
				)
			),
	 	);
 		return $this->load_view(__FUNCTION__, array(), $structure);
	}
	
	public function payment_gateways()
	{
		return $this->load_view(__FUNCTION__);
	}
	public function helpspot_ajax_fields()
	{
		$category_id = $this->EE->input->post("category_id"); 

		$categories = $this->helpspot_categories(); 
		$categories = element('category', $categories, array()); 
		
		if ( ! $category_id)
		{
			$category_id = element("xCategory", $categories[0]);
		}
		if ( ! AJAX_REQUEST)
		{
#			exit;
		}		
		
		$helpspot_labels = $this->helpspot_field_labels(); 
		$custom_fields = $this->helpspot_custom_fields(); 
		$custom_fields = element("field", $custom_fields); 
		
		$html = NULL; 
 		foreach ($categories as $category)
		{
 			if (element("xCategory", $category) !== $category_id)
			{
				continue; 
			} 
			$cf = element('sCustomFieldList', $category); 
 			$cat_fields = element('xCustomField', $cf); 

		$ignored_cats = array(9, 8, 7, 1,2,20, 16 /*profile:edit version*/); 
 
			$list = array(); 

			foreach ($custom_fields as  $value)
			{
 				if (in_array(element('xCustomField', $value),  $cat_fields) && !in_array(element('xCustomField', $value), $ignored_cats))
				{
					$id = element("xCustomField", $value); 
					
					$html .= "<label>".element('Custom'.$id, $helpspot_labels)."</label> "; 
					switch(element('fieldType', $value))
					{
                     case 'select':
                            $html .= "<select name=\"Custom".element("xCustomField", $value)."\">";
                            foreach (element("item", $value['listItems']) as $option)
                            {
                                $html .= "<option value=\"$option\">$option</option>";
                            }
                            $html .= "</select>";
                            break;
                        case 'checkbox':
                            $html .= "<input type=\"checkbox\" name=\"Custom".element("xCustomField", $value)."\" value=\"1\" />";
                            break;
                        case 'lrgtext':
                            $html .= (element("lrgTextRows", $value) !='')?"<textarea name=\"Custom".element("xCustomField", $value)."\" rows=\"".element("lrgTextRows", $value)."\"></textarea>":"<textarea name=\"Custom".element("xCustomField", $value)."\"></textarea>";
                            break;
                        case 'date':
                            $html .= "<input type=\"text\" class=\"datepicker\" name=\"Custom".element("xCustomField", $value)."\" value=\"\" />";
                            break;
                        case 'text':
                        default:
                            $html .= (element("sTxtSize", $value)!='')?"<input type=\"text\" name=\"Custom".element("xCustomField", $value)."\" value=\"\" maxlength=\"".element("sTxtSize", $value)."\" />":"<input type=\"text\" name=\"Custom".element("xCustomField", $value)."\" value=\"\" />";
                            break;
				
					}
					$html.="<br>"; 
				}
			}
		}
		echo $html; exit; 
	}
	public function support()
	{
		// initiates the cartthrob:whatever_setting needed for license number
		$this->initialize(); 
		$this->EE->load->helper("array");
		$requests = $this->helpspot_list(); 
		$helpspot_labels = $this->helpspot_field_labels(); 
		
		$first_name = ""; 
		$last_name = ""; 
		
		if (!empty($requests))
		{
			$req = $requests[0]; 
			$first_name = element('sFirstName', $req); 
			$last_name = element('sLastName', $req); 
		}
		
		$cats = $this->helpspot_categories(); 
		$categories = array(); 
		foreach (element("category", $cats) as $c)
		{
			$categories[element('xCategory', $c)]= element('sCategory', $c); 
		}

		// @TODO there' probably a better way to do this with some built in function
		 $query = $this->EE->db->select('action_id')
							->where('method','helpspot_ajax_fields')
							->limit('1')
							->get('actions');
		$ajax_url = NULL; 
		if ($query->result() && $query->row() )
		{
			$ajax_url = $this->EE->functions->fetch_site_index(0, 0).QUERY_MARKER.'ACT='.$query->row('action_id');
 		}
		
		$category_switcher = '
					$.post("'.$ajax_url.'", function(data) {
						$("#support-form-custom-fields").html(data);
					});
					$("#xCategory").change(function() {
						$.post("'.$ajax_url.'", { category_id: $(this).val() } , function(data) {
							$("#support-form-custom-fields").html(data);
						});
					});

		';
 		$this->EE->javascript->output($category_switcher);

  		return $this->load_view(
			__FUNCTION__,
			array(
				'helpspot_create_form' => form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob'.AMP.'method=helpspot_create', array('enctype' => 'multipart/form-data')),
				'helpspot_update_form' => form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob'.AMP.'method=helpspot_update', array('enctype' => 'multipart/form-data')),
				'requests'	=> $requests,
				'helpspot_labels'	=> $helpspot_labels, 
				'first_name'	=> $first_name,
				'last_name'		=> $last_name,
				'categories'	=> $categories,
			)
		);
	}
	
	public function helpspot_member_create()
	{
		$this->EE->load->helper(array('security', 'string', 'text'));
		
		$data['group_id'] = 1;
		if ($this->EE->config->item('req_mbr_activation') === 'email')
		{
			$data['authcode'] = $this->EE->functions->random('alnum', 10);
		}
		$email_address = uniqid("support_"). "_@livecart.com";
		$password = $this->EE->functions->random('alpha', 16);
		
		$data['email'] = $email_address;
		$data['screen_name'] = $email_address;
		$data['username'] = $email_address; 
		
		$data['password'] = sha1(stripslashes($password));

		$data['ip_address'] = $this->EE->input->ip_address();
		$data['unique_id'] = $this->EE->functions->random('encrypt');

		$data['join_date'] = $this->EE->localize->now;
		$data['timezone'] = ($this->EE->config->item('default_site_timezone') && $this->EE->config->item('default_site_timezone') != '') ? $this->EE->config->item('default_site_timezone') : $this->EE->config->item('default_site_timezone');
		$data['daylight_savings'] = ($this->EE->config->item('default_site_dst') && $this->EE->config->item('default_site_dst') != '') ? $this->EE->config->item('default_site_dst') : $this->EE->config->item('daylight_savings');
		$data['time_format'] = ($this->EE->config->item('time_format') && $this->EE->config->item('time_format') != '') ? $this->EE->config->item('time_format') : 'us';
		
		$this->EE->load->model('member_model');
 		
	 	$member_id = $this->EE->member_model->create_member($data, array());
 		return $data; 
	}
	public function helpspot_create()
	{
		include_once PATH_THIRD.'cartthrob/libraries/HelpSpotAPI'.EXT;
		
		if (!$this->EE->config->item('cartthrob:license_number'))
		{
			$this->EE->session->set_flashdata('message_failure', sprintf('%s', lang('helpspot_requires_license_number') ));	
			$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob');
		}
 		$hsapi = new HelpSpotAPI(array("helpSpotApiURL" => $this->helpspot_url)); 

		$post_data = array(); 

		$post_data['Custom9'] = $this->EE->config->item('site_url'). BASE;  // admin page
		$post_data['Custom22'] = APP_VER. " - ". APP_BUILD;  // EE version info page
		$post_data['Custom21'] = $this->version(); // cartthrob version
		if ($this->EE->input->post('grant_access'))
		{
			$data = $this->helpspot_member_create(); 
			
			$post_data['Custom8'] = element('username', $data); 
			$post_data['Custom7'] = element('password', $data); 
			
		}
		foreach ($this->helpspot_custom_fields() as $value)
		{
			if ($this->EE->input->post(element('xCustomField', $value)))
			{
				$post_data[element('xCustomField', $value)] = $this->EE->input->post(element('xCustomField', $value)); 
			}
		}
		$request = array_merge(
			array(
				'output'		=> 'PHP',
				'fUrgent'		=> ($this->EE->input->post('fUrgent') ? $this->EE->input->post('fUrgent') : 0),
				'sUserId'		=> $this->EE->session->userdata('username'),
				'sFirstName' => $this->EE->input->post('first_name'),
				'sLastName'	=> $this->EE->input->post('last_name'),
				'sTitle'	=> $this->EE->input->post('sTitle'),
				'sEmail' => $this->EE->session->userdata('email'),
				'tNote' => $this->EE->input->post('tNote'),
				'xCategory'	=> $this->EE->input->post('xCategory'),
				'Custom20'	=> $this->EE->config->item('cartthrob:license_number')
				),
			$post_data
		); 
		
 		$result = $hsapi->requestCreate($request);
		
		if ($result)
		{
			$this->EE->load->helper('array'); 
			
			$access_key = element("accesskey", $result['request']); 
			$this->EE->db->insert('helpspot_support', array('access_key'=> $access_key)); 

			$this->EE->session->set_flashdata('message_success', sprintf('%s', lang('helpspot_submission_successful') ));	
			$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob'.AMP.'method=support');
			
		}
		$this->EE->session->set_flashdata('message_failure', sprintf('%s', lang('helpspot_submission_failed') ));	
		$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob'.AMP.'method=support');
		
	}
	
	public function helpspot_categories()
	{
		include_once PATH_THIRD.'cartthrob/libraries/HelpSpotAPI'.EXT;
		
		$this->EE->load->helper('array'); 
		include_once PATH_THIRD.'cartthrob/libraries/HelpSpotAPI'.EXT;
		
		$hsapi = new HelpSpotAPI(array("helpSpotApiURL" => $this->helpspot_url)); 
 		
		$result = $hsapi->requestGetCategories(array(
						'output'=> 'PHP',
				));
				
		return element("categories", $result);
		
	}
	public function helpspot_update()
	{
		include_once PATH_THIRD.'cartthrob/libraries/HelpSpotAPI'.EXT;
		
		if (!$this->EE->config->item('cartthrob:license_number'))
		{
			$this->EE->session->set_flashdata('message_failure', sprintf('%s', lang('helpspot_requires_license_number') ));	
			$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob');
		}
 		$hsapi = new HelpSpotAPI(array("helpSpotApiURL" => $this->helpspot_url)); 

		$post_data = array(); 

		$post_data['Custom9'] = $this->EE->config->item('site_url'). BASE;  // admin page
		$post_data['Custom2'] = APP_VER. " - ". APP_BUILD;  // EE version info page
		$post_data['Custom1'] = $this->version(); // cartthrob version
		if ($this->EE->input->post('grant_access'))
		{
			$data = $this->helpspot_member_create(); 
			
			$post_data['Custom8'] = element('username', $data); 
			$post_data['Custom7'] = element('password', $data); 
		}
		foreach ($this->helpspot_custom_fields() as $value)
		{
			if ($this->EE->input->post(element('xCustomField', $value)))
			{
				$post_data['Custom'.element('xCustomField', $value)] = $this->EE->input->post(element('xCustomField', $value)); 
			}
		}
		$request = array_merge(
			array(
				'output'		=> 'PHP',
				'accesskey'	=> $this->EE->input->post("accesskey"),
				'tNote' => $this->EE->input->post('tNote'),
				'fUrgent'		=> ($this->EE->input->post('fUrgent') ? $this->EE->input->post('fUrgent') : 0),
				'Custom20'	=> $this->EE->config->item('cartthrob:license_number')
				),
			$post_data
		); 
		
		$result = $hsapi->requestUpdate($request);
		
		if ($result)
		{
			$this->EE->load->helper('array'); 
			
			$req = element("xRequest", $result['request']); 

			$this->EE->session->set_flashdata('message_success', sprintf('%s', lang('helpspot_update_submission_successful') ));	
			$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob'.AMP.'method=support');
			
		}
		
		$this->EE->session->set_flashdata('message_failure', sprintf('%s', lang('helpspot_update_submission_failed') ));	
		$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob'.AMP.'method=support');
		
	}
	
	public function helpspot_field_labels()
	{
		$this->EE->load->helper('array'); 
		include_once PATH_THIRD.'cartthrob/libraries/HelpSpotAPI'.EXT;
		
		$hsapi = new HelpSpotAPI(array("helpSpotApiURL" => $this->helpspot_url)); 
 		
		$result = $hsapi->utilGetFieldLabels(array(
						'output'=> 'PHP',
				));
		return element("labels", $result); 
		
	}
	public function helpspot_custom_fields()
	{
		$this->EE->load->helper('array'); 
		include_once PATH_THIRD.'cartthrob/libraries/HelpSpotAPI'.EXT;
		
		$hsapi = new HelpSpotAPI(array("helpSpotApiURL" => $this->helpspot_url)); 
 		
		$result = $hsapi->requestGetCustomFields(array(
						'output'=> 'PHP',
				));
		return element("customfields", $result);
	}
	public function helpspot_list($status="1")
	{
		include_once PATH_THIRD.'cartthrob/libraries/HelpSpotAPI'.EXT;

		$query = $this->EE->db->get('helpspot_support'); 
 
 		$hsapi = new HelpSpotAPI(array("helpSpotApiURL" => $this->helpspot_url)); 

 		$requests = array(); 
		if ($query->result() && $query->num_rows() > 0)
		{
			$q = $query->result_array(); 
			$q = array_reverse($q); 
			reset($q); 
			foreach ($q as $row)
			{
				$result = $hsapi->requestGet(array(
								'output'=> 'PHP',
								'accesskey'		=> (string) $row['access_key'],
 							));
  				
				if (!empty($result['request']))
				{
					if ($status)
					{
						if ($status == element('fOpen', $result['request']))
						{
							// need access key to update
							$result['request']['accesskey']  = (string) $row['access_key']; 
							$requests[]= $result['request']; 
						}
 					}
				}
 			}

			return $requests; 
		}
		
		return array(); 
	}
	
	public function members()
	{
		$profile_edit_active = FALSE; 
		if (isset($this->EE->extensions->extensions['channel_form_submit_entry_start'][10]['Profile_ext']))
		{
			$profile_edit_active = TRUE; 
		}
		return $this->load_view(
			__FUNCTION__,
			array(
				'profile_edit_active' => $profile_edit_active,
			)
		);
	}
	
	public function import_export()
	{
		return $this->load_view(
			__FUNCTION__,
			array(
				'form_open' => form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob'.AMP.'method=import_settings', array('enctype' => 'multipart/form-data')),
			)
		);
	}
	
	public function installation()
	{
		return $this->load_view(
			__FUNCTION__,
			array(
				'form_open' => form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob'.AMP.'method=install_templates'),
				'template_errors' => ($this->EE->session->flashdata('template_errors')) ? $this->EE->session->flashdata('template_errors') : array(),
				'templates_installed' => ($this->EE->session->flashdata('templates_installed')) ? $this->EE->session->flashdata('templates_installed') : array(),
				'theme_errors' => ($this->EE->session->flashdata('theme_errors')) ? $this->EE->session->flashdata('theme_errors') : array(),
				'themes_installed' => ($this->EE->session->flashdata('themes_installed')) ? $this->EE->session->flashdata('themes_installed') : array(),
				'themes' => $this->get_themes(),
			)
		);
	}
	
	private function load_view($current_nav, $more = array(), $structure = array())
	{
		$this->EE->load->library('cartthrob_payments');
		
		if ( ! $this->EE->config->item('encryption_key'))
		{
			$this->EE->cp->cp_page_title =  $this->EE->lang->line('cartthrob_module_name').' - '.$this->EE->lang->line('encryption_key');
			
			return $this->EE->load->view('encryption_key', array(), TRUE);
		}
		
		$this->EE->load->library('addons');
		
		$modules = $this->EE->addons->get_installed();
		
		if ( ! isset($modules['cartthrob']['module_version']) || version_compare($this->version(), $modules['cartthrob']['module_version'], '>'))
		{
			$this->EE->cp->cp_page_title =  $this->EE->lang->line('cartthrob_module_name').' - '.$this->EE->lang->line('update_required');
			
			return $this->EE->load->view('update_required', array(), TRUE);
		}
		
		if ($this->module_enabled('subscriptions'))
		{
			$this->EE->load->add_package_path(PATH_THIRD.'cartthrob_subscriptions/');
		}
		
		$this->initialize();
		
		// check if we need to use the old or the new installer.xml
		if(version_compare(APP_VER, '2.6', '<'))
		{
			$this->EE->load->library('package_installer', array('xml' => PATH_THIRD.'cartthrob/installer/installer_legacy.xml'));
		}
		else
		{
			$this->EE->load->library('package_installer', array('xml' => PATH_THIRD.'cartthrob/installer/installer.xml'));
		}
		
		$this->EE->cp->cp_page_title =  $this->EE->lang->line('cartthrob_module_name').' - '.$this->EE->lang->line('nav_'.$current_nav);
		
		$vars = array();
		
		// if it's not set to TRUE, and there's an uninintialized setting (since the default settings haven't been loaded) we're screwed
 		$settings = $this->get_saved_settings(TRUE);

		if ($this->module_enabled('subscriptions'))
		{
			if (!isset($settings['purchased_items_sub_id_field']))
			{
				$settings['purchased_items_sub_id_field'] = NULL; 
			}
		}
		elseif (isset($settings['purchased_items_sub_id_field']))
		{
			unset($settings['purchased_items_sub_id_field']); 
		}
		
		$site_id = NULL; 
		if ($this->EE->config->item('cartthrob:msm_show_all'))
		{
			$site_id = "all";
		}
		$channels = $this->EE->channel_model->get_channels($site_id)->result_array();
		
		$fields = array();
		
		$channel_titles = array();
		
		$statuses = array();
		
		foreach ($channels as $channel)
		{
			$channel_titles[$channel['channel_id']] = $channel['channel_title'];
			
			// $fields[$channel['channel_id']] = $this->EE->field_model->get_fields($channel['field_group'])->result_array();
			// only want to capture a subset of data, because we're using this for JSON and we were getting too much data previously
			$channel_fields = $this->EE->field_model->get_fields($channel['field_group'])->result_array(); 
			foreach ($channel_fields as $key => &$data)
			{
				/*
				This is 5.2 only... sigh this will eventually replace the 3 lines below. 
				$fields[$channel['channel_id']][$key] = array_intersect_key($data, array_fill_keys(array('field_id', 'site_id', 'group_id', 'field_name', 'field_type', 'field_label'), TRUE));
				*/ 
				$array_fill_keys= array('field_id', 'site_id', 'group_id', 'field_name', 'field_type', 'field_label'); 
				$combined = array_combine($array_fill_keys,array_fill(0,count($array_fill_keys),TRUE));

				$fields[$channel['channel_id']][$key] = array_intersect_key($data,$combined);
			}
			
			$statuses[$channel['channel_id']] = $this->EE->channel_model->get_channel_statuses($channel['status_group'])->result_array();
		}
		$status_titles = array(); 
		foreach ($statuses as $status)
		{
			foreach ($status as $item)
			{
				$status_titles[$item['status']] = $item['status']; 
			}
		}
		if ( ! empty($settings['product_channels']))
		{
			foreach ($settings['product_channels'] as $i => $channel_id)
			{
				if ( ! isset($channel_titles[$channel_id]))
				{
					unset($settings['product_channels'][$i]);
				}
			}
		}
		
		if ( ! empty($settings['product_channel_fields']))
		{
			foreach ($settings['product_channel_fields'] as $channel_id => $values)
			{
				if ( ! isset($channel_titles[$channel_id]))
				{
					unset($settings['product_channel_fields'][$channel_id]);
				}
			}
		}
		
		$nav = self::nav();
		$no_nav = self::$no_nav; 
		
		$settings_views = array();

		$view_paths = array();
		
		// -------------------------------------------
		// 'cartthrob_add_settings_nav' hook.
		//
		if ($this->EE->extensions->active_hook('cartthrob_add_settings_nav') === TRUE)
		{
			if ($addl_nav = $this->EE->extensions->call('cartthrob_add_settings_nav', $nav))
			{
				$nav = array_merge($nav, $addl_nav);
			}
		}
		
		// -------------------------------------------
		// 'cartthrob_add_settings_views' hook.
		//
		if ($this->EE->extensions->active_hook('cartthrob_add_settings_views') === TRUE)
		{
			$settings_views = $this->EE->extensions->call('cartthrob_add_settings_views', $settings_views);
		}
		
		if (is_array($settings_views) && count($settings_views))
		{
			foreach ($settings_views as $key => $value)
			{
				if (is_array($value))
				{
					if (isset($value['path']))
					{
						$view_paths[$key] = $value['path'];
					}
					
					if (isset($value['title']))
					{
						$nav['more_settings'][$key] = $value['title'];
					}
				}
				else
				{
					$nav['more_settings'][$key] = $value;
				}
			}
		}
		
		$sections = array();
		
		foreach ($nav as $top_nav => $_nav)
		{
			if ($top_nav != $current_nav)
			{
				continue;
			}
			
			foreach ($_nav as $url_title => $section)
			{
				if ( ! preg_match('/^http/', $url_title))
				{
					$sections[] = $url_title;
				}
			}
		}

		$this->EE->load->model('member_model');
		
		$member_fields = array('' => '----');
		
		if ($this->EE->cartthrob->store->config('use_profile_edit') && isset($this->EE->extensions->extensions['channel_form_submit_entry_start'][10]['Profile_ext']))
		{
			$this->EE->load->add_package_path(PATH_THIRD.'profile/');
			
			$this->EE->load->model('profile_model');
			
			$this->EE->load->remove_package_path(PATH_THIRD.'profile/');
			
			if ($profile_edit_channel_id = $this->EE->profile_model->settings('channel_id'))
			{
				//profile might be on a different MSM site, therefore it might not already be in the $fields array
				//let's double check
				if (isset($fields[$profile_edit_channel_id]))
				{
					$profile_fields = $fields[$profile_edit_channel_id];
				}
				else
				{
					$site_id = $this->EE->profile_model->site_id();
					
					if ($site_id != $this->EE->config->item('site_id'))
					{
						$this->EE->cartthrob_field_model->load_fields($site_id);
					}
					
					$profile_fields = $this->EE->cartthrob_field_model->get_fields_by_channel($profile_edit_channel_id);
				}
				
				foreach ($profile_fields as $field)
				{
					$member_fields[$field['field_id']] = $field['field_label'];
				}
			}
		}
		else
		{
			foreach ($this->EE->member_model->get_all_member_fields(array(), FALSE)->result() as $row)
			{
				$member_fields[$row->m_field_id] = $row->m_field_label;
			}
		}

		$member_groups = array();

		$query = $this->EE->member_model->get_member_groups(array(), array('group_id >=' => 5));

		foreach ($query->result() as $row)
		{
			$member_groups[$row->group_id] = $row->group_title;
		}

		$query->free_result();
		
		if ( ! version_compare(APP_VER, '2.2', '<'))
		{
			foreach ($view_paths as $path)
			{
				$this->EE->load->add_package_path($path);
			}
		}
		
		$this->EE->load->library('paths');
		
		$data = array(
			'structure'	=> $structure, 
			'nav' => $nav,
			'subnav' => $this->has_subnav($current_nav),
			'current_nav' => $current_nav,
			'sections' => $sections,
			'channels' => $channels,
			'channel_titles' => $channel_titles,
			'fields' => $fields,
			'statuses' => $statuses,
			'status_titles' => $status_titles,
			'templates' => array('' => $this->EE->lang->line('choose_a_template')),
			'payment_gateways' => $this->get_payment_gateways(),
			'shipping_plugins' => $this->get_shipping_plugins(),
			'tax_plugins' => $this->get_tax_plugins(),
			//'news' => $this->get_news(),
			'install_channels' => array(),
			'install_template_groups' => array(),
			'install_member_groups' => array(),
			'view_paths' => $view_paths,
			'cartthrob_mcp' => $this,
			'form_open' => form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob'.AMP.'method=quick_save'.AMP.'return='.$this->EE->input->get('method', TRUE)),
			'extension_enabled' => $this->extension_enabled,
			'module_enabled' => $this->module_enabled,
			'settings' => $settings,
			'orders_status'	 => $settings['orders_status'],
			'states_and_countries' => array_merge(array('global' => 'Global', '' => '---'), $this->EE->locales->states(), array('0' => '---'), $this->EE->locales->all_countries()),
			'states' => $this->EE->locales->states(),
			'countries' => $this->EE->locales->all_countries(),
			'no_form' => (in_array($current_nav, $this->no_form)),
			'no_nav'	=> $no_nav,
			'member_fields' => $member_fields,
			'member_groups' => $member_groups,
			'customer_data_fields' => array(
				'first_name',
				'last_name',
				'address',
				'address2',
				'city',
				'state',
				'zip',
				'country',
				'country_code',
				'company',
				'phone',
				'email_address',
				'use_billing_info',
				'shipping_first_name',
				'shipping_last_name',
				'shipping_address',
				'shipping_address2',
				'shipping_city',
				'shipping_state',
				'shipping_zip',
				'shipping_country',
				'shipping_country_code',
				'shipping_company',
				'language',
				'shipping_option',
				'region'
			),
		);
		
		$data['templates'] = $this->get_templates();

		foreach ($this->EE->package_installer->packages() as $index => $template)
		{
			switch($template->getName())
			{
				case 'channel':
					$data['install_channels'][$index] = $template->attributes()->channel_title;
					break;
				case 'template_group':
					$data['install_template_groups'][$index] = $template->attributes()->group_name;
					break;
				case 'member_group':
					$data['install_member_groups'][$index] = $template->attributes()->group_name;
					break;
			}
		}
		
		if (!empty($structure))
		{
			$data['html'] = $this->EE->load->view('settings_template', $data, TRUE);
		}
		$data = array_merge($data, $more);
		
		$self = $data;
		
		$data['data'] = $self;
		
		unset($self);
		
		$this->EE->cp->add_js_script('ui', 'accordion');
		
		if (version_compare(APP_VER, '2.2', '<'))
		{
			$this->EE->cp->add_to_head('<link href="'.URL_THIRD_THEMES.'cartthrob/css/cartthrob.css" rel="stylesheet" type="text/css" />');
			$this->EE->cp->add_to_foot($this->EE->load->view('settings_form_head', $data, TRUE));
			
			$output = $this->EE->load->view('settings_form', $data, TRUE);
		}
		else
		{
			//$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
			
			$this->EE->cp->add_to_head('<link href="'.URL_THIRD_THEMES.'cartthrob/css/cartthrob.css" rel="stylesheet" type="text/css" />');
			$this->EE->cp->add_to_foot($this->EE->load->view('settings_form_head', $data, TRUE));
			
			$output = $this->EE->load->view('settings_form', $data, TRUE);
			
			foreach ($view_paths as $path)
			{
				$this->EE->load->remove_package_path($path);
			}
		}
		
		return $output;
	}
	public function configurator_ajax()
	{
		if ( ! AJAX_REQUEST)
 		{
			$debug= FALSE; // change this as needed. should always be off in production
		}
		else
		{
			$debug = FALSE; // don't change this value. 
		}
		
		if ($debug)
		{
			if (!empty($_POST))
			{
				@session_start(); 
				$_SESSION['post_data'] = $_POST; 
 			}
		}
 		$html = NULL; 
 		if ( ! AJAX_REQUEST)
		{
			if ($debug)
			{
				@session_start(); 
 				if (empty($_SESSION['post_data']))
				{
					// test data
			$_POST =   array (
				  'field_id_81' => 
				  array (
				    0 => 
				    array (
				"all_values" =>"",
				"option_value"=>"1515",
				"option_name"=>"111",
				"price"=> "111",
				      'option_group' => 'size',
				      'options' => 
				      array (
				        'option' => 
				        array (
				          2 => 'small',
				          290428430 => 'medium',
				          1034489027 => 'large',
				        ),
				        'price' => 
				        array (
				          2 => '10',
				          290428430 => '20',
				          1034489027 => '30',
				        ),
				        'option_template' => '',
				        'price_template' => '',
				      ),

				    ),
				    1 => 
				    array (
				      'option_group' => 'color',
				      'options' => 
				      array (
				        'option' => 
				        array (
				          2 => 'red',
				          4824268948 => 'blue',
				        ),
				        'price' => 
				        array (
						          2 => '',
						          4824268948 => '',
				        ),
				        'option_template' => '',
				        'price_template' => '',
				      ),
				    ),
				  ),
				  'opt_field_name' => '81',
				  'CSRF_TOKEN' => '{CSRF_TOKEN}',
				); 
				}
				else
				{
					$_POST = $_SESSION['post_data']; 
				}
			}
		}
		
		/*
		if (REQ !== 'CP' && ! $this->EE->security->secure_forms_check($this->EE->input->post('csrf_token')))
		{
			if (!$debug)
			{
				exit;
			}
		}
		*/ 

		$this->EE->load->helper(array("data_formatting_helper", "html", 'array', 'form', 'array'));
		
		$field_id = element('opt_field_name', $_POST); 
		$field_id_name = 'field_id_'. $field_id; 

		$p_opt = element($field_id_name, $_POST, array() ) ; 

		$html = NULL; 
		
		$options = array(); 
		$prices = array(); 
		$saved_all_values = array(); 
		$saved_option_value = array(); 
		$saved_option_label = array(); 
		$saved_price = array();
		$saved_inventory = array(); 
		 
		foreach ($p_opt as $key => $value)
		{
			if (element('options', $value))
			{
				$o = element('option', $value['options']); 
				if ($o)
				{
					foreach ($o as $k => $v)
					{
						$price = element($k, $value['options']['price']);
						if (!$price)
						{
							$price = 0; 
						} 
						$options[element('option_group', $value)][] = $v; 
						$prices[element('option_group', $value)][] = $price; 
					}
				}
			}
			
			if (array_key_exists('option_value', $value))
			{
				$price = element('price', $value); 
				if (!$price)
				{
					$price =0; 
				}
				$saved_all_values[] =  element('all_values', $value); 
				$saved_option_value[] = element('option_value', $value); 
				$saved_option_label[] = element('option_name', $value); 
				$saved_price[] = $price; 
		 		$saved_inventory[] = element('inventory', $value); 
 			}
 		}
		$final_options = array(); 
		$final_prices = array(); 
 		$final_options = cartesian($options);
		$final_prices = cartesian($prices);
		$prices = cartesian_to_price($final_prices);

 		$all_values = array(); 
		$option_value = array(); 
		$option_label = array(); 
		$price = array(); 
		$inventory = array(); 
		
		foreach ($final_options as $k => $v)
		{
			$cost = element($k, $prices); 
			if (!$cost)
			{
				$cost = 0; 
			}
			$all_values[$k] = base64_encode(serialize($v));
			$option_value[$k] = ""; //implode("-",$v); // sku
			$option_label[$k] = ""; // ucwords(str_replace("_" , " ", implode(", ",$v))); // name
			$price[$k] = $cost; 
			$inventory[$k] = "";
		}
 		if (count($saved_all_values) && !empty($saved_all_values) && is_array($saved_all_values))
		{
	 		$copy = $final_options;
	
 			foreach ($saved_all_values as $key => $value)
			{
				$opt =  @unserialize(base64_decode($value)); 
 				
				if (is_array($copy) && is_array($opt))
				{
				 	foreach($copy as $k => $v)
					{
						$temp_arr = array_intersect_assoc($opt, $v); 
 	
						if (count($temp_arr) == count($opt))
						{
 							$all_values[$k] = base64_encode(serialize($v));
							$option_value[$k] = element($key, $saved_option_value); 
							$option_label[$k] = element($key, $saved_option_label); 
							//$price[$k] = element($key, $saved_price); 
							$inventory[$k] = element($key, $saved_inventory); 
		  					unset($copy[$k]); 
						}
					}
				}
	 		}
		}


		$data = array(
			'all_values'	=> $all_values,
			'option_value'  => $option_value, 
			'option_label'  => $option_label, 
			'price'  		=> $price,
			'inventory' 	=> $inventory,
			'options' 		=> $final_options,
			'field_id'		=> $field_id,
			'field_id_name' => $field_id_name,
			'show_inventory' => element('show_inventory', $_POST, 0 ),
		); 
 		
		$html = NULL; 
		$html .= $this->EE->load->view('configurator', $data, TRUE);

  		if (!$html)
		{
			$html = "could not be loaded"; 
		}
		
		@ob_start();
		var_dump($data);
		$output = @ob_get_clean();
		
		if ($debug)
		{
			$html.=$output; 
			echo "<pre>";
			var_export($output); 
			echo "</pre>";
			exit; 
		}
		else
		{
			$this->EE->output->send_ajax_response(array('success' => $html, 'CSRF_TOKEN' => $this->EE->functions->add_form_security_hash('{CSRF_TOKEN}')));
		}
	}

	public function reports()
	{
		if ($this->EE->input->get('save'))
		{
			$reports_settings = $this->EE->input->post('reports_settings');
			
			if (is_array($reports_settings) && isset($reports_settings['reports']))
			{
				$_POST = array('reports' => $reports_settings['reports']);
			}
			else
			{
				$_POST = array('reports' => array());
			}
			
			$_GET['return'] = 'reports';
			
			return $this->quick_save();
		}
		
		$this->initialize();
		
		if ($this->EE->input->get('entry_id'))
		{
			$this->EE->functions->redirect(BASE.AMP.'C=content_publish'.AMP.'M=entry_form'.AMP.'entry_id='.$this->EE->input->get('entry_id'));
		}
		
		$this->EE->load->library('reports');
		
		$this->EE->load->library('number');
		
		if ($this->EE->input->get_post('report'))
		{
			$this->EE->load->library('template_helper');
			
			$this->EE->template_helper->reset(array(
				'base_url' => BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob'.AMP.'method=reports'.AMP.'report=',
				'template_key' => 'report',
			));
			
			$data['view'] = $this->EE->template_helper->cp_render();
		}
		//default view
		else
		{
			if ($this->EE->input->get('year'))
			{
				if ($this->EE->input->get('month'))
				{
					if ($this->EE->input->get('day'))
					{
						$name = date('D d', mktime(0, 0, 0, $this->EE->input->get('month'), $this->EE->input->get('day'), $this->EE->input->get('year')));
						
						$rows = $this->EE->reports->get_daily_totals($this->EE->input->get('day'), $this->EE->input->get('month'), $this->EE->input->get('year'));
						
						$overview = lang('narrow_by_order');
					}
					else
					{
						$name = date('F Y', mktime(0, 0, 0, $this->EE->input->get('month'), 1, $this->EE->input->get('year')));
						
						$rows = $this->EE->reports->get_monthly_totals($this->EE->input->get('month'), $this->EE->input->get('year'));
						
						$overview = lang('narrow_by_day');
					}
				}
				else
				{
					$name = $this->EE->input->get('year');
					
					$rows = $this->EE->reports->get_yearly_totals($this->EE->input->get('year'));
					
					$overview = lang('narrow_by_month');
				}
			}
			else
			{
				$name = $this->EE->lang->line('reports_order_totals_to_date');
				
				$rows = $this->EE->reports->get_all_totals();
				
				$overview = lang('narrow_by_month');
			}
		
			if ($rows)
			{
				$this->EE->javascript->output('cartthrobChart('.json_encode($rows).', "'.$name.'");');
			}
			
			$data['view'] = $this->EE->load->view('reports_home', array('overview' => $overview), TRUE);
		}
		
		$this->EE->load->library('table');
		
		$this->EE->table->clear();
		
		$this->EE->table->set_template(array('table_open' => '<table border="0" cellpadding="0" cellspacing="0" class="mainTable padTable">'));
		
		$data['order_totals'] = $this->EE->table->generate(array(
			array(lang('order_totals'), lang('amount')),
			array(lang('today_sales'), $this->EE->number->format($this->EE->reports->get_current_day_total())),
			array(lang('month_sales'), $this->EE->number->format($this->EE->reports->get_current_month_total())),
			array(lang('year_sales'), $this->EE->number->format($this->EE->reports->get_current_year_total())),
		));
		
		$data['current_report'] = $this->EE->input->get_post('report');
		
		$data['reports'] = array(
			'' => lang('order_totals'),
		);
		
		if ($this->EE->config->item('cartthrob:reports'))
		{
			foreach ($this->EE->config->item('cartthrob:reports') as $report)
			{
				$data['reports'][$report['template']] = $report['name'];
			}
		}
		
		$plugin_vars = array(
			'cartthrob_mcp' => $this,
			'settings' => array(
				'reports_settings' => array(
					'reports' => $this->EE->config->item('cartthrob:reports'),
				),
			),
			'plugin_type' => 'reports',
			'plugins' => array(
				array(
					'classname' => 'reports',
					'title' => 'reports_settings_title',
					'overview' => 'reports_settings_overview',
					'settings' => array(
						array(
							'name' => 'reports',
							'short_name' => 'reports',
							'type' => 'matrix',
							'settings' => array(
								array(
									'name' => 'report_name',
									'short_name' => 'name',
									'type' => 'text'
								),
								array(
									'name' => 'report_template',
									'short_name' => 'template',
									'type' => 'select',
									'options' => $this->get_templates(),
								),
							)
						)
					)
				)
			),
		);
		
		$data['reports_list'] = $this->EE->load->view('plugin_settings', $plugin_vars, TRUE);
		
		return $this->load_view(__FUNCTION__, $data);
	}
	
	// --------------------------------
	//  Plugin Settings
	// --------------------------------
	/**
	 * Creates setting controls
	 * 
	 * @access private
	 * @param string $type text|textarea|radio The type of control that is being output
	 * @param string $name input name of the control option
	 * @param string $current_value the current value stored for this input option
	 * @param array|bool $options array of options that will be output (for radio, else ignored) 
	 * @return string the control's HTML 
	 * @since 1.0.0
	 * @author Rob Sanchez
	 */
	public function plugin_setting($type, $name, $current_value, $options = array(), $attributes = array())
	{
		$output = '';
		
		if ( ! is_array($options))
		{
			$options = array();
		}
		else
		{
			$new_options = array(); 
			foreach ($options as $key => $value)
			{
				// optgropus
				if (is_array($value))
				{	
					$key = lang($key); 
					foreach ($value as $sub=> $item)
					{
						$new_options[$key][$sub] = lang($item);
					}
				}
				else
				{
					$new_options[$key] = lang($value);
				}
			}
			$options = $new_options; 
		}
		
		if ( ! is_array($attributes))
		{
			$attributes = array();
		}

		switch ($type)
		{
			case 'add_to_head':
				$output = NULL; 
				
				if (strpos($current_value, '<script') !== FALSE)
				{
	 				$this->EE->cp->add_to_foot($current_value); 
				}
				else
				{
	 				$this->EE->cp->add_to_head($current_value); 
				}
 			 	break;
			case 'add_to_foot':
				$output = NULL; 
 				$this->EE->cp->add_to_foot($current_value); 
			 	break;		
			case 'note':
				$output = $current_value;
				break;
			case 'select':
				if (empty($options)) $attributes['value'] = $current_value;
				$output = form_dropdown($name, $options, $current_value, _attributes_to_string($attributes));
				break;
			case 'multiselect':
				$output = form_multiselect($name."[]", $options, $current_value, _attributes_to_string($attributes));
				break;
			case 'checkbox':
				$output = form_checkbox($name, 1, ! empty($current_value), isset($options['extra']) ? $options['extra'] : '')
							.'&nbsp;'. form_label((!empty($options['label'])? $options['label'] : $this->EE->lang->line('yes') ), $name);
				break;
			case 'text':
				$attributes['name'] = $name;
				$attributes['value'] = $current_value;
				$output =  form_input($attributes);
				break;
			case 'textarea':
				$attributes['name'] = $name;
				$attributes['value'] = $current_value;
				$output =  form_textarea($attributes);
				break;
			case 'radio':
				if (empty($options))
				{
					$output .= form_label(form_radio($name, 1, (bool) $current_value).'&nbsp;'. $this->EE->lang->line('yes'), $name, array('class' => 'radio'));
					$output .= form_label(form_radio($name, 0, (bool) ! $current_value).'&nbsp;'. $this->EE->lang->line('no'), $name, array('class' => 'radio'));
				}
				else
				{
					//if is index array
					if (array_values($options) === $options)
					{
						foreach($options as $option)
						{
							$output .= form_label(form_radio($name, $option, ($current_value === $option)).'&nbsp;'. $option, $name, array('class' => 'radio'));
						}
					}
					//if associative array
					else
					{
						foreach($options as $option => $option_name)
						{
							$output .= form_label(form_radio($name, $option, ($current_value === $option)).'&nbsp;'. lang($option_name), $name, array('class' => 'radio'));
						}
					}
				}
				break;
			default:
		}
		return $output;
	}
	// END
	public function email_test()
	{
		if ( ! AJAX_REQUEST)
		{
			exit;
		}
		
		if (REQ !== 'CP')
		{
			exit;
		}
		
		$this->EE->load->library('cartthrob_emails');
		$email_event = $this->EE->input->post('email_event'); 
		if (!$email_event)
		{
			$emails = $this->EE->cartthrob_emails->get_email_for_event(NULL, "open", "closed"); 
		}
		else
		{
			$emails = $this->EE->cartthrob_emails->get_email_for_event($email_event); 
		}
		if (!empty($emails))
		{
			$test_panel = array(
				'inventory'						=> 5,
				'billing_address'              =>	'Test Avenue'	,
				'billing_address2'             =>	'Apt 1',
				'billing_city'                 =>	'Testville',
				'billing_company'              =>	'Testco',
				'billing_country'              =>	'United States',
				'billing_country_code'         =>	'USA',
				'billing_first_name'           =>	'Testy',
				'billing_last_name'            =>	'Testerson',
				'billing_state'                =>	'MO',
				'billing_zip'                  =>	'63303',
				'customer_email'               =>	'test@yoursite.com',
				'customer_name'                =>	'Test Testerson',
				'customer_phone'               =>	'555-555-5555',
				'discount'                     =>	'0.00',
				'entry_id'                     =>	'111',
				'group_id'                     =>	'1',
				'member_id'                    =>	'1',
				'order_id'                     =>	'111',
				'shipping'                     =>	'10',
				'shipping_plus_tax'            =>	'10.80',
				'subtotal'                     =>	'110.00',
				'subtotal_plus_tax'            =>	'123.45',
				'tax'                          =>	'13.45',
				'title'                        =>	'111',
				'total'                        =>	'123.45',
				'total_cart'                   =>	'123.45'	,
				'transaction_id'               => "12345678"	,
			);
			foreach ($emails as $email_content)
			{
				$this->EE->cartthrob_emails->send_email($email_content, $test_panel); 
			}
		}
		
 		//forces json output
		$this->EE->output->send_ajax_response(array('CSRF_TOKEN' => $this->EE->functions->add_form_security_hash('{CSRF_TOKEN}')));
	}
	public function save_price_modifier_presets_action()
	{
		if ( ! AJAX_REQUEST)
		{
			exit;
		}
		
		if (REQ !== 'CP' && ! $this->EE->security->secure_forms_check($this->EE->input->post('csrf_token')))
		{
			exit;
		}
		
		$this->EE->db->from('cartthrob_settings')
				->where('`key`', 'price_modifier_presets')
				->where('site_id', $this->EE->config->item('site_id'));
		
		$presets = ($this->EE->input->post('price_modifier_presets')) ? $this->EE->input->post('price_modifier_presets', TRUE) : array();
		
		$value = array();
		
		foreach ($presets as $preset)
		{
			if ( ! is_array($preset['values']))
			{
				continue;
			}
			
			$value[$preset['name']] = $preset['values'];
		}
		
		$data = array(
			'value' => serialize($value),
			'serialized' => 1,
		);
		
		if ($this->EE->db->count_all_results() == 0)
		{
			$data['site_id'] = $this->EE->config->item('site_id');
			$data['`key`'] = 'price_modifier_presets';
			
			$this->EE->db->insert('cartthrob_settings', $data);
		}
		else
		{
			$this->EE->db->update(
				'cartthrob_settings',
				$data,
				array(
					'site_id' => $this->EE->config->item('site_id'),
					'`key`' => 'price_modifier_presets',
				)
			);
		}
		
		//forces json output
		$this->EE->output->send_ajax_response(array('CSRF_TOKEN' => $this->EE->functions->add_form_security_hash('{CSRF_TOKEN}')));
	}
	
	private function json_response($data)
	{
		$this->EE->load->library('javascript');
		
		if ($this->EE->config->item('send_headers') == 'y')
		{
			$this->EE->load->library('user_agent', NULL, 'user_agent');
			
			//many browsers do not consistently like this content type
			//array('Firefox', 'Mozilla', 'Netscape', 'Camino', 'Firebird')
			if (0 && is_array($msg) && in_array($this->EE->user_agent->browser(), array('Safari', 'Chrome')))
			{
				@header('Content-Type: application/json');
			}
			else
			{
				@header('Content-Type: text/html; charset=UTF-8');	
			}
		}
		
		die(json_encode($data));
	}
	
	// END
	// --------------------------------
	//  Save Settings
	// --------------------------------
	/**
	 * Validates, cleans, saves data, reports errors if fields were not filled in, saves and updates CartThrob settings in the database
	 * 
	 * @access public
	 * @param NULL
	 * @return void
	 * @since 1.0.0
	 * @author Rob Sanchez
	 */
	public function install_templates()
	{
		$this->initialize();
		
		if (version_compare(APP_VER, '2.2', '<'))
		{
			$orig_view_path = $this->EE->load->_ci_view_path;
			
			$this->EE->load->_ci_view_path = PATH_THIRD.'cartthrob/views/';
			
			$this->EE->load->library('package_installer', array('xml' => PATH_THIRD.'cartthrob/installer/installer.xml'));
			
			$this->EE->load->_ci_view_path = $orig_view_path;
		}
		else
		{
			$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
			
			$this->EE->load->library('package_installer', array('xml' => PATH_THIRD.'cartthrob/installer/installer.xml'));
		}
		
		if (is_array($templates_to_install = $this->EE->input->post('templates')) || is_array($channels_to_install = $this->EE->input->post('channels')))
		{
			/*
			foreach ($this->EE->package_installer->packages() as $row_id => $package)
			{
				if ( ! in_array($row_id, $templates_to_install))
				{
					$this->EE->package_installer->remove_package($row_id);
				}
			}
			
			$this->EE->package_installer->set_template_path(PATH_THIRD.'cartthrob/installer/templates/')->install();
			
			$this->EE->session->set_flashdata('template_errors', $this->EE->package_installer->errors());
			
			$this->EE->session->set_flashdata('templates_installed', $this->EE->package_installer->installed());
			
			$this->EE->session->set_flashdata('message_failure', implode('<br>', $this->EE->package_installer->errors()));
			
			$this->EE->session->set_flashdata('message_success', implode('<br>', $this->EE->package_installer->installed()));
			*/ 
			
			$this->EE->mbr_addon_builder->install_templates($this->EE->input->post('templates')); 

			// package data won't work since it uses product IDs in the serialized string... and whaddya know that stuff's all wrong. 
			// related item data won't work either. 
	 		$this->EE->mbr_addon_builder->install_channels($this->EE->input->post('channels'), $this->EE->input->post('channel_data')); 
	 		
			$_POST = array();
			
			$settings = $this->get_settings();
			
			$_POST['product_channels'] = element('product_channels', $settings);
			$_POST['product_channel_fields'] = element('product_channel_fields', $settings);
			
			$query = $this->EE->channel_model->get_channels(NULL, array(), array(array('channel_name' => array('products', 'store_packages', 'orders', 'purchased_items', 'coupon_codes', 'discounts'))));

			foreach ($query->result() as $channel)
			{
				$query = $this->EE->field_model->get_fields($channel->field_group);
				
				if ($channel->channel_name == 'products')
				{
					if (is_array($_POST['product_channels']))
					{
						$_POST['product_channels'][] = $channel->channel_id;
					}
					else
					{
						$_POST['product_channels'] = array($channel->channel_id);
					}
					
					$_POST['product_channels'] = array_unique($_POST['product_channels']);
					
					foreach ($query->result() as $field)
					{
						switch($field->field_name)
						{
							case 'product_price':
								$_POST['product_channel_fields'][$channel->channel_id]['price'] = $field->field_id;
								break;
							case 'product_shipping':
								$_POST['product_channel_fields'][$channel->channel_id]['shipping'] = $field->field_id;
								break;
							case 'product_weight':
								$_POST['product_channel_fields'][$channel->channel_id]['weight'] = $field->field_id;
								break;
							case 'product_inventory':
								$_POST['product_channel_fields'][$channel->channel_id]['inventory'] = $field->field_id;
								break;
							case 'product_size':
							case 'product_options_other':
							case 'product_color':
								if (isset($_POST['product_channel_fields'][$channel->channel_id]['price_modifiers']))
								{
									$_POST['product_channel_fields'][$channel->channel_id]['price_modifiers'][] = $field->field_id;
								}
								else
								{
									$_POST['product_channel_fields'][$channel->channel_id]['price_modifiers'] = array($field->field_id);
								}
								break;
						}
					}
				}
				
				if ($channel->channel_name === 'store_packages')
				{
					if (is_array($_POST['product_channels']))
					{
						$_POST['product_channels'][] = $channel->channel_id;
					}
					else
					{
						$_POST['product_channels'] = array($channel->channel_id);
					}
					
					$_POST['product_channels'] = array_unique($_POST['product_channels']);
					
					foreach ($query->result() as $field)
					{
						switch($field->field_name)
						{
							case 'packages_price':
								$_POST['product_channel_fields'][$channel->channel_id]['price'] = $field->field_id;
								break;
						}
					}
				}
				
				if ($channel->channel_name == 'orders')
				{
					$_POST['save_orders'] = 1;
					
					$_POST['orders_channel'] = $channel->channel_id;
				
					foreach ($query->result() as $field)
					{
						switch($field->field_name)
						{
							case 'order_items':
								$_POST['orders_items_field'] = $field->field_id;
								break;
							case 'order_subtotal':
								$_POST['orders_subtotal_field'] = $field->field_id;
								break;
							case 'order_ip_address':
								$_POST['orders_customer_ip_address'] = $field->field_id;
								break;
							case 'order_payment_gateway':
								$_POST['orders_payment_gateway'] = $field->field_id;
								break;
							case 'order_full_billing_address':
								$_POST['orders_full_billing_address'] = $field->field_id;
								break;
							case 'order_billing_company':
								$_POST['orders_billing_company'] = $field->field_id;
								break;
							case 'order_billing_country':
								$_POST['orders_billing_country'] = $field->field_id;
								break;
							case 'order_country_code':
								$_POST['orders_country_code'] = $field->field_id;
								break;
							case 'order_full_shipping_address':
								$_POST['orders_full_shipping_address'] = $field->field_id;
								break;
							case 'order_shipping_company':
								$_POST['orders_shipping_company'] = $field->field_id;
								break;
							case 'order_shipping_country':
								$_POST['orders_shipping_country'] = $field->field_id;
								break;
							case 'order_shipping_country_code':
								$_POST['orders_shipping_country_code'] = $field->field_id;
								break;
							case 'order_customer_full_name':
								$_POST['orders_customer_name'] = $field->field_id;
								break;
							case 'order_discount':
								$_POST['orders_discount_field'] = $field->field_id;
								break;
							case 'order_subtotal_plus_tax':
								$_POST['orders_subtotal_plus_tax_field'] = $field->field_id;
								break;	
							case 'order_tax':
								$_POST['orders_tax_field'] = $field->field_id;
								break;
							case 'order_shipping':
								$_POST['orders_shipping_field'] = $field->field_id;
								break;
							case 'order_shipping_plus_tax':
								$_POST['orders_shipping_plus_tax_field'] = $field->field_id;
								break;	
							case 'order_total':
								$_POST['orders_total_field'] = $field->field_id;
								break;
							case 'order_transaction_id':
								$_POST['orders_transaction_id'] = $field->field_id;
								break;
							case 'order_last_four':
								$_POST['orders_last_four_digits'] = $field->field_id;
								break;
							case 'order_coupons':
								$_POST['orders_coupon_codes'] = $field->field_id;
								break;
							case 'order_customer_email':
								$_POST['orders_customer_email'] = $field->field_id;
								break;
							case 'order_customer_phone':
								$_POST['orders_customer_phone'] = $field->field_id;
								break;
							case 'order_billing_first_name':
								$_POST['orders_billing_first_name'] = $field->field_id;
								break;
							case 'order_billing_last_name':
								$_POST['orders_billing_last_name'] = $field->field_id;
								break;
							case 'order_billing_address':
								$_POST['orders_billing_address'] = $field->field_id;
								break;
							case 'order_billing_address2':
								$_POST['orders_billing_address2'] = $field->field_id;
								break;
							case 'order_billing_city':
								$_POST['orders_billing_city'] = $field->field_id;
								break;
							case 'order_billing_state':
								$_POST['orders_billing_state'] = $field->field_id;
								break;
							case 'order_billing_zip':
								$_POST['orders_billing_zip'] = $field->field_id;
								break;
							case 'order_shipping_first_name':
								$_POST['orders_shipping_first_name'] = $field->field_id;
								break;
							case 'order_shipping_last_name':
								$_POST['orders_shipping_last_name'] = $field->field_id;
								break;
							case 'order_shipping_address':
								$_POST['orders_shipping_address'] = $field->field_id;
								break;
							case 'order_shipping_address2':
								$_POST['orders_shipping_address2'] = $field->field_id;
								break;
							case 'order_shipping_city':
								$_POST['orders_shipping_city'] = $field->field_id;
								break;
							case 'order_shipping_state':
								$_POST['orders_shipping_state'] = $field->field_id;
								break;
							case 'order_shipping_zip':
								$_POST['orders_shipping_zip'] = $field->field_id;
								break;
							case 'order_shipping_option':
								$_POST['orders_shipping_option'] = $field->field_id;
								break;
							case 'order_error_message':
								$_POST['orders_error_message_field'] = $field->field_id;
								break;
							case 'order_site_id':
								$_POST['orders_site_id'] = $field->field_id;
								break;							
							case 'order_subscription_id':
								$_POST['orders_subscription_id'] = $field->field_id;
								break;
							case 'order_vault_id':
								$_POST['orders_vault_id'] = $field->field_id;
								break;
						}
					}
				}
				
				if ($channel->channel_name == 'purchased_items')
				{
					$_POST['save_purchased_items'] = 1;
					
					$_POST['purchased_items_channel'] = $channel->channel_id;
				
					foreach ($query->result() as $field)
					{
						switch($field->field_name)
						{
							case 'purchased_id':
								$_POST['purchased_items_id_field'] = $field->field_id;
								break;
							case 'purchased_quantity':
								$_POST['purchased_items_quantity_field'] = $field->field_id;
								break;
							case 'purchased_price':
								$_POST['purchased_items_price_field'] = $field->field_id;
								break;
							case 'purchased_order_id':
								$_POST['purchased_items_order_id_field'] = $field->field_id;
								break;
							case 'purchased_items_package_id':
								$_POST['purchased_items_package_id_field'] = $field->field_id;
							case 'purchased_items_license_number':
								$_POST['purchased_items_license_number_field'] = $field->field_id;
								break;
						}
					}
				}
				
				if ($channel->channel_name == 'coupon_codes')
				{
					$_POST['coupon_code_field'] = 'title';
					
					$_POST['coupon_code_channel'] = $channel->channel_id;
				
					foreach ($query->result() as $field)
					{
						switch($field->field_name)
						{
							case 'coupon_code_type':
								$_POST['coupon_code_type'] = $field->field_id;
								break;
						}
					}
				}
				
				if ($channel->channel_name == 'discounts')
				{
					$_POST['discount_channel'] = $channel->channel_id;
				
					foreach ($query->result() as $field)
					{
						switch($field->field_name)
						{
							case 'discount_type':
								$_POST['discount_type'] = $field->field_id;
								break;
						}
					}
				}
			}
			
			$_GET['return'] = 'installation';
			
			$this->quick_save(FALSE);
		}
		
		$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob');
	}
	
	public function install_theme()
	{
		$themes = $this->get_themes();
		
		$theme = $this->EE->input->post('theme');
		
		if ( ! in_array($theme, $themes))
		{
			show_error(lang('invalid_theme'));
		}
		
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		
		if ($this->EE->config->item('cartthrob_third_party_path'))
		{
			$theme_path = $this->EE->config->slash_item('cartthrob_third_party_path').'installer/'.$theme.'/';
		}
		else
		{
			$theme_path = PATH_THIRD.'cartthrob/third_party/installer/'.$theme.'/';
		}
		
		$this->EE->load->library('package_installer', array('xml' => $theme_path.'installer.xml'));
		
		$this->EE->package_installer->set_template_path($theme_path.'templates/')->install();
		
		$this->EE->session->set_flashdata('theme_errors', $this->EE->package_installer->errors());
		
		$this->EE->session->set_flashdata('themes_installed', $this->EE->package_installer->installed());
		
		$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob'.AMP.'method=installation');
	}
	
	public function save_template_variables()
	{
		#$theme = $this->EE->input->post('theme');
		
		#$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		
		$this->EE->session->set_flashdata('template_variables_updated', $this->EE->package_installer->installed());
		
		$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob'.AMP.'method=installation');
	}
	
	private function get_themes()
	{
		if ($this->EE->config->item('cartthrob_third_party_path'))
		{
			$theme_base_path = $this->EE->config->slash_item('cartthrob_third_party_path').'installer/';
		}
		else
		{
			$theme_base_path = PATH_THIRD.'cartthrob/third_party/installer/';
		}
		
		$this->EE->load->helper('directory');
		
		$themes = array();
		
		if ($map = directory_map($theme_base_path, 1))
		{
			foreach ($map as $theme)
			{
				$theme_path = $theme_base_path.$theme;
				
				if (@is_file($theme_path) || ! @is_dir($theme_path) || ! @file_exists($theme_path.'/installer.xml') || ! @is_dir($theme_path.'/templates'))
				{
					continue;
				}
				
				$themes[$theme] = $theme;
			}
		}
		
		return $themes;
	}
	
	public function import_settings()
	{
		$this->initialize();
		
		if (isset($_FILES['settings']) && $_FILES['settings']['error'] == 0)
		{
			$this->EE->load->helper('file');
			
			if ($new_settings = read_file($_FILES['settings']['tmp_name']))
			{
				$_POST = _unserialize($new_settings);
			}
			
			$_GET['return'] = 'import_export';
			
			$this->quick_save();
		}
		
		$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob'.AMP.'method=import_export');
	}
	// END
	
	public function quick_save($set_success_message = TRUE)
	{
		$this->initialize();
		
		$settings = $this->get_saved_settings($get_all_settings = FALSE);
		
		$data = array();
		
		//if they change fingerprint method we have to wipe their sessions
		if (isset($_POST['session_fingerprint_method']) && isset($settings['session_fingerprint_method']) && $_POST['session_fingerprint_method'] != $settings['session_fingerprint_method'])
		{
			$this->EE->db->truncate('cartthrob_sessions');
		}
		
		foreach (array_keys($_POST) as $key)
		{
			if ( ! in_array($key, $this->remove_keys) && ! preg_match('/^(Cartthrob_.*?_settings|product_weblogs|product_weblog_fields|default_location|tax_settings)_.*/', $key))
			{
				$data[$key] = $this->EE->input->post($key);
			}
		}
		
		foreach ($data as $key => $value)
		{
			$where = array(
				'site_id' => $this->EE->config->item('site_id'),
				'`key`' => $key
			);
			
			//custom key actions
			switch($key)
			{
				/*
				case 'use_session_start_hook':
					
					$is_installed = (bool) $this->EE->db->where('class', 'Cartthrob_ext')->where('hook', 'sessions_end')->count_all_results('extensions');
					
					if ($value)
					{
						if ( ! $is_installed)
						{
							$this->EE->db->insert('extensions', array(
								'class' => 'Cartthrob_ext', 
								'method' => 'sessions_end',
								'hook' => 'sessions_end', 
								'settings' => '', 
								'priority' => 10, 
								'version' => $this->version(),
								'enabled' => 'y',
							));
						}
					}
					else
					{
						if ($is_installed)
						{
							$this->EE->db->where('class', 'Cartthrob_ext')->where('hook', 'sessions_end')->delete('extensions');
						}
					}
					
					break;
					*/
				case 'cp_menu':
					
					$is_installed = (bool) $this->EE->db->where('class', 'Cartthrob_ext')->where('hook', 'cp_menu_array')->count_all_results('extensions');
					
					if ($value)
					{
						if ( ! $is_installed)
						{
							$this->EE->db->insert('extensions', array(
								'class' => 'Cartthrob_ext', 
								'method' => 'cp_menu_array',
								'hook' => 'cp_menu_array', 
								'settings' => '', 
								'priority' => 10, 
								'version' => $this->version(),
								'enabled' => 'y',
							));
						}
					}
					else
					{
						if ($is_installed)
						{
							$this->EE->db->where('class', 'Cartthrob_ext')->where('hook', 'cp_menu_array')->delete('extensions');
						}
					}
					
					break;
			}
			
			if (is_array($value))
			{
				$row['serialized'] = 1;
				$row['value'] = serialize($value);
			}
			else
			{
				$row['serialized'] = 0;
				$row['value'] = $value;
			}
			
 			if (isset($settings[$key]) && $this->EE->db->count_all('cartthrob_settings') > 0)
			{
				if ($value !== $settings[$key])
				{
					$this->EE->db->update('cartthrob_settings', $row, $where);
				}
			}
			else
			{
				$this->EE->db->insert('cartthrob_settings', array_merge($row, $where));
			}
		}
		
 		
		if ($set_success_message)
		{
			$this->EE->session->set_flashdata('message_success', sprintf('%s %s %s', lang('cartthrob_module_name'), lang('nav_'.$this->EE->input->get('return')), lang('settings_saved')));
		}
		
		$return = ($this->EE->input->get('return')) ? AMP.'method='.$this->EE->input->get('return', TRUE) : '';
		
		$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob'.$return);
	}
	
	public function set_encryption_key()
	{
		$this->EE->config->_update_config(array('encryption_key' => $this->EE->input->post('encryption_key', TRUE)));
		
		$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob');
	}
	
	// --------------------------------
	//  Validate Settings
	// --------------------------------
	/**
	 * Checks to see if any fields are missing. If the fields are missing, The "missing" array is returned, and 'valid' boolean is false. 
	 * 
	 * @access private
	 * @param NULL
	 * @return array
	 * @since 1.0.0
	 * @author Rob Sanchez
	 */
	function validate_settings()
	{
		$valid = TRUE;
		
		$missing = array();
		
		foreach ($this->required_settings as $required)
		{
			if ( ! $this->EE->input->post($required))
			{
				$missing[] = $required;
				
				$valid = FALSE;
			}
		}
		
		return array('valid'=>$valid, 'missing'=>$missing);
	}
	//END 
	
	// --------------------------------
	//  Export Settings
	// --------------------------------
	/**
	 * Generates & downloads a file called "cartthrob_settings.txt" that contains current settings for CartThrob 
	 * Useful for backup and transfer. 
	 *
	 * @access private
	 * @param NULL
	 * @return void
	 * @since 1.0.0
	 * @author Rob Sanchez
	 */
	public function export_settings()
	{
		$this->initialize();
		
		$this->EE->load->helper('download');
		
		force_download('cartthrob_settings.txt', serialize($this->get_settings()));
	}
	//END
	
	// --------------------------------
	//  GET Settings
	// --------------------------------
	/**
	 * Loads cart, and gets default settings, then gets saved settings
	 *
	 * @access private
	 * @param NULL
	 * @return array $settings
	 * @since 1.0.0
	 * @author Rob Sanchez
	 */
	public function get_settings()
	{
		$this->initialize();
		
		return $this->EE->cartthrob_settings_model->get_settings();
	}
	
	// gets saved settings, overrides cached settings. 
	public function get_saved_settings($get_all_settings = FALSE)
	{
		$settings = array();
		
		foreach ($this->EE->db->where('site_id', $this->EE->config->item('site_id'))->get('cartthrob_settings')->result() as $row)
		{
			if ($row->serialized)
			{
				$settings[$row->key] = @unserialize($row->value);
			}
			else
			{
				$settings[$row->key] = $row->value;
			}
		}
		
		if ($get_all_settings)
		{
		$settings = array_merge($this->get_settings(), $settings); 
		}
		
		return $settings;
	}
	// END 
	
	// --------------------------------
	//  Get Payment Gateways
	// --------------------------------
	/**
	 * Loads payment gateway files
	 *
	 * @access private
	 * @param NULL
	 * @return array $gateways Array containing settings and information about the gateway
	 * @since 1.0.0
	 * @author Rob Sanchez
	 */
	function get_payment_gateways()
	{
		$this->initialize();
		
		$this->EE->load->helper('file');
		$this->EE->load->library('api/api_cartthrob_payment_gateways');
			
		$templates = array('' => $this->EE->lang->line('gateways_default_template'));
		
		$this->EE->load->model('template_model');
		
		$query = $this->EE->template_model->get_templates();
		
		foreach ($query->result_array() as $row)
		{
			$templates[$row['group_name'].'/'.$row['template_name']] = $row['group_name'].'/'.$row['template_name'];
		}
		
		$gateways = $this->EE->api_cartthrob_payment_gateways->gateways();
		
		foreach ($gateways as &$plugin_data)
		{
			$this->EE->lang->loadfile(strtolower($plugin_data['classname']), 'cartthrob', FALSE);
			
			foreach (array('title', 'affiliate', 'overview') as $key)
			{
				if (isset($plugin_data[$key]))
				{
					$plugin_data[$key] = $this->EE->lang->line($plugin_data[$key]);
				}
			}
			
			$plugin_data['html'] = $this->EE->api_cartthrob_payment_gateways->set_gateway($plugin_data['classname'])->gateway_fields(TRUE);
			
			if (isset($plugin_data['settings']) && is_array($plugin_data['settings']))
			{
				foreach ($plugin_data['settings'] as $key => $setting)
				{
					$plugin_data['settings'][$key]['name'] = $this->EE->lang->line($setting['name']);
				}
				
				$plugin_data['settings'][] = array(
					'name' => $this->EE->lang->line('template_settings_name'),
					'note' => $this->EE->lang->line('template_settings_note'),
					'type' => 'select',
					'short_name' => 'gateway_fields_template',
					'options' => $templates
				);
			}
		}
		
		$this->EE->load->library('data_filter');
		
		$this->EE->data_filter->sort($gateways, 'title');
		
		return $gateways;
	}
	// END
	
	function get_shipping_plugins()
	{
		return $this->get_plugins('shipping');
	}
	
	function get_tax_plugins()
	{
		return $this->get_plugins('tax');
	}
	// --------------------------------
	//  Get Shipping Plugins
	// --------------------------------
	/**
	 * Loads shipping plugin files
	 *
	 * @access private
	 * @param NULL
	 * @return array $plugins Array containing settings and information about the plugin
	 * @since 1.0.0
	 * @author Rob Sanchez
	 */
	function get_plugins($type)
	{
		$this->initialize();
		
		$this->EE->load->helper(array('file', 'data_formatting'));
	
		$plugins = array();
		
		$paths[] = CARTTHROB_PATH.'plugins/'.$type.'/';
		
		if ($this->EE->config->item('cartthrob_third_party_path'))
		{
			$paths[] = rtrim($this->EE->config->item('cartthrob_third_party_path'), '/').'/'.$type.'_plugins/';
		}
		else
		{
			$paths[] = PATH_THIRD.'cartthrob/third_party/'.$type.'_plugins/';
		}
		
		require_once CARTTHROB_PATH.'core/Cartthrob_'.$type.EXT;
		
		foreach ($paths as $path)
		{
			if ( ! is_dir($path))
			{
				continue;
			}
			
			foreach (get_filenames($path, TRUE) as $file)
			{
				if ( ! preg_match('/^Cartthrob_/', basename($file, EXT)))
				{
					continue;
				}
				
				require_once $file;
			
				$class = basename($file, EXT);
				
				$language = set($this->EE->session->userdata('language'), $this->EE->input->cookie('language'), $this->EE->config->item('deft_lang'), 'english');			
				
				if (file_exists(PATH_THIRD.'cartthrob/language/'.$language.'/'.strtolower($class).'_lang.php'))
				{
					$this->EE->lang->loadfile(strtolower($class), 'cartthrob', FALSE);
				}
				else if (file_exists($path.'../language/'.$language.'/'.strtolower($class).'_lang.php'))
				{
					$this->EE->lang->load(strtolower($class), $language, FALSE, TRUE, $path.'../', FALSE);
				}
				
				$plugin_info = get_class_vars($class);
				
				$plugin_info['classname'] = $class;
				
				$settings = $this->get_settings();
				
				if (isset($plugin_info['settings']) && is_array($plugin_info['settings']))
				{
					foreach ($plugin_info['settings'] as $key => $setting)
					{
						//retrieve the current set value of the field
						$current_value = (isset($settings[$class.'_settings'][$setting['short_name']])) ? $settings[$class.'_settings'][$setting['short_name']] : FALSE;
						//set the value to the default value if there is no set value and the default value is defined
						$current_value = ($current_value === FALSE && isset($setting['default'])) ? $setting['default'] : $current_value;
						
						if ($setting['type'] == 'matrix')
						{
							if ( ! is_array($current_value) || ! count($current_value))
							{
								$current_values = array(array());
								
								foreach ($setting['settings'] as $matrix_setting)
								{
									$current_values[0][$matrix_setting['short_name']] = isset($matrix_setting['default']) ? $matrix_setting['default'] : '';
								}
							}
							else
							{
								$current_values = $current_value;
							}
						}
					}
				}
				
				$plugins[] = $plugin_info;
			}
		}
		
		return $plugins;
	}
	
	public function get_templates()
	{
		static $templates;
		
		if (is_null($templates))
		{
			$templates = array();
			
			$this->EE->load->model('template_model');
			
			$query = $this->EE->template_model->get_templates();
			
			foreach ($query->result() as $row)
			{
				$templates[$row->group_name.'/'.$row->template_name] = $row->group_name.'/'.$row->template_name;
			}
		}
		
		return $templates;
	}
	
	
	/// BEGIN  TAXES ****************************************
	
	
	public function taxes()
	{
		// @TODO tax model
		$this->EE->load->model('tax_model');
		$limit ="50";
		/////////// pagination //////////////////////////////
		if ( ! $offset = $this->EE->input->get_post('rownum'))
		{		
			$offset = 0;
		}
		$this->EE->load->library('pagination');
		
		$total = $this->EE->db->count_all('cartthrob_tax');


		if ($total == 0)
		{
		//	$this->EE->session->set_flashdata('message_failure', sprintf('%s %s %s', lang('cartthrob_module_name'), lang('nav_'.$this->EE->input->get('return')), lang('taxes_none')));	
		}
		$this->EE->pagination->initialize( $this->pagination_config('taxes', $total, $limit) );

		$pagination = $this->EE->pagination->create_links();
		/////////// end pagination //////////////////////////////

		$data = $this->EE->tax_model->read(NULL,$limit,$offset); 
		
		return $this->load_view(
					__FUNCTION__,
					array(
						'taxes' => $data, 
						'pagination' => $pagination,
						'form_open'	=> form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob'.AMP.'method=quick_save'.AMP.'return=taxes'),
						'add_href'	=> BASE.AMP. 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob'.AMP.'method=add_tax',
						'edit_href'	=> BASE.AMP. 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob'.AMP.'method=edit_tax'.AMP.'id=',
						'delete_href'	=> BASE.AMP. 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob'.AMP.'method=delete_tax'.AMP.'id=',
					)
				);
	}

	private function pagination_config($method, $total_rows, $per_page=50)
	{
		$config['base_url'] = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob'.AMP.'method='.$method;
		$config['total_rows'] = $total_rows;
		$config['per_page'] = $per_page;
		$config['page_query_string'] = TRUE;
		$config['query_string_segment'] = 'rownum';
		$config['full_tag_open'] = '<p id="paginationLinks">';
		$config['full_tag_close'] = '</p>';
		$config['prev_link'] = '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_prev_button.gif" width="13" height="13" alt="<" />';
		$config['next_link'] = '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_next_button.gif" width="13" height="13" alt=">" />';
		$config['first_link'] = '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_first_button.gif" width="13" height="13" alt="< <" />';
		$config['last_link'] = '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_last_button.gif" width="13" height="13" alt="> >" />';

		return $config;
	}

	public function add_tax()
	{
		return $this->load_view(
					__FUNCTION__,
					array(
						'form_edit'	=> form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob'.AMP.'method=form_update_tax'.AMP.'return=taxes')
					)
				);
	}
	public function edit_tax()
	{
		$this->EE->load->model('tax_model'); 

		$data = $this->EE->tax_model->read($this->EE->input->get('id')); 
		return $this->load_view(
					__FUNCTION__,
					array(
						'tax' => $data, 
						'form_edit'	=> form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob'.AMP.'method=form_update_tax'.AMP.'return=taxes')
					)
				);
	}
	public function delete_tax()
	{
		$this->EE->load->model('tax_model'); 

		$data = $this->EE->tax_model->read($this->EE->input->get('id')); 
		return $this->load_view(
					__FUNCTION__,
					array(
						'tax' => $data, 
						'form_edit'	=> form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob'.AMP.'method=form_update_tax'.AMP.'return=taxes')
					)
				);
	}

	public function form_update_tax()
	{
		// @TODO add tax
		$this->initialize();
		$this->EE->load->model('tax_model'); 
		$data = array();

		foreach (array_keys($_POST) as $key)
		{
			if ( ! in_array($key, $this->remove_keys) && ! preg_match('/^(Cartthrob_.*?_settings|product_weblogs|product_weblog_fields|default_location)_.*/', $key))
			{
				$data[$key] = $this->EE->input->post($key, TRUE);
			}
		}

		if (!$this->EE->input->post('id'))
		{
			$data['id'] = $this->EE->input->post('add_id'); 
			$this->EE->tax_model->create($data);

		}
		elseif($this->EE->input->post('delete_tax'))
		{
			$this->EE->tax_model->delete($this->EE->input->post('id'));
		}
		else
		{
			$this->EE->tax_model->update($data, $this->EE->input->post('id'));

		}
		$this->EE->session->set_flashdata('message_success', sprintf('%s %s %s', lang('cartthrob_module_name'), lang('nav_'.$this->EE->input->get('return')), lang('settings_saved')));

		$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob'.AMP.'method='.$this->EE->input->get('return', TRUE));
	}
	/// END TAXES ****************************************

	/**
	 * package filter
	 *
	 * used in package fieldtype to process entry filter ajax request
	 * 
	 * @return Type    Description
	 */
	public function package_filter()
	{
		if ( ! AJAX_REQUEST)
		{
			show_error($this->EE->lang->line('unauthorized_access'));
		}
		
		$this->EE->load->library('cartthrob_loader');
		
		$channels = $this->EE->config->item('cartthrob:product_channels');
		
		$this->EE->load->model('search_model');
		
		if ($this->EE->input->get_post('channel_id') && $this->EE->input->get_post('channel_id') != 'null')
		{
			$channels = $this->EE->input->get_post('channel_id');
		}
		
		$keywords = $this->EE->input->get_post('keywords');
		
		$this->EE->load->model('cartthrob_entries_model');
		
		//typed in an entry_id
		if (is_numeric($keywords))
		{
			$entries = array();
			
			if ($entry = $this->EE->cartthrob_entries_model->entry($keywords))
			{
				$entries[] = $entry;
			}
		}
		else
		{
			$this->EE->load->helper('text');
			
			$search = array(
				'channel_id' => $channels,
				'cat_id' => ($this->EE->input->get_post('cat_id') != 'all') ? $this->EE->input->get_post('cat_id') : '',
				'status' => ($this->EE->input->get_post('status') != 'all') ? $this->EE->input->get_post('status') : '',
				'date_range' => $this->EE->input->get_post('date_range'),	
				'author_id' => $this->EE->input->get_post('author_id'),
				'search_in' => ($this->EE->input->get_post('search_in')) ? $this->EE->input->get_post('search_in') : 'title',
				'exact_match' => $this->EE->input->get_post('exact_match'),
				'keywords' => $keywords,
				'search_keywords' => ($this->EE->config->item('auto_convert_high_ascii') === 'y') ? ascii_to_entities($keywords) : $keywords,
				'_hook_wheres' => array(),
				//'perpage' => $this->EE->input->get_post('perpage'),
				//'rownum' => $this->EE->input->get_post('rownum'),
			);
			
			$data = $this->EE->search_model->build_main_query($search, array('title' => 'asc'));
			
			$this->EE->load->library('data_filter');
			
			$entry_ids = $this->EE->data_filter->key_values($data['result_obj']->result_array(), 'entry_id');
		
			$entries = $this->EE->cartthrob_entries_model->entries($entry_ids);
		}
		
		$this->EE->load->model(array('product_model', 'cartthrob_field_model'));
		
		foreach ($entries as &$entry)
		{
			$entry['price_modifiers'] = $this->EE->product_model->get_all_price_modifiers($entry['entry_id']);
			
			foreach ($entry['price_modifiers'] as $price_modifier => $options)
			{
				$entry['price_modifiers'][$price_modifier]['label'] = $this->EE->cartthrob_field_model->get_field_label($this->EE->cartthrob_field_model->get_field_id($price_modifier));
			}
		}
		
		$this->EE->output->send_ajax_response(array(
			'entries' => $entries,
			'id' => $this->EE->input->get_post('filter_id'),
		));
	}
	
	public function crontabulous_get_pending_subscriptions()
	{
		$this->EE->load->library('cartthrob_loader', array('cart' => array()));
		
		$this->EE->load->library(array('crontabulous_responder', 'paths'));
		
		$this->EE->crontabulous_responder->set_private_key($this->EE->cartthrob->store->config('crontabulous_api_key'));
		
		if ($this->EE->crontabulous_responder->validate_request())
		{
			$this->EE->load->model('subscription_model');
			
			$query = $this->EE->subscription_model->get_pending_subscriptions();
			
			foreach ($query->result() as $row)
			{
				//add the process subscription url for this pending subscription to the queue
				$this->EE->crontabulous_responder->enqueue(
					$this->EE->paths->build_action_url(
						'Cartthrob_mcp',
						'crontabulous_process_subscription',
						array(
							'id' => $row->id,
						)
					)
				);
			}
		}
		
		$this->EE->crontabulous_responder->send_response();
	}
	
	public function crontabulous_process_subscription()
	{
		$this->EE->load->library('cartthrob_loader', array('cart' => array()));
		
		$this->EE->load->library(array('crontabulous_responder', 'paths'));
		
		$this->EE->crontabulous_responder->set_private_key($this->EE->cartthrob->store->config('crontabulous_api_key'));
		
		if ($this->EE->crontabulous_responder->validate_request())
		{
			$this->process_subscription($this->EE->input->get('id'));
		}
		
		$this->EE->crontabulous_responder->send_response();
	}
	
	/**
	 * This method is accessed in many ways
	 *
	 * a) cron_subscriptions.sh
	 * b) cron_subsciptions.pl
	 * c) extload.php
	 * d) url
	 * 
	 * @return Type    Description
	 */
	public function process_subscriptions($get_ids = FALSE)
	{
		//load cartthrob core
		$this->EE->load->library('cartthrob_loader', array('cart' => array()));
		
		$this->EE->load->library('cartthrob_payments');
		
		$this->EE->load->model(array('vault_model', 'subscription_model', 'order_model', 'customer_model'));
		
		$subscriptions = array();
		
		//get subscriptions that are due for billing and/or expired
		$query = $this->EE->subscription_model->get_pending_subscriptions();
		
		$subscriptions = $query->result_array();
		
		$query->free_result();
		
		/**
		 * return a space delimited list of subscription ids which the shell script will use to process subs individually
		 */
		if ($get_ids)
		{
			$subscription_ids = array();
			
			foreach($subscriptions as $subscription)
			{
				$subscription_ids[] = $subscription['id'];
			}
			
			exit(implode(' ', $subscription_ids));
		}
		
		//extload
		/**
		 * loop through subscriptions and process each one via php cli and passthru
		 */
		if (@php_sapi_name() === 'cli')
		{
			if (empty($_SERVER['argv']))
			{
				exit;
			}
			
			$args = $_SERVER['argv'];
			
			//add the php command
			array_unshift($args, 'php');
			
			//remove the current cron command
			array_pop($args);
			
			//add the process_subscription cron command
			array_push($args, 'process_subscription');
			
			foreach ($subscriptions as $subscription)
			{
				//add the id to the command
				array_push($args, $subscription['id']);
				
				passthru(implode(' ', $args));
				
				array_pop($args);
			}
			
			exit;
		}
		
		//this is the url interface, process ONE subscription
		if ($subscription = array_shift($subscriptions))
		{
			$this->process_subscription($subscription['id']);
		}
	}
	
	public function process_subscription($subscription_id)
	{
		if ( ! is_numeric($subscription_id))
		{
			exit;
		}
		
		//load cartthrob core
		$this->EE->load->library('cartthrob_loader', array('cart' => array()));
		
		$this->EE->load->library('cartthrob_payments');
		
		$this->EE->load->model(array('vault_model', 'subscription_model', 'order_model', 'customer_model'));
		
		return $this->EE->cartthrob_payments->apply('subscriptions', 'process_subscription', $subscription_id);
	}
	
	/**
	 * Used by the manual processing in the CP
	 */
	public function ajax_process_subscription()
	{
		$subscription_id = $this->EE->input->get_post('subscription_id');
		
		//exit($subscription_id);
		$this->process_subscription($subscription_id);
		
		exit;
	}
	
	public function ajax_get_pending_subscriptions()
	{
		if ( ! $this->EE->input->is_ajax_request())
		{
			return show_404();
		}
		
		$this->EE->load->model('subscription_model');
		
		$query = $this->EE->subscription_model->get_pending_subscriptions();
		
		$data = array(
			'count' => $query->num_rows(),
			'subscriptions' => array(),
		);
		
		foreach ($query->result() as $row)
		{
			$data['subscriptions'][] = $row->id;
		}
		
		$query->free_result();
		
		$this->EE->output->send_ajax_response($data);
		
		exit;
	}
	
	public function has_subnav($which)
	{
		foreach (self::$subnav as $subnav)
		{
			if (in_array($which, $subnav))
			{
				return $subnav;
			}
		}
		
		return FALSE;
	}
	
	/**
	 * get_news
	 *
	 * @return string
	 * @author Newton
	 **/
	public function get_news()
	{
		$this->initialize();
		
		$this->EE->load->library('curl');
		$this->EE->load->library('simple_cache');
		$this->EE->load->helper('data_formatting');
		
		$return_data['version_update'] = NULL; 
		$return_data['news'] = NULL; 
	
		$cache = $this->EE->simple_cache->get('cartthrob/version');
		
		if ( ! $cache)
		{
			$data = $this->EE->curl->simple_get('http://livecart.com/site/versions/cartthrob_2');
			
			if ( ! $data)
			{
				return $return_data;
			}
			
			$cache = $this->EE->simple_cache->set('cartthrob/version', $data);
		}
		
		if (empty($cache))
		{
			return $return_data;
		}
		
		parse_str($cache, $content);
		
		//$data = $this->curl_transaction("http://livecart.com/site/versions/cartthrob_ecommerce_system");
		//$content = $this->split_url_string($data);
		
		if (isset($content['version']) && $content['version'] > $this->version())
		{
			$return_data['version_update'] = "<a href='http://livecart.com/cart/purchased_items/'>CartThrob has been updated to version ". $content['version']. "</a>";
		}
		else
		{
			$return_data['version_update'] 	= $this->EE->lang->line('there_are_no_updates'); 
		}
		
		if ( ! empty($content['news']))
		{
			$return_data['news'] = stripslashes(urldecode($content['news']));
		}
		
		return $return_data; 
	}
	
	public function garbage_collection()
	{
		header('X-Robots-Tag: noindex');
		
		$this->EE->db->where('expires <', @time())->delete('cartthrob_sessions');
		
		$this->EE->db->query('DELETE `'.$this->EE->db->dbprefix('cartthrob_cart').'`
				  FROM `'.$this->EE->db->dbprefix('cartthrob_cart').'`
				  LEFT OUTER JOIN `'.$this->EE->db->dbprefix('cartthrob_sessions').'`
				  ON `'.$this->EE->db->dbprefix('cartthrob_cart').'`.`id` = `'.$this->EE->db->dbprefix('cartthrob_sessions').'`.`cart_id`
				  WHERE `'.$this->EE->db->dbprefix('cartthrob_sessions').'`.`cart_id` IS NULL');
	}

	public function version()
	{
		if (is_null($this->version))
		{
			include_once PATH_THIRD.'cartthrob/config.php';
			
			$this->version = CARTTHROB_VERSION;
		}
		
		return $this->version;
	}
	
	protected function html($content, $tag = 'p', $attributes = '')
	{
		if (is_array($attributes))
		{
			$attributes = _parse_attributes($attributes);
		}
		
		return '<'.$tag.$attributes.'>'.$content.'</'.$tag.'>';
	}
}

/* End of file mcp.cartthrob.php */
/* Location: ./system/expressionengine/third_party/cartthrob/mcp.cartthrob.php */