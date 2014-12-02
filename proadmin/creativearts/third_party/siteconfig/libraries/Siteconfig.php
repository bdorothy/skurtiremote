<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CreativeArts - by LiveStore
 *
 * @package		CreativeArts
 * @author		LiveStore Dev Team
 * @copyright	Copyright (c) 2003 - 2012, LiveStore, Inc.
 * @license		http://creativearts.com/user_guide/license.html
 * @link		http://creativearts.com
 * @since		Version 2.0
 */
 
// ------------------------------------------------------------------------

/**
 * CreativeArts Stats Library
 *
 * @package		CreativeArts
 * @subpackage	Core
 * @category	Libraries
 * @author		LiveStore Dev Team
 * @link		http://creativearts.com
 */

class EE_Siteconfig {

// --------------------------------------------------------------------	
	
	/**
	 * Class Constructor
	 */
	function __construct()
	{
		$this->EE =& get_instance();

		if ($this->EE->db->cache_on === TRUE)
		{
			$this->EE->db->cache_off();
			$this->cache_off = TRUE;
		}
	}

	// --------------------------------------------------------------------
	
	function item($item){	
	$this->EE->db->select('settings');
	$this->EE->db->where('site_id',$this->EE->config->item('site_id'));
	$query = $this->EE->db->get('siteconfig');
	if ($query->num_rows() > 0)
	{
	$row = $query->row();	
	$settings	 =  $row->settings;
	$settings = unserialize(base64_decode($settings));
	return $settings[$item];
	}
	} 
	

}