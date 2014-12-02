<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* @TODO
if ($this->EE->cartthrob->store->config('save_orders') && $this->EE->cartthrob->store->config('orders_channel'))
this module shouldn't even run if orders aren't being saved
*/
/* 
@NOTE @TODO

currently this requries the following additional fields to be set up

order_refund_id
order_shipping_note
order_tracking_number

Email Address
Subject
Order Complete
*/
class Cartthrob_order_manager_mcp {

	private $module_name;
	public $required_settings = array();
	public $template_errors = array();
	public $templates_installed = array();
	public $extension_enabled = 0;
	public $module_enabled = 0;
#	public $required_by 	= array('extension');
	
	public $limit = "100"; 
	public $version;
	
	private $currency_code = NULL; 
	private $prefix = NULL; 
	private $dec_point = NULL; 
	
	private $initialized = FALSE;
	
	public $nav = array(
	);
	
	public $no_nav = array(
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
	
	public $default_columns = array(
		'row_id',
		'row_order',
		'order_id',
		'entry_id',
		'title',
		'quantity',
		'price',
		'price_plus_tax',
		'weight',
		'shipping',
		'no_tax',
		'no_shipping',
		'extra',
	);
	
	public $order_fields = array(
		'orders_billing_first_name',
		'orders_billing_last_name' ,
		'orders_billing_company' ,
		'orders_billing_address' ,
		'orders_billing_address2',
		'orders_billing_city',
		'orders_billing_state' ,
		'orders_billing_zip' ,
		'orders_country_code',

		'orders_shipping_first_name' ,
		'orders_shipping_last_name',
		'orders_shipping_company',
		'orders_shipping_address',
		'orders_shipping_address2' ,
		'orders_shipping_city' ,
		'orders_shipping_state',
		'orders_shipping_zip',
		'orders_shipping_country_code' ,

		'orders_customer_email',
		'orders_customer_phone',
		'orders_language_field',
		
		'orders_full_billing_address',
		'orders_full_shipping_address' 

	);
	
	public $total_fields = array(
		'orders_total',
		'orders_tax',
		'orders_subtotal',
		'orders_shipping'
	); 
	public $params; 
	
	public $cartthrob, $store, $cart;
	
	public $table = "cartthrob_order_manager_table"; 
	
    function __construct()
    {
		$this->module_name = strtolower(str_replace(array('_ext', '_mcp', '_upd'), "", __CLASS__));
	
        $this->EE =& get_instance();
		$this->EE->load->add_package_path(PATH_THIRD.$this->module_name.'/');
		include PATH_THIRD.$this->module_name.'/config'.EXT;
		
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/'); 
		$this->EE->load->library('cartthrob_loader'); 
		$this->EE->load->library('get_settings');
		$this->EE->load->library('number'); 
		
		$this->EE->load->helper('form'); 
		
		if (! $this->cartthrob_enabled() )
		{
			$this->EE->session->set_flashdata($this->module_name.'_system_error', sprintf('%s', lang($this->module_name.'_cartthrob_must_be_installe')));
			$this->EE->functions->redirect(BASE);			
		}
		
		if (! $this->EE->cartthrob->store->config('save_orders') && ! $this->EE->cartthrob->store->config('orders_channel'))
		{
			$this->EE->session->set_flashdata($this->module_name.'_system_error', sprintf('%s', lang($this->module_name.'_orders_channel_must_be_configured')));
			$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob'.AMP.'method=order_settings');
		}
    }
	/**
	 * cartthrob_enabled
	 * 
	 * determines if cartthrob is enabled
	 *
	 * @return boolean
	 * @author Chris Newton
	 */
	private function cartthrob_enabled()
	{
		$query = $this->EE->db->select('module_name')
				->where_in('module_name', 'Cartthrob')
				->get('modules');
		
		if ($query->result())
		{
			$query->free_result();
			
			return TRUE; 
		}
		return false; 
	}
	private function initialize()
	{
		$this->params['module_name']	= $this->module_name; 
 		$this->params['skip_extension'] = TRUE; 
	 	$this->params['nav'] = array(
			'om_sales_dashboard' => array(
				'om_sales_dashboard' => $this->EE->lang->line("om_sales_dashboard"),
			),
			//// ORDERS
			'view' => array(
				'view' => $this->EE->lang->line("cartthrob_order_manager_orders_list"),
			),
			'order_report' => array(
				'order_report' => $this->EE->lang->line("order_report"),
			),
			'edit'	=> array(
				'edit'	=> $this->EE->lang->line("edit"),
 			),
			'delete' => array(
				'delete' => $this->EE->lang->line("delete"),
			),

			//// PRODUCTS
 			'view_products' => array(
				'view_products' => $this->EE->lang->line("view_products"),
			),
			'add_products'	=> array(
				'add_products'	=> $this->EE->lang->line("add_products"),
 			),
			'edit_products'	=> array(
				'edit_products'	=> $this->EE->lang->line("edit_products"),
 			),
			'delete_products' => array(
				'delete_products' => $this->EE->lang->line("delete_products"),
			),
 			/// REPORTS
			'customer_report' => array(
				'customer_report' => $this->EE->lang->line("customer_report"),
			),
			'product_report' => array(
				'product_report' => $this->EE->lang->line("product_report"),
			),

			'run_report' => array(
				'run_report' => $this->EE->lang->line("run_report"),
			),
			
			/// UTILITIES
			'system_settings' => array(
				'system_settings' => $this->EE->lang->line("system_settings"),
			), 
			'print_invoice' => array(
				'print_invoice' => $this->EE->lang->line("print_invoice"),
			),
			'print_packing_slip' => array(
				'print_packing_slip' => $this->EE->lang->line("print_packing_slip"),
			),

		); 
		
		if (! $this->EE->cartthrob->store->config('show_product_admin'))
		{
			unset(
				$this->params['nav']['delete_products'],
				$this->params['nav']['edit_products'],
				$this->params['nav']['add_products'],
				$this->params['nav']['view_products']
			); 
		}
 		$this->params['no_form'] = array(
			'om_sales_dashboard',
			'edit',
			'delete',
			'view',
			'order_report',
			'run_report',
			'customer_report',
			'print_packing_slip',
			'print_invoice',
			'product_report', 
			'add_products',
  		);
		$this->params['no_nav'] = array(
			'edit',
			'delete',
			'run_report',
			'print_invoice',
			'print_packing_slip',
			'delete_products',
			'edit_products',
		);
		
 		$this->EE->load->library('mbr_addon_builder');
		$this->EE->mbr_addon_builder->initialize($this->params);
	}
	public function quick_save()
	{
		return $this->EE->mbr_addon_builder->quick_save();
	 	
	}
	
	//// PRODUCTS
	public function delete_products()
	{
		$this->initialize();
		
		$structure = array(); 
  		return $this->EE->mbr_addon_builder->load_view(__FUNCTION__, array(), $structure);
		
	}
	public function add_products()
	{
		$this->initialize();
		$this->EE->load->library('file_field');
		$this->EE->load->helper("form"); 
 		$this->EE->cp->add_js_script(array('ui' => 'datepicker'));
		
		$date_picker_js = "

			if ($('.datepicker').size() > 0) 
			{
				$('.datepicker').datepicker({dateFormat: 'yy-mm-dd'});
			}
		"; 
 		$this->EE->javascript->output($date_picker_js);

		$config = array(
			'publish'	=> TRUE,
			#'trigger'	=> 'file_upload',
		);
		$this->EE->file_field->browser($config);
		
		$structure = array(
			'plugin_type' => 'add_products',
			'plugins' => array(
				array(
					'classname' => 'add_products',
					'title' => 'add_products_title',
					'overview' => 'add_products_overview',
					'note'	=> NULL,
					'settings' => array(
						array(
							'name' => 'title',
							'short_name' => 'title',
							'default'	=> NULL,
							'type' => 'text',
						), 
						array(
							'name' => 'url_title',
							'short_name' => 'url_title',
							'default'	=> NULL,
							'type' => 'text',
						),
						array(
							'name' => 'status',
							'short_name' => 'status',
							'default'	=> 'active',
							'type' => 'select',
							'options' => array(
							    'active' => 'active',
								'inactive' => 'inactive',
							),
						),
						array(
							'name' => 'description',
							'short_name' => 'description',
							'default'	=> NULL,
							'type' => 'textarea',
							'attributes'	=> array(
								'class' => 'wysiwyg'
							)
						),
						array(
							'name' => 'sku',
							'short_name' => 'sku',
							'type' => 'text',
						),
						array(
							'name' => 'featured',
							'short_name' => 'featured',
							'default'	=> 'no',
							'type' => 'select',
							'options' => array(
							     'no' => 'no',
								'yes' => 'yes',
							),
						),
			
						///// INVENTORY
						array(
							'name' => 'inventory',
							'short_name' => 'inventory',
							'default'	=> '',
							'type' => 'text',
						),
						////// SHIPPING
						array(
							'name' => 'shipping',
							'short_name' => 'shipping',
							'type' => 'header',
						),
						array(
							'name' => 'shippable',
							'short_name' => 'shippable',
							'default'	=> 'yes',
							'type' => 'select',
							'options' => array(
							     'yes' => 'yes',
								'no' => 'no',
							),
						), 
						array(
							'name' => 'weight',
							'short_name' => 'weight',
							'default'	=> '',
							'type' => 'text',
						),
			
						///// TAX
						array(
							'name' => 'tax',
							'short_name' => 'tax',
							'type' => 'header',
						), 
						array(
							'name' => 'taxable',
							'short_name' => 'taxable',
							'default'	=> 'yes',
							'type' => 'select',
							'options' => array(
							     'yes' => 'yes',
								'no' => 'no',
							),
						), 

						///// PRICING
						array(
							'name' => 'price_header',
							'short_name' => 'price_header',
							'type' => 'header',
						), 
			
						array(
							'name' => 'price',
							'short_name' => 'price',
							'default'	=> '',
							'type' => 'text',
						), 
						array(
							'name' => 'store_cost',
							'short_name' => 'store_cost',
							'default'	=> '',
							'type' => 'text',
						), 
						///// SALE
						array(
							'name' => 'sale_pricing',
							'short_name' => 'sale_pricing',
							'type' => 'header',
						), 
			
						array(
							'name' => 'sale_price',
							'short_name' => 'sale_price',
							'default'	=> '',
							'type' => 'text',
						), 
						array(
							'name' => 'sale_start',
							'short_name' => 'sale_start',
							'default'	=> '',
							'type' => 'text',
							'attributes'=> array(
								'class'	=> 'datepicker'
							)
						), 
						array(
							'name' => 'sale_end',
							'short_name' => 'sale_end',
							'default'	=> '',
							'type' => 'text',
							'attributes'=> array(
								'class'	=> 'datepicker'
							)
						),
						/// IMAGES
						array(
							'name' => 'images_header',
							'short_name' => 'images_header',
							'type' => 'header',
						), 
						array(
							'name' => 'images',
							'short_name' => 'images',
							'type' => 'matrix',
							'settings' => array(
								/// @TODO replace with an uploader
								array(
									'name' => 'userfile',
									'short_name' => 'userfile',
									'default'	=> '',
									'type' => 'file',
								),
							     array(
									'name' => 'title',
									'short_name' => 'title',
									'default'	=> '',
									'type' => 'text',
								), 
								array(
									'name' => 'active',
									'short_name' => 'active',
									'default'	=> 'yes',
									'type' => 'radio',
									'options' => array(
										'yes' => 'yes',
										'no' => 'no',
									),
								), 
								array(
									'name' => 'delete',
									'short_name' => 'delete',
									'type' => 'checkbox',
								), 
					
							),
						), 
						///// OPTIONS
						array(
							'name' => 'item_options',
							'short_name' => 'item_options',
							'type' => 'header',
						), 
						array(
							'name' => 'option_groups',
							'short_name' => 'option_groups',
							'type' => 'matrix',
							'settings' => array(
							      array(
									'name' => 'item_option_name',
									'short_name' => 'item_option_name',
									'default'	=> '',
									'type' => 'text',
								), 
								array(
									'name' => 'required',
									'short_name' => 'required',
									'default'	=> 'yes',
									'type' => 'checkbox',
								), 
								// @TODO make this use JS to generate new options
								array(
									'name' => 'option_data',
									'short_name' => 'option_data',
									'type' => 'textarea',
								), 
					
							),
						), 
						array(
							'name' => 'meta',
							'short_name' => 'meta',
							'type' => 'header',
						), 
						array(
							'name' => 'keywords',
							'short_name' => 'keywords',
							'default'	=> '',
							'type' => 'textarea',
						), 
					),
				),
			)
 		);
		/*
		$plugin_vars = array(
			'cartthrob_mcp' => $this,
			'settings' => array(
				'reports_settings' => array(
					'reports' => $this->EE->get_settings->get_setting($this->module_name, "reports")
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
									'options' => $this->EE->mbr_addon_builder->get_templates(),
								),
							)
						)
					)
				)
			),
		);
		*/ 

		$data['html'] = $this->EE->mbr_addon_builder->view_plugin_settings($structure); 
		return $this->EE->mbr_addon_builder->load_view(
					__FUNCTION__,
					array(
						'data' => $data, 
						'add_product_action'	=> form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob_order_manager'.AMP.'method=add_product_action&return='."view_products"),						
					)
				);
   		return $this->EE->mbr_addon_builder->load_view(__FUNCTION__, array(), $structure);
	}
	public function add_product_action()
	{
		$this->EE->load->helper("data_formatting"); 
		$create_data = array();  
		$post_data = $this->EE->input->post("add_products_settings"); 
		foreach ($post_data as $key => $value)
		{
			if (is_array($value))
			{
				$value = serialize($value); 
			}
			
			if ($value !== NULL)
			{
				$create_data[$key] = $value;
			}
			
		}
		
		if (!empty($create_data))
		{
			$this->EE->load->model('generic_model');
			$products = new Generic_model("cartthrob_products");

			$id = $products->create($create_data);
		}

		if (isset($id))
		{
			$this->EE->session->set_flashdata($this->module_name.'_system_message', sprintf('%s', lang($this->module_name.'_product_added_successfully')));
		}
		else
		{
			$this->EE->session->set_flashdata($this->module_name.'_system_error', sprintf('%s', lang($this->module_name.'_product_addition_failed')));
		}
		$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method='.$this->EE->input->get('return', TRUE));			
		
		
		// @TODO fix
 
		// http://expressionengine.com/user_guide/development/usage/file_field.html
		#$this->EE->load->library('file_field');
		#$this->EE->file_field->validate($data, $field_name, $required = 'n');
		#$this->EE->file_field->format_data($file_name, $directory_id = 0);
		//$this->EE->file_field->parse_field($data);
		
		
	}
	
	
	public function view_products()
	{
		$this->initialize();

		$this->EE->load->model('generic_model');
		$cartthrob_products = new Generic_model("cartthrob_products");
		$products = $cartthrob_products->read();
		$data['products'] = array(); 
		if ($products)
		{
			foreach ($products as &$prod)
			{
				$prod = array(
					$prod['title'], 
					$prod['price'],
				);
			}
		}

		$this->EE->load->library('table');
		$this->EE->table->clear();
		$this->EE->table->set_template(array(
			'table_open' => '<table border="0" cellpadding="0" cellspacing="0" class="mainTable padTable">',
		    'heading_cell_start'  => '<th colspan="6">',
		));

		$this->EE->table->set_heading(""); 
 		$data['product_table'] = $this->EE->table->generate($products);
		
		
		return $this->EE->mbr_addon_builder->load_view(
					__FUNCTION__,
					array(
						'data' => $data, 
					)
				);
				
  		return $this->EE->mbr_addon_builder->load_view(__FUNCTION__, array(), $structure);
		
	}
	public function edit_products()
	{
		$this->initialize();
		$structure = array(); 

  		return $this->EE->mbr_addon_builder->load_view(__FUNCTION__, array(), $structure);
		
	}
	//// ORDER
	public function delete_order()
	{
		$this->EE->load->model("order_management_model"); 
		$this->EE->load->library('api');
		$this->EE->api->instantiate('channel_entries');
		
		if (!$this->EE->input->post('id'))
		{
			$this->EE->session->set_flashdata($this->module_name.'_system_error', sprintf('%s', lang($this->module_name.'_no_item_was_selected_for_deletion')));
			$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method='.$this->EE->input->get('return', TRUE));	
		}

		$entry_ids = (array) $this->EE->order_management_model->get_purchased_items_by_order( $this->EE->input->post('id') ); 

		foreach ($entry_ids as $id)
		{
			if (!empty($id))
			{
				$this->EE->api_channel_entries->delete_entry( $id );
			}
		}
		
		$this->EE->api_channel_entries->delete_entry( $this->EE->input->post('id') );
		
 		$this->EE->session->set_flashdata($this->module_name.'_system_message', sprintf('%s', lang($this->module_name.'_order_deleted')));
		$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method='.$this->EE->input->get('return', TRUE));	
	}
	public function form_update()
	{
		if ( $this->EE->input->post('delete_order'))
		{
			if (!$this->EE->input->post('id'))
			{
				$this->EE->session->set_flashdata($this->module_name.'_system_error', sprintf('%s', lang($this->module_name.'_no_item_was_selected_for_deletion')));
				$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method='.$this->EE->input->get('return', TRUE));	
			}
			else
			{
				$this->delete_order(); 
			}
		}
		return $this->EE->mbr_addon_builder->form_update($this->table); 
	}
	
	public function plugin_setting($type, $name, $current_value, $options = array(), $attributes = array())
	{
		return $this->EE->mbr_addon_builder->plugin_setting($type, $name, $current_value, $options, $attributes);
	}
	public function system_settings()
	{
		$this->initialize();
		
		$structure['class']			= 'order_manager'; 
		$structure['description']	= ''; 
		$structure['caption']		= ''; 
		$structure['title']			= "cartthrob_order_manager_general_settings";
	 	$structure['settings'] = array(
			array(
				'name' => 'cartthrob_order_manager_cp_menu',
				'short_name' => 'cp_menu',
				'type' => 'select',
				'default' => 'yes',
				'options' => array('yes' => 'yes' , 'no' => 'no')
			),
			array(
				'name' => 'cartthrob_order_manager_invoice_template',
				'short_name' => 'invoice_template',
				'note'	=> 'cartthrob_order_manager_invoice_template_note',
				'type' => 'select',
				'attributes'=> array(
					'class' 	=> 'templates_blank',
				),
			), 
			array(
				'name' => 'cartthrob_order_manager_packing_slip_template',
				'short_name' => 'packing_slip_template',
				'note'	=> 'cartthrob_order_manager_packing_slip_template_note',
				'type' => 'select',
				'attributes'=> array(
					'class' 	=> 'templates_blank',
				),
			),
			array(
				'name' => 'custom_templates',
				'short_name' => 'custom_templates',
				'type' => 'matrix',
				'settings' => array(
				      array(
						'name' => 'cartthrob_order_manager_custom_template_name',
						'short_name' => 'custom_template_name',
						'default'	=> 'Custom Template',
						'type' => 'text',
					), 
					array(
						'name' => 'cartthrob_order_manager_custom_template',
						'short_name' => 'custom_template',
		 				'type' => 'select',
						'attributes'=> array(
							'class' 	=> 'templates_blank',
						),
					),
 				),
			),
	 
			/*
			array(
				'name' => 'cartthrob_order_manager_tracking_template',
				'short_name' => 'tracking_template',
				'type' => 'select',
				'attributes'=> array(
					'class' 	=> 'templates',
				),
			),
			*/
 		);

  		return $this->EE->mbr_addon_builder->load_view(__FUNCTION__, array(), $structure);

	}
	public function product_report()
	{
		$this->initialize();
		
 		$this->EE->cp->add_js_script(array('ui' => 'datepicker'));
		$date_picker_js = "
			if ($('.datepicker').size() > 0) 
			{
				$('.datepicker').datepicker({dateFormat: 'yy-mm-dd'});
				$('.datepicker').on('change',function(){
					var field_name = $(this).attr('name');
					var value = $(this).val();
					$(\"input[name='\"+field_name+\"']\").val(value);
				});
			}
		";
		$this->EE->javascript->output($date_picker_js);
		
 		
		$this->EE->load->model('order_management_model'); 
		$this->EE->load->helper("data_formatting"); 
		$this->EE->load->library('table');
		
		$data = array();
		
		$date_range = $this->EE->input->get_post('where');
		
		$where = array();
		if($date_range){
			if($date_range['date_start'])
			{
				$start_date = explode("-",$date_range['date_start']);
				$start_time = mktime(0, 0, 0, $start_date[1], $start_date[2], $start_date[0]);
				$where[$this->EE->db->dbprefix.'cartthrob_order_items.entry_date >='] = $start_time;
			}
			else
			{
				$data['date_start'] = NULL;
			}
			if($date_range['date_finish'])
			{
				$end_date = explode("-",$date_range['date_finish']);
				$end_time = mktime(23, 59, 59, $end_date[1], $end_date[2], $end_date[0]);
				$where[$this->EE->db->dbprefix.'cartthrob_order_items.entry_date <='] = $end_time;
			}
			else
			{
				$data['date_finish'] = NULL;
			}
			/*
			$where = array(
				$this->EE->db->dbprefix.'cartthrob_order_items.entry_date >=' => $data['date_start'],
				$this->EE->db->dbprefix.'cartthrob_order_items.entry_date <=' => $data['date_finish']
				);
				*/
		}
		$data['date_start'] = NULL;//$this->EE->localize->decode_date('%Y-%m-%d',$this->EE->localize->now - 7*24*60*60); 
		$data['date_finish'] = NULL; //$this->EE->localize->decode_date('%Y-%m-%d',$this->EE->localize->now ); 
		 
		if ($this->EE->input->get_post('id'))
		{
			$products = $this->EE->order_management_model->get_purchased_item($this->EE->input->get_post("id")); 

		}
		else
		{
			$products = $this->EE->order_management_model->get_purchased_products($where); 
		}

		foreach ($products as &$row)
		{
			$row['options'] = NULL; 
			if ($row['extra'] && $extra = _unserialize($row['extra'], TRUE))
			{
				foreach ($extra as $key => $value)
				{
					if (! $this->EE->input->get_post("download"))
					{
						$row['options'] .= "<strong>".$key."</strong>: ". $value."<br>"; 
					}
					else
					{
						$row['options'] .= $key.": ". $value."| "; 
					}
				}
 			}
			$row['total_sales'] 		= $this->EE->number->format($row['total_sales']); 
 			$row['price']		 		= $this->EE->number->format($row['price']);  
			$row['price_plus_tax'] 		= $this->EE->number->format($row['price_plus_tax']); 
 		
			if (! $this->EE->input->get_post("download") )
			{
				$href=  BASE.AMP.'C=content_publish'.AMP.'M=entry_form'.AMP.'entry_id='.$row['entry_id'];
				$detail_href= BASE.AMP. 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=run_reload'.AMP.'product_id='.$row['entry_id']; 

				$row['entry_id']			= $row['entry_id']."<a href='".$href."'> (".lang('cartthrob_order_manager_edit_product')."&raquo;)</a>"; 
				$row['title']			=  $row['title']."<a href='".$detail_href."'> (".lang('cartthrob_order_manager_product_detail_report')."&raquo;)</a>"; 
				
			}
			unset($row['row_id']);
			unset($row['row_order']);
			unset($row['quantity']);
			unset($row['order_id']);
			unset($row['weight']);
			unset($row['no_tax']);
			unset($row['no_shipping']);
			unset($row['extra']);
			unset($row['entry_date']);
		}
		if (!isset($products[0]))
		{
			// nothing has been sold. go back. 
			$this->EE->session->set_flashdata($this->module_name.'_system_message', sprintf('%s', lang($this->module_name.'_no_products_have_been_sold')));
			$this->EE->functions->redirect(BASE.AMP. 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=om_sales_dashboard');
		}
		$keys = array_keys($products[0]); 
		foreach($keys as &$val)
		{
			$val = lang("cartthrob_order_manager_".$val); 
		}
 		$this->EE->table->clear();

		$this->EE->table->set_template(array('table_open' => '<table border="0" cellpadding="0" cellspacing="0" class="mainTable padTable">'));

		$this->EE->table->set_heading(
			$keys
		); 
		
		if ($this->EE->input->get_post("download"))
		{
   			$this->export_csv($products, $keys , $this->EE->input->post('filename'));
		}
		
 		$data['products'] = $this->EE->table->generate(
				$products
		);
		
		$data['hidden_inputs'] = NULL;
		$this->EE->load->helper("form"); 
		if ($this->EE->input->get_post("id"))
		{
			
			foreach ($_POST as $key => $value)
			{
				$data['hidden_inputs'] .= form_hidden("id", $this->EE->input->get_post("id"));
			}
		}
		
		
		
		if(isset($_POST['where']['date_start']))
		{
			$data['hidden_inputs'] .= form_hidden("where[date_start]", $_POST['where']['date_start']);
		}
		else
		{
			$data['hidden_inputs'] .= form_hidden("where[date_start]",NULL);
		}
		if(isset($_POST['where']['date_finish']))
		{
			$data['hidden_inputs'] .= form_hidden("where[date_finish]", $_POST['where']['date_finish']);
		}
		else
		{
			$data['hidden_inputs'] .= form_hidden("where[date_finish]",NULL);
		}
		
		return $this->EE->mbr_addon_builder->load_view(
					__FUNCTION__,
					array(
						'data' => $data, 
						'run_report'	=> form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=product_report'.AMP.'return='.__FUNCTION__),
						'export_csv'	=> form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method='.__FUNCTION__.''.AMP.'return='.__FUNCTION__),
						
					)
				);
	}
	public function om_sales_dashboard()
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

			$_GET['return'] = 'om_sales_dashboard';
 			return $this->quick_save();
		}

		$this->initialize();
		$this->EE->load->model('order_model'); 
		$this->EE->load->helper("form" ,'array'); 
		
		if ($this->EE->input->get('entry_id'))
		{
			$this->EE->functions->redirect(BASE.AMP. 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=edit'.AMP.'id='.$this->EE->input->get('entry_id'));
			// this goes directly to the entry id
			#$this->EE->functions->redirect(BASE.AMP.'C=content_publish'.AMP.'M=entry_form'.AMP.'entry_id='.$this->EE->input->get('entry_id'));
		}

		$this->EE->load->library('reports');
		$this->EE->load->library('number');

 		if ($this->EE->input->get_post('report'))
		{
			$this->EE->load->library('template_helper');

			$this->EE->template_helper->reset(array(
				'base_url' => BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob_order_manager'.AMP.'method=om_sales_dashboard'.AMP.'report=',
				'template_key' => 'report',
			));

			if (is_numeric($this->EE->input->get_post('report')))
			{
				$this->EE->load->model('generic_model');
				$reports_model = new Generic_model("cartthrob_order_manager_reports");

				$report_info = $reports_model->read($this->EE->input->get_post("report"));
				if ($report_info)
				{

$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=run_reload'.AMP."id=".$report_info['id']);
					
				}
				
			}
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
			elseif($this->EE->input->get_post("date_start") || $this->EE->input->get_post("date_finish"))
			{
				$name = $this->EE->lang->line('reports_order_totals_in_range');
				$start = NULL; 
				$end = NULL; 
				if ($this->EE->input->get_post("date_start"))
				{
					$start = strtotime($this->EE->input->get_post("date_start")); 
				}
				if ($this->EE->input->get_post("date_end"))
				{
					$end = strtotime($this->EE->input->get_post("date_finish")); 
				}
				$rows = $this->EE->reports->get_all_totals($start, $end);
				$overview = lang('narrow_by_month');
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

			$data['view'] = $this->EE->load->view('om_reports_home', array('overview' => $overview), TRUE);
		}

		$this->EE->load->library('table');

		$this->EE->table->clear();

		$this->EE->table->set_template(array('table_open' => '<table border="0" cellpadding="0" cellspacing="0" class="mainTable padTable">'));

		$this->EE->load->model('order_model'); 
		$this->EE->load->model('order_management_model'); 
		
		$this->EE->load->library("localize"); 
		
		$orders = $this->EE->order_model->get_orders(array(
					'year'=> $this->EE->localize->format_date("%Y"), 
					'month'=> $this->EE->localize->format_date("%m"), 
					'day' => $this->EE->localize->format_date("%d"))); 
		
		$todays_order_list = array(); 
 		foreach($orders as $order)
		{
			$first_name = NULL; 
			$last_name = NULL; 
			if ( $this->EE->cartthrob->store->config('orders_billing_first_name') )
			{
				$first_name = element('field_id_'. $this->EE->cartthrob->store->config('orders_billing_first_name'), $order); 
			}
			if ( $this->EE->cartthrob->store->config('orders_billing_last_name') )
			{
				$last_name = element('field_id_'. $this->EE->cartthrob->store->config('orders_billing_last_name'), $order); 

			}
 			$href= BASE.AMP. 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=edit'.AMP.'id='.element('entry_id', $order); 
			$member = BASE.AMP.'C=myaccount'.AMP.'M=edit_profile'. AMP.'D=cp'.AMP.'id=';  
			
			$this->EE->load->model("order_management_model"); 
		
			if ($this->EE->order_management_model->is_member($order['author_id']))
			{
				$member_info =  "<a href='".$member.element('author_id', $order)."'>(".element('author_id', $order).") ". $first_name." ". $last_name." &raquo;</a><br> "; 
			}
			else
			{
				$member_info =    $first_name." ". $last_name;
			}
			$this->EE->load->library("localize"); 
			$date = $this->EE->localize->format_date("%h%i %a", element('entry_date', $order), $localize = TRUE);  
			/// date("H:i A", element('entry_date', $order)); 
			
			$todays_order_list[] = array(
				"<a href='".$href."'>".element('title', $order)." &nbsp;</a>", 
				$this->EE->number->format(element('order_total',$order)), 
				element('status', $order),
				$member_info, 
				$date, 
				""
			); 
		}
		if (empty($todays_order_list))
		{
 			$todays_order_list[] = array("none","","","","",""); 
		}
		$todays_orders_header = array(
			lang('cartthrob_order_manager_todays_orders'), 
				lang('cartthrob_order_manager_total'), 
				lang('cartthrob_order_manager_status'), 
				lang('cartthrob_order_manager_customer'), 
				lang('cartthrob_order_manager_time'), 
				""
		); 
		
		array_unshift($todays_order_list, $todays_orders_header); 
 		$data['todays_orders'] = $this->EE->table->generate( $todays_order_list);


		$order_totals = $this->EE->order_model->order_totals(); 
		
 		$average_total = "Not available"; 
		if (isset($order_totals['average_total']))
		{
			$average_total = $this->EE->number->format($order_totals['average_total']); 
		}

		$this->EE->load->library("localize"); 
		$year_totals  = $this->EE->order_model->order_totals(array('year' =>  $this->EE->localize->format_date("%Y") )); 

		$months_orders = $this->EE->order_model->order_totals( array(
																	'year' => $this->EE->localize->format_date("%Y"),
																	'month' => $this->EE->localize->format_date("%m"),
 																));

		$todays_orders = $this->EE->order_model->order_totals( array(
																'year' => $this->EE->localize->format_date("%Y"),
																'month' => $this->EE->localize->format_date("%m"),
																'day'	=> $this->EE->localize->format_date("%d"),
																)); 
		
 																													
		$data['order_totals'] = $this->EE->table->generate(array(
			array(lang('order_totals'), lang('amount')),
			array(lang('today_sales'), $this->EE->number->format($this->EE->reports->get_current_day_total())),
			array(lang('month_sales'), $this->EE->number->format($this->EE->reports->get_current_month_total())),
			array(lang('year_sales'), $this->EE->number->format($this->EE->reports->get_current_year_total())),
			array(lang('cartthrob_order_manager_total_sales'), $this->EE->number->format($order_totals['total'])),
			array(lang('cartthrob_order_manager_average_sale'),  $average_total ),
			
			array(lang('cartthrob_order_manager_todays_orders'), $todays_orders['orders']),
			array(lang('cartthrob_order_manager_months_orders'), $months_orders['orders']),
			array(lang('cartthrob_order_manager_years_orders'), $year_totals['orders']),
			array(lang('cartthrob_order_manager_total_orders'), $order_totals['orders']),

			array(lang('cartthrob_order_manager_total_customers'), $this->EE->order_management_model->get_customer_count()),

		));

		$data['current_report'] = $this->EE->input->get_post('report');

		$reports = $this->EE->get_settings->get_setting($this->module_name, "reports"); 
		
		// the put any already created in CT in there
		if (!$reports)
		{
			$reports = $this->EE->cartthrob->store->config('reports') ;
		}
		$data['reports'] = array(); 
		foreach ($reports  as $report)
		{
			$data['reports']['Custom Reports'][$report['template']] = $report['name'];
		}
		if (!empty($data['reports']))
		{
			asort($data['reports']);
			// shoving the default on the front
			array_unshift($data['reports'], lang('order_totals')); 
		}
		
		$this->EE->load->model('generic_model');
		$reports_model = new Generic_model("cartthrob_order_manager_reports");
 					
		$order_reports = $reports_model->read(NULL,$order_by=NULL,$order_direction='asc',$field_name="type",$string="order" );

		if ($order_reports)
		{
			foreach ($order_reports as $rep)
			{
				$data['reports']['Order Reports'][$rep['id']] = $rep['report_title']; 
			}
		}
		
		
  		$plugin_vars = array(
			'cartthrob_mcp' => $this,
			'settings' => array(
				'reports_settings' => array(
					'reports' => $this->EE->get_settings->get_setting($this->module_name, "reports")
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
									'options' => $this->EE->mbr_addon_builder->get_templates(),
								),
							)
						)
					)
				)
			),
		);
		$data['reports_list'] = $this->EE->mbr_addon_builder->view_plugin_settings($plugin_vars); 
		
		$data['reports_filter'] = form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob_order_manager'.AMP.'method=om_sales_dashboard', 'id="reports_filter"');

		$data['reports_date'] = form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob_order_manager'.AMP.'method=om_sales_dashboard', 'id="reports_date"');
 		
		$date_picker_js = "
			if ($('.datepicker').size() > 0) 
			{
				$('.datepicker').datepicker({dateFormat: 'yy-mm-dd'});
			}
		";		
		$this->EE->cp->add_js_script(array('ui' => 'datepicker'));
 		$this->EE->javascript->output($date_picker_js);
		
  		return $this->EE->mbr_addon_builder->load_view("om_sales_dashboard", $data);
	}
	public function customer_export_datasource($where =array(), $order_by= "entry_date", $sort="DESC", $limit = NULL, $offset = NULL)
	{
		$this->EE->load->model('cartthrob_field_model'); 
		$channel_id = $this->EE->cartthrob->store->config('orders_channel'); 
		$order_channel_fields = $this->EE->cartthrob_field_model->get_fields_by_channel($channel_id); 
		
		$fields =  $this->order_fields;
		
		$default_show = array(); 
		$headers = array(); 
		
		foreach ($fields as $field)
		{
			if ($this->EE->cartthrob->store->config($field))
			{
				$default_show[] = "field_id_".$this->EE->cartthrob->store->config($field); 
				$lang = lang($field); 
				$headers[ "field_id_".$this->EE->cartthrob->store->config($field) ] = ($lang ? $lang : $field); 
			}
		}
		
		
		$this->EE->load->model("order_management_model"); 
		
		$rows = $this->EE->order_management_model->get_customers($where, $order_by, $sort, $limit, $offset); 
		
		$new_rows = array(); 
		foreach ($rows as $key => &$row)
		{
			$new_row = $fields; 
			foreach ($row as $k => $r)
			{
				if (! in_array($k, $default_show))
				{
					unset($row[$k]); 
				}
			}
			$new_rows[] = array_merge($headers, $row);
		}
 		return array("data" => $new_rows, "headers" => $headers); 
 	}
	public function customer_report_datasource($state, $params = array())
	{
		$offset = $state['offset'];
		if (isset($params['limit']))
		{
			$limit= $params['limit']; 
		}
		else
		{
			$limit=$this->limit; 
		}
	    $this->sort = $state['sort'];
		
		$this->EE->load->model("order_management_model"); 
		
		$sub_params = array(); 
		foreach ($state['sort'] as $key => $value)
		{
			$sub_params['order_by'][] = $key;
			$sub_params['sort'][] = $value; 
		}
		
		// let's only show orders that have a status that matches "completed/authorized" orders
		$default_status = $this->EE->get_settings->get_setting("cartthrob","orders_default_status");
		$where = array();
		$where_url = '';
		if($default_status)
		{
			$where['status'] = $default_status;
			$where_url = AMP.'where[status]='.$default_status;
		}
		$rows = $this->EE->order_management_model->get_customers($where, $sub_params['order_by'], $sub_params['sort'], $limit, $offset); 
		
		foreach ($rows as $key => &$row)
		{
			$email_address = NULL; 
			$first_name = NULL; 
			$last_name = NULL; 
			$phone = NULL; 
			
			if (!empty($row["field_id_".$this->EE->cartthrob->store->config('orders_customer_email')]))
			{
				$email_address = $row["field_id_".$this->EE->cartthrob->store->config('orders_customer_email')]; 
			}
			if (!empty($row["field_id_".$this->EE->cartthrob->store->config('orders_billing_first_name')]))
			{
				$first_name = $row["field_id_".$this->EE->cartthrob->store->config('orders_billing_first_name')]; 
			}
			if (!empty($row["field_id_".$this->EE->cartthrob->store->config('orders_billing_last_name')]))
			{
				$last_name = $row["field_id_".$this->EE->cartthrob->store->config('orders_billing_last_name')]; 
			}
			if (!empty($row["field_id_".$this->EE->cartthrob->store->config('orders_customer_phone')]))
			{
				$phone = $row["field_id_".$this->EE->cartthrob->store->config('orders_customer_phone')]; 
			}

			// first and last name + link to edit information
			
 			if ($this->EE->order_management_model->is_member($row['author_id']))
			{
			$new_row["field_id_".$this->EE->cartthrob->store->config('orders_billing_last_name')] = 
					'<a href="'.BASE.AMP.'C=myaccount'.AMP.'M=edit_profile'. AMP.'D=cp'.AMP.'id='.$row['author_id'] .'">'.$first_name." ". $last_name.'('.$row['author_id'].')</a>'; 
			}
			else
			{
				$new_row["field_id_".$this->EE->cartthrob->store->config('orders_billing_last_name')] =  $first_name." ". $last_name; 
				
			}
			// email address + link		
			$new_row["field_id_".$this->EE->cartthrob->store->config('orders_customer_email')] = 
					'<a href="mailto:'.$email_address.'">'.$email_address.'</a>'; 
			// customer phone
			$new_row["field_id_".$this->EE->cartthrob->store->config('orders_customer_phone')] = $phone; 

			$new_row["order_total"] 	= $this->EE->number->format($row['order_total']);
			
			$new_row["order_count"] 	= $row['order_count']; 
			
			$this->EE->load->library("localize"); 
			$new_row["order_last"]	 	= $this->EE->localize->format_date("%m-%d-%Y %h:%i %a", $row['order_last'], $localize = TRUE);  
			// @date("m-d-Y h:i a", $row['order_last']);
			
			// link to customer report. can't use post, so we have to use a link
			$new_row['actions']			= '<a href="'.BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=run_reload'.AMP.'return='.__FUNCTION__.AMP.'customer_email='.$email_address.$where_url.'" class="submit">'.lang('cartthrob_order_manager_view_customer_orders').'</a>'; 
			
			$row = $new_row; 			
		}
		$count = $this->EE->order_management_model->get_customer_count(); 
 
  		return array(
		    'rows' => $rows, 
		    'pagination' => array(
		        'per_page'   => $limit,
		        'total_rows' => $count,
		    ),
		);
		
	}
 	public function customer_report()
	{
		$this->EE->load->helper("form"); 
		$this->EE->load->model("order_management_model"); 
		
		$data['customer_count'] = $this->EE->order_management_model->get_customer_count(); 
		
		if ($this->EE->input->get_post("download"))
		{
			$this->EE->load->library("cartthrob_file"); 
			$filename = $this->EE->input->get_post("filename"); 
			$this->EE->cartthrob_file->output_streaming_file_headers($filename); 
			$limit = $this->limit; 

			for ($i = 0;  $i < $data['customer_count'] ; $i += $limit)
			{
				$data = $this->customer_export_datasource($where =array(), $order_by= "entry_date", $sort="DESC", $limit, $offset=$i ); 
				echo $this->export_csv($data['data'], $data['headers'], $filename, $return_data = FALSE); 
			}
			die(); 
		}
		else
		{
			$this->initialize();
			$this->EE->load->library("table"); 
			
			$this->EE->table->clear();
			$this->EE->table->set_template(array(
				'table_open' => '<table border="0" cellpadding="0" cellspacing="0" class="mainTable padTable">',
			));
			
			// though more data will be returned than these fields, these will be the sorting keys
			$customer_name_key 		= "field_id_".$this->EE->cartthrob->store->config('orders_billing_last_name'); 
			$customer_phone_field 	= "field_id_".$this->EE->cartthrob->store->config('orders_customer_phone'); 
			$customer_email_field 	= "field_id_".$this->EE->cartthrob->store->config('orders_customer_email'); 
			$order_amount_key 		= "order_total"; // not sortable, since this is a sum  
			$order_last 			= "order_last"; // not sortable since this is dynamic 
			$order_count 			= "order_count"; // not sortable since this is a sum 
			
			$this->EE->table->set_columns(array(
				$customer_name_key  	=> array('header' => lang('cartthrob_order_manager_customer_name')), 
				$customer_email_field 	=> array('header' => lang('cartthrob_order_manager_customer_email')), 
				$customer_phone_field 	=> array('header' => lang('cartthrob_order_manager_customer_phone')), 
				$order_amount_key 		=> array('header' => lang('cartthrob_order_manager_total_order_amount')), 
				$order_count  			=> array('header' => lang('cartthrob_order_manager_total_order_count')), 
				$order_last 			=> array('header' => lang('cartthrob_order_manager_total_order_date')), 
				"actions"				=> array('header'  => lang('cartthrob_order_manager_total_order_date')),
			));
 
			$defaults = array(
			    'sort' => array($customer_name_key => 'asc'),
			);
			
		 	$data_table = $this->EE->table->datasource( 'customer_report_datasource' , $defaults, array('limit'=> $this->limit));
		
			
			$content = $data_table['table_html'];
			$content .= $data_table['pagination_html'];
			$data['html'] = $content; 
 
  	 		return $this->EE->mbr_addon_builder->load_view(
						__FUNCTION__,
						array(
							'data'	=> $data,
							'export_csv'	=> form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method='.__FUNCTION__.''.AMP.'return='.__FUNCTION__),
						)
 					);
		}
	}
	
	public function order_report()
	{
		if (is_numeric($this->EE->input->get_post('report')))
		{
			$this->EE->load->model('generic_model');
			$reports_model = new Generic_model("cartthrob_order_manager_reports");
			$report_info = $reports_model->read($this->EE->input->get_post("report"));
			if ($report_info)
			{
				$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=run_reload'.AMP."id=".$report_info['id']);
			}
		}
					
		$this->initialize();
 		$this->EE->cp->add_js_script(array('ui' => 'datepicker'));
		$this->EE->load->helper('form'); 

		$data = array(); 
		$data['date_start'] = NULL;//$this->EE->localize->decode_date('%Y-%m-%d',$this->EE->localize->now - 7*24*60*60); 
		$data['date_finish'] = NULL; //$this->EE->localize->decode_date('%Y-%m-%d',$this->EE->localize->now ); 
		
		$this->EE->load->model('generic_model');
		$reports_model = new Generic_model("cartthrob_order_manager_reports");
		$order_reports = $reports_model->read(NULL,$order_by=NULL,$order_direction='asc',$field_name="type",$string="order" );
		$data['reports'] = array(); 
		if ($order_reports)
		{
			foreach ($order_reports as $rep)
			{
				$data['reports'][$rep['id']] = $rep['report_title']; 
			}
		}

		$date_picker_js = "
			if ($('.datepicker').size() > 0) 
			{
				$('.datepicker').datepicker({dateFormat: 'yy-mm-dd'});
			}
		"; 
		
		// ORDER TOTALS
		$fields = array(
			'average_total',
			'discount',
			'orders',
			'shipping',
			'shipping_plus_tax',
			'subtotal', 
			'subtotal_plus_tax',
			'tax',
			'total',
 		);
		
		$default_show = array(
			'total',
			'subtotal', 
			'tax',
			'shipping',
			'discount',
			'orders', 
			'order_items',
		);
		foreach ($fields as $value)
		{
			$checked=NULL; 
			if (in_array($value, $default_show))
			{
				$checked = ' checked="checked" '; 
			}
			$fields_input[] =  lang("cartthrob_order_manager_".$value); 
			$fields_input[] =  ' <input type="checkbox" value="'.$value.'" '.$checked.' name="show_fields[]" />';
		}

		$this->EE->load->library('table');
		$this->EE->table->clear();
		$this->EE->table->set_template(array(
			'table_open' => '<table border="0" cellpadding="0" cellspacing="0" class="mainTable padTable">',
            'heading_cell_start'  => '<th colspan="6">',
		));
		
		$this->EE->table->set_heading(lang("cartthrob_order_manager_include_total_fields")); 
		$new_list = $this->EE->table->make_columns($fields_input, 6);
		$data['order_totals'] = $this->EE->table->generate($new_list);
		///// SEARCH ORDER FIELDS
		
		$search_fields = array(
			'cartthrob_mcp' => $this,
			'plugin_type' => 'search_fields',
			'plugins' => array(
				array(
					'classname' => 'search_fields',
					'title' => 'search_fields_title',
 					'settings' => array(
						array(
							'name' => 'search_fields',
							'short_name' => 'search_fields',
							'type' => 'matrix',
							'settings' => array(
								array(
									'name' => 'search_field',
									'short_name' => 'search_field',
									'type'	=> 'select',
									'attributes'	=> array('class' => 'order_channel_fields')
 								),
								array(
									'name' => 'search_content',
									'short_name' => 'search_content',
									'type' => 'text',
								),
								array(
									'name' => 'search_not_empty',
									'short_name' => 'search_not_empty',
									'type' => 'checkbox',
								),
							)
						)
					)
				)
			),
		);
		$data['search_fields'] = $this->EE->mbr_addon_builder->view_plugin_settings($search_fields, TRUE);
 
		
		///// MEMBER INPUT FIELDS
		
		$this->EE->table->clear();
		$this->EE->table->set_template(array(
			'table_open' => '<table border="0" cellpadding="0" cellspacing="0" class="mainTable padTable">',
	        'heading_cell_start'  => '<th colspan="4">',
		));

		$this->EE->table->set_heading(""); 

		$member_field_list = array(
			'author_id',
			'email_address',
			'first_name',
			'last_name',
			'city',
			'state',
			'country_code'
		); 
		$member_field_inputs = array(); 

		foreach ($member_field_list as $value)
		{
			$member_field_inputs[] = lang("cartthrob_order_manager_".$value); 

			switch($value)
			{
				case "state": 
					$class = "states_blank"; 
					 $member_field_inputs[] = form_dropdown("where[".$value."]", array(), null, "class='".$class."'");
					break;
				case "country_code": 
					$class = "countries_blank";
					 $member_field_inputs[] = form_dropdown("where[".$value."]", array(), null, "class='".$class."'");
					break; 
				default:
				$member_field_inputs[] = form_input("where[".$value."]", ""); 
			}
	 	}
		$this->EE->table->set_heading(lang("cartthrob_order_manager_search_member_data")); 
	
		$new_list = $this->EE->table->make_columns($member_field_inputs, 4);
		$data['member_inputs'] = $this->EE->table->generate($new_list);
		
		///// ORDER FIELDS
		
		$this->EE->load->model('cartthrob_field_model'); 
		$channel_id = $this->EE->cartthrob->store->config('orders_channel'); 
		$order_channel_fields = $this->EE->cartthrob_field_model->get_fields_by_channel($channel_id); 
		
		$order_fields = array(); 
		$default_show = array(
			'order_total',
			'order_subtotal',
			'order_shipping',
			'order_shipping',
			'order_tax',
			'discount',
			'order_items',
		);
		
		$order_fields['title'] =      'title';
		$order_fields['entry_id'] =    'entry_id';
		$order_fields['status'] =      'status';
		$order_fields['year'] =        'year';
		$order_fields['month'] =       'month';
		$order_fields['day'] =         'day';
		$order_fields['entry_date'] =  'entry_date';
				
		foreach ($order_channel_fields as $value)
		{
			if (in_array($value['field_name'], $default_show))
			{
				$default_show[] = "field_id_".$value['field_id']; 
			}
			$order_fields["field_id_".$value['field_id']]  = $value['field_label']; 
		}
		asort($order_fields); 

		foreach ($order_fields as $key => $value)
		{
			$checked=NULL; 
			if (in_array($key, $default_show))
			{
				$checked = ' checked="checked" '; 
			}
			$order_fields_input[] =  $value; 
			$order_fields_input[] =   ' <input type="checkbox" value="'.$value.'" '.$checked.' name="order_fields['.$key.']" />';
		}
		
		// GENERATE ORDER FIELDS TABLE
		$this->EE->table->clear();
		$this->EE->table->set_template(array(
			'table_open' => '<table border="0" cellpadding="0" cellspacing="0" class="mainTable padTable">',
            'heading_cell_start'  => '<th colspan="6">',
		));
		
		$this->EE->table->set_heading(lang("cartthrob_order_manager_include_order_fields")); 
		$new_list = $this->EE->table->make_columns($order_fields_input, 6);
		$data['order_fields'] = $this->EE->table->generate($new_list);
		
		// JAVASCRIPT
		$this->EE->cp->add_js_script(array('ui' => 'datepicker'));
 		$this->EE->javascript->output($date_picker_js);
		
 		return $this->EE->mbr_addon_builder->load_view(
					__FUNCTION__,
					array(
						'data' => $data, 
						'run_report'	=> form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=run_reload'.AMP.'return='.__FUNCTION__),
						'reports_filter' => form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob_order_manager'.AMP.'method='.__FUNCTION__, 'id="reports_filter"'),
						
					)
				);
	}
	
	public function run_reload()
	{
		$return = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=run_report'; 
		$string = NULL; 
		$new_post = array(); 
 		$new_post = $_POST;
		
		foreach ($new_post as $key => $value)
		{
			$new_post[$key] = xss_clean($value); 
		}
		
		foreach($_GET as $key => $value)
		{
			if ($key != "submit" && $key != 'method' && $key != 'module')
			{
				$new_post[$key] = xss_clean($value);
			}
		}
		
		if (!empty($new_post))
		{
			$string = AMP. "params=".urlencode(base64_encode(serialize($new_post))); 
		}
 
		$return = $return . $string;
		
		$this->EE->functions->redirect($return);			
	}
	public function remove_report()
	{
		$string = NULL; 
		$new_get = array(); 
 		$new_get = $_GET; 
		foreach ($new_get as $key => $value)
		{
			$new_get[$key] = xss_clean($value); 
		}
		
		if (!empty($new_get))
		{
			$string = AMP. "params=".urlencode(base64_encode(serialize($new_get))); 
		}
        $this->EE->load->model('generic_model');
        $reports_model = new Generic_model("cartthrob_order_manager_reports");

        $report_info = $reports_model->read($new_get['reportID']);
        if ($report_info)
        {
            $reports_model->delete($new_get['reportID']);
            $this->EE->functions->redirect(BASE.AMP. 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=order_report');
        }              
	}
	public function run_report()
	{
		$this->EE->load->helper("array"); 
		
		$p_data = array(); 
		
		if (!isset($_GET['params']) && isset($_SERVER['QUERY_STRING']))
		{
			$get = array(); 
 			parse_str($_SERVER['QUERY_STRING'], $get); 
 			if (isset($get['params']))
			{
				$_GET['params'] = $get['params']; 
			}
		}
		
		if (isset($_GET['params']))
		{
			$p_data = @unserialize(base64_decode(urldecode($_GET['params']))); 
		}
		elseif ( $this->EE->input->post("download") )
		{
			$p_data = array_merge($_GET, $_POST); 
		}
		
		if (isset($p_data['where']) && is_array($p_data['where']) && strtolower(element('status', $p_data['where'])) == "any")
		{
			unset($p_data['where']['status']); 
			$any_status = "any" ;
		} 

 		$this->EE->load->model('cartthrob_field_model'); 
		$this->EE->load->model('order_management_model');
		$this->EE->load->model('order_model');
		// 1. ========= AFFECTS PAGE LOAD AND CONTENTS
		///////////// LOAD EXISTING REPORTS
		if (element("id", $p_data))
		{
			$this->EE->load->model('generic_model');
			$reports_model = new Generic_model("cartthrob_order_manager_reports");

			$report_info = $reports_model->read(element("id", $p_data));
 			if ($report_info)
			{
				$this->EE->load->helper("data_formatting"); 
				$settings = _unserialize($report_info['settings']); 
				if (isset($settings['report_title']))
				{
					// if this is unset, then the report title will not auto fill the export name for the file so you end up with a generic name
					#unset($settings['report_title']); 
				}
				if (isset($settings['save_report']))
				{
					unset($settings['save_report']); 
				}
				$p_data= array_merge($p_data, $settings); 
			}
		}
		////////// SAVE REPORTS
		if (element("save_report", $p_data))
		{
			$settings = array(); 
			foreach ($p_data as $key => $value)
			{
				if (!in_array($key,$this->remove_keys) && !empty($value))
				{
					$settings[$key]  = $value; 
				}
			}
			$this->EE->load->library("localize"); 
  			$key = $this->save_report($settings, element("report_title", $p_data, "Untitled " . $this->EE->localize->format_date("%m-%d-%Y %h:%i %a") ) );
			// @date("m-d-Y h:i a")
			if ($key)
			{
				$this->EE->session->set_flashdata($this->module_name.'_system_message', sprintf('%s', lang($this->module_name.'_report_saved_successfully')));
				$this->EE->functions->redirect(BASE.AMP. 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=order_report');
				
			}
			else
			{
				$this->EE->session->set_flashdata($this->module_name.'_system_message', sprintf('%s', lang($this->module_name.'_report_not_saved')));
				$this->EE->functions->redirect(BASE.AMP. 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=order_report');
			}
		}
		
		// 2. ========= SET UP PARAMETERS FOR DB SEARCH
		$where_array = (array) element("where", $p_data); 
		$where = array(); 
		
		$where_array = array_filter($where_array);
		//////// FILTER BY CUSTOMER
		if (element("customer_email", $p_data))
		{
			$where_array["field_id_".$this->EE->cartthrob->store->config('orders_customer_email')] = element("customer_email", $p_data); 
		}
		
		foreach ($where_array as $key => $value)
		{
			switch ($key)
			{
				case "date_start": 
					$where['entry_date >='] =  strtotime($value);
 					break;
				case "date_finish": 
					$where['entry_date <='] =  strtotime($value);
					break;
				case "total_maximum":
					$field_id = $this->EE->cartthrob->store->config('orders_total_field'); 
 					if ($field_id)
					{
						$where["field_id_".$field_id. " <"] = $this->EE->cartthrob->sanitize_number($value, TRUE);
					}
					break;
				case "total_minimum":
					$field_id = $this->EE->cartthrob->store->config('orders_total_field'); 
 					if ($field_id)
					{
						$where["field_id_".$field_id. " >"] = $this->EE->cartthrob->sanitize_number($value, TRUE);
					}
					break; 
				default:
					$where[$key] = $value; 
			}
		}
		
		////// FILTER BY CONTENT IN SPECIFIC FIELDS
		$search_fields = element('search_fields_settings', $p_data); 
		$product_order_ids = array(); 
 		if ( !empty( $search_fields))
		{
			$order_items_field = NULL; 
			$order_items_field_id = $this->EE->cartthrob->store->config('orders_items_field');
			if ($order_items_field_id)
			{
				$order_items_field = "field_id_". $order_items_field_id; 
			}
			
			foreach($search_fields['search_fields'] as $key=>$value)
			{
 				if (!empty($value['search_content']) && !empty($value['search_field']))
				{
					// this could get to be a really slow search on large DBs
					if ($order_items_field && $value['search_field'] == $order_items_field)
					{
						$order_items_where = array(); 
						if (is_numeric($value['search_content']))
						{
							// i happen to know that we'll have to specify the DB prefix here
 							$order_items_where[$this->EE->db->dbprefix.'cartthrob_order_items.entry_id'] =  $value['search_content']; 
						}
						else
						{
							// i happen to know that we'll have to specify the DB prefix here
							// this will only get EXACT results. no... "like" search. 
 							$order_items_where[$this->EE->db->dbprefix.'cartthrob_order_items.title'] =  $value['search_content']; 
						}
						
						$purchased_products_status = NULL; 
						if (isset($p_data['where']['status']))
						{
							$purchased_products_status = $p_data['where']['status']; 
						}
						elseif (isset($any_status))
						{
							$purchased_products_status = "any"; 
						}
						
						$product_entries = $this->EE->order_management_model->get_purchased_products(array(), $order_by= "title", $sort="ASC", NULL, NULL, $order_items_where, $purchased_products_status); 
						
						if ($product_entries)
						{
							foreach ($product_entries as $e)
							{
								$ordz = $this->EE->order_management_model->get_related_orders_by_item(element('entry_id', $e)); 
 								if ($ordz)
								{
									foreach ($ordz as $k => $v)
									{
										$product_order_ids[] = element('order_id', $v); 
									}
								}
							}
						}
					}
					else
					{
					$where[$value['search_field']] = $value['search_content']; 
				}
				}
				elseif (!empty($value['search_field']) && !empty($value['search_not_empty']))
				{
					$where[$value['search_field']] = "IS NOT NULL"; 
				}
			}
		}
		
		if ($product_order_ids)
		{
			$product_order_ids = array_unique($product_order_ids);
			$where[$this->EE->db->dbprefix.'channel_titles.entry_id'] = $product_order_ids; 
		}
		// remove empties
		$where = array_filter($where); 
 
		// 3. ========= SET UP THE FIELDS THAT WILL BE SHOWN
  		$show_fields = element("show_fields", $p_data); 
		if (!$show_fields)
		{
			$show_fields = array(
				'average_total',
				'discount',
				'orders',
				'shipping',
				'shipping_plus_tax',
				'subtotal', 
				'subtotal_plus_tax',
				'tax',
				'total'
	 		);
		}
 		$order_fields = array(); 
		$order_fields = element("order_fields", $p_data);
		if (!$order_fields)
		{
 			$order_fields = array(
				'title'	=> 'title',
				'status'	=> 'status',
				'entry_date'	=> 'entry_date',
				"field_id_".$this->EE->cartthrob->store->config("orders_total_field")	 	=> lang("cartthrob_order_manager_".'total'),
				"field_id_".$this->EE->cartthrob->store->config("orders_subtotal_field") 	=> lang("cartthrob_order_manager_".'subtotal'), 
				"field_id_".$this->EE->cartthrob->store->config("orders_tax_field") 		=> lang("cartthrob_order_manager_".'tax'),
				"field_id_".$this->EE->cartthrob->store->config("orders_shipping_field") 	=> lang("cartthrob_order_manager_".'shipping'),
				"field_id_".$this->EE->cartthrob->store->config("orders_discount_field") 	=> lang("cartthrob_order_manager_".'discount'),
 				"field_id_".$this->EE->cartthrob->store->config("orders_items_field") 		=> lang("cartthrob_order_manager_".'items')
			);
		}
		
		// 4. ========= OUTPUT THE CONTENT
		$header_data =  $this->order_data($where, $order_by = "title", $sort="DESC", 1, 0, $order_fields, "CSV"); 
		
 		if (empty($header_data['order_data']))
		{
			$this->EE->session->set_flashdata($this->module_name.'_system_error', sprintf('%s', lang($this->module_name.'_report_no_results')));
			$this->EE->functions->redirect(BASE.AMP. 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=order_report');
		}
		/// based on the single line item we grabbed above, we'll generate the CSV and standard headers
		// headers = standard, raw_headers = csv headers. 
		$headers = $raw_headers = array(); 
		$params = array(); 
		
		foreach ($header_data['order_headers'] as $field_id => $type)
		{
			$label =  $this->EE->cartthrob_field_model->get_field_label(str_replace("field_id_", "", $field_id)); 
			if ($label)
			{
				$headers[$field_id] = array('header' => $label); 
				$params['fields'][$field_id] = $label;  
				
				$raw_headers[] = $label; 
			}
			else
			{
				if ($field_id == "title")
				{
					$headers[$field_id] = array('header' => lang("order_id")); 
					$params['fields'][$field_id] = lang("order_id");  
					
					$raw_headers[] = lang("order_id"); 
				}
				else
				{
					$headers[$field_id] = array('header' => lang($field_id)); 
					$params['fields'][$field_id] =  lang($field_id); 
					
					$raw_headers[] = lang($field_id); 
					
				}
			}
 		}
		////// SEARCH BY PRODUCT ID
		$order_ids = NULL;
		
		if (element("product_id", $p_data))
		{
			$this->EE->load->model('order_management_model');
			
			$order_ids = $this->EE->order_management_model->get_related_orders_by_item(element("product_id", $p_data)); 
			if ($order_ids)
			{
				foreach ($order_ids as $key =>&$value)
				{
					$value = $value['order_id']; 
				}
			}
  		}
 		///// EXPORT TO CSV
		if (element("download", $p_data))
		{
			$this->EE->load->library("cartthrob_file"); 
			$filename = element("filename", $p_data); 
		
			if (!$filename)
			{
				$filename = "untitled";
			}
			if (element("report_title", $p_data))
			{
				$filename = element("report_title", $p_data); 
			}
			
			if (strpos($filename, ".xls") === FALSE)
			{
				$filename = $filename . ".xls"; 
			}
			
 			$this->EE->cartthrob_file->output_streaming_file_headers($filename); 
			$limit= $this->limit; 
			$order_totals = $this->EE->order_model->order_totals($where); 
			for ($i = 0;  $i < $order_totals['orders'] ; $i += $limit)
			{
				if ($order_ids)
				{
					$this->EE->db->where_in("channel_data.entry_id", $order_ids);
				}
				$data = $this->order_data($where, $order_by = "title", $sort="DESC", $limit, $offset = $i, $order_fields, "CSV"); 
				
				if (!isset($send_headers))
				{
					$send_headers = $data['order_headers'] ; 
					$first = TRUE; 
				}
				else
				{
					$send_headers = array(); 
				}
 				// must set $return_data to true so that this can stream output as a download in case it's huge. 
	  			echo $this->export_csv($data['order_data'], $send_headers, $filename, $return_data = TRUE);
			}
			// once everythign's done streaming... kill the process
			die();
		}
		///// LOAD THE PAGE
		else
		{
			$this->initialize(); 
			$this->EE->load->library("table"); 
			
			$this->EE->table->clear();

			$defaults = array(
			    'sort' => array("title" => 'desc'),
			);
			
			if ($order_ids)
			{
				$where['channel_data.entry_id'] = $order_ids; 
				$params['where'] = $where; 
			}
			elseif (element("customer_email", $p_data) || element("product_id", $p_data))
			{
				$params['where'] = $where; 
			}
			elseif ($where)
			{
				$params['where'] = $where; 
 			}
			
			$params['limit'] = $this->limit; 

			
			if (!empty($order_fields))
			{
				$this->EE->table->set_columns( $headers );
			}
		 	$data_table = $this->EE->table->datasource( 'order_datasource' , $defaults, $params);
			// we just got the datasource. now we throw it into the data being passed to the view. 
			$content = $data_table['table_html'];
			$content .= $data_table['pagination_html'];
			$data['order_table'] = $content; 
			
			// adding the order totals that appear at the top of the screen
 			$data = array_merge( $data, $this->get_totals($where,$show_fields)); 
	
			// adding in any of the get / post variables back into the page in case we want to save the report or generate a CSV using the same parameters
			$data['hidden_inputs'] = NULL; 
			$data['report_title'] = "Report"; 
			
			if (element("report_title", $p_data))
			{
				$data['report_title'] = element("report_title", $p_data); 
			}
			
			foreach ($p_data as $key => $value)
			{
				if (!in_array($key,$this->remove_keys) && !empty($value))
				{
					if ($key == "search_fields_settings")
					{
						foreach ($value as $k => $v)
						{
							foreach ($v as $kk => $vv)
							{
								foreach ($vv as $kkl => $vvv)
								{
									$data['hidden_inputs'] .= form_hidden($key."[".$k."][".$kk."][". $kkl."]", $vvv); 
								}
							}
						}
					}
					else
					{
						$data['hidden_inputs'] .= form_hidden($key, $value);
					}
				}
			}
			foreach ($_GET as $key => $value)
			{
				if (!in_array($key,$this->remove_keys) && !empty($value))
				{
					$data['hidden_inputs'] .= form_hidden($key, $value);
				}
			}
			
			// CUSTOMER SPECIFIC. If this is customer report, we'll add a custom header. 
			if (element("customer_email", $p_data))
			{
				$customer = $this->EE->order_management_model->get_customers(array(
						"field_id_".$this->EE->cartthrob->store->config('orders_customer_email')
							=> element("customer_email", $p_data)),
						$order_by= "author_id", 
						$sort="DESC", 
						$limit=1, 
						$offset=NULL); 
						
				$email_address =element("customer_email", $p_data);

				$first_name = NULL; 
				$last_name = NULL; 
				if (!empty($customer[0]["field_id_".$this->EE->cartthrob->store->config('orders_billing_first_name')]))
				{
					$first_name = $customer[0]["field_id_".$this->EE->cartthrob->store->config('orders_billing_first_name')]; 
				}
				$last_hame = NULL; 
				if (!empty($customer[0]["field_id_".$this->EE->cartthrob->store->config('orders_billing_last_name')]))
				{
					$last_name = $customer[0]["field_id_".$this->EE->cartthrob->store->config('orders_billing_last_name')]; 
				}
				$data['report_title'] = lang("cartthrob_order_manager_customer_report_title"). " ". $first_name ." ". $last_name; 
			}
			elseif (element("product_id", $p_data))
			{
 				$data['report_title'] = lang("cartthrob_order_manager_order_by_product_report_title"). ": (". element("product_id", $p_data).")"; 
			}
			
			// adding the filename dynamically based on the report title
			$data['hidden_inputs'] .= form_hidden("filename", $data['report_title'] );
			
			$data['export_csv'] = form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method='.__FUNCTION__.AMP.'return='.__FUNCTION__); 

  	 		return $this->EE->mbr_addon_builder->load_view(
						__FUNCTION__,
						array(
							'data'	=> $data,
						)
 					);
		}
	}
	public function get_totals($where, $totals_fields)
	{
		$this->EE->load->model('order_model');
		$this->EE->load->library('table');

		$data = array(); 

		// Start creating the table to output this stuff. 
		$this->EE->table->clear();
		$this->EE->table->set_template(array(
			'table_open' => '<table border="0" cellpadding="0" cellspacing="0" class="mainTable padTable">',
            'heading_cell_start'  => '<th colspan="2">',
		));
		$this->EE->table->set_heading(lang('cartthrob_order_manager_totals_header')); 
		
		$order_totals = $this->EE->order_model->order_totals($where); 
		
		// only going to display the selected fields for this report. 
 		foreach ($totals_fields as $value)
		{
			if (isset($order_totals[$value]) )
			{
			if ($value !="orders")
			{
				if (is_numeric($order_totals[$value]))
				{
					$show_fields[] = array(lang("cartthrob_order_manager_".$value), $this->EE->number->format($order_totals[$value])); 
				}
				else
				{
					$show_fields[] = array(lang("cartthrob_order_manager_".$value), $order_totals[$value]); 
				}
			}
		}
			else
			{
				$show_fields[] = array(lang("cartthrob_order_manager_".$value), lang("cartthrob_order_manager_not_available")); 
			}
		}
		$data['total_data'] = $order_totals; 
		$data['total_table'] = $this->EE->table->generate(	$show_fields);
		
		return $data; 
	}
	
	public function order_datasource($state, $params = array())
	{
		$offset = $state['offset'];
		if (isset($params['limit']))
		{
			$limit= $params['limit']; 
		}
		else
		{
			$limit=$this->limit; 
		}

		$sub_params = array("order_by" => array(), "sort" => array() );  
		if (!empty($state['sort']))
		{
			foreach ($state['sort'] as $key => $value)
			{
				$sub_params['order_by'][] = $key;
				$sub_params['sort'][] = $value; 
			}
		}
		
		$where= array(); 
		$fields = array(); 
		
		if (isset($params['where']))
		{
			$where= $params['where']; 
		}
 		if (isset($params['fields']))
		{
			$fields= $params['fields']; 
		}
		
		$rows = $this->order_data($where, $sub_params['order_by'] , $sub_params['sort'] , $limit, $offset, $fields, "REPORT"); 
		
		$this->EE->load->model('order_model');
		$order_totals = $this->EE->order_model->order_totals($where); 
  		return array(
		    'rows' => $rows['order_data'], 
		    'pagination' => array(
		        'per_page'   => $limit,
		        'total_rows' => $order_totals['orders'],
		    ),
		);
		
	}
	
	/**
	 * order_data function
	 *
	 * this function gets order data from the order model and formats it to our requirements for a standard output, or csv. 
	 * 
	 * @return array
	 * @author Newton
	 **/
	public function order_data($where, $order_by = "title", $sort="DESC", $limit=NULL, $offset=0, $fields = array(), $format = "REPORT")
	{
		$this->EE->load->model('order_management_model');
		$this->EE->load->model('cartthrob_field_model'); 

		$data = array(); 
 
 		///// GET THE ORDERS

		foreach ($where as $key =>$value)
		{
			if (!is_array($value) && $value == "IS NOT NULL")
			{
				unset($where[$key]); 
 				$this->EE->db->where($key." <> ''", NULL, FALSE); 
				$this->EE->db->where($key." IS NOT NULL", NULL, FALSE); 
			}
 		}

		$orders = $this->EE->order_management_model->get_orders($where, $order_by, $sort, $limit, $offset); 
 		if (! $orders)
		{
			$data['order_data'] = array(); 
			$data['order_headers'] = array();

			return $data;
		}
		///// GET THE FIELD TYPES FOR EACH FIELD. SOME OF THEM WE'RE GOING TO HAVE TO PARSE
		$field_types = array(); 
		
		foreach ($fields as $field_id => $label)
		{
			if ($field_id=="field_id_"){unset($fields[$field_id]); continue;}
			$field = $this->EE->cartthrob_field_model->get_field_by_id(str_replace("field_id_", "", $field_id)); 
			if ($field)
			{
				$field_types[$field_id]  = $field['field_type'];
			}
			else
			{
				$field_types[$field_id] = "text"; 
			}
		}
		
		// Default columns if none are supplied
		$default_item_columns = array_fill_keys(array(
			'row_id',
			'row_order',
			'order_id',
			'entry_id',
			'title',
			'quantity',
			'price',
			'price_plus_tax',
			'weight',
			'shipping',
			'no_tax',
			'no_shipping',
			'extra'), 
			""
		);
 		// Cleaning the orders

		$country_code_field = $this->EE->cartthrob_field_model->get_field_by_name('order_country_code'); 
		$state_field = $this->EE->cartthrob_field_model->get_field_by_name('order_billing_state'); 
		$ip_address_field = $this->EE->cartthrob_field_model->get_field_by_name('order_ip_address');
		$this->EE->load->helper("array"); 
		$this->EE->load->library("localize"); 
		$this->EE->load->model('order_model');
		
 		foreach ($orders as $key => &$ord)
		{
			$order_id = $ord['entry_id']; 
 			$title = $ord['title']; 
			$ord1= $ord; 
 			$ord = array_intersect_key($ord, $field_types ); 
			foreach ($field_types as $key => $type)
			{
				$href= BASE.AMP. 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=edit'.AMP.'id='.$order_id; 
				if ( $format == "REPORT")
				{
					// create a link to the entry
					if ($key == "entry_id")
					{
			 			$ord['entry_id'] = "<a href='".$href."'>".$order_id." &raquo;</a>";
					}
					// create a link to the entry
					if ($key == "title")
					{
			 			$ord['title'] = "<a href='".$href."'>".$title." &raquo;</a>";
					}
				}
				
				// format the entry date
				if ($key == "entry_date")
				{
					$ord['entry_date'] =  $this->EE->localize->format_date("%m-%d-%Y %h:%i %a", $ord['entry_date']);
				}
				
				
				if (isset($country_code_field['field_id']) && isset($state_field['field_id']) && isset($ip_address_field['field_id']))
				{
					$this->multi_location(element("field_id_".$country_code_field['field_id'], $ord1), element("field_id_".$state_field['field_id'], $ord1), element("field_id_".$ip_address_field['field_id'], $ord1)); 
				}
				
				if (!is_null($this->currency_code))
				{
					$this->EE->number->set_prefix($this->currency_code); 
				}
				if (!is_null($this->prefix))
				{
					$this->EE->number->set_prefix($this->prefix); 
				}

				if (!is_null ($this->dec_point))
				{
					$this->EE->number->set_dec_point( $this->dec_point ); 
				}
				
				// do some magic on the specialty fields
				switch($type)
				{
					case "cartthrob_order_items": 
						
						$order_items = $this->EE->order_model->get_order_items(array($order_id)); 
 						$items = NULL; 

						if (is_array($order_items))
						{
							foreach ($order_items as $item)
							{
								if ( $format == "REPORT")
								{
									// create a link to the item
									$items .= "<a href='".BASE.AMP."D=cp".AMP."C=content_publish".AMP."M=entry_form".AMP."entry_id=".$item['entry_id']."' >"; 
									$items .= $item['title']; 
									$items .= "(".$item['entry_id'].")"; 
									$items .= "</a>"; 
									$items .= "<br>"; 
									$items .= $item['quantity'] ." x ". $this->EE->number->format($item['price']) ."(".$this->EE->number->format($item['price_plus_tax']).")"; 
									$items .= "<br>"; 
									$item_options = array_diff_key($item, $default_item_columns); 
	 								if ($item_options)
									{
										foreach ($item_options as $option_key => $option)
										{
											if(is_array($option))
											{
												foreach($option as $opt)
												{
													if($opt['title'])
													{
														$items .= $option_key .": ". $opt['title']."<br>";
													}
												}
											}
											else
											{
											$items .= $option_key .": ". $option."<br>"; 
										}
									}
									}
	  								$items .= "<br><br>";									
								}
								else
								{
									$items .= $item['entry_id'].":".$item['title'].":".$item['quantity'].":".$item['price']."|"; 
								}

							}
						}
 						$ord[$key] = $items; 
					
 					break;
					case "cartthrob_price_simple":
						if ($format == "REPORT")
						{
							$ord[$key]  = $this->EE->number->format($ord[$key]); 
						}
					break;
 					
				}
			}
  		}
	
		$single_order = array_keys($orders[0]); 

 		$entry_keys = array(
			'entry_id' 	=> 'entry_id',
			'title'  	=> 'title',
			'status'  	=> 'status',
			'year'    	=> 'year',
			'month'    	=> 'month',
			'day'      	=> 'day',
			'entry_date'	=> 'entry_date'
		); 
		// because we're getting back all of this stuff in a specific format... we can't just do normal sorting. 
		$available_keys = array_intersect(array_values($field_types), $entry_keys); 
 
		$headers = array_merge(array_flip($single_order), $available_keys); 
		$headers = array_merge($headers, $fields);
		
		$data['order_data'] = $orders; 
		$data['order_headers'] = $headers;
 		
		return $data; 
	}


 	public function save_report($settings, $title)
	{
		$this->EE->load->model('generic_model');
		$reports = new Generic_model("cartthrob_order_manager_reports");
 		
		$report_data = array(
				'settings' => serialize($settings),
				'report_title' => $title, 
				'type'		=> 'order'
			); 
		$key = $reports->create($report_data);
		
		return $key; 
	}
	public function export_csv(array $data, $headers = array(), $filename = NULL, $return_data = FALSE)
	{
		if (!$filename)
		{
			$filename = "data"; 
		}
		$content = NULL; 
		if (is_array($data) && !empty($data))
		{
			$rows = array(); 

			$headers_count = count($headers); 
			// data = rows of data key_count == row_count. 
			$columns_count = count(array_keys($data[0])); 
 			foreach ($data as $key => $value)
			{
				if (empty($value))
				{
					continue; 
				}
				$rows[] = $this->format_csv($value); 
			}
			if ($headers_count && $columns_count == $headers_count)
			{
				$content .= $this->format_csv($headers); 
			}
			$content .= implode('', $rows); 
		}
		
		if ($content)
		{
			if ($return_data)
			{
				return $content; 
			}
			else
			{
				$this->EE->load->helper('download'); 
				force_download($filename.".xls", $content); 
			}
			
		}
	}
	
	function format_csv($row)
	{
 	    static $fp = false;
	    if ($fp === false)
	    {
			// see http://php.net/manual/en/wrappers.php.php
	        $fp = fopen('php://temp', 'r+');  
 	    }
	    else
	    {
	        rewind($fp);
	    }
		// don't love this
		foreach ($row as $key => $value)
		{
			$row[$key] = str_replace(array("\r", "\n"), " ", $value); 
		}
		// ascii 9 is tab, and ascii 0 is NULL
		// http://www.asciitable.com/
	    if (fputcsv($fp, $row, chr(9), chr(0)) === false)
	    {
	        return false;
	    }

	    rewind($fp);
	    $csv = fgets($fp);
 
 	    return $csv;
	}
	
 	public function view()
	{
		/*
		// not using this method, because of the quick view. The quickview would need to be a different type of popup. 
 		$where = array(); 
 
		$fields = array(
			'title',
			'entry_date',
			
			'orders_billing_first_name',
			'orders_billing_last_name' ,
			'orders_total',
			'orders_subtotal',
			'orders_tax',
			'orders_shipping',
			'orders_shipping_option',
			'status',
			'entry_id',
			
			'orders_billing_company' ,
			'orders_billing_address' ,
			'orders_billing_address2',
			'orders_billing_city',
			'orders_billing_state' ,
			'orders_billing_zip' ,
			'orders_country_code',

			'orders_shipping_first_name' ,
			'orders_shipping_last_name',
			'orders_shipping_company',
			'orders_shipping_address',
			'orders_shipping_address2' ,
			'orders_shipping_city' ,
			'orders_shipping_state',
			'orders_shipping_zip',
			'orders_shipping_country_code' ,

			'orders_customer_email',
			'orders_customer_phone',
			'orders_language_field',
			
			'orders_full_billing_address',
			'orders_full_shipping_address' ,
			
		); 
		
		$order_fields = array(); 
		$headers = array(); 
		
		foreach ($fields as $field)
		{
			if ($this->EE->cartthrob->store->config($field))
			{
				$lang = lang($field); 
				$order_fields["field_id_".$this->EE->cartthrob->store->config($field) ] =($lang ? $lang : $field); 
				$headers[ "field_id_".$this->EE->cartthrob->store->config($field) ] =  array('header' => ($lang ? $lang : $field)) ; 
			}
		}
		
		asort($order_fields);

		$this->initialize();
		$this->EE->load->library("table"); 
		$this->EE->load->helper("form"); 
		$this->EE->load->model("order_management_model"); 
		$this->EE->load->model('cartthrob_field_model'); 
		
		$this->EE->table->clear();
		$this->EE->table->set_template(array(
			'table_open' => '<table border="0" cellpadding="0" cellspacing="0" class="mainTable padTable">',
		));
		
		$this->EE->table->set_columns( $headers );

		$defaults = array(
		    'sort' => array("title" => 'desc'),
		);
 
	 	$data_table = $this->EE->table->datasource( 'order_datasource' , $defaults, array("where" => $where, "fields" => $order_fields));
		
		var_dump($data_table); 
 	*/
		
		
 		$data = array('orders'=> array());
		$this->initialize();
		
 		$offset = $this->EE->input->get_post('rownum');

		$this->EE->db->where("channel_id", $this->EE->cartthrob->store->config('orders_channel'));
		
		$total = $this->EE->db->select( 'entry_id')
			->from('channel_data')
			->count_all_results();
	
		$this->EE->load->library("pagination"); 
		$this->EE->pagination->initialize( $this->EE->mbr_addon_builder->pagination_config(__FUNCTION__, $total, $this->limit) );
		$pagination = $this->EE->pagination->create_links();
		
 		if ($offset)
		{
			$this->EE->db->offset($offset); 
		}
		$this->EE->db->where("channel_id", $this->EE->cartthrob->store->config('orders_channel'));
 		// @TODO need to pull only data from this site id
		$this->EE->db->limit($this->limit); 
 
		$query = $this->EE->db->select( 'entry_id')
			->from('channel_data')
			->order_by("entry_id", "desc")
			->get();

		$this->EE->load->model('order_model'); 
		foreach ($query->result_array() as $row)
		{
			$data["orders"][] = $this->EE->order_model->get_order($row['entry_id']); 
		}
  		if (empty($data['orders']))
		{
			$this->EE->session->set_flashdata($this->module_name.'_system_message', sprintf('%s', lang($this->module_name.'_none') ));
			$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=om_sales_dashboard');
  
		}
		$quick_view_js = "
			$('.show_quick_view, .hide_quick_view').click(function() {
				$(this).hide();
 				if ($(this).attr('class') == 'show_quick_view')
				{
					$(this).closest('tr').nextAll('.quick_view:first').show();
					$(this).next('.hide_quick_view').show(); 
				}
				else
				{
					$(this).closest('tr').nextAll('.quick_view:first').hide();
					$(this).prev('.show_quick_view').show(); 
				}
			});
		"; 
		$this->EE->javascript->output($quick_view_js);

 		if ($pagination === FALSE)
		{
			$this->EE->session->set_flashdata($this->module_name.'_system_error', sprintf('%s', lang($this->module_name.'_none') ));
			// @TODO put in a redirect to somewhere
		}
		
 		return $this->EE->mbr_addon_builder->load_view(
					__FUNCTION__,
					array(
						__FUNCTION__ => $data, 
						'pagination' => $pagination,
						'form_edit'	=> form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=update_order'.AMP.'return='.__FUNCTION__),
						'delete_href'	=> BASE.AMP. 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=delete'.AMP.'id=',
						'entry_href'	=> BASE.AMP. 'C=content_publish'.AMP.'M=entry_form' .AMP.'channel_id='.$this->EE->cartthrob->store->config('orders_channel') .AMP.'entry_id=',
						'edit_href'	=> BASE.AMP. 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=edit'.AMP.'id=',
					)
				);
				
 
	}
	public function update_order()
	{
		$this->EE->load->model('order_model');
		
		$order_ids = $this->EE->input->post('id'); 
		$redirect_id=NULL; 
		// some servers are emptying the _GET after emailing so capture the method at the beginning for the return
		$method = $this->EE->input->get('return', TRUE);
 		if (is_array($order_ids))
		{
			foreach ($order_ids as $key => $id)
			{
				$order_update = array(); 
				foreach ($_POST as $post_key => $post_value)
				{
					$post_value = $this->EE->input->post($post_key); 
					// warning: this will not find any "name" key because that's one of the ignored keys
					if (!in_array($post_key,$this->remove_keys))
					{
						if (is_array($post_value))
						{
							if (array_key_exists($id, $post_value))
							{
								$order_update[$post_key] = $post_value[$id]; 
							}
						}
						else
						{
							$order_update[$post_key] = $post_value; 
						}
					}
					
					if ($post_key == "toggle_status" && !empty($post_value))
					{
						$order_update["status"] = (string) $post_value; 
						
					}
					
					if (array_key_exists("status", $order_update) && empty($order_update['status']))
					{
						unset($order_update['status']);
					}
				}
				// let's get the old status to see if we need to send status update emails.
				$this->EE->load->model('cartthrob_entries_model');
				$current_entry = $this->EE->cartthrob_entries_model->entry($id);
				$current_status = $current_entry['status'];
				if(isset($order_update['status']) && ($order_update['status'] !== $current_status))
				{
					// the status has changed. Let's send the email event
					$current_entry['previous_status'] = $current_status;
					$current_entry['status'] = $order_update['status'];
					$this->notifications("status_change", $current_entry); 
				}
				$this->EE->order_model->update_order($id, $order_update);
			}
			$this->EE->session->set_flashdata($this->module_name.'_system_message', sprintf('%s', lang($this->module_name.'_orders_updated')));
	 		
		}
		elseif ($order_ids)
		{
			$order_update = array(); 
			foreach ( $_POST as $post_key => $post_value)
			{
				// warning: this will not find any "name" key because that's one of the ignored keys
				if (!in_array($post_key, $this->remove_keys))
				{
					$order_update[$post_key] = $post_value; 
				}
			}
			$redirect_id = AMP.'id=' .$order_ids; 
			$this->EE->order_model->update_order($id, $order_update);
			$this->EE->session->set_flashdata($this->module_name.'_system_message', sprintf('%s', lang($this->module_name.'_order_updated')));
	 		
		}

		$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method='.$method.$redirect_id);
		
	}
	
	public function print_invoice()
	{
		$this->EE->load->library('template_helper');
		$vars['entry_id'] = $this->EE->input->get_post("id"); 
		$template =  $this->EE->get_settings->get_setting($this->module_name, "invoice_template"); 
		echo $this->EE->template_helper->fetch_and_parse($template, $vars); 
 		// have to exit otherwise EE will do all of its auto-outputtign business
		exit; 
	}
	public function print_packing_slip()
	{
 		$this->EE->load->library('template_helper');
		$vars['entry_id'] = $this->EE->input->get_post("id"); 
		$template =  $this->EE->get_settings->get_setting($this->module_name, "packing_slip_template"); 
		echo $this->EE->template_helper->fetch_and_parse($template, $vars); 
 		exit;
	}
	
	public function print_custom_template()
	{
		if ($this->EE->get_settings->get_setting($this->module_name, "custom_templates"))
		{
			foreach ($this->EE->get_settings->get_setting($this->module_name, "custom_templates") as $key =>  $template)
			{
				if ($key == $this->EE->input->get_post('custom_template_id'))
				{
					$this->EE->load->library('template_helper');
					$vars['entry_id'] = $this->EE->input->get_post("id"); 
					$template =  $template['custom_template']; 
					echo $this->EE->template_helper->fetch_and_parse($template, $vars); 
					
				}

			}
 		}
 		// have to exit otherwise EE will do all of its auto-outputtign business
		exit; 
	}
	public function email_custom_template()
	{
		// _custom_email_sent
		// _email
		// some servers are emptying the _GET after emailing so capture the method at the beginning for the return
		$method = $this->EE->input->get('return', TRUE);
		if ($this->EE->get_settings->get_setting($this->module_name, "custom_templates"))
		{
			foreach ($this->EE->get_settings->get_setting($this->module_name, "custom_templates") as $key =>  $template)
			{
				if ($key == $this->EE->input->get_post('custom_template_id'))
				{
					$this->EE->load->library('cartthrob_emails'); 
					
					// using email address and such from completed email!!!!!!!!!
					$email_content = $this->EE->cartthrob_emails->get_email_for_event($selected_event = "completed"); 
					
					$this->EE->load->library('template_helper');
					$vars['entry_id'] = $this->EE->input->get_post("id"); 
					$order_id = $this->EE->input->post('id'); 
					$redirect_id=NULL; 

					$variables = $this->reinstate_order($order_id); 
					
					$email_content['variables'] = $variables; 
 					$email_content['message_template'] = $template['custom_template'];
  					
					if ($this->EE->input->post('email_address'))
					{
						$email_content['to'] = $this->EE->input->post('email_address'); 
					}
					if ($this->EE->input->post('email_subject'))
					{
						$email_content['subject'] = $this->EE->input->post('email_subject'); 
					}
					
					$this->EE->cartthrob_emails->send_email($email_content);

					$redirect_id = AMP.'id=' .$order_id; 
			        
				}
			}
 		}
 		$this->EE->session->set_flashdata($this->module_name.'_system_message', sprintf('%s', lang($this->module_name.'_custom_email_sent')));
		$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method='.$method.$redirect_id);
	}
	
	public function add_tracking_to_order()
	{
		$entry_id = $this->EE->input->post('id'); 
		$tracking_number = $this->EE->input->post('order_tracking_number'); 
		$note = $this->EE->input->post('order_shipping_note'); 
		$status = $this->EE->input->post('status'); 
		// some servers are emptying the _GET after emailing so capture the method at the beginning for the return
		$method = $this->EE->input->get('return', TRUE);
		if (is_array($entry_id))
		{
			foreach ($entry_id as $key => $value)
			{
				$variables = $this->reinstate_order($value); 
				
				$variables['entry_id'] = $value; 
				$variables['order_tracking_number'] = $tracking_number[$key]; 
				$variables['order_shipping_note'] = $note[$key];
				$variables['status'] = $status[$key]; 
 				
				$this->save_tracking($value, $variables); 
				
				$this->notifications(ucwords($this->module_name)."_tracking_added_to_order", $variables); 
				
			}
			$redirect_id=NULL; 
		}
		else
		{
			$variables = $this->reinstate_order($entry_id); 
			
			$variables["entry_id"]				= $entry_id;
			$variables['order_tracking_number']			= $tracking_number;
			$variables['order_shipping_note']			= $note;
			$variables['status']						= $status;

			$this->save_tracking($entry_id, $variables); 
			
 			$redirect_id = AMP.'id=' .$entry_id; 
		// when notifications are registered by third party, need to add module name (case sensitive) 
			$this->notifications(ucwords($this->module_name)."_tracking_added_to_order", $variables); 
		}
					
 		$this->EE->session->set_flashdata($this->module_name.'_system_message', sprintf('%s', lang($this->module_name.'_tracking_added')));
		$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method='.$method.$redirect_id);
		
	}
	public function save_tracking($order_id, $constants = array())
	{
		$this->EE->load->model("order_model"); 
		
		$order_update= array(
			'status'			=> $constants['status'],
			'order_tracking_number'	=> $constants['order_tracking_number'],
			'order_shipping_note'	=> $constants['order_shipping_note']
			); 
			
		$this->EE->order_model->update_order($order_id, $order_update);
		
		return TRUE; 
	}
	public function resend_email()
	{
		$order_id = $this->EE->input->post('id'); 
		$redirect_id=NULL; 
		// some servers are emptying the _GET after emailing so capture the method at the beginning for the return
		$method = $this->EE->input->get('return', TRUE);
		if (is_array($order_id))
		{
			/*
			// @TODO send email
			foreach ($order_id as $key => $value)
			{
				$constants['entry_id'] = $value; 
			}
			$redirect_id=NULL; 
			*/
		}
		else
		{
			$variables = $this->reinstate_order($order_id); 
			$this->notifications($selected_event="completed", $variables );
			
 			$redirect_id = AMP.'id=' .$order_id; 
		}
	
 		$this->EE->session->set_flashdata($this->module_name.'_system_message', sprintf('%s', lang($this->module_name.'_email_resent')));
		$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method='.$method.$redirect_id);
	}
	
	public function refund()
	{
		$order_id = $this->EE->input->post('id'); 

		
		if (!$order_id)
		{
			$this->EE->session->set_flashdata($this->module_name.'_system_error', sprintf('%s', lang($this->module_name.'_refund_failed')));
			$this->EE->functions->redirect(BASE.AMP. 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=view');
		}
		
		$this->EE->load->library(array('form_validation', 
					'encrypt', 
					'form_builder',
					'api/api_cartthrob_payment_gateways', 
					'api/api_cartthrob_tax_plugins',
					'cartthrob_payments'));
		$this->EE->load->model('purchased_items_model');
		$this->EE->load->model("order_model");
		
		$original_order = $this->EE->order_model->get_order($order_id); 
		
  		$gateway = isset($original_order['order_payment_gateway']) ? $original_order['order_payment_gateway'] : $this->EE->cartthrob->store->config('payment_gateway');

		// Load the payment processing plugin that's stored in the extension's settings.
		if ( ! $this->EE->cartthrob_payments->set_gateway($gateway)->gateway())
		{
			$this->EE->session->set_flashdata($this->module_name.'_system_error', sprintf('%s', lang($this->module_name.'_problem_loading_gateway')." " . lang($this->module_name.'_refund_failed')));
			$this->EE->functions->redirect(BASE.AMP. 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=edit'.AMP.'id='.$order_id);
		}
		if (!method_exists($this->EE->cartthrob_payments->gateway(), "refund"))
		{
			$this->EE->session->set_flashdata($this->module_name.'_system_error', sprintf('%s', lang($this->module_name.'_gateway_could_not_process_refund')." " . lang($this->module_name.'_refund_failed')));
			$this->EE->functions->redirect(BASE.AMP. 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=edit'.AMP.'id='.$order_id);
		}
		
		$partial_refund = FALSE;
		$refund = 0;
		if($this->EE->input->post('total'))
		{
			$refund = $this->EE->cartthrob->sanitize_number($this->EE->input->post('total'));
		} 
		
		if (!$this->EE->input->post('total') && ($this->EE->input->post('subtotal') || $this->EE->input->post('shipping') ||  $this->EE->input->post('tax')) )
		{
			$refund = $this->EE->cartthrob->sanitize_number($this->EE->input->post('subtotal')) + $this->EE->cartthrob->sanitize_number($this->EE->input->post('tax')) + $this->EE->cartthrob->sanitize_number($this->EE->input->post('shipping'));
			$partial_refund = TRUE; 
		}
		
		$auth = $this->EE->cartthrob_payments->refund($original_order['order_transaction_id'], $refund, $original_order['order_last_four']);
		
		if ($auth['authorized'])
		{
			// full refund
			if (! $partial_refund)
			{
 				foreach ($this->EE->order_model->get_order_items($order_id) as $item)
				{
					$item_options = array_diff_key($item, $this->default_columns); 
					$this->EE->load->model('product_model'); 
					$this->EE->product_model->increase_inventory($item['entry_id'], $item['quantity'], $item_options);
				}
				
				$this->EE->cartthrob_payments->set_status_refunded($auth, $order_id, $send_email = TRUE); 
				
				/*
				// don't need to update the order since we're setting the whole thing to refunded
				
				$order_update= array(
					'auth'		=> $auth,
					'order_total' => $original_order['order_total'] - $refund,
					'order_subtotal_plus_tax' =>  $original_order['order_subtotal_plus_tax'] - $this->EE->input->post('subtotal') - $this->EE->input->post('tax'), 
					'order_subtotal' => $original_order['order_subtotal'] - $this->EE->input->post('subtotal'), 
					'order_tax'	=> $original_order['order_tax'] - $this->EE->input->post('tax'),
					);
					$this->EE->order_model->update_order($order_id, $order_update);
					*/ 
				$order_update= array(
					'order_refund_id' => $auth['transaction_id']
					);
				$this->EE->order_model->update_order($order_id, $order_update);
			
			}
			// partial refund
			else
			{
 				$order_update= array(
					'auth'		=> $auth,
					'order_total' => $original_order['order_total'] - $refund,
					'order_subtotal_plus_tax' =>  $original_order['order_subtotal_plus_tax'] - $this->EE->input->post('subtotal') - $this->EE->input->post('tax'), 
					'order_subtotal' => $original_order['order_subtotal'] - $this->EE->input->post('subtotal'), 
					'order_tax'	=> $original_order['order_tax'] - $this->EE->input->post('tax'),
					);
				$this->EE->order_model->update_order($order_id, $order_update);
			}

			$this->EE->session->set_flashdata($this->module_name.'_system_message', sprintf('%s', lang($this->module_name.'_refund_succeeded')));
		}
		else
		{
			$this->EE->session->set_flashdata($this->module_name.'_system_error', sprintf('%s', lang($this->module_name.'_refund_failed'). $auth['error_message']));
		}
		
		$this->EE->functions->redirect(BASE.AMP. 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=edit'.AMP.'id='.$order_id);
	}
	public function authorize_and_charge()
	{
		$order_id = $this->EE->input->post('id'); 

		if (!$order_id)
		{
			// @TODO redirect 
		}
		$this->reinstate_order($order_id); 
		
		$this->EE->load->library(array('form_validation', 
					'encrypt', 
					'form_builder',
					'api/api_cartthrob_payment_gateways', 
					'api/api_cartthrob_tax_plugins',
					'cartthrob_payments'));
		$this->EE->load->model('purchased_items_model');
		$this->EE->load->model("order_model");
				
		$original_order = $this->EE->order_model->get_order($order_id); 
		
  		$gateway = isset($original_order['order_payment_gateway']) ? $original_order['order_payment_gateway'] : $this->EE->cartthrob->store->config('payment_gateway');

		// Load the payment processing plugin that's stored in the extension's settings.
		if ( ! $this->EE->api_cartthrob_payment_gateways->set_gateway($gateway)->gateway())
		{
			$this->EE->session->set_flashdata($this->module_name.'_system_error', sprintf('%s', lang($this->module_name.'_problem_loading_gateway')));
			$this->EE->functions->redirect(BASE.AMP. 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=edit'.AMP.'id='.$order_id);
		}
		if (!method_exists($this->EE->cartthrob_payments->gateway(), "auth_and_charge"))
		{
			$this->EE->session->set_flashdata($this->module_name.'_system_error', sprintf('%s', lang($this->module_name.'_gateway_could_not_process')));
			$this->EE->functions->redirect(BASE.AMP. 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=edit'.AMP.'id='.$order_id);
		}
		
		$amount = $this->EE->input->post('subtotal') + $this->EE->input->post('tax') + $this->EE->input->post('shipping');
		
		// @TODO create this method. need to make gateways that support auth + auth and charge. 
		$auth = $this->EE->cartthrob_payments->set_gateway($gateway)->auth_and_charge();
		
		if ($auth['authorized'])
		{
			$this->EE->session->set_flashdata($this->module_name.'_system_message', sprintf('%s', lang($this->module_name.'_auth_and_charge_succeeded')));

		}
		else
		{
			$this->EE->session->set_flashdata($this->module_name.'_system_error', sprintf('%s', lang($this->module_name.'_auth_and_charge_failed'). $auth['error_message']));
		}
 		$this->EE->functions->redirect(BASE.AMP. 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=edit'.AMP.'id='.$order_id);

	}
	// new method added for capturing prior authorized orders
	public function capture()
	{
		$order_id = $this->EE->input->post('id'); 
		
		if (!$order_id)
		{
			$this->EE->session->set_flashdata($this->module_name.'_system_error', sprintf('%s', lang($this->module_name.'_capture_failed')));
			$this->EE->functions->redirect(BASE.AMP. 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=view');
		}
		
		$this->EE->load->library(array('form_validation', 
					'encrypt', 
					'form_builder',
					'api/api_cartthrob_payment_gateways', 
					'api/api_cartthrob_tax_plugins',
					'cartthrob_payments'));
		$this->EE->load->model('purchased_items_model');
		$this->EE->load->model("order_model");
		$this->EE->load->model('cartthrob_field_model');
		$original_order = $this->EE->order_model->get_order($order_id); 
		
  		$gateway = isset($original_order['order_payment_gateway']) ? $original_order['order_payment_gateway'] : $this->EE->cartthrob->store->config('payment_gateway');

		// Load the payment processing plugin that's stored in the extension's settings.
		if ( ! $this->EE->cartthrob_payments->set_gateway($gateway)->gateway())
		{
			$this->EE->session->set_flashdata($this->module_name.'_system_error', sprintf('%s', lang($this->module_name.'_problem_loading_gateway')));
			$this->EE->functions->redirect(BASE.AMP. 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=edit'.AMP.'id='.$order_id);
		}
		if (!method_exists($this->EE->cartthrob_payments->gateway(), "capture"))
		{
			$this->EE->session->set_flashdata($this->module_name.'_system_error', sprintf('%s', lang($this->module_name.'_gateway_could_not_process')));
			$this->EE->functions->redirect(BASE.AMP. 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=edit'.AMP.'id='.$order_id);
		}
		
		$transaction_id = $original_order["field_id_". $this->EE->cartthrob->store->config('orders_transaction_id')];
		$amount = $this->EE->input->post('total');
		// need to unset the post variables before sending to the capture method
		unset($_POST);
		
		$auth = $this->EE->cartthrob_payments->gateway()->capture($transaction_id, $amount, $order_id);
		
		if ($auth['authorized'])
		{
			$this->EE->cartthrob_payments->relaunch_cart_snapshot($order_id);
			$this->EE->cartthrob_payments->checkout_complete_offsite($auth,$order_id,'stop_processing');
			$this->EE->session->set_flashdata($this->module_name.'_system_message', sprintf('%s', lang($this->module_name.'_capture_succeeded')));
			$order_items = $this->EE->order_model->get_order_items($order_id);
			foreach ($order_items as $item)
			{
				$item_options = array_diff_key($item, $this->default_columns); 
				$this->EE->load->model('product_model'); 
				$this->EE->product_model->reduce_inventory($item['entry_id'], $item['quantity'], $item_options);
			}
			
			//$this->EE->cartthrob_payments->set_status_authorized($auth, $order_id); 
			
		}
		else
		{
			$this->EE->session->set_flashdata($this->module_name.'_system_error', sprintf('%s', lang($this->module_name.'_capture_failed'). $auth['error_message']));
		}
 		$this->EE->functions->redirect(BASE.AMP. 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=edit'.AMP.'id='.$order_id);

	}
	// new method added for voiding orders that aren't captured
	public function void()
	{
		$order_id = $this->EE->input->post('id'); 

		if (!$order_id)
		{
			// @TODO redirect 
		}
		$this->reinstate_order($order_id); 
		
		$this->EE->load->library(array('form_validation', 
					'encrypt', 
					'form_builder',
					'api/api_cartthrob_payment_gateways', 
					'api/api_cartthrob_tax_plugins',
					'cartthrob_payments'));
		$this->EE->load->model('purchased_items_model');
		$this->EE->load->model("order_model");
		$this->EE->load->model('cartthrob_field_model');
		$original_order = $this->EE->order_model->get_order($order_id); 
		
  		$gateway = isset($original_order['order_payment_gateway']) ? $original_order['order_payment_gateway'] : $this->EE->cartthrob->store->config('payment_gateway');

		// Load the payment processing plugin that's stored in the extension's settings.
		if ( ! $this->EE->cartthrob_payments->set_gateway($gateway)->gateway())
		{
			$this->EE->session->set_flashdata($this->module_name.'_system_error', sprintf('%s', lang($this->module_name.'_problem_loading_gateway')));
			$this->EE->functions->redirect(BASE.AMP. 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=edit'.AMP.'id='.$order_id);
		}
		if (!method_exists($this->EE->cartthrob_payments->gateway(), "void"))
		{
			$this->EE->session->set_flashdata($this->module_name.'_system_error', sprintf('%s', lang($this->module_name.'_gateway_could_not_process')));
			$this->EE->functions->redirect(BASE.AMP. 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=edit'.AMP.'id='.$order_id);
		}
		
		$transaction_id = $original_order["field_id_". $this->EE->cartthrob->store->config('orders_transaction_id')];
		
		$auth = $this->EE->cartthrob_payments->gateway()->void($transaction_id);
		
		if ($auth['authorized'])
		{
			$this->EE->session->set_flashdata($this->module_name.'_system_message', sprintf('%s', lang($this->module_name.'_void_succeeded')));
			$this->EE->cartthrob_payments->set_status_voided($auth, $order_id); 
		}
		else
		{
			$this->EE->session->set_flashdata($this->module_name.'_system_error', sprintf('%s', lang($this->module_name.'_void_failed'). $auth['error_message']));
		}
 		$this->EE->functions->redirect(BASE.AMP. 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=edit'.AMP.'id='.$order_id);

	}
	public function reinstate_order($order_id)
	{
		$this->EE->load->helper("array"); 
		$this->EE->load->library(array(
				'api/api_cartthrob_payment_gateways',
				'api/api_cartthrob_tax_plugins',
				'cartthrob_payments',
				'form_builder',
				'encrypt', 
				));
		$this->EE->load->model('order_model');
				
		$this->EE->cartthrob->cart->set_calculation_caching(FALSE);

		$original_order = $this->EE->order_model->get_order($order_id); 
  		$gateway = ($this->EE->input->post('gateway')) ? xss_clean($this->EE->encrypt->decode($this->EE->input->post('gateway'))) : $this->EE->cartthrob->store->config('payment_gateway');
		
 		// @NOTE several variables like group id, create user, credit card number are all removed since we donn't need them
		$vars = array(
			'shipping'					=> element('entry_id', $original_order),
			'shipping_plus_tax'			=> element('order_shipping_plus_tax', $original_order),
			'tax'						=> element('order_tax', $original_order),
			'subtotal'					=> element('order_subtotal', $original_order),
			'subtotal_plus_tax'			=> element('order_subtotal_plus_tax', $original_order),
			'discount'					=> element('order_discount', $original_order),
			'total'						=> element('order_total', $original_order),
			'authorized' 				=> TRUE,
			'transaction_id'			=> element('order_transaction_id', $original_order),
			'credit_card_number'		=> NULL,
			'create_user'				=> FALSE,
			'group_id'					=> NULL, 
			'create_user_data'			=> NULL,
			'payment_gateway'			=> $gateway,
		);
		
		$order_data = array_merge($original_order, $vars); 
		//save order to session
		$order_data['entry_id'] = $order_data['order_id'] =  element('entry_id', $original_order);
		
		$set_order_data = $order_data; 
		$set_order_data['auth'] = array('authorized' => TRUE, "transaction_id" => element('order_transaction_id', $original_order) ); 
 		$this->EE->cartthrob->cart->set_order($set_order_data);
		$this->EE->cartthrob->cart->save(); 
		return $order_data; 
	}
	
	public function multi_location($country_code, $state = NULL, $ip_address = NULL)
	{
		/*
		static $loaded;
 		if ( ! is_null($loaded))
		{
			return $loaded;
		}
		*/ 
		$loaded = NULL; 
		$this->EE->load->library('get_settings');
		$settings = $this->EE->get_settings->settings('cartthrob_multi_location');

		if ($settings)
		{
			$european_union_array = array('AUT','BEL','BGR','CYP','CZE','DNK','EST','FIN','FRA','DEU','GRC','HUN','IRL','ITA','LVA','LTU','LUX','MLT','NLD','POL','PRT','ROU', 'ROM','SVK','SVN','ESP','SWE','GBR'); 

			$europe_array = array_merge(array(
					'HRV',
					'MKD',
					'ISL',
					'MNE',
					'SRB',
					'TUR',
					'ALB',
					'AND',
					'ARM',
					'AZE',
					'BLR',
					'BIH',
					'GEO',
					'LIE',
					'MDA',
					'MCO',
					'NOR',
					'RUS',
					'SMR', 
					'CHE',
					'UKR',
					'VAT'

				), $european_union_array); 

			$us_offshore = array('HI', 'AK');
			$this->EE->load->library('locales');
			$this->EE->load->library('number'); 
			
			$country_code =	$this->EE->locales->alpha3_country_code($country_code); 
			
			if ( $ip_address && $this->EE->db->table_exists('ip2nation'))
			{
				$this->EE->load->add_package_path(APPPATH.'modules/ip_to_nation/');
				$this->EE->load->model('ip_to_nation_data', 'ip_data');

				$country_code = $this->EE->ip_data->find( $ip_address ); 

				if ($country_code !== FALSE)
				{   
					if ( ! isset($this->EE->session->cache['ip_to_nation']['countries']))
					{
						if ( include(APPPATH.'config/countries.php'))
						{
							$this->EE->session->cache['ip_to_nation']['countries'] = $countries; // the countries.php file above contains the countries variable. 
						}
					}
					$country_code =  strtoupper($country_code); 
					// damn you UK and your alpha3 exceptions
					if ($country_code == "UK") $country_code = "GB"; 
				}
				$country_code = $this->EE->locales->alpha3_country_code($country_code); 
			}
			if (isset($settings['other']))
			{
				foreach($settings['other'] as $other)
				{
					if ( $country_code == $other['country'] 
						|| $other['country'] == 'global' 
						|| ($other['country'] == "non-continental_us" && in_array($state, $us_offshore)) 
						|| ($other['country'] == "europe" && in_array($country_code, $europe_array))
						|| ($other['country'] == "european_union" && in_array($country_code, $european_union_array)))
					{ 
						if (!empty($other['currency_code']))
						{
							// going to set the local var, and the config as well. must do this for order totals and other things that use the number lib. 
							// each time number lib gets used, seems the set_ methods don't last till the next use
							$this->currency_code = $other['currency_code']; 
							$this->EE->cartthrob->cart->set_config('number_format_defaults_currency_code', $other['currency_code']);
						}
						if (!empty($other['prefix']))
						{
							// going to set the local var, and the config as well. must do this for order totals and other things that use the number lib. 
							// each time number lib gets used, seems the set_ methods don't last till the next use
							$this->prefix = $other['prefix']; 
	 						$this->EE->cartthrob->cart->set_config('number_format_defaults_prefix', $other['prefix']);
						}
						if (!empty($other['dec_point']))
						{
							switch($other['dec_point'])
							{
								case "comma": 
									$dec_point = ","; 
									break;
								case "period": 
									$dec_point = "."; 
									break;
								default: 
									$dec_point = "."; 
							}
							// going to set the local var, and the config as well. must do this for order totals and other things that use the number lib. 
							// each time number lib gets used, seems the set_ methods don't last till the next use
							$this->dec_point = $dec_point; 
							$this->EE->cartthrob->cart->set_config('number_format_defaults_dec_point', $dec_point);
						}
	 					break ; 
					}
				}
			}
			
			return $loaded = $settings; 
		}
		$loaded= FALSE; 
		return $loaded; 
	}
	
 	public function edit()
	{
		$this->initialize();
		
		$this->EE->load->helper("array");
		$this->EE->load->model("order_model"); 
		$this->EE->load->model('cartthrob_field_model'); 

		$data  =  (array) $this->EE->order_model->get_order($this->EE->input->get_post('id')); 
		
		// based on (at least) the country code we're going to try to get the correct currency symbol for this order. only works if multi currency is installed
 		if (isset($data['order_country_code']))
		{
			$this->multi_location($data['order_country_code'], element('order_billing_state', $data), element('order_ip_address', $data)); 
		}
		#$data  =  (array) $this->EE->order_model->get_order($this->EE->input->get_post('id')); 
		
		if (! $this->EE->cartthrob_field_model->get_field_by_name('order_tracking_number'))
		{
			$data['order_tracking_number'] = NULL; 
		}
		if (! $this->EE->cartthrob_field_model->get_field_by_name('order_shipping_note'))
		{
			$data['order_shipping_note'] = NULL; 
		}
		if (! $this->EE->cartthrob_field_model->get_field_by_name('order_transaction_id'))
		{
			// setting the order_transaction_id based on the configured transaction id field
			$data['order_transaction_id'] = $data["field_id_". $this->EE->cartthrob->store->config('orders_transaction_id')]; 
		}
		$data['order_items'] = $this->EE->order_model->get_order_items( $this->EE->input->get_post('id') );
		
		$keys = array_merge($this->order_fields ,$this->total_fields); 
		
  		foreach ($keys as $k)
		{
			if (!is_null($this->currency_code))
			{
				$this->EE->number->set_prefix($this->currency_code); 
			}
			if (!is_null($this->prefix))
			{
				$this->EE->number->set_prefix($this->prefix); 
			}

			if (!is_null ($this->dec_point))
			{
				$this->EE->number->set_dec_point( $this->dec_point ); 
			}
			
			$data[$k] = NULL; 
 			if ($this->EE->cartthrob->store->config($k."_field") && array_key_exists("field_id_". $this->EE->cartthrob->store->config($k."_field"), $data))
			{
 				$data[$k] = $data["field_id_". $this->EE->cartthrob->store->config($k."_field")]; 
				
				if (in_array($k, $this->total_fields))
				{
 					$data[$k] = $this->EE->number->format($data[$k]); 
				}
			}
			elseif ($this->EE->cartthrob->store->config($k) &&  array_key_exists("field_id_". $this->EE->cartthrob->store->config($k), $data) )
			{
 				$data[$k] = $data["field_id_". $this->EE->cartthrob->store->config($k)]; 
				
				if (in_array($k, $this->total_fields))
				{
					$data[$k] = $this->EE->number->format($data[$k]); 
				}
			}
		}
		
  		$this->EE->load->model('order_management_model');
		if (! $this->EE->order_management_model->is_member($data['author_id']))
		{
			$data['author_id'] = NULL; 
		}
		
		// added because one customer's site could't handle $data['view'] = $data;  
		$view = $data; 
		$data['order_payment_gateway'] = $view['order_payment_gateway']  = isset($data['order_payment_gateway']) ? $data['order_payment_gateway'] : $this->EE->cartthrob->store->config('payment_gateway');
		$data['view'] = $view; 
  		$data['form_edit'] = form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=form_update'.AMP.'return=edit');
 		$data['form_delete'] = form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=delete_order'.AMP.'return=view');
 		$data['resend_email'] = form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=resend_email'.AMP.'return=edit');
		$data['href_entry'] = BASE.AMP. 'C=content_publish'.AMP.'M=entry_form' .AMP.'entry_id=';
		$data['href_member'] = BASE.AMP.'C=myaccount'.AMP.'M=edit_profile'. AMP.'D=cp'.AMP.'id='; 
		
		$data['href_invoice'] = NULL; 
		$data['href_packing_slip'] = NULL; 
		$data['custom_templates'] = array(); 
		if ($this->EE->get_settings->get_setting($this->module_name, "invoice_template"))
		{
		$data['href_invoice'] =  BASE.
										AMP.'C=addons_modules'.
										AMP.'M=show_module_cp'.
										AMP.'module='.$this->module_name.
										AMP.'id='.$this->EE->input->get_post('id').
										AMP.'method=print_invoice';

		}
		if ($this->EE->get_settings->get_setting($this->module_name, "packing_slip_template"))
		{
		$data['href_packing_slip']= BASE.
										AMP.'C=addons_modules'.
										AMP.'M=show_module_cp'.
										AMP.'module='.$this->module_name.
										AMP.'id='.$this->EE->input->get_post('id').
										AMP.'method=print_packing_slip'; 
			
		}
		if ($this->EE->get_settings->get_setting($this->module_name, "custom_templates"))
		{
			foreach ($this->EE->get_settings->get_setting($this->module_name, "custom_templates") as $key =>  $template)
			{
				$data['custom_templates'][$key] = array(
					'link'							=> BASE.
													AMP.'C=addons_modules'.
													AMP.'M=show_module_cp'.
													AMP.'module='.$this->module_name.
													AMP.'id='.$this->EE->input->get_post('id').
													AMP. 'custom_template_id='. $key.
													AMP.'method=print_custom_template', 
					'name'							=> $template['custom_template_name'],
					'form'							=> 	form_open('C=addons_modules'.
															AMP.'M=show_module_cp'.
															AMP.'module='.$this->module_name.
															AMP. 'custom_template_id='. $key.
															AMP.'method=email_custom_template'.
															AMP.'return=edit')
					
				); 
			}
 		}
		
 		// adding in the capture functionality
		$data['captured'] = NULL;
		$data['voided'] = NULL;
		$data['form_capture'] = NULL;
		$data['form_void'] = NULL;
 
		$data['refunded'] = NULL; 																	
		$data['form_refund'] = NULL; 
  		
		if (!empty($data['order_payment_gateway']))
		{
			$this->EE->load->library('cartthrob_payments');
			if ( $this->EE->cartthrob_payments->set_gateway($data['order_payment_gateway'])->gateway())
			{
				if ($this->EE->cartthrob_payments->get_order_status($this->EE->input->get_post('id'))!="refunded")
				{
					if (method_exists($this->EE->cartthrob_payments->gateway(), "refund"))
					{
						$data['form_refund'] = form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=refund'.AMP.'return=edit');
					}
			}
				else
				{
					$data['refunded'] = lang('refunded'); 
				}
				
				// adding in the capture button
				if ($this->EE->cartthrob_payments->get_order_status($this->EE->input->get_post('id'))=="processing")
				{
					if (method_exists($this->EE->cartthrob_payments->gateway(), "capture"))
					{
						$data['form_capture'] = form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=capture'.AMP.'return=edit');
					}
			}
				else
				{
					$data['captured'] = lang('captured'); 
				}
				
				// adding in the void button
				if ($this->EE->cartthrob_payments->get_order_status($this->EE->input->get_post('id'))=="processing")
				{
					if (method_exists($this->EE->cartthrob_payments->gateway(), "void"))
					{
						$data['form_void'] = form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=void'.AMP.'return=edit');
					}
			}
				else
				{
					$data['captured'] = lang('captured'); 
				}
			}
		}
 		$data['add_tracking'] = form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=add_tracking_to_order'.AMP.'return=edit');
		
		$data['entry_href'] = BASE.AMP. 'C=content_publish'.AMP.'M=entry_form' .AMP.'channel_id='.$this->EE->cartthrob->store->config('orders_channel') .AMP.'entry_id='; 
		
		return $this->EE->mbr_addon_builder->load_view(__FUNCTION__, $data);
	}
	public function delete()
	{
		$this->initialize();
		
		$this->EE->load->model("order_model"); 

		$data = $this->EE->order_model->get_order($this->EE->input->get_post('id')); 
		$data['view'] = $data; 
		$data['form_edit'] = form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=form_update'.AMP.'return=view'); 
		
		return $this->EE->mbr_addon_builder->load_view(__FUNCTION__, $data);
	}
	public function index()
	{
		$this->initialize();
		if (isset($this->params['nav']['view']))
		{
			$method = "view"; 
		}
		else
		{
 			$method = "add"; 
		}
		$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method='.$method);
	}
 	
	////////////// NOTIFICATIONS /////////////
	private function notifications($selected_event, $variables = array())
	{
		$this->EE->load->library('cartthrob_emails');
		if($selected_event == "status_change")
		{
			$emails = $this->EE->cartthrob_emails->get_email_for_event($selected_event, $variables['previous_status'], $variables['status']); 
		}
		else
		{
			$emails = $this->EE->cartthrob_emails->get_email_for_event($selected_event); 
		}
		
         if (!empty($emails))
        {
            foreach ($emails as $email_content)
            {
				$this->EE->load->helper('array'); 

                $email_content['variables'] = $variables; 
				
				// this is for the completed event only. 
				if ($this->EE->input->post('email_address') && $selected_event == "completed")
				{
					if (strpos(element("to", $email_content), "{customer_email}") !== FALSE)
					{
						$email_content['to'] = $this->EE->input->post('email_address'); 
					}
					else
					{
 						// we don't want to send any template not directed to {customer_email}
						// otherwise we might send emails to customers that should be only for vendors or fulfillment. 
						continue; 
					}
				}
				elseif ($this->EE->input->post('email_address'))
				{
					$email_content['to'] = $this->EE->input->post('email_address'); 
				}
				elseif (strpos(element("to", $email_content), "{customer_email}") !== FALSE)
				{
					$email_content['to'] = element("order_customer_email", $variables); 
				}
				
				if (strpos(element("from_name", $email_content), "{customer_name}") !== FALSE)
				{
					$email_content['from_name'] = element("order_customer_full_name", $variables); 
				}
				
				if (strpos(element("from_reply_to", $email_content), "{customer_email}") !== FALSE)
				{
					$email_content['from_reply_to'] = element("order_customer_email", $variables); 
				}

				if (strpos(element("from_reply_to_name", $email_content), "{customer_name}") !== FALSE)
				{
					$email_content['from_reply_to_name'] = element("order_customer_full_name", $variables); 
				}
				
				
				if ($this->EE->input->post('email_subject'))
				{
					$email_content['subject'] = $this->EE->input->post('email_subject'); 
				}
				
                $this->EE->cartthrob_emails->send_email($email_content); 
            }
        }
 	}
 
}
