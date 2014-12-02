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
 * MTT Ratings Module Install/Update File
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Raj Sadh
 * @link		http://www.sixth.co.in
 */

class Favourites_upd {
	
	public $version = '2.0';
	
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
		$mod_data = array(
			'module_name'			=> 'Favourites',
			'module_version'		=> $this->version,
			'has_cp_backend'		=> "y",
			'has_publish_fields'	=> 'n'
		);
		
		$this->EE->db->insert('modules', $mod_data);
		
		$this->EE->load->dbforge();
		
		$fields = array(
		'entry_id'   	 => array('type' => 'int', 'constraint' => '10', 'null' => TRUE),
		'member_id'   	 => array('type' => 'int', 'constraint' => '10', 'null' => TRUE),
		'date'   		 => array('type' => 'int', 'constraint' => '10', 'null' => TRUE)
		);
		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->create_table('favourites');
		unset($fields);
		
		
		 $data = array(
        'class'     => 'Favourites',
        'method'     => 'favourite_process');
		$this->EE->db->insert('actions', $data);
		
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
									'module_name'	=> 'Favourites'
								))->row('module_id');
		
		$this->EE->db->where('module_id', $mod_id)
					 ->delete('module_member_groups');
		
		$this->EE->db->where('module_name', 'Favourites')
					 ->delete('modules');
		
		$this->EE->load->dbforge();
		$this->EE->dbforge->drop_table('favourites');
		
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
/* End of file upd.Favourites.php */
/* Location: /system/expressionengine/third_party/Favourites/upd.Favourites.php */