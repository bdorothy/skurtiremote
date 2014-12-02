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

// ------------------------------------------------------------------------

/**
 * Creativearts Controller
 *
 * @package		Creativearts
 * @subpackage	Core
 * @category	Core
 * @author		Creativelab Dev Team
 * @link		http://creativelab.com
 */
class EE_Controller extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->library('core');

		$this->core->bootstrap();
		$this->core->run_ee();
	}
}

// ------------------------------------------------------------------------

/**
 * Creativearts Control Panel Controller
 *
 * @package		Creativearts
 * @subpackage	Core
 * @category	Core
 * @author		Creativelab Dev Team
 * @link		http://creativelab.com
 */
class CP_Controller extends EE_Controller {

	function __construct()
	{
		parent::__construct();
		$this->core->run_cp();
	}
}


/* End of file  */
/* Location: system/creativearts/libraries/core/EE_Controller.php */