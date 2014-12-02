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

class Mtt_favourites_upd {
	
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
			'module_name'			=> 'Mtt_favourites',
			'module_version'		=> $this->version,
			'has_cp_backend'		=> "y",
			'has_publish_fields'	=> 'n'
		);
		
		$this->EE->db->insert('modules', $mod_data);
		
		ee()->load->dbforge();
		
		$fields = array(
		'entry_id'   	 => array('type' => 'int', 'constraint' => '10', 'null' => TRUE),
		'member_id'   	 => array('type' => 'int', 'constraint' => '10', 'null' => TRUE),
		'date'   		 => array('type' => 'int', 'constraint' => '10', 'null' => TRUE)
		);
		ee()->dbforge->add_field($fields);
		ee()->dbforge->create_table('mtt_favourites');
		unset($fields);
		
		
		$data = array(
        'class'     => 'Mtt_favourites',
        'method'     => 'favourite_process');
		$this->EE->db->insert('actions', $data);
		
		$data = array(
        'class'     => 'Mtt_favourites',
        'method'     => 'set_comparelist');
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
								
		$query = ee()->db->select('module_id')
		->from('modules')
		->where('module_name', 'Mtt_favourites')
		->get();
		
		ee()->db->where('module_id', $query->row('module_id'));
		ee()->db->delete('module_member_groups');
		
		
		$this->EE->db->where('module_name', 'Mtt_favourites')
					 ->delete('modules');
					 
		$this->EE->db->where('class', 'Mtt_favourites')
					 ->delete('actions');			 
		
		ee()->load->dbforge();
		ee()->dbforge->drop_table('mtt_favourites');
		
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
/* End of file upd.Mtt_favourites.php */
/* Location: /system/expressionengine/third_party/Mtt_favourites/upd.Mtt_favourites.php */