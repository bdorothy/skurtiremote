<?php
if ( ! defined('CARTTHROB_VERSION'))
{
	define('CARTTHROB_VERSION', '2.61');
}

if (defined('PATH_THEMES'))
{
	if ( ! defined('PATH_THIRD_THEMES'))
	{
		define('PATH_THIRD_THEMES', PATH_THEMES.'third_party/');
	}
	
	if ( ! defined('URL_THIRD_THEMES'))
	{
		define('URL_THIRD_THEMES', get_instance()->config->slash_item('theme_folder_url').'third_party/');
	}
}

if ( ! defined('CARTTHROB_HELPSPOT_URL'))
{
	define('CARTTHROB_HELPSPOT_URL', 'https://mightyliverobot.com/support/api/index.php');
}

$config['name'] = 'CartThrob';
$config['version'] = CARTTHROB_VERSION;
$config['nsm_addon_updater']['versions_xml'] = 'http://livecart.com/versions/cartthrob2';