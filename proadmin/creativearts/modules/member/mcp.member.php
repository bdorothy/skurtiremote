<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

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

// --------------------------------------------------------------------

/**
 * Member Management Module
 *
 * @package		Creativearts
 * @subpackage	Modules
 * @category	Modules
 * @author		Creativelab Dev Team
 * @link		http://creativelab.com
 */

 /*
  Purpose: Member management system - CP
  Note: Because member management is so tightly
  integrated into the core system, most of the
  member functions are contained in the core and cp
  files.
 */

class Member_mcp {

	function Member_mcp()
	{
		// Make a local reference to the Creativearts super object
		$this->EE =& get_instance();
	}
}
// END CLASS

/* End of file mcp.member.php */
/* Location: ./system/creativearts/modules/member/mcp.member.php */