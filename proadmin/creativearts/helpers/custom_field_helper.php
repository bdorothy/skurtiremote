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
 * Creativearts Segment Helper
 *
 * @package		Creativearts
 * @subpackage	Helpers
 * @category	Helpers
 * @author		Creativelab Dev Team
 * @link		http://creativelab.com
 */

// ------------------------------------------------------------------------

/**
 * Encode multi select field data
 *
 * Creates a pipe concatenated string with all superfluous pipes escaped
 *
 * @access	public
 * @param	array	the multi select data
 * @return	string
 */
function encode_multi_field($data = array())
{
	if ( ! is_array($data))
	{
		$data = array($data);
	}

	// Escape pipes
	foreach($data as $key => $val)
	{
		$data[$key] = str_replace(array('\\', '|'), array('\\\\', '\|'), $val);
	}

	// Implode on seperator
	return implode('|', $data);
}

// ------------------------------------------------------------------------

/**
 * Decode multi select field data
 *
 * Explodes the stored string and cleans up escapes
 *
 * @access	public
 * @param	string	data string
 * @return	array
 */
function decode_multi_field($data = '')
{
	if ($data == '')
	{
		return array();
	}

	if (is_array($data))
	{
		return $data;
	}

	// Explode at non-escaped pipes ([\\\\] == one backslash, thanks to php + regex escaping)
	$data = preg_split("#(?<![\\\\])[|]#", $data);

	// Reduce slashes
	return str_replace(array('\|', '\\\\'), array('|', '\\'), $data);
}

/* End of file custom_field_helper.php */
/* Location: ./system/creativearts/helpers/custom_field_helper.php */
