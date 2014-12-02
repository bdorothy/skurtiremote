<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// include super model
if ( ! class_exists('Mtt_search_model'))
{
	require_once(PATH_THIRD.'mtt_search/model.mtt_search.php');
}

/**
 * Mtt Search Group Model class
 *
 * @package        mtt_search
 * @author         MyTopTeacher <contact@mytopteacher.com>
 * @link           http://mytopteacher.com/addons/mtt-search
 * @copyright      Copyright (c) 2014, Mtt
 */
class Mtt_search_group_model extends Mtt_search_model {

	// --------------------------------------------------------------------
	// METHODS
	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @access      public
	 * @return      void
	 */
	function __construct()
	{
		// Call parent constructor
		parent::__construct();

		// Initialize this model
		$this->initialize(
			'mtt_search_groups',
			'group_id',
			array(
				'site_id'     => 'int(4) unsigned NOT NULL',
				'group_label' => 'varchar(150)'
			)
		);
	}

	// --------------------------------------------------------------------

	/**
	 * Installs given table
	 *
	 * @access      public
	 * @return      void
	 */
	public function install()
	{
		// Call parent install
		parent::install();

		// Add indexes to table
		foreach (array('site_id') AS $key)
		{
			ee()->db->query("ALTER TABLE {$this->table()} ADD INDEX (`{$key}`)");
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Get site groups ordered by name
	 *
	 * @access      public
	 * @return      void
	 */
	public function get_by_site($site_id = NULL)
	{
		static $cache = array();

		// Get current site id
		if (empty($site_id)) $site_id = $this->site_id;

		if ( ! isset($cache[$site_id]))
		{
			ee()->db->where('site_id', $site_id);
			ee()->db->order_by('group_label');
			$cache[$site_id] = $this->get_all();
		}

		return $cache[$site_id];
	}

} // End class

/* End of file Mtt_search_group_model.php */