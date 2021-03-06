<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
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
 * Creativearts Site Model
 *
 * @package		Creativearts
 * @subpackage	Core
 * @category	Model
 * @author		Creativelab Dev Team
 * @link		http://creativelab.com
 */
class Site_model extends CI_Model {

	/**
     * Get an array of all available site ids.
	 *
	 * @return array An array of integer site ids in no particular order.
	 */
	public function get_site_ids()
	{
		if($this->config->item('multiple_sites_enabled') != 'y')
		{
			return array(1);
		}

		$site_query = $this->db->select('site_id')
				->get('sites');

		$site_ids = array();
		foreach($site_query->result_array() as $result)
		{
			$site_ids[] = $result['site_id'];
		}
		return $site_ids;
	}

	/**
	 * Returns all info on a site, or all sites
	 *
	 * @access	public
	 * @param	id		Site Id
	 * @return	object
	 */
	public function get_site($site_id = '')
	{
		if ($site_id != '')
		{
			$this->db->where('site_id', $site_id);
		}

		$this->db->order_by('site_label');

		return $this->db->get('sites');
	}

	// --------------------------------------------------------------------

	/**
	 * Returns the site system preferences
	 *
	 * @access	public
	 * @param	id		Site Id
	 * @return	object
	 */
	public function get_site_system_preferences($site_id = '')
	{
		if ($site_id != '')
		{
			$this->db->where('site_id', $site_id);
		}

		$this->db->select('site_id, site_system_preferences');

		return $this->db->get('sites');
	}

	// --------------------------------------------------------------------

	/**
	 * Updates the site system preferences
	 *
	 * @access	public
	 * @param	Array	Preferences
	 * @param	id		Site Id
	 * @return	void
	 */
	public function update_site_system_preferences($prefs, $site_id = '')
	{
		if ($site_id != '')
		{
			$this->db->where('site_id', $site_id);
		}

		$this->db->set('site_system_preferences', base64_encode(serialize($prefs)));
		$this->db->update('sites');
	}

}

/* End of site_model.php */
/* Location: ./system/creativearts/models/site_model.php */
