<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Creativearts - by Creativelab
 *
 * @package		Creativearts
 * @author		Creativelab Dev Team
 * @copyright	Copyright (c) 2003 - 2014, Creativelab, Inc.
 * @license		http://creativelab.com/creativearts/user-guide/license.html
 * @link		http://creativelab.com
 * @since		Version 2.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Creativearts Query Module
 *
 * @package		Creativearts
 * @subpackage	Modules
 * @category	Update File
 * @author		Creativelab Dev Team
 * @link		http://creativelab.com
 */

class Query_upd {

	var $version = '2.0';

	function Query_upd()
	{
		// Make a local reference to the Creativearts super object
		$this->EE =& get_instance();
	}

	// --------------------------------------------------------------------

	/**
	 * Module Installer
	 *
	 * @access	public
	 * @return	bool
	 */
	function install()
	{
		$data = array(
			'module_name' 	 => 'Query',
			'module_version' => $this->version,
			'has_cp_backend' => 'n'
		);

		ee()->db->insert('modules', $data);

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Module Uninstaller
	 *
	 * @access	public
	 * @return	bool
	 */
	function uninstall()
	{
		ee()->db->select('module_id');
		$query = ee()->db->get_where('modules', array('module_name' => 'Query'));
		$module_id = $query->row('module_id');

		ee()->db->where('module_id', $module_id);
		ee()->db->delete('module_member_groups');

		ee()->db->where('module_name', 'Query');
		ee()->db->delete('modules');

		ee()->db->where('class', 'Query');
		ee()->db->delete('actions');

		ee()->db->where('class', 'Query_mcp');
		ee()->db->delete('actions');

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Module Updater
	 *
	 * @access	public
	 * @return	bool
	 */
	function update($current='')
	{
		return FALSE;
	}

}
// END CLASS

/* End of file upd.query.php */
/* Location: ./system/creativearts/modules/query/upd.query.php */