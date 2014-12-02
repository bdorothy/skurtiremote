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
 * Creativearts CP Home Page Class
 *
 * @package		Creativearts
 * @subpackage	Control Panel
 * @category	Control Panel
 * @author		Creativelab Dev Team
 * @link		http://creativelab.com
 */
class Tools extends CP_Controller {

	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function __construct()
	{
		parent::__construct();

		if ( ! $this->cp->allowed_group('can_access_tools'))
		{
			show_error(lang('unauthorized_access'));
		}

		$this->lang->loadfile('tools');
	}

	// --------------------------------------------------------------------

	/**
	 * Index function
	 *
	 * @access	public
	 * @return	void
	 */
	function index()
	{
		$this->view->cp_page_title = lang('tools');
		$this->view->controller = 'tools';

		$this->cp->render('_shared/overview');
	}

}

/* End of file tools.php */
/* Location: ./system/creativearts/controllers/cp/tools.php */