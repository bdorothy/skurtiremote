<?php

/*
* --------------------------------------------------------------------
*  ADJUST THE FOLLOWING VARIABLES IF YOU EXPERIENCE PROBLEMS USING OFFSITE GATEWAYS
* --------------------------------------------------------------------
* 
* The extload.php file is a part of CartThrob that is used to handle responses
* from some "offsite" gateways. If your gateway provider's service hanldes all 
* credit card number entry and processing, it's likely you are using an offsite gateway. 
* 
* Generally, you will need to adjust the configuration variables below if you have moved your system folder
* And if you are using Multi-Site Manager.
*/ 



/*
 * --------------------------------------------------------------------
 *  System Path
 * --------------------------------------------------------------------
 *
 * This variable contains the server path to your EE "system" folder. 
 * This path MUST be relative to the position (*!IMPORTANT!*) THEMES FOLDER 
 * If you would prefer to use an absolute server path, uncomment the "system_server_path" and update the full server path to your system folder
 * This should not be set as a web URL 
 *
 * http://ellislab.com/expressionengine/user-guide/installation/best_practices.html
 * 
 */
 
	$system_path = "system"; 
	// if changing the sytem file name above does not have the desired effect
	// $system_server_path = /*example*/ "/usr/var/www/htdocs/system"; 
	$third_party_themes_path = "themes/third_party"; 
	
/*
 * --------------------------------------------------------------------
 *  Multiple Site Manager
 * --------------------------------------------------------------------
 *
 * If you are using the Multiple Site Manager, uncomment and update the following items:
 * 1. Set the Short Name of this site
 * 2. (if you save templates as files) Set the template file Absolute Server Path (not the web URL... the server path)
 * Set the Short Name of the site this file will display, the URL of
 * this site's admin.php file, and the main URL of the site (without
 * index.php) 
 *
 *  http://ellislab.com/expressionengine/user-guide/cp/sites
 */

	//$assign_to_config['site_name']  = /*example*/ 'your_site_name'; 
	//$assign_to_config['site_url']  = /*example*/ 'http://example.com'; // no trailing slash 
	//$assign_to_config['tmpl_file_basepath'] = /*example*/ '/usr/var/www/htdocs/your_template_path'; 

/*
* --------------------------------------------------------------------
*  CUSTOM CONFIG VALUES
* --------------------------------------------------------------------
*
*/

	//	$assign_to_config['template_group'] = '';
	//	$assign_to_config['template'] = '';
	//	$assign_to_config['site_index'] = '';
	//	$assign_to_config['site_404'] = '';
	//	$assign_to_config['global_vars'] = array(); // This array must be associative
	
/*
 * --------------------------------------------------------------------
 *  END OF CONFIGURATION. DO NOT EDIT BELOW THIS LINE
 * --------------------------------------------------------------------
 */
	
	
/*
 * --------------------------------------------------------------------
 *  Set system path
 * --------------------------------------------------------------------
 */
	
	// look for the full server URL if set.
	if ( ! empty($system_server_path))
	{
		$system_path = $system_server_path;
	}
	else
	{
		// current file location
		$current_location = substr(__FILE__,0,strrpos(__FILE__,'/'));
		// get the system path relative to teh current file location.
		$system_path = substr($current_location, 0,  strrpos($current_location, $third_party_themes_path.'/cartthrob/lib') ).$system_path; 
	}

	if ( realpath( $system_path ) !== FALSE)
	{
		$system_path = realpath( $system_path ).'/';
	}

	$system_path = rtrim( $system_path, '/').'/';

	if ( ! is_dir( $system_path ))
	{
		exit("CartThrob has noticed that your system folder path does not appear to be set correctly. Please open CartThrob's Extload file and update the URL: ".pathinfo(__FILE__, PATHINFO_BASENAME));
	}

/*
 * --------------------------------------------------------------------
 *  CLEAR GET VARIABLES SO EE DOES NOT HAVE A PROBLEM WITH IP ADDRESSES, ETC
 * --------------------------------------------------------------------
 */
	$get = array(); 
	if (isset($_GET))
	{
		$get = $_GET; 
		unset($_GET); 
	}

/*
 * --------------------------------------------------------------------
 *  TURN QUERY STRING INTO ARRAY
 * --------------------------------------------------------------------
 */

	parse_str(@$_SERVER['QUERY_STRING'], $query_string);


	foreach (array('_GET', '_POST', '_COOKIE', '_REQUEST') as $key)
	{
		if ( ! isset(${$key}))
		{
			${$key} = array();
		}
	}

/*
 * --------------------------------------------------------------------
 *  Set up constants
 * --------------------------------------------------------------------
 */

	define('APPPATH', $system_path.'expressionengine/');

	define('BASEPATH', str_replace("\\", "/", $system_path.'codeigniter/system/'));

	define('CI_VERSION', '2.0.1');
	define('CI_CORE', TRUE);
		

	define('DEBUG',FALSE);

	define('EXT', '.php');

	define('FCPATH', str_replace( pathinfo(__FILE__, PATHINFO_BASENAME) , '', __FILE__));

	define('UTF8_ENABLED', FALSE);

	define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

	define('SYSDIR', trim(strrchr(trim(str_replace("\\", "/", $system_path), '/'), '/'), '/'));


/*
 * --------------------------------------------------------------------
 *  Debug. Leave this at 0... unless you want something to break.
 * --------------------------------------------------------------------
 */
	error_reporting(0);	

/*
 * --------------------------------------------------------------------
 *  LOAD global functions and constants.
 * --------------------------------------------------------------------
 */

	if (!is_file(BASEPATH.'core/Common'.EXT) ||  !is_file(APPPATH.'config/constants'.EXT) || !is_file(BASEPATH.'core/Controller'.EXT))
	{
		die("CartThrob ecommerce could not find necessary files required to complete this transaction. Please update the system folder location in the extload.php library."); 
	}

	require(BASEPATH.'core/Common'.EXT);
	require(APPPATH.'config/constants'.EXT);

	// define custom error handler
	set_error_handler('_exception_handler');

	if ( ! is_php('5.3'))
	{
		@set_magic_quotes_runtime(0); 
	}


	// register autoloader
	require(APPPATH.'../EllisLab/ExpressionEngine/Core/Autoloader.php');

	Autoloader::getInstance()->register();



/*
 * --------------------------------------------------------------------
 *  Instantiate the config class.
 * --------------------------------------------------------------------
 */
	$CFG	=& load_class('Config', 'core');

/*
 * ------------------------------------------------------
 *  Instantiate Classes. See core/codeigniter.php for more info
 * ------------------------------------------------------
 */

	// added UNI for EE 2.7.2
	$UNI 	=& load_class('Utf8', 'core');

	$URI	=& load_class('URI', 'core');
	$OUT	=& load_class('Output', 'core');
	$SEC	=& load_class('Security', 'core');
	$IN		=& load_class('Input', 'core');	
	$LANG	=& load_class('Lang', 'core');
 
/*
 * ------------------------------------------------------
 *  Unlike CodeIgniter... we don't need routing. Just get the core controller
 * ------------------------------------------------------
 */
	require(BASEPATH.'core/Controller'.EXT);

	// previously we were just checking to see if php_sapi_name was CLI. Some servers output various other bits of nonsense, so to make it more robust
	// we're chekcing to see if there are args & server software set...which there shouldn't be if it really is a CLI command. 
	function check_is_cli() {
	  return (!isset($_SERVER['SERVER_SOFTWARE']) && (php_sapi_name() == 'cli' || (is_numeric($_SERVER['argc']) && $_SERVER['argc'] > 0)));
	}
	$is_cli = check_is_cli();
 	function &get_instance()
	{
	    return CI_Controller::get_instance();
	}
 
	function ee()
	{
		static $EE;
		if ( ! $EE) $EE = get_instance();
		return $EE;
	}
	
	class EE_ext_loader extends CI_Controller {
		function __construct($assign_to_config = array())
		{
			parent::__construct();

			$this->load->library('core');
			$this->core->bootstrap();

			$this->load->library('config');

		 /*
		 * ------------------------------------------------------
		 *  set config items.
		 * ------------------------------------------------------
		 */
			$site_name = NULL; 
			if (isset($assign_to_config['site_name']))
			{
				$site_name = $assign_to_config['site_name']; 
			}
			// Do we have any manually set config items in this file? Analogous to the items in MSM sites
			if (isset($assign_to_config))
			{
				$this->config->_assign_to_config($assign_to_config);
			}
			
			if ($site_name && $this->config->item('multiple_sites_enabled') == 'y')
			{
				$this->config->set_item('site_name', preg_replace('/[^a-z0-9\-\_]/i', '', $site_name));
				$this->config->site_prefs($this->config->item('site_name'));
				
			}
			/*
		 * ------------------------------------------------------
		 *  Turn off secure forms
		 * ------------------------------------------------------
		 */
			// we don't need secure forms turned on for this, because we're not worried about the XID hash on this right now. Not worried about comment form spam or other spamming since the only thing we're pinging is gateway files, and though they could get hit a zillion times, they will only operate if the correct data is sent through, and basically just respond with errors if it's not correct... essentially doing the same job as secure forms would do. 
			$this->config->set_item('secure_forms', 'n');
		 	$this->config->set_item('disable_csrf_protection', 'y'); 
		 			
			$this->core->run_ee(); 
		}
	}

	if ($assign_to_config)
	{
		$extload = new EE_ext_loader($assign_to_config);
	}
	else
	{
	 	$extload = new EE_ext_loader;
	}
	
 	// get the URI information
	$extload->uri->_fetch_uri_string();
	$extload->uri->_remove_url_suffix();
	$extload->uri->_explode_segments();

	// add info about cartthrob
 	$extload->load->add_package_path(PATH_THIRD.'cartthrob/');
	
	// if they're using extload from the command line, ie. cron
	$cli_methods = array('cron');

	if ( $is_cli )
	{
		// first arg is the script name, remove it
		$args = array_slice($argv, 1);
	
		if (in_array($args[0], $cli_methods))
		{
			$command = array_shift($args);
		
			$method = array_shift($args);
		
			switch ($command)
			{
				case 'cron':
				
					$valid_actions = array(
						'garbage_collection',
						'process_subscriptions',
						'process_subscription',
					);
				
					if ( ! $method)
					{
						die("error No cron action specified".PHP_EOL);
					}
				
					if ( ! in_array($method, $valid_actions))
					{
						die("error Invalid cron action specified".PHP_EOL);
					}
				
					require_once PATH_THIRD.'cartthrob/mcp.cartthrob.php';
				
					$mcp = new Cartthrob_mcp;
				
					call_user_func_array(array($mcp, $method), $args);
				
					exit;
				default:

					$valid_modules = array(
						'cartthrob_expired_cart_notifications',
					); 
				
					$valid_actions = array(
						'get_expired_carts',
					);

					if ( ! $method)
					{
						die("error No cron action specified".PHP_EOL);
					}

					if ( ! in_array($command, $valid_modules))
					{
						die("error Invalid module specified".PHP_EOL);
					}
				
					if ( ! in_array($method, $valid_actions))
					{
						die("error Invalid cron action specified".PHP_EOL);
					}

				
					require_once PATH_THIRD.$command.'/mod.'.$command.'.php';

					$mod_name = ucwords($command); 
					$mod = new $mod_name;

					call_user_func_array(array($mod, $method), $args);

					exit;
			
			}
		}
		else
		{
			// use the command line arguments as segment variables
			$extload->uri->segments = $args;
		}
	}

	$extload->load->library('cartthrob_loader');
	$extload->load->library('cartthrob_payments');

	// the gateway is the first (zero) segment. We're getting that...  need it to load the gateway
	$gateway = $extload->uri->segment(0);

	// these are optional, and may not need them, but we're capturing them anyway just in case
	// currently anything more than the first 3 segments are ignored. More than that should be passed 
	// via post variables or get variables
	$post['ct_gateway'] = $gateway; 
	$post['ct_action']  = $extload->uri->segment(1); 
	$post['ct_option'] = $extload->uri->segment(2); 

	if (!$gateway)
	{
		die("No gateway was specified"); 
	}
	$extload->cartthrob_payments->set_gateway($gateway); 

	// conglomerate all of the query and post data
	$vars = array_merge($query_string, $post, $_POST, $get); 

	// only calling one specific method, so that it's not easy to just take over the system
	// if this method exists, it should handle its own security. 
	if (method_exists($extload->cartthrob_payments->gateway(), "extload"))
	{
		$extload->cartthrob_payments->gateway()->extload($vars);
	}
	else
	{
		die('Response method for gateway '.$gateway.' does not exist'); 
	}

