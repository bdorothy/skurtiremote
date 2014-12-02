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

class Ls_ratings_upd {
	
	public $version = '3.2';
	
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
			'module_name'			=> 'Ls_ratings',
			'module_version'		=> $this->version,
			'has_cp_backend'		=> "n",
			'has_publish_fields'	=> 'n'
		);
		
		$this->EE->db->insert('modules', $mod_data);
		
		
		ee()->load->dbforge();
		
		$fields = array(
		'entry_id'   	 => array('type' => 'int', 'constraint' => '11', 'null' => TRUE),
		'comment_id'   	 => array('type' => 'int', 'constraint' => '11', 'null' => TRUE),
		'rating'	   	 => array('type' => 'int', 'constraint' => '11', 'null' => TRUE),		
		);
		ee()->dbforge->add_field($fields);
		ee()->dbforge->create_table('ls_ratings');
		unset($fields);
			
		$data = array(
        'class'     => 'Ls_ratings',
        'method'     => 'response_process'
		);
		
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
									'module_name'	=> 'Ls_ratings'
								))->row('module_id');
		
		$this->EE->db->where('module_id', $mod_id)
					 ->delete('module_member_groups');
		
		$this->EE->db->where('module_name', 'Ls_ratings')
					 ->delete('modules');
		
		ee()->load->dbforge();
		ee()->dbforge->drop_table('ls_ratings');
		$this->EE->db->where('class', 'Ls_ratings')
					 ->delete('actions');
					 
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
/* End of file upd.mtt_ratings.php */
/* Location: /system/expressionengine/third_party/mtt_ratings/upd.mtt_ratings.php */