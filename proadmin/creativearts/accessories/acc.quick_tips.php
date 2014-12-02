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
 * Creativearts Learning Accessory
 *
 * Currently this Accessory is only available in English, thus it doesn't use any
 * language files.
 *
 * @package		Creativearts
 * @subpackage	Control Panel
 * @category	Accessories
 * @author		Creativelab Dev Team
 * @link		http://creativelab.com
 */
class Quick_tips_acc {

	var $name			= 'Quick Tips';
	var $id				= 'quick_tips';
	var $version		= '1.1';
	var $description	= 'Brief tips and hints for Creativearts';
	var $sections		= array();
	var $tips			= array(
								'Tables are sortable.',
								'Forums are an easy way to add functionality',
								'Remember, PHP settings for each Template are honored on a per-Template basis.',
								'Did you know that Creativearts.com has over 75,000 registered members! If we laid down in a line we\'d circle the Earth and then some!',
								'Make sure you assign a status group to all of your channels.',
								'Have you backed up your database lately?',
								'Did you know… that the help button is context sensitive based on where you are in the control panel?',
								'Did you know… that you can view previously sent email from the Communicate tool?',
								'Did you know… that Accessories can be assigned to member groups?  Or even sections of the control panel?',
								'You can use the "disable" parameter to eliminate unneeded queries and improve performance.',
								'Need help? Want to network? Visit the <a rel="external" href="http://creativelab.com/forums/">forums</a>.',
								'To hide EE tags in your templates you can use the EE comment feature {!—EE Comment—} as opposed to typical HTML comments <!—HTML Comment—>',
								'"Display SQL Queries & Template debugging" is a great way to see under the hood of EE during development.',
								);

	/**
	 * Constructor
	 */
	function __construct()
	{

		$this->EE =& get_instance();
		ee()->load->helper('array');
	}

	function update()
	{
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Sections
	 *
	 * Set content for the accessory
	 *
	 * @access	public
	 * @return	void
	 */
	function set_sections()
	{
		$this->sections[$this->name] = $this->_build_tips();
	}

	// --------------------------------------------------------------------

	function _build_tips()
	{
		return random_element($this->tips);
	}

}
// END CLASS

/* End of file acc.quick_tips.php */
/* Location: ./system/creativearts/accessories/acc.quick_tips.php */