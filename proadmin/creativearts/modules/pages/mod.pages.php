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
 * Creativearts Pages Module
 *
 * @package		Creativearts
 * @subpackage	Modules
 * @category	Modules
 * @author		Creativelab Dev Team
 * @link		http://creativelab.com
 */
class Pages {

	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function Pages()
	{
		// Make a local reference to the Creativearts super object
		$this->EE =& get_instance();
	}

	// --------------------------------------------------------------------

	/**
	 * Output Javascript
	 *
	 * Outputs Javascript files, triggered most commonly by an action request,
	 * but also available as a tag if desired.
	 *
	 * @access	public
	 * @return	string
	 */
	function load_site_pages()
	{
        $sites			= ee()->TMPL->fetch_param('site', '');
		$current_site	= ee()->config->item('site_short_name');

		// Always include the current site

		$site_names = explode('|', $sites);

		if ( ! in_array($current_site, $site_names))
		{
			$site_names[] = $current_site;
		}

		// Fetch all pages

		ee()->db->select('site_pages, site_name, site_id');
		ee()->db->where_in('site_name', $site_names);
		$query = ee()->db->get('sites');

		$new_pages = array();

		if ($query->num_rows() > 0)
		{
			foreach($query->result_array() as $row)
			{
				$site_pages = unserialize(base64_decode($row['site_pages']));

				if (is_array($site_pages))
				{
					$new_pages += $site_pages;
				}
			}
		}

		// Update config

		ee()->config->set_item('site_pages', $new_pages);

		return '';
	}
}
// End Pages Class

/* End of file mod.pages.php */
/* Location: ./system/creativearts/modules/pages/mod.pages.php */