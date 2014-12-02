<?php

if ( ! defined('CT_ORDER_MANAGER'))
{
	define('CT_ORDER_MANAGER', '2.61');
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


$config['version'] =  CT_ORDER_MANAGER; 
$config['name'] = 'CartThrob Order Manager';
$config['cartthrob_order_manager_description'] = 'cartthrob_order_manager_description';
$config['nsm_addon_updater']['versions_xml'] = 'http://cartthrob.com/versions/cartthrob-order-manager';