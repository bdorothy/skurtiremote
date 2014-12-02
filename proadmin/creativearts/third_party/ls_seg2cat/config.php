<?php

/**
 * LS  Seg2Cat Extension class
 *
 */

if ( ! defined('LS_SEG2CAT_NAME'))
{
	define('LS_SEG2CAT_NAME',    'LS Seg2Cat');
	define('LS_SEG2CAT_PACKAGE', 'ls_seg2cat');
	define('LS_SEG2CAT_VERSION', '2.8.1');
	define('LS_SEG2CAT_DOCS',    'http://sixth.co.in');
}

/**
 * < EE 2.6.0 backward compat
 */
if ( ! function_exists('ee'))
{
	function ee()
	{
		static $EE;
		if ( ! $EE) $EE = get_instance();
		return $EE;
	}
}

/**
 * NSM Addon Updater
 */
$config['name']    = LS_SEG2CAT_NAME;
$config['version'] = LS_SEG2CAT_VERSION;
$config['nsm_addon_updater']['versions_xml'] = LS_SEG2CAT_DOCS.'/feed';