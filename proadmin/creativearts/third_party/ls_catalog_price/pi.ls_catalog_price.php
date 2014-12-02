<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2003 - 2011, EllisLab, Inc.
 * @license		http://expressionengine.com/user_guide/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.0
 * @filesource
 */
 
// ------------------------------------------------------------------------

/**
 * LS Catalog Price Plugin
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Plugin
 * @author		Sixthsense
 * @link		http://www.sixth.co.in
 */

$plugin_info = array(
	'pi_name'		=> 'LS Catalog Price',
	'pi_version'	=> '3.0',
	'pi_author'		=> 'Sixthsense',
	'pi_author_url'	=> 'http://www.sixth.co.in',
	'pi_description'=> 'Calculates the catalog pricing as required',
	'pi_usage'		=> Ls_catalog_price::usage()
);


class Ls_catalog_price {

	public $return_data;
    
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
	}
	
	// This is the base price of the catalog
	public function selling_price(){
	
	}
	
	
	// The price for individual calculated products based on wholesale or retail price set in siteconfig
	public function catalog_actual_product_price(){
	}
	
	
	public function discount_schemes(){
	// coming soon
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Plugin Usage
	 */

	public static function usage()
	{
		ob_start();
?>

{exp:catalog_price:selling_price}
{exp:catalog_price:catalog_actual_product_price}


{exp:catalog_price:discount_schemes}
<?php
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}
}


/* End of file pi.ls_catalog_price.php */
/* Location: /system/expressionengine/third_party/ls_catalog_price/pi.ls_catalog_price.php */