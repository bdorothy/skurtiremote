<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// include super model
if ( ! class_exists('Mtt_search_model'))
{
	require_once(PATH_THIRD.'mtt_search/model.mtt_search.php');
}

/**
 * Mtt Search Replace Log Model class
 *
 * @package        mtt_search
 * @author         MyTopTeacher <contact@mytopteacher.com>
 * @link           http://mytopteacher.com/addons/mtt-search
 * @copyright      Copyright (c) 2014, Mtt
 */
class Mtt_search_replace_log_model extends Mtt_search_model {

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
			'mtt_search_replace_log',
			'log_id',
			array(
				'site_id'      => 'int(4) unsigned NOT NULL',
				'member_id'    => 'int(10) unsigned NOT NULL',
				'replace_date' => 'int(10) unsigned NOT NULL',
				'keywords'     => 'varchar(150) NOT NULL',
				'replacement'  => 'varchar(150) NOT NULL',
				'fields'       => 'TEXT NOT NULL',
				'entries'      => 'TEXT NOT NULL'
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
		ee()->db->query("ALTER TABLE {$this->table()} ADD INDEX (`site_id`)");
	}

	// --------------------------------------------------------------------

	/**
	 * Prune rows
	 *
	 * @access      public
	 * @param       int
	 * @param       int
	 * @return      void
	 */
	public function prune($site_id, $keep = 500)
	{
		// Get first id after keep-threshold
		$query = ee()->db->select($this->pk())
		       ->from($this->table())
		       ->where('site_id', $site_id)
		       ->order_by($this->pk(), 'desc')
		       ->limit(1, $keep)
		       ->get();

		// That's the one
		$id = $query->row($this->pk());

		// If the id is larger than the amount to keep,
		// go ahead and prune...
		if ($id && $id > $keep)
		{
			ee()->db->where($this->pk(). ' <=', $id);
			ee()->db->where('site_id', $site_id);
			ee()->db->delete($this->table());
		}
	}

	// --------------------------------------------------------------

} // End class

/* End of file Mtt_search_replace_log_model.php */