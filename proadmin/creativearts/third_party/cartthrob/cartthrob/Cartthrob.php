<?php
/* change if you move the cartthrob directory
   or otherwise need to specify it explicitly */
//define('CARTTHROB_PATH', '/var/etc/etc/');
define('CARTTHROB_PATH', dirname(__FILE__).'/');

/* do not change */
define('CARTTHROB_CORE_PATH', CARTTHROB_PATH.'core/');
define('CARTTHROB_DRIVER_PATH', CARTTHROB_PATH.'drivers/');
define('CARTTHROB_PLUGIN_PATH', CARTTHROB_PATH.'plugins/');
define('CARTTHROB_SHIPPING_PLUGIN_PATH', CARTTHROB_PATH.'plugins/shipping/');
define('CARTTHROB_TAX_PLUGIN_PATH', CARTTHROB_PATH.'plugins/tax/');
define('CARTTHROB_PRICE_PLUGIN_PATH', CARTTHROB_PATH.'plugins/price/');
define('CARTTHROB_DISCOUNT_PLUGIN_PATH', CARTTHROB_PATH.'plugins/discount/');

require_once CARTTHROB_CORE_PATH.'Cartthrob_core.php';

/**
 * How to instantiate:
 * 
 * $cartthrob = Cartthrob_core::instance('your_driver_name', $params);
 *
 * Where $params is an array of parameters.
 */