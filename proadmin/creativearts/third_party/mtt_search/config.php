<?php

/**
 * Mtt Search config file
 *
 * @package        mtt_search
 * @author         MyTopTeacher <contact@mytopteacher.com>
 * @link           http://mytopteacher.com/addons/mtt-search
 * @copyright      Copyright (c) 2014, Mtt
 */

if ( ! defined('MTT_SEARCH_NAME'))
{
	define('MTT_SEARCH_NAME',       'Mtt Search');
	define('MTT_SEARCH_PACKAGE',    'mtt_search');
	define('MTT_SEARCH_VERSION',    '3.2.0');
	define('MTT_SEARCH_DOCS',       'http://mytopteacher.com/addons/mtt-search');
	define('MTT_SEARCH_DEBUG',      FALSE);
	define('MTT_SEARCH_MAX_WEIGHT', 3);
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
$config['name']    = MTT_SEARCH_NAME;
$config['version'] = MTT_SEARCH_VERSION;
$config['nsm_addon_updater']['versions_xml'] = MTT_SEARCH_DOCS.'/feed';
