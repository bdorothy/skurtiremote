<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Load CI model if it doesn't exist
if ( ! class_exists('CI_model'))
{
	load_class('Model', 'core');
}

/**
 * Mtt Search Model class
 *
 * @package        mtt_search
 * @author         MyTopTeacher <contact@mytopteacher.com>
 * @link           http://mytopteacher.com/addons/mtt-search
 * @copyright      Copyright (c) 2014, Mtt
 */
class Mtt_search_model extends CI_Model {

	// --------------------------------------------------------------------
	// PROPERTIES
	// --------------------------------------------------------------------

	/**
	 * Name of table
	 *
	 * @access      private
	 * @var         string
	 */
	private $_table;

	/**
	 * Name of primary key
	 *
	 * @access      private
	 * @var         string
	 */
	private $_pk;

	/**
	 * Other attributes of the table
	 *
	 * @access      private
	 * @var         array
	 */
	private $_attributes = array();

	/**
	 * Site id shortcut
	 *
	 * @access      protected
	 * @var         int
	 */
	protected $site_id;

	// --------------------------------------------------------------------
	// METHODS
	// --------------------------------------------------------------------

	/**
	 * PHP5 Constructor
	 *
	 * @return     void
	 */
	function __construct()
	{
		// Call parent constructor
		parent::__construct();

		// Set site id shortcut
		$this->site_id = ee()->config->item('site_id');
	}

	// --------------------------------------------------------------------

	/**
	 * Sets table, PK and attributes
	 *
	 * @access      protected
	 * @param       string    Table name
	 * @param       string    Primary Key name
	 * @param       array     Attributes
	 * @return      void
	 */
	protected function initialize($table, $pk, $attributes)
	{
		// Check table prefix
		$prefix = ee()->db->dbprefix;

		// Add prefix to table name if not there
		if (substr($table, 0, strlen($prefix)) != $prefix)
		{
			$table = $prefix.$table;
		}

		// Set the values
		$this->_table       = $table;
		$this->_pk          = $pk;
		$this->_attributes  = $attributes;
	}

	// --------------------------------------------------------------------

	/**
	 * Return table name
	 *
	 * @access      public
	 * @return      string
	 */
	public function table()
	{
		return $this->_table;
	}

	// --------------------------------------------------------------------

	/**
	 * Return primary key
	 *
	 * @access      public
	 * @return      string
	 */
	public function pk()
	{
		return $this->_pk;
	}

	// --------------------------------------------------------------------

	/**
	 * Return array of attributes, sans PK
	 *
	 * @access      public
	 * @return      array
	 */
	public function attributes()
	{
		return array_keys($this->_attributes);
	}

	// --------------------------------------------------------------------

	/**
	 * Return one record by primary key or attribute
	 *
	 * @access      public
	 * @param       int       id of the record to fetch
	 * @param       string    attribute to check
	 * @return      array
	 */
	public function get_one($id, $attr = FALSE)
	{
		if ($attr === FALSE) $attr = $this->_pk;

		return ee()->db->where($attr, $id)->get($this->_table)->row_array();
	}

	// --------------------------------------------------------------------

	/**
	 * Return multiple records
	 *
	 * @access      public
	 * @return      array
	 */
	public function get_all()
	{
		return ee()->db->get($this->_table)->result_array();
	}

	// --------------------------------------------------------------------

	/**
	 * Return an empty row for data initialisation
	 *
	 * @access      public
	 * @return      array
	 */
	public function empty_row()
	{
		$row = array_merge(array($this->_pk), $this->attributes());
		$row = array_combine($row, array_fill(0, count($row), ''));
		return $row;
	}

	// --------------------------------------------------------------------

	/**
	 * Insert record into DB
	 *
	 * @access      public
	 * @param       array     data to insert
	 * @return      int
	 */
	public function insert($data = array())
	{
		if (empty($data))
		{
			// loop through attributes to get posted data
			foreach ($this->attributes() AS $attr)
			{
				if (($val = ee()->input->post($attr)) !== FALSE)
				{
					$data[$attr] = $val;
				}
			}
		}

		// Insert data and return inserted id
		ee()->db->insert($this->_table, $data);
		return ee()->db->insert_id();
	}

	// --------------------------------------------------------------------

	/**
	 * Update record into DB
	 *
	 * @access      public
	 * @param       mixed
	 * @param       array     data to insert
	 * @return      void
	 */
	public function update($id, $data = array())
	{
		if (empty($data))
		{
			// loop through attributes to get posted data
			foreach ($this->attributes() AS $attr)
			{
				if (($val = ee()->input->post($attr)) !== FALSE)
				{
					$data[$attr] = $val;
				}
			}
		}

		$where = is_array($id) ? 'where_in' : 'where';

		// Update the table
		ee()->db->$where($this->_pk, $id);
		ee()->db->update($this->_table, $data);
	}

	// --------------------------------------------------------------------

	/**
	 * Delete record
	 *
	 * @access      public
	 * @param       array     data to insert
	 * @param       string    optional attribute to delete records by
	 * @return      void
	 */
	public function delete($id, $attr = FALSE)
	{
		if ( ! is_array($id))
		{
			$id = array($id);
		}

		if ($attr === FALSE) $attr = $this->_pk;

		ee()->db->where_in($attr, $id)->delete($this->_table);
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
		// Begin composing SQL query
		$sql = "CREATE TABLE IF NOT EXISTS {$this->_table} ( ";

		// Add primary key -- is it an array?
		if (is_array($this->_pk))
		{
			foreach ($this->_pk AS $key)
			{
				$sql .= "{$key} int(10) unsigned NOT NULL, ";
			}
		}
		else
		{
			$sql .= "{$this->_pk} int(10) unsigned NOT NULL AUTO_INCREMENT, ";
		}

		// add other attributes
		foreach ($this->_attributes AS $attr => $props)
		{
			$sql .= "{$attr} {$props}, ";
		}

		// Set PK
		$sql .= "PRIMARY KEY (".implode(',', (array) $this->_pk).")) ";

		// And character set
		$sql .= "CHARACTER SET utf8 COLLATE utf8_general_ci;";

		// Execute query
		ee()->db->query($sql);
	}

	// --------------------------------------------------------------------

	/**
	 * Uninstalls given table
	 *
	 * @access      public
	 * @return      void
	 */
	public function uninstall()
	{
		ee()->db->query("DROP TABLE IF EXISTS {$this->_table}");
	}

	// --------------------------------------------------------------------

}
// End of file model.mtt_search.php