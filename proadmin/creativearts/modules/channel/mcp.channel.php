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
 * Creativearts Channel Module
 *
 * @package		Creativearts
 * @subpackage	Modules
 * @category	Modules
 * @author		Creativelab Dev Team
 * @link		http://creativelab.com
 */

class Channel_mcp {

	var $stats_cache	= array(); // Used by mod.stats.php

	/**
	  * Constructor
	  */
	function Channel_mcp()
	{
		// Make a local reference to the Creativearts super object
		$this->EE =& get_instance();
	}
}
// END CLASS

/* End of file mcp.channel.php */
/* Location: ./system/creativearts/modules/channel/mcp.channel.php */