<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @property CI_Controller $EE
 */
class Cartthrob_upd
{ 
	public $module_name; 
	public $version;
	public $current; 
	private $mod_actions = array(
		'delete_from_cart_action',
		'cart_action',
		'download_file_action',
		'add_to_cart_action',
		'update_cart_action',
		'add_coupon_action',
		'multi_add_to_cart_action',
		'update_live_rates_action',
		'delete_recurrent_billing_action',
		'update_recurrent_billing_action',
		'save_customer_info_action',
		'update_item_action',
		'checkout_action',
		'payment_return_action',
		'update_subscription_action',
		'change_gateway_fields_action',
	);
	
	private $mcp_actions = array(
		'save_price_modifier_presets_action',
		'garbage_collection',
		'email_test',
		'process_subscriptions',
		'crontabulous_get_pending_subscriptions',
		'crontabulous_process_subscription',
		'helpspot_create',
		'helpspot_update',
		'helpspot_ajax_fields',
		'configurator_ajax',
		
	);
	
	/**
	 * Tables
	 *
	 * List of custom tables to be used with table_model->update_tables() on install/update
	 *
	 * Notes about field attributes:
	 * -use an int, not a string, for constraint
	 * -use custom attributes, key, index and primary_key set to TRUE
	 * -don't set null => false unneccessarily
	 * -default values MUST be strings
	 *
	 * But really, use the console and run the table model table_to_array() method
	 *
	 * @var array 
	 */
	private $tables = array(
		'cartthrob_sessions' => array(
			'session_id' => array(
				'type' => 'varchar',
				'constraint' => 32,
				'primary_key' => TRUE,
			),
			'cart_id' => array(
				'type' => 'int',
				'constraint' => 10,
				'index' => TRUE,
			),
			'fingerprint' => array(
				'type' => 'varchar',
				'constraint' => 40,
				'default' => '',
				'index' => TRUE,
			),
			'expires' => array(
				'type' => 'int',
				'constraint' => 11,
				'default' => '0',
				'index' => TRUE,
			),
			'member_id' => array(
				'type' => 'int',
				'constraint' => 10,
				'index' => TRUE,
			),
			'sess_key' => array(
				'type' => 'varchar',
				'constraint' => 40,
				'default' => '',
			),
			'sess_expiration' => array(
				'type' => 'int',
				'constraint' => 11,
				'default' => 0,
			),
		),
		'cartthrob_settings' => array(
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
		'helpspot_support' => array(
			'access_key' => array(
				'type' => 'varchar',
				'constraint' => 255,
			),
		),
		'cartthrob_cart' => array(
			'id' => array(
				'type' => 'int',
				'constraint' => 10,
				'unsigned' => TRUE,
				'auto_increment' => TRUE,
				'primary_key' => TRUE,
			),
			'cart' => array(
				'type' => 'text',
				'null' => TRUE,
			),
			'timestamp' => array(
				'type' => 'int',
				'default' => '0',
			),
			'url' => array(
				'type' => 'text',
				'null' => TRUE,
			),
		), 
		'cartthrob_permissions' => array(
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
			'sub_id' => array(
				'type' => 'varchar',
				'constraint' => 100,
				'null' => TRUE,
			),
			'order_id' => array(
				'type' => 'int',
				'constraint' => 10,
				'null' => TRUE,
			),
			'item_id' => array(
				'type' => 'int',
				'constraint' => 10,
				'null' => TRUE,
			),
			'permission' => array(
				'type' => 'varchar',
				'constraint' => 100,
				'null' => TRUE,
 			),
		),
		'cartthrob_tax' => array(
			'id' => array(
				'type' => 'int',
				'constraint' => 10,
				'unsigned' => TRUE,
				'auto_increment' => TRUE,
				'primary_key' => TRUE,
			),
			'tax_name' => array(
				'type' => 'text',
				'null' => TRUE,
			),
			'percent' => array(
				'type' => 'varchar',
				'constraint' => 5,
				'null' => TRUE,
			),
			'shipping_is_taxable' => array(
				'type' => 'tinyint',
				'constraint' => 1,
				'default' => '0',
			),
			'special' => array(
				'type' => 'varchar',
				'constraint' => 100,
				'null' => TRUE,
			),
			/*
			'plugin' => array(
				'type' => 'tinyint',
				'constraint' => 3,
				'default' => '0',
			),
			'field' => array(
				'type' => 'varchar',
				'constraint' => 100,
				'null' => TRUE,
			),
			*/
			'state' => array(
				'type' => 'varchar',
				'constraint' => 100,
				'null' => TRUE,
			),
			'zip' => array(
				'type' => 'varchar',
				'constraint' => 10,
				'null' => TRUE,
			),
			'country' => array(
				'type' => 'varchar',
				'constraint' => 100,
				'null' => TRUE,
			),
		),
		'cartthrob_vault' => array(
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
			'order_id' => array(
				'type' => 'int',
				'constraint' => 10,
				'null' => TRUE,
			),
			'token' => array(
				'type' => 'varchar',
				'constraint' => 128,
				'null' => TRUE,
			),
			'gateway'	=> array(
				'type' => 'varchar',
				'constraint' => 32,
				'null' => TRUE,
			),
			'customer_id' => array(
				'type'	=> 'varchar',
				'constraint'	=> 100,
				'null'	=> TRUE,
			), // this is a value that may or may not be assigned by the merchant account provider.
			'last_four' => array(
				'type' => 'varchar',
				'constraint' => 4,
				'null' => TRUE,
			),
			'sub_id' => FALSE,
			'vault_id' => FALSE,
			'timestamp' => FALSE,
			'expires' => FALSE,
			'status' => FALSE,
			'description' => FALSE,
			'total_occurrences' => FALSE,
			'trial_occurrences' => FALSE,
			'total_intervals' => FALSE,
			'interval_units' => FALSE,
			'allow_modification' => FALSE,
			'price' => FALSE,
			'trial_price' => FALSE,
			'error_message' => FALSE,
		),
		'cartthrob_subscriptions' => array(
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
			'order_id' => array(
				'type' => 'int',
				'constraint' => 10,
				'null' => TRUE,
			),
			/**
			 * a serialized version of the subscription item, used to create an order entry when the sub is rebilled
			 */
			'serialized_item' => array(
				'type'	=> 'text',
				'null'	=> FALSE,
			),
			'vault_id' => array(
				'type' => 'int',
				'constraint' => 10,
				'null' => TRUE,
			),
			/**
			 * the created date
			 */
			'start_date' => array(
				'type' => 'int',
				'contraint' => 11,
				'default' => '0',
			),
			'modified' => array(
				'type' => 'int',
				'contraint' => 11,
				'default' => '0',
			),
			/**
			 * the date this sub was last rebilled, used for determining whether a sub needs rebilling
			 */
			'last_bill_date' => array(
				'type' => 'int',
				'constraint' => 11,
			),
			/**
			 * an explicit expiration date, in unix time
			 */
			'end_date' => array(
				'type' => 'int',
				'constraint' => 11,
				'default' => '0',
			),
			'status' => array(
				'type' => 'varchar',
				'constraint' => 10,
				'default' => 'closed',
			),
			'name' => array(
				'type' => 'varchar',
				'constraint' => 255,
				'null' => TRUE,
			),
			'description' => array(
				'type' => 'text',
				'null' => TRUE,
			),
			/**
			 * how many times to rebill
			 */
			'total_occurrences' => array(
				'type' => 'int',
				'constraint' => 5,
			),
			/**
			 * how many times this has been rebilled
			 */
			'used_total_occurrences' => array(
				'type' => 'int',
				'constraint' => 5,
			),
			/**
			 * how many times to rebill with trial price
			 */
			'trial_occurrences' => array(
				'type' => 'int',
				'constraint' => 5,
			),
			/**
			 * how many times this trial has been rebilled
			 */
			'used_trial_occurrences' => array(
				'type' => 'int',
				'constraint' => 5,
			),
			/**
			 * how many of each interval unit to wait between rebillings, ie. this would be "7" if interval units was "days" and rebilling every 7 days
			 */
			'interval_length' => array(
				'type' => 'int',
				'constraint' => 4,
			),
			/**
			 * days, weeks, or months
			 */
			'interval_units' => array(
				'type' => 'varchar',
				'constraint' => 32,
				'null' => TRUE,
			),
			'allow_modification' => array(
				'type' => 'tinyint',
				'constraint' => 1,
				'null' => TRUE,
				'default' => '1',
			),
			'price' => array(
				'type' => 'varchar',
				'constraint' => 100,
				'null' => TRUE,
			),
			'trial_price' => array(
				'type' => 'varchar',
				'constraint' => 100,
				'null' => TRUE,
			),
			'error_message' => array(
				'type' => 'varchar',
				'constraint' => 100,
				'null' => TRUE,
			),
			/**
			 * this is a value that may or may not be assigned by the merchant account provider
			 */
			'sub_id' => array(
				'type'	=> 'varchar',
				'constraint'	=> 100,
				'null'	=> TRUE,
			),
			'token' => array(
				'type'	=> 'varchar',
				'constraint'	=> 100,
				'null'	=> TRUE,
			),
			'plan_id' => array(
				'type'	=> 'varchar',
				'constraint'	=> 100,
				'null'	=> TRUE,
			),
			'rebill_attempts' => array(
				'type' => 'int',
				'constraint' => 5,
			),
			/**
			 * the date this sub will be rebilled again, used for reference
			 */
			'next_bill_date' => array(
				'type' => 'int',
				'constraint' => 11,
			),
		),
		// snapshot data
		'cartthrob_status' => array(
			'entry_id' => array(
				'type' => 'int',
				'constraint' => 10,  
				'primary_key'	=> TRUE,
			), 
			'session_id' => array(
				'type' => 'varchar',
				'constraint' => 32,
				'null' => TRUE,
				'index' => TRUE,
 			),
			'status' => array(
				'type' => 'varchar',
				'constraint' => 10,
				'default' => 'processing',
			),
			'inventory_processed' => array(
				'type' => 'int',
				'constraint' => 2,
				'default' => '0',
			),
			'discounts_processed' => array(
				'type' => 'int',
				'constraint' => 2,
				'default' => '0',
			),
			'error_message' => array(
				'type' => 'varchar',
				'constraint' => 255,
			),
			'transaction_id' => array(
				'type' => 'varchar',
				'constraint' => 255,
			),
			'cart' => array(
				'type' => 'text',
				'null' => TRUE,
			),
			'cart_id' => array(
				'type' => 'int',
				'constraint' => 10,
			),
		),
		'cartthrob_notification_events' => array(
			// the name of the registering application
			'application' => array(
				'type' => 'varchar',
				'constraint' => 255,
 			),
			// the event being added
			'notification_event' => array(
				'type' => 'varchar',
				'constraint' => 255,
			), 
		),
		'cartthrob_order_items' => array(
			'row_id' => array(
				'type' => 'int',
				'constraint' => 10,
				'auto_increment' => TRUE,
				'primary_key' => TRUE,
			),
			'row_order' => array(
				'type' => 'int',
				'constraint' => 10,
			),
			'order_id' => array(
				'type' => 'int',
				'constraint' => 10,
				'index' => TRUE,
			),
			'entry_id' => array(
				'type' => 'int',
				'constraint' => 10,
				'null' => TRUE,
				'index' => TRUE,
			),
			'title' => array(
				'type' => 'varchar',
				'constraint' => 255,
				'null' => TRUE,
			),
			'site_id' => array(
				'type' => 'int',
				'constraint' => 10,
 			),
			'quantity' => array(
				'type' => 'varchar',
				'constraint' => 10,
				'null' => TRUE,
			),
			'price' => array(
				'type' => 'varchar',
				'constraint' => 100,
				'null' => TRUE,
			),
			'price_plus_tax' => array(
				'type' => 'varchar',
				'constraint' => 100,
				'null' => TRUE,
			),
			'weight' => array(
				'type' => 'varchar',
				'constraint' => 100,
				'null' => TRUE,
			),
			'shipping' => array(
				'type' => 'varchar',
				'constraint' => 100,
				'null' => TRUE,
			),
			'no_tax' => array(
				'type' => 'tinyint',
				'constraint' => 1,
				'null' => TRUE,
				'default' => '0',
			),
			'no_shipping' => array(
				'type' => 'tinyint',
				'constraint' => 1,
				'null' => TRUE,
				'default' => '0',
			),
			'extra' => array(
				'type' => 'text',
				'null' => TRUE,
			),
			'entry_date' => array(
				'type' => 'int',
				'contraint' => 11,
				'default' => '0',
			),
		),
		'cartthrob_email_log' => array(
			'id' => array(
				'type' => 'int',
				'constraint' => 10,
				'unsigned' => TRUE,
				'auto_increment' => TRUE,
				'primary_key' => TRUE,
			),
			'from' => array(
				'type' => 'text',
				'null' => TRUE,
			),			
			'from_name' => array(
				'type' => 'text',
				'null' => TRUE,
			),
			'to' => array(
				'type' => 'text',
				'null' => TRUE,
			),
			'message_template' => array(
				'type' => 'text',
				'null' => TRUE,
			), 
			'subject' => array(
				'type' => 'text',
				'null' => TRUE,
			), 
			'email_event' => array(
				'type' => 'text',
				'null' => TRUE,
			), 
			'message' => array(
				'type' => 'text',
				'null' => TRUE,
			), 
		),
	);
	
	private $fieldtypes = array(
		'cartthrob_discount',
		'cartthrob_order_items',
		'cartthrob_price_modifiers',
		'cartthrob_price_quantity_thresholds',
		'cartthrob_price_simple',
		'cartthrob_package',
	);
	
	private $hooks = array(
		array('member_member_logout'),
		array('member_member_login', 'member_member_login_multi', NULL, 1),
		array('member_member_login', 'member_member_login_single', NULL, 1),
		array('member_member_login', 'cp_member_login', NULL, 1),
		array('cp_menu_array', 'cp_menu_array'),
		#array('entry_submission_ready', 'entry_submission_ready'),
		array('entry_submission_end', 'entry_submission_end'),
		array('publish_form_entry_data', 'publish_form_entry_data'),
		array('channel_form_submit_entry_start', 'channel_form_submit_entry_start'),
		array('channel_form_submit_entry_end', 'channel_form_submit_entry_end'),
	);
     
	public function __construct()
	{ 
		$this->EE =& get_instance();
		
		$this->module_name = strtolower(str_replace(array('_ext', '_mcp', '_upd'), "", __CLASS__));
		
		include_once PATH_THIRD.'cartthrob/config.php';
		
		$this->version = CARTTHROB_VERSION;
		
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		
		//disable hooks and fieldtypes installation since 2.4 does it automatically
		//NOT TRUE, they removed the automagic FT and EXT installation
		/*
		if (version_compare(APP_VER, '2.4', '>='))
		{
			$this->hooks = array();
			$this->fieldtypes = array();
		}
		*/
	}

	public function install()
	{
		//install module to exp_modules
		$data = array(
			'module_name' => 'Cartthrob' ,
			'module_version' => $this->version,
			'has_cp_backend' => 'y',
			'has_publish_fields' => 'n'
		);

		$this->EE->db->insert('modules', $data);
		
		$this->sync();
		
		//install the fieldtypes
		require_once APPPATH.'fieldtypes/EE_Fieldtype'.EXT;
		
		foreach ($this->fieldtypes as $fieldtype)
		{
			require_once PATH_THIRD.$fieldtype.'/ft.'.$fieldtype.EXT;
			
			$class = ucwords($fieldtype.'_ft');
			
			$ft = get_class_vars($class);
			
			$this->EE->db->insert('fieldtypes', array(
				'name' => $fieldtype,
				'version' => $ft['info']['version'],
				'settings' => base64_encode(serialize(array())),
				'has_global_settings' => method_exists($class, 'display_global_settings') ? 'y' : 'n'
			));
		}
		
		return TRUE;
	}
	
	public function update($current = '')
	{
		$this->current = $current;
		
		if ($this->current == $this->version)
		{
			return FALSE;
		}
		
		$this->legacy_updates();
		
		return $this->sync();
	}
	
	/**
	 * Installs hooks and actions that aren't already installed
	 * and updates the tables
	 * 
	 * @return void
	 */
	public function sync()
	{
		$existing_mod_actions = array();
		$existing_mcp_actions = array();
		$existing_hooks = array();
		
		$query = $this->EE->db->select('method')
				      ->where('class', 'Cartthrob_ext')
				      ->get('extensions');
		
		foreach ($query->result() as $row)
		{
			$existing_hooks[] = $row->method;
		}
		
		//install extension
		foreach ($this->hooks as $row)
		{
			if ( ! in_array($row[0], $existing_hooks))
			{
				$this->EE->db->insert(
					'extensions',
					array(
						'class' => 'Cartthrob_ext',
						'method' => $row[0],
						'hook' => ( ! isset($row[1])) ? $row[0] : $row[1],
						'settings' => ( ! isset($row[2])) ? '' : $row[2],
						'priority' => ( ! isset($row[3])) ? 10 : $row[3],
						'version' => $this->version,
						'enabled' => 'y',
					)
				);
			}
		}
		
		$this->EE->db->update('extensions', array('version' => $this->version), array('class' => 'Cartthrob_ext'));
		
		//check for CartThrob actions in the database
		//so we don't get duplicates
		$query = $this->EE->db->select('method, class')
				      ->where_in('class', array('Cartthrob', 'Cartthrob_mcp'))
				      ->get('actions');

		foreach ($query->result() as $row)
		{
			if ($row->class === 'Cartthrob')
			{
				$existing_mod_actions[] = $row->method;
			}
			else
			{
				$existing_mcp_actions[] = $row->method;
			}
		}
		
		$this->EE->load->model('table_model');
		
		$this->EE->table_model->update_tables($this->tables);

		//install the module actions from $this->mod_actions
		foreach ($this->mod_actions as $method)
		{
			if ( ! in_array($method, $existing_mod_actions))
			{
				$this->EE->db->insert('actions', array('class' => 'Cartthrob', 'method' => $method));
			}
		}
		
		//install the module actions from $this->mcp_actions
		foreach ($this->mcp_actions as $method)
		{
			if ( ! in_array($method, $existing_mcp_actions))
			{
				$this->EE->db->insert('actions', array('class' => 'Cartthrob_mcp', 'method' => $method));
			}
		}
		
		return TRUE;
	}
	
	public function legacy_updates()
	{
		$this->EE->load->dbforge();
		
		$sites = array();
		
		$query = $this->EE->db->select('site_id')
				      ->get('sites');
		
		foreach ($query->result() as $row)
		{
			$sites[] = $row->site_id;
		}
		
		$query->free_result();
		
		$settings = array();
		
		$query = $this->EE->db->get('cartthrob_settings');
		
		foreach ($query->result() as $row)
		{
			$settings[$row->site_id][$row->key] = $row->serialized ? @unserialize($row->value) : $row->value;
		}
		
		$query->free_result();
		
		include PATH_THIRD.'cartthrob/config/config.php';
		
		foreach ($sites as $site_id)
		{
			// added support for more detailed gateway code, don't want to screwup existing installs
			if ($this->older_than('2.2') || empty($settings[$site_id]['gateways_format']))
			{
				$config['cartthrob_default_settings']['gateways_format'] = "default";
			}
			
			$settings[$site_id] = isset($settings[$site_id])
						? array_merge($config['cartthrob_default_settings'], $settings[$site_id])
						: $config['cartthrob_default_settings'];
		}
		
		//remove the member_member_login hook
		//update sessions database
		if ($this->older_than('2.0271')) 
		{
 
			$this->EE->db->delete('extensions', array('method' => 'member_member_login'));
			
			if ($this->EE->db->table_exists('cartthrob_sessions'))
			{
				foreach (array('last_activity', 'ip_address', 'user_agent') as $column)
				{
					if ($this->EE->db->field_exists($column, 'cartthrob_sessions'))
					{
						$this->EE->dbforge->drop_column('cartthrob_sessions', $column);
					}
				}
				
				$fields = array(
					'sess_key' => array(
						'type' => 'varchar',
						'constraint' => 40,
						'default' => '',
					),
					'sess_expiration' => array(
						'type' => 'int',
						'constraint' => 11,
						'default' => 0,
					),
				);
				
				$this->EE->dbforge->add_column('cartthrob_sessions', $fields);
			}
		}
		
		if ($this->older_than('2.0318'))
		{
			$this->EE->dbforge->add_field($this->tables['cartthrob_order_items']);
			
			$this->EE->dbforge->add_key('row_id', TRUE);
			
			$this->EE->dbforge->create_table('cartthrob_order_items', TRUE);
			
			$fields = $this->EE->db->select('field_id, group_id')
					       ->where('field_type', 'cartthrob_order_items')
					       ->get('channel_fields')
					       ->result();
			
			$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
			
			$this->EE->load->helper('data_formatting');
			
			foreach ($fields as $field)
			{
				$entries = $this->EE->db->select('entry_id, field_id_'.$field->field_id)
						      ->join('channels', 'channels.channel_id = channel_data.channel_id')
						      ->where('field_group', $field->group_id)
						      ->where('field_id_'.$field->field_id.' !=', '')
						      ->get('channel_data')
						      ->result();
				
				foreach ($entries as $entry)
				{
					$data = _unserialize($entry->{'field_id_'.$field->field_id}, TRUE);
					
					foreach ($data as $row_id => $row)
					{
						$insert = array(
							'order_id' => $entry->entry_id,
							'row_order' => $row_id,
						);
						
						foreach (array('entry_id', 'title', 'quantity', 'price') as $key)
						{
							$insert[$key] = (isset($row[$key])) ? $row[$key] : '';
							unset($row[$key]);
						}
						
						$insert['extra'] = (count($row) > 0) ? base64_encode(serialize($row)) : '';
						
						$this->EE->db->insert('cartthrob_order_items', $insert);
					}
					
					$this->EE->db->update('channel_data', array('field_id_'.$field->field_id => 1), array('entry_id' => $entry->entry_id));
				}
			}
		}
		
		if ($this->older_than('2.0323'))
		{
			$field = ($this->EE->db->field_exists('order_id', 'cartthrob_order_items')) ? 'order_id' : 'parent_id';
			
			$parents = $this->EE->db->select($field)
					      ->distinct()
					      ->get('cartthrob_order_items')
					      ->result();
			
			$updated_channels = array();
			
			$order_items_fields = $this->EE->db->select('site_id, value')
							  ->where('`key`', 'orders_items_field')
							  ->get('cartthrob_settings')
							  ->result();
			
			foreach ($parents as $parent)
			{
				$site_id = $this->EE->db->select('site_id')
							   ->where('entry_id', $parent->{$field})
							   ->get('channel_titles')
							   ->row('site_id');
				
				foreach ($order_items_fields as $row)
				{
					if ($site_id == $row->site_id && $row->value)
					{
						$this->EE->db->update('channel_data', array('field_id_'.$row->value => 1), array('entry_id' => $parent->{$field}));
						
						break;
					}
				}
			}
		}
		
		if ($this->older_than('2.0325'))
		{
			if ($this->EE->db->field_exists('parent_id', 'cartthrob_order_items'))
			{
				$this->EE->dbforge->modify_column(
					'cartthrob_order_items',
					array(
						'parent_id' => array(
							'name' => 'order_id',
							'type' => 'int',
							'constraint' => 10,
						),
					)
				);
 			}
		}
		
		if ($this->older_than('2.0378'))
		{
			$this->EE->db->insert('extensions', array(
				'class' => 'Cartthrob_ext', 
				'method' => 'cp_menu_array',
				'hook' => 'cp_menu_array', 
				'settings' => '', 
				'priority' => 10, 
				'version' => $this->version,
				'enabled' => 'y',
			));
		}
		// adding status (specifically for use in PayPal and other non real-time payment systems. Will allow us to check existing status before sending notification)
		if ($this->older_than('2.0387')) 
		{
			$query = $this->EE->db->where('`key`', 'encrypted_sessions')->get('cartthrob_settings');
			
			foreach ($query->result_array() as $row)
			{
				unset($row['key']);
				
				$row['`key`'] = 'session_use_fingerprint';
				
				$this->EE->db->insert('cartthrob_settings', $row);
			}
			
 		}
		
		if ($this->older_than('2.0400'))
		{
			foreach (array('cp_member_login', 'member_member_login_single', 'member_member_login_multi') as $hook)
			{
				$this->EE->db->insert(
					'extensions',
					array(
						'class' => 'Cartthrob_ext',
						'method' => 'member_member_login',
						'hook' => $hook,
						'settings' => '',
						'priority' => 10,
						'version' => $this->version,
						'enabled' => 'y',
					)
				);
			}
		}
		
		if ($this->older_than('2.0413'))
		{
			$this->EE->db->update('extensions', array('method' => 'sessions_end', 'hook' => 'sessions_end'), array('class' => 'Cartthrob_ext', 'hook' => 'sessions_start'));
		}
		
		if ($this->older_than('2.0433'))
		{
			$this->EE->db->delete('cartthrob_settings', array('`key`' => 'use_session_start_hook'));
			$this->EE->db->delete('extensions', array('class' => 'Cartthrob_ext', 'method' => 'sessions_end'));
			$this->EE->db->delete('extensions', array('class' => 'Cartthrob_ext', 'method' => 'sessions_start'));
		}
		
		if ($this->older_than('2.0512'))
		{
			$this->EE->load->model('table_model');
			
			foreach ($this->tables as $table_name => $fields)
			{
				$this->EE->table_model->update_table($table_name, $fields);
				
				if ( ! $this->EE->db->table_exists($table_name))
				{
					continue;
				}
				
				$indexes = $this->EE->table_model->indexes($table_name);
				
				foreach ($fields as $field_name => $field)
				{
					if ( ! empty($field['index']) && ! isset($indexes[$field_name]))//don't create index if it already exists
					{
						$this->EE->table_model->create_index($table_name, $field_name, $field['index']);
					}
				}
			}
		}
		
		if ($this->older_than('2.0517'))
		{	
			foreach ($sites as $site_id)
			{
				$last_order_number = 0;
				
				if ($settings[$site_id]['orders_channel'])
				{
					$query = $this->EE->db->select('title')
								->from('channel_titles')
								->where('channel_id', $settings[$site_id]['orders_channel'])
								->where('site_id', $site_id)
								->like('title', $settings[$site_id]['orders_title_prefix'], 'after')
								->like('title', $settings[$site_id]['orders_title_suffix'], 'before')
								->order_by('entry_date', 'desc')
								->limit(1)
								->get();
					
					if ($query->num_rows())
					{
						$last_order_number = str_replace(array($settings[$site_id]['orders_title_prefix'], $settings[$site_id]['orders_title_suffix']), '', $query->row('title'));
					}
				}
				
				$this->EE->db->insert('cartthrob_settings', array(
					'`key`' => 'last_order_number',
					'value' => $last_order_number,
					'site_id' => $site_id,
					'serialized' => 0,
				));
			}
			
			$this->EE->load->helper('array');
			
			$updated_settings = array();
			
			foreach ($sites as $site_id)
			{
				$templates = array();
				
				$template_groups = array();
				
				$query = $this->EE->db->select('template_groups.group_id, group_name, template_name')
						      ->where('template_groups.site_id', $site_id)
						      ->join('template_groups', 'template_groups.group_id = templates.group_id')
						      ->order_by('is_site_default', 'desc')
						      ->get('templates');
				
				foreach ($query->result() as $row)
				{
					if ( ! array_key_exists($row->group_id, $template_groups))
					{
						$template_groups[$row->group_id] = $row->group_name;
					}
					
					$templates[] = $row->group_name.'/'.$row->template_name;
				}
				
				if ( ! $templates)
				{
					continue;
				}
				
				$group_id = $query->row('group_id');
				
				$group_name = $query->row('group_name');
				
				foreach ($template_groups as $template_group_id => $template_group_name)
				{
					if ($template_group_name === 'cart')
					{
						$group_id = $template_group_id;
						
						$group_name = 'cart';
						
						break;
					}
				}
				
				$emails = array(
					'cart/email_customer' => array(
						'enabled' => 'send_confirmation_email',
						'email_template' => 'email_order_confirmation',
						'email_subject' => 'email_order_confirmation_subject',
						'email_from_name' => 'email_order_confirmation_from_name',
						'email_from' => 'email_order_confirmation_from',
						'email_type' => 'email_order_confirmation_plaintext',
					),
					'cart/email_admin' => array(
						'enabled' => 'send_email',
						'email_template' => 'email_admin_notification',
						'email_subject' => 'email_admin_notification_subject',
						'email_from_name' => 'email_admin_notification_from_name',
						'email_from' => 'email_admin_notification_from',
						'email_type' => 'email_admin_notification_plaintext',
					),
					'cart/email_low_stock' => array(
						'enabled' => 'send_inventory_email',
						'email_template' => 'email_inventory_notification',
						'email_subject' => 'email_inventory_notification_subject',
						'email_from_name' => 'email_inventory_notification_from_name',
						'email_from' => 'email_inventory_notification_from',
						'email_type' => 'email_low_stock_notification_plaintext',
					),
				);
				
				$updated_settings[$site_id]['notifications'] = $settings[$site_id]['notifications'];
				
				$i = 0;
				
				foreach ($emails as $template_name => $email_settings_map)
				{
					$enabled = element($email_settings_map['enabled'], $settings[$site_id]);
					
					if ( ! $enabled)
					{
						unset($updated_settings[$site_id]['notifications'][$i]);
						
						continue;
					}
					
					foreach ($email_settings_map as $email_setting_name => $setting_name)
					{
						if ($email_setting_name !== 'email_template' && $email_setting_name !== 'enabled')
						{
							$updated_settings[$site_id]['notifications'][$i][$email_setting_name] = element($setting_name, $settings[$site_id]);
						}
					}
					
					//rename if already exists
					if (in_array($template_name, $templates))
					{
						$template_name .= '_custom';
					}
					
					$template_data = element($email_settings_map['email_template'], $settings[$site_id]);
					
					if ($template_data)
					{
						if (preg_match('/{embed=([\042\047])?(.*?)\\1}/', $settings[$site_id][$email_settings_map['email_template']], $match))
						{
							$template_name = $match[2];
						}
						else
						{
							$parts = explode('/', $template_name);
							
							//create the template
							$this->EE->db->insert('templates', array(
								'site_id' => $site_id,
								'group_id' => $group_id,
								'template_name' => $parts[1],
								'save_template_file' => 'n',
								'template_type' => 'webpage',
								'template_data' => $template_data,
								'template_notes' => '',
								'last_author_id' => '1',
								'cache' => 'n',
								'refresh' => '0',
								'no_auth_bounce' => '',
								'enable_http_auth' => 'n',
								'allow_php' => 'n',
								'php_parse_location' => 'o',
								'hits' => '0',
							));
							
							$template_name = $group_name.'/'.$parts[1];
						}
					}
					
					$updated_settings[$site_id]['notifications'][$i]['email_template'] = $template_name; 
					
					$i++;
				}
			}
			
			if ($updated_settings)
			{
				foreach ($updated_settings as $site_id => $settings)
				{
					foreach ($settings as $key => $value)
					{
						$data = array(
							'`key`' => $key,
							'value' => $value,
							'site_id' => $site_id,
							'serialized' => 0,
						);
						
						if (is_array($value))
						{
							$data['value'] = serialize($value);
							
							$data['serialized'] = 1;
						}
						
						$this->EE->db->where(array('`key`' => $key, 'site_id' => $site_id));
						
						if ($this->EE->db->count_all_results('cartthrob_settings') === 0)
						{
							$this->EE->db->insert('cartthrob_settings', $data);
						}
						else
						{
							$this->EE->db->update('cartthrob_settings', array(
								'value' => $data['value'],
								'serialized' => $data['serialized'],
							), array(
								'`key`' => $key,
								'site_id' => $site_id,
							));
						}
					}
				}
			}
		}
		if ($this->older_than('2.5'))
		{
			$query = $this->EE->db->select()
						->where('`key`', 'description')
					      ->get('cartthrob_settings');
		
 			if ($query->result() && $query->num_rows() > 0)
			{
				foreach ($sites as $site_id)
				{
					$this->EE->db->limit(1)->delete('cartthrob_settings', array('`key`' => 'description', 'site_id' => $site_id));
				}
			}
		}
		
		if ($this->older_than('2.502'))
		{
			if ($this->EE->db->table_exists('cartthrob_subscriptions'))
			{
				foreach (array('token') as $column)
				{
					if ($this->EE->db->field_exists($column, 'cartthrob_subscriptions'))
					{
						$this->EE->dbforge->drop_column('cartthrob_subscriptions', $column);
					}
				}
			}
		}
		//remove the safecracker_submit_entry_end & safecracker_submit_entry_start hook
		if ( ! $this->older_than("2.7", APP_VER))
		{
			$query = $this->EE->db->select()
						->where('class', 'Cartthrob_ext')
						  ->where('method', 'safecracker_submit_entry_start')
					      ->get('extensions');
			
 			if ($query->result() && $query->num_rows() > 0)
			{
				$this->EE->db->limit(1)->delete('extensions', array('class' => 'Cartthrob_ext', 'method' => 'safecracker_submit_entry_start'));
			}
			
			$query = $this->EE->db->select()
						->where('class', 'Cartthrob_ext')
						  ->where('method', 'safecracker_submit_entry_end')
					      ->get('extensions');
			
 			if ($query->result() && $query->num_rows() > 0)
			{
				$this->EE->db->limit(1)->delete('extensions', array('class' => 'Cartthrob_ext', 'method' => 'safecracker_submit_entry_end'));
			}		
		}
		return TRUE;
	}
	
	public function uninstall()
	{
		$this->EE->db->delete('modules', array('module_name' => 'Cartthrob'));
		
		$this->EE->db->like('class', 'Cartthrob', 'after')->delete('actions');
		
		$this->EE->db->delete('extensions', array('class' => 'Cartthrob_ext'));
		
		//should we do this?
		//nah, do it yourself if you really want to
		/*
		foreach (array_keys($this->tables) as $table)
		{
			$this->EE->dbforge->drop_table($table);
		}
		*/
		
		return TRUE;
	}
	
	private function older_than($version, $curr = NULL)
	{
		//if it only has one point, it's a beta version
		if (substr_count($version, '.') === 1 && substr_count($this->current, '.') === 2)
		{
			return FALSE;
		}
		
		/*
		// not using this anymore because PHP is too picky about number formatting. 
		// really this is only good for PHP version checking as far as I'm concerned. 
		// IE: 5.2 is considered older than 5.2.0 using this method... which is just wrong. 
		// and 2.4 is considered older than 2.0721. Which makes no sense whatsoever. 
		// return version_compare($this->current, $version, '<');
		*/ 
		if (! $curr)
		{
			$curr = $this->current;
		}
  		list($c1, $c2, $c3) = array_merge(explode('.', $curr), array(0,0,0)); 
 		list($v1, $v2, $v3) = array_merge(explode('.', $version), array(0,0,0)); 
		
		$c2 = str_pad($c2, 3, 0); 
		$c3 = str_pad($c3, 3, 0); 
		$v2 = str_pad($v2, 3, 0);
		$v3 = str_pad($v3, 3, 0);
		
		$current = $c1 .".". $c2. "." . $c3; 
		$version = $v1 . ".". $v2. "." . $v3; 
		
		$compare = strnatcasecmp($current, $version); 
		
		if ($compare >= 0)
		{
			// not older than
			return FALSE; 
		}
		elseif ($compare < 0)
		{
			// older than. 
			return TRUE; 
		}
	}
}

/* End of file upd.cartthrob.php */
/* Location: ./system/expressionengine/third_party/cartthrob/upd.cartthrob.php */
