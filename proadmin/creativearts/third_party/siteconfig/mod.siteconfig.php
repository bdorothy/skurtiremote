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
 * Site Config Front End File
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Liveshop
 * @link		http://www.sixth.co.in
 */

class Siteconfig {
	
		/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();	
	}
	
	// ----------------------------------------------------------------

	public function get(){
	$this->EE->load->add_package_path(PATH_THIRD.'siteconfig/');		
		$this->EE->load->library('siteconfig');
		$item = $this->EE->TMPL->fetch_param('item');	
		$output = $this->EE->siteconfig->item($item);
		
		$remove_html = $this->EE->TMPL->fetch_param('remove_html'); // if asked to remove html (useful for meta)
		if($remove_html == "yes"){
		$output = preg_replace("/\r|\n/", "", $output);					// remove all returns from text
		$output = preg_replace("/<br \/>|<br>/", "\r\n", $output);		// convert soft returns
		$output = preg_replace("/<\/p>/", "\r\n\r\n", $output);			// convert paragraphs returns
		$output = preg_replace('/<[^>]*>/', '', $output);					// remove all tags
		$output = trim($output);
		return $output;
		}else
		{
			return $output;
		}
		
	}
	
}
/* End of file mod.siteconfig.php */
/* Location: /system/expressionengine/third_party/siteconfig/mod.siteconfig.php */