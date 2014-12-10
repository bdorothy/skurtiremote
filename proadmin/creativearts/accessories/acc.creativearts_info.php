<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Creativearts - by Creativelab
 *
 * @package		Creativearts
 * @author		Creativelab Dev Team
 * @copyright	Copyright (c) 2003 - 2014, Creativelab, Inc.
 * @license		http://creativelab.com/creativearts/user-guide/license.html
 * @link		http://creativelab.com
 * @since		Version 2.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Creativearts Creativearts Info Accessory
 *
 * @package		Creativearts
 * @subpackage	Control Panel
 * @category	Accessories
 * @author		Creativelab Dev Team
 * @link		http://creativelab.com
 */
class Creativearts_info_acc {

	var $name			= 'Creativearts Info';
	var $id				= 'Creativearts_info';
	var $version		= '1.0';
	var $description	= 'Links and Information about Creativearts';
	var $sections		= array();

	/**
	 * Constructor
	 */
	function __construct()
	{
		$this->EE =& get_instance();
	}

	// --------------------------------------------------------------------

	/**
	 * Set Sections
	 *
	 * Set content for the accessory
	 *
	 * @access	public
	 * @return	void
	 */
	 function set_sections()
	{
		ee()->lang->loadfile('Creativearts_info');

		// localize Accessory display name
		$this->name = lang('Creativearts_info');

		// set the sections
		$this->sections[lang('resources')] = $this->_fetch_resources();
		$this->sections[lang('version_and_build')] = $this->_fetch_version();
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch Resources
	 *
	 * @access	public
	 * @return	string
	 */
	function _fetch_resources()
	{
		return '
		<ul>
			<li><a href="'.ee()->cp->masked_url('http://creativelab.com/creativearts/user-guide/').'">'.lang('documentation').'</a></li>
			<li><a href="'.ee()->cp->masked_url('http://creativelab.com/support/').'">'.lang('support_resources').'</a></li>
			<li><a href="'.ee()->cp->masked_url('https://store.creativelab.com/manage').'">'.lang('downloads').'</a></li>
		</ul>
		';
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch Version
	 *
	 * @access	public
	 * @return	string
	 */
	function _fetch_version()
	{
		ee()->load->library('el_pings');
		$details = ee()->el_pings->get_version_info();

		$download_url = ee()->cp->masked_url('https://store.creativelab.com/manage');

		if ( ! $details)
		{
			return str_replace(array('%v', '%b'), array(APP_VER, APP_BUILD), lang('error_getting_version'));
		}

		end($details);
		$latest_version = current($details);

		if ($latest_version[0] > APP_VER)
		{
			$instruct_url = ee()->cp->masked_url(ee()->config->item('doc_url').'installation/update.html');

			$str = '<p><strong>' . lang('version_update_available') . '</strong></p><br />';
			$str .= '<ul>';
			$str .= '<li>'.str_replace(array('%v', '%b'), array($latest_version[0], $latest_version[1]), lang('current_version')).'</li>';
			$str .= '<li>'.str_replace(array('%v', '%b'), array(APP_VER, APP_BUILD), lang('installed_version')).'</li>';
			$str .= '</ul>';
			$str .= '<br /><p>'.NL.str_replace(array('%d', '%i'), array($download_url, $instruct_url), lang('version_update_inst')).'</p>';

			return $str;
		}
/*
		elseif($latest_version[1] > APP_BUILD)
		{
			$instruct_url = ee()->cp->masked_url(ee()->config->item('doc_url').'installation/update_build.html');

			$str = '<p><strong>' . lang('build_update_available') . '</strong></p><br />';
			$str .= '<ul>';
			$str .= '<li>'.str_replace(array('%v', '%b'), array($latest_version[0], $latest_version[1]), lang('current_version')).'</li>';
			$str .= '<li>'.str_replace(array('%v', '%b'), array(APP_VER, APP_BUILD), lang('installed_version')).'</li>';
			$str .= '</ul>';
			$str .= '<br /><p>'.NL.str_replace(array('%d', '%i'), array($download_url, $instruct_url), lang('build_update_inst')).'</p>';

			return $str;
		}
*/

		return str_replace(array('%v', '%b'), array(APP_VER, APP_BUILD), lang('running_current'));
	}

}
// END CLASS

/* End of file acc.Creativearts_info.php */
/* Location: ./system/creativearts/accessories/acc.Creativearts_info.php */