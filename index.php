<?php
/**
 * Creativearts - by Creativelab
 *
 * @package		Creativearts
 * @author		Creativelab Dev Team
 * @copyright	Copyright (c) 2003 - 2014, Creativelab, Inc.
 * @license		http://creativelab.com/creativearts/user-guide/license.html
 * @link		http://creativelab.com
 * @since		Version 2.0
 */

/*
 * --------------------------------------------------------------------
 *  System Path
 * --------------------------------------------------------------------
 *
 * The following variable contains the server path to your
 * Creativearts "system" folder.  By default the folder is named
 * "system" but it can be renamed or moved for increased security.
 * Indicate the new name and/or path here. The path can be relative
 * or it can be a full server path.
 *
 * http://creativelab.com/creativearts/user-guide/installation/best_practices.html
 *
 */
	$system_path = './proadmin';


/*
 * --------------------------------------------------------------------
 *  Multiple Site Manager
 * --------------------------------------------------------------------
 *
 * Uncomment the following variables if you are using the Multiple
 * Site Manager: http://creativelab.com/creativearts/user-guide/cp/sites
 *
 * Set the Short Name of the site this file will display, the URL of
 * this site's admin.php file, and the main URL of the site (without
 * index.php)
 *
 */
 //  $assign_to_config['site_name']  = 'domain2_short_name';
 //  $assign_to_config['cp_url'] = 'http://domain2.com/admin.php';
 //  $assign_to_config['site_url'] = 'http://domain2.com';


/*
 * --------------------------------------------------------------------
 *  Error Reporting
 * --------------------------------------------------------------------
 *
 * PHP and database errors are normally displayed dynamically based
 * on the authorization level of each user accessing your site.
 * This variable allows the error reporting system to be overridden,
 * which can be useful for low level debugging during site development,
 * since errors happening before a user is authenticated will not normally
 * be shown.  Options:
 *
 *	$debug = 0;  Default setting. Errors shown based on authorization level
 *
 *	$debug = 1;  All errors shown regardless of authorization
 *
 * NOTE: Enabling this override can have security implications.
 * Enable it only if you have a good reason to.
 *
 */
	$debug = 1;

/*
 * --------------------------------------------------------------------
 *  CUSTOM CONFIG VALUES
 * --------------------------------------------------------------------
 *
 * The $assign_to_config array below will be passed dynamically to the
 * config class. This allows you to set custom config items or override
 * any default config values found in the config.php file.  This can
 * be handy as it permits you to share one application between more then
 * one front controller file, with each file containing different
 * config values.
 *
 * Un-comment the $assign_to_config array below to use this feature
 *
 * NOTE: This feature can be used to run multiple EE "sites" using
 * the old style method.  Instead of individual variables you'll
 * set array indexes corresponding to them.
 *
 */
//	$assign_to_config['template_group'] = '';
//	$assign_to_config['template'] = '';
//	$assign_to_config['site_index'] = '';
//	$assign_to_config['site_404'] = '';
//	$assign_to_config['global_vars'] = array(); // This array must be associative
	$assign_to_config['global_vars'] = array('skin_url' => '/theme/standard/skin');
	
		
	/*
	 * --------------------------------------------------------------------
	 *  STASH CACHING
	 * --------------------------------------------------------------------
	 */
	$assign_to_config['stash_file_basepath'] = '../stash_templates';
	$assign_to_config['stash_file_sync'] = TRUE; // set to FALSE for production
	$assign_to_config['stash_file_extensions'] = array('html', 'md', 'css', 'js', 'rss', 'xml');
	$assign_to_config['stash_static_basepath'] = './stash_cache/';
	$assign_to_config['stash_static_url'] = '/static_cache/'; // should be a relative url
	$assign_to_config['stash_static_cache_enabled'] = TRUE; // set to TRUE to enable static caching
	$assign_to_config['stash_cookie'] = 'stashid'; // the stash cookie name
	$assign_to_config['stash_cookie_expire'] = 0; // seconds - 0 means expire at end of session
	$assign_to_config['stash_default_scope'] = 'local'; // default variable scope if not specified
	$assign_to_config['stash_limit_bots'] = TRUE; // stop database writes by bots to reduce load on busy sites
	$assign_to_config['stash_bots'] = array('bot', 'crawl', 'spider', 'archive', 'search', 'java', 'yahoo', 'teoma');
	$assign_to_config['stash_prune_enabled'] = TRUE; // set FALSE if you trigger pruning with a CRON/scheduled task (via Mustash API)
	$assign_to_config['stash_prune_probability'] = 4;  // % chance that a request initiates cache pruning
	$assign_to_config['stash_invalidation_period']  = 0;  // duration of cache invalidation in seconds
	$assign_to_config['stash_parse_if_in']  = FALSE;  // enable parsing of {if var IN (1|2|3)...{/if} style conditionals in Stash templates
	# $assign_to_config['stash_var_prefix'] = 'preview-'; // apply a prefix to all saved variables, useful for entry previews
	


/*
 * --------------------------------------------------------------------
 *  END OF USER CONFIGURABLE SETTINGS.  DO NOT EDIT BELOW THIS LINE
 * --------------------------------------------------------------------
 */


/*
 * ---------------------------------------------------------------
 *  Disable all routing, send everything to the frontend
 * ---------------------------------------------------------------
 */
	$routing['directory'] = '';
	$routing['controller'] = 'ee';
	$routing['function'] = 'index';

/*
 * --------------------------------------------------------------------
 *  Mandatory config overrides
 * --------------------------------------------------------------------
 */
	$assign_to_config['subclass_prefix'] = 'EE_';

/*
 * --------------------------------------------------------------------
 *  Resolve the system path for increased reliability
 * --------------------------------------------------------------------
 */

	if (realpath($system_path) !== FALSE)
	{
		$system_path = realpath($system_path).'/';
	}

	// ensure there's a trailing slash
	$system_path = rtrim($system_path, '/').'/';

	// Is the sytsem path correct?
	if ( ! is_dir($system_path))
	{
		exit("Your system folder path does not appear to be set correctly. Please open the following file and correct this: ".pathinfo(__FILE__, PATHINFO_BASENAME));
	}

/*
 * --------------------------------------------------------------------
 *  Now that we know the path, set the main constants
 * --------------------------------------------------------------------
 */
	// The name of THIS file
	define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

	// The PHP file extension
	define('EXT', '.php');

 	// Path to the system folder
	define('BASEPATH', str_replace("\\", "/", $system_path.'codeigniter/system/'));

	// Path to the "application" folder
	define('APPPATH', $system_path.'creativearts/');

	// Path to the front controller (this file)
	define('FCPATH', str_replace(SELF, '', __FILE__));

	// Name of the "system folder"
	define('SYSDIR', trim(strrchr(trim(str_replace("\\", "/", $system_path), '/'), '/'), '/'));

	// The $debug value as a constant for global access
	define('DEBUG', $debug);  unset($debug);

/*
 * --------------------------------------------------------------------
 *  Set the error reporting level
 * --------------------------------------------------------------------
 */
	if (DEBUG == 1)
	{
		error_reporting(E_ALL);
		@ini_set('display_errors', 1);
	}
	else
	{
		error_reporting(0);
	}

/*
 *---------------------------------------------------------------
 * LOAD THE BOOTSTRAP FILE
 *---------------------------------------------------------------
 *
 * And away we go...
 *
 */
	require_once BASEPATH.'core/CodeIgniter'.EXT;

/* End of file index.php */
/* Location: ./index.php */
