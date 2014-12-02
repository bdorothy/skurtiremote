<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
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
 * Creativearts Language Model
 *
 * @package		Creativearts
 * @subpackage	Core
 * @category	Model
 * @author		Creativelab Dev Team
 * @link		http://creativelab.com
 */
class Language_model extends CI_Model {

	/**
	 * Language Pack Names
	 *
	 * @access	public
	 * @return	array
	 */
	function language_pack_names()
	{
		$source_dir = APPPATH.'language/';

		$dirs = array();

		if ($fp = @opendir($source_dir))
		{
			while (FALSE !== ($file = readdir($fp)))
			{
				if (is_dir($source_dir.$file) && substr($file, 0, 1) != ".")
				{
					$dirs[$file] = ucfirst($file);
				}
			}
			closedir($fp);
		}

		 return $dirs;
	}

}

/* End of file language_model.php */
/* Location: ./system/creativearts/models/language_model.php */