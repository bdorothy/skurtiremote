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
class Query_mcp {

	var $version = '1.0';

	function Query_mcp()
	{
		// Make a local reference to the Creativearts super object
		$this->EE =& get_instance();
	}
}
// END CLASS

/* End of file mcp.query.php */
/* Location: ./system/creativearts/modules/query/mcp.query.php */