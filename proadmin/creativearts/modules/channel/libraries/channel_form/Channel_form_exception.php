<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Creativearts - by Creativelab
 *
 * @package		Creativearts
 * @author		Creativelab Dev Team
 * @copyright	Copyright (c) 2003 - 2014, Creativelab, Inc.
 * @license		http://creativelab.com/creativearts/user-guide/license.html
 * @link		http://creativelab.com
 * @since		Version 2.7
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Creativearts Channel Form Exception Class
 *
 * @package		Creativearts
 * @subpackage	Core
 * @category	Core
 * @author		Creativelab Dev Team
 * @link		http://creativelab.com
 */
class Channel_form_exception extends Exception {

	private $_type;

	/**
	 * Override the constructor to work more like show_user_error
	 */
	public function __construct($message, $type = 'submission')
	{
		if (is_array($message))
		{
			$message = implode("</li>\n<li>", $message);
		}

		parent::__construct($message);
		$this->_type = $type;
	}

	// --------------------------------------------------------------------

	/**
	 * Custom accessor to show the user error at the catch site
	 */
	public function show_user_error()
	{
		return ee()->output->show_user_error($this->_type, $this->getMessage());
	}
}