<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cartthrob_order_manager_upd
{
	public $module_name;
	private $mod_actions = array(
	#	'tracker_action',
	);
	private $mcp_actions = array(
		'refund',
		'add_tracking_to_order',
		'delete_order',
		'update_order',
		'resend_email',
		'create_new_report',
		'run_report',
                'remove_report',
	);
	private $fieldtypes = array(
	);
	private $hooks = array(
			array('cp_menu_array', 'cp_menu_array'),
			array('cartthrob_addon_register')
		#		array('cartthrob_update_cart_end'),
		#		array('template_fetch_template'),
  	);
	public $version;
	public $current;
	public $notification_events =  array( 
		#"order_updated",
		#"order_completed",
		#"order_refunded",
		"tracking_added_to_order",
	 );
	
	private $tables = array(
   		'cartthrob_order_manager_settings' => array(
			'site_id' => array(
				'type' => 'int',
				'constraint' => 4,
				'default' => '1',
			),
			'`key`' => array(
				'type' => 'varchar',
				'constraint' => 255,
			),
			'value' => array(
				'type' => 'text',
				'null' => TRUE,
			),
			'serialized' => array(
				'type' => 'int',
				'constraint' => 1,
				'null' => TRUE,
			),
		),
		'cartthrob_order_manager_table' => array(
			'id' => array(
				'type' => 'int',
				'constraint' => 10,
				'unsigned' => TRUE,
				'auto_increment' => TRUE,
				'primary_key' => TRUE,
			),
			'member_id' => array(
				'type' => 'int',
				'constraint' => 10,
				'null' => TRUE,
			),
			'track_event' => array(
				'type' => 'int',
				'constraint' => 10,
				'null' => TRUE,
			),
		),
		'cartthrob_order_manager_reports' => array(
			'id' => array(
				'type' => 'int',
				'constraint' => 10,
				'unsigned' => TRUE,
				'auto_increment' => TRUE,
				'primary_key' => TRUE,
			),
			'report_title' => array(
				'type' => 'varchar',
				'default'	=> 'Order Report',
				'constraint' => 255,
			),
			'type' => array(
				'type' => 'varchar',
				'defalt'	=> 'order',
				'constraint' => 32,
			),
			'settings' => array(
				'type' => 'text',
				'null' => TRUE,
			),
		),	
		'cartthrob_products'	=> array(
			'id' => array(
				'type' => 'int',
				'constraint' => 10,
				'unsigned' => TRUE,
				'auto_increment' => TRUE,
				'primary_key' => TRUE,
			),
			'title' => array(
				'type' => 'varchar',
				'constraint' => 255,
			),
			'url_title' => array(
				'type' => 'varchar',
				'constraint' => 65,
			),
			'status' => array(
				'type' => 'varchar',
				'default'	=> 'open',
				'constraint' => 20,
			),
 			'description' => array(
				'type' => 'text',
				'null' => TRUE,
			),
			 'sku' => array(
				'type' => 'varchar',
				'constraint' => 65,
			),
			 'featured' => array(
				'type' => 'tinyint',
				'default'=> 0,
			), 
			 'shipping' => array(
				'type' => 'decimal',
				'constraint' => '10,4',
			),
			 'shippable' => array(
				'type' => 'tinyint',
				'default'=> 0,
			),
			 'weight' => array(
				'type' => 'decimal',
				'constraint' => '10,4',
			),
			 'tax' => array(
				'type' => 'decimal',
				'constraint' => '10,4',
			),
			 'taxable' => array(
				'type' => 'tinyint',
				'default'=> 0,
			),
			 'taxable' => array(
				'type' => 'decimal',
				'constraint' => '10,4',
			),
			 'price' => array(
				'type' => 'decimal',
				'constraint' => '10,4',
			),
 			 'store_cost' => array(
				'type' => 'decimal',
				'constraint' => '10,4',
			),
			 'sale_price' => array(
				'type' => 'decimal',
				'constraint' => '10,4',
			),
			 'sale_start' => array(
				'type' => 'int',
				'constraint' => 32,
			),
			 'sale_end' => array(
				'type' => 'int',
				'constraint' => 32,
			),
			'images' => array(
				'type' => 'text',
				'null' => TRUE,
			),
			'item_options' => array(
				'type' => 'text',
				'null' => TRUE,
			),
			'option_groups' => array(
				'type' => 'text',
				'null' => TRUE,
			),
			'keywords' => array(
				'type' => 'text',
				'null' => TRUE,
			),
		),
	);
	
	public function __construct()
	{
		$this->EE =& get_instance();
		$this->module_name = strtolower(str_replace(array('_ext', '_mcp', '_upd'), "", __CLASS__));
		
		include PATH_THIRD.$this->module_name.'/config'.EXT;
		$this->version = $config['version'];

		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/'); 
		$this->EE->load->add_package_path(PATH_THIRD.$this->module_name.'/');
		
		$this->params = array(
			'module_name'	=> $this->module_name, 
			'current'       => $this->current ,
			'version'       => $this->version ,
			'hooks'         => $this->hooks ,
			'fieldtypes'    => $this->fieldtypes ,
			'mcp_actions'   => $this->mcp_actions ,
			'mod_actions'   => $this->mod_actions ,
			'tables'		=> $this->tables,
			'notification_events'=> $this->notification_events,
			); 
			
  		$this->EE->load->library('mbr_addon_builder');
		$this->EE->mbr_addon_builder->initialize($this->params);
		
	}
 	public function install()
	{
		return $this->EE->mbr_addon_builder->install($has_cp_backend = "y", $has_publish_fields = "n"); 
	}
	function update($current = '')
	{
		return $this->EE->mbr_addon_builder->update($current); 
	}
	public function uninstall()
	{
		return $this->EE->mbr_addon_builder->uninstall(); 
	}
}