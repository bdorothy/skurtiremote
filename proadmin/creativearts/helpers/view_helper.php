<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Creativearts - by Creativelab
 *
 * @package		Creativearts
 * @author		Creativelab Dev Team
 * @copyright	Copyright (c) 2003 - 2014, Creativelab, Inc.
 * @license		http://creativelab.com/creativearts/user-guide/license.html
 * @link		http://creativelab.com
 * @since		Version 2.6
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Creativearts View Helper
 *
 * @package		Creativearts
 * @subpackage	Helpers
 * @category	Helpers
 * @author		Creativelab Dev Team
 * @link		http://creativelab.com
 */

// ------------------------------------------------------------------------

/**
 * Extend a view in the _template directory
 */
function extend_template($which, $disable = array())
{
	ee()->view->extend('_templates/'.$which, $disable);
}

// ------------------------------------------------------------------------

/**
 * Extend a view. Contents of the current view
 * are passed in as $EE_Rendered_view
 */
function extend_view($which, $disable = array())
{
	ee()->view->extend($which, $disable);
}

// ------------------------------------------------------------------------

/**
 * Check if a view block is disabled
 */
function disabled($which)
{
	return ee()->view->disabled($which);
}

// ------------------------------------------------------------------------

/**
 * Check if a view block is enabled
 */
function enabled($which)
{
	return ! ee()->view->disabled($which);
}

/* End of file */
/* Location: system/creativearts/helpers/view_helper.php */