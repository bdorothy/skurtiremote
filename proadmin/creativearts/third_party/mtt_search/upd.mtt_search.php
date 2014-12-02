<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// include base class
if ( ! class_exists('Mtt_search_base'))
{
	require_once(PATH_THIRD.'mtt_search/base.mtt_search.php');
}

/**
 * Mtt Search Update class
 *
 * @package        mtt_search
 * @author         MyTopTeacher <contact@mytopteacher.com>
 * @link           http://mytopteacher.com/addons/mtt-search
 * @copyright      Copyright (c) 2014, Mtt
 */
class Mtt_search_upd extends Mtt_search_base {

	// --------------------------------------------------------------------
	// PROPERTIES
	// --------------------------------------------------------------------

	/**
	 * Actions used
	 *
	 * @access      private
	 * @var         array
	 */
	private $actions = array(
		array('Mtt_search', 'catch_search'),
		array('Mtt_search', 'build_index'),
		array('Mtt_search_mcp', 'build_index'),
		array('Mtt_search', 'save_search')
	);

	/**
	 * Hooks used
	 *
	 * @access      private
	 * @var         array
	 */
	private $hooks = array(
		'entry_submission_end',
		'delete_entries_loop',
		'channel_entries_query_result',
		'category_save',
		'category_delete',
		'custom_field_modify_data'
	);

	// --------------------------------------------------------------------
	// METHODS
	// --------------------------------------------------------------------

	/**
	 * Install the module
	 *
	 * @access      public
	 * @return      bool
	 */
	public function install()
	{
		// --------------------------------------
		// Install tables
		// --------------------------------------

		foreach ($this->models AS $model)
		{
			ee()->$model->install();
		}

		// --------------------------------------
		// Add row to modules table
		// --------------------------------------

		ee()->db->insert('modules', array(
			'module_name'    => $this->class_name,
			'module_version' => $this->version,
			'has_cp_backend' => 'y'
		));

		// --------------------------------------
		// Add rows to action table
		// --------------------------------------

		foreach ($this->actions AS $row)
		{
			$this->_add_action($row);
		}

		// --------------------------------------
		// Add rows to extensions table
		// --------------------------------------

		foreach ($this->hooks AS $hook)
		{
			$this->_add_hook($hook);
		}

		// --------------------------------------

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Uninstall the module
	 *
	 * @return	bool
	 */
	public function uninstall()
	{
		// --------------------------------------
		// get module id
		// --------------------------------------

		$query = ee()->db->select('module_id')
		       ->from('modules')
		       ->where('module_name', $this->class_name)
		       ->get();

		// --------------------------------------
		// remove references from module_member_groups
		// --------------------------------------

		ee()->db->where('module_id', $query->row('module_id'));
		ee()->db->delete('module_member_groups');

		// --------------------------------------
		// remove references from modules
		// --------------------------------------

		ee()->db->where('module_name', $this->class_name);
		ee()->db->delete('modules');

		// --------------------------------------
		// remove references from actions
		// --------------------------------------

		ee()->db->where_in('class', array($this->class_name, $this->class_name.'_mcp'));
		ee()->db->delete('actions');

		// --------------------------------------
		// remove references from extensions
		// --------------------------------------

		ee()->db->where('class', $this->class_name.'_ext');
		ee()->db->delete('extensions');

		// --------------------------------------
		// Uninstall tables
		// --------------------------------------

		foreach ($this->models AS $model)
		{
			ee()->$model->uninstall();
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Update the module
	 *
	 * @return	bool
	 */
	public function update($current = '')
	{
		// --------------------------------------
		// Same version? A-okay, daddy-o!
		// --------------------------------------

		if ($current == '' OR version_compare($current, $this->version) === 0)
		{
			return FALSE;
		}

		// --------------------------------------
		// Update to 1.2.0
		// --------------------------------------

		if (version_compare($current, '1.2.0', '<'))
		{
			ee()->mtt_search_replace_log_model->install();
		}

		// --------------------------------------
		// Update to 2.0.0
		// --------------------------------------

		if (version_compare($current, '2.0.0', '<'))
		{
			// Insert another action
			$this->_add_action($this->actions[2]);

			// Change hook entry_submission_absolute_end to entry_submission_end
			// so the API triggers it
			ee()->db->where('class', $this->class_name.'_ext');
			ee()->db->where('hook', 'entry_submission_absolute_end');
			ee()->db->update('extensions', array(
				'method' => 'entry_submission_end',
				'hook'   => 'entry_submission_end'
			));
		}

		// --------------------------------------
		// Update to 2.1.0
		// --------------------------------------

		if (version_compare($current, '2.1.0', '<'))
		{
			$this->_v210();
		}

		// --------------------------------------
		// Update to 2.4.0
		// --------------------------------------

		if (version_compare($current, '2.4.0', '<'))
		{
			$this->_v240();
		}

		// --------------------------------------
		// Update to 3.0.0
		// --------------------------------------

		if (version_compare($current, '3.0.0', '<'))
		{
			$this->_v300();
		}

		// --------------------------------------
		// Update to 3.0.2
		// --------------------------------------

		if (version_compare($current, '3.0.2', '<'))
		{
			$this->_v302();
		}

		// --------------------------------------
		// Update to 3.2.0
		// --------------------------------------

		if (version_compare($current, '3.1.4', '<'))
		{
			$this->_v314();
		}

		// --------------------------------------
		// Update extension version
		// --------------------------------------

		ee()->db->where('class', $this->class_name.'_ext')
		        ->update('extensions', array('version' => $this->version));

		// Return TRUE to update version number in DB
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Add action to actions table
	 *
	 * @access     private
	 * @param      array
	 * @return     void
	 */
	private function _add_action($array)
	{
		list($class, $method) = $array;

		ee()->db->insert('actions', array(
			'class'  => $class,
			'method' => $method
		));
	}

	/**
	 * Add hook to extensions table
	 *
	 * @access     private
	 * @param      string
	 * @return     void
	 */
	private function _add_hook($hook)
	{
		ee()->db->insert('extensions', array(
			'class'     => $this->class_name.'_ext',
			'method'    => $hook,
			'hook'      => $hook,
			'priority'  => ($hook == $this->hooks[0] ? 101 : 10),
			'version'   => $this->version,
			'enabled'   => 'y',
			'settings'  => serialize(ee()->mtt_search_settings->get())
		));
	}

	// --------------------------------------------------------------------

	/**
	 * Update routines for version 2.1.0
	 *
	 * @access     private
	 * @return     void
	 */
	private function _v210()
	{
		// Fields to add to the DB
		$fields = array(
			'modifier' => 'decimal(2,1) unsigned NOT NULL default 1.0',
			'excerpt'  => 'int(6) unsigned NOT NULL default 0'
		);

		// Template query
		$tmpl = 'ALTER TABLE `%s` ADD `%s` %s AFTER `collection_label`';
		$tbl = ee()->mtt_search_collection_model->table();

		// Add fields
		foreach ($fields AS $field => $properties)
		{
			ee()->db->query(sprintf($tmpl, $tbl, $field, $properties));
		}

		// Get the collections and re-do the settings
		foreach (ee()->mtt_search_collection_model->get_all() AS $row)
		{
			// Initiate data array
			$data = array();

			// Decode the settings
			$settings = mtt_search_decode($row['settings'], FALSE);

			// Set new property values
			$data['modifier'] = (float) (isset($settings['modifier']) ? $settings['modifier'] : 1.0);
			$data['excerpt']  = (int) (isset($settings['excerpt']) ? $settings['excerpt'] : 0);

			// Remove these properties from settings
			unset($settings['modifier'], $settings['excerpt']);

			// filter it
			$settings = array_filter($settings);

			// Encode the new settings for DB usage
			$data['settings'] = mtt_search_encode($settings, FALSE);

			// Update row
			ee()->mtt_search_collection_model->update($row['collection_id'], $data);
		}
	}

	/**
	 * Update routines for version 2.4.0
	 *
	 * @access     private
	 * @return     void
	 */
	private function _v240()
	{
		// Register category hooks
		$this->_add_hook($this->hooks[3]);
		$this->_add_hook($this->hooks[4]);
	}

	/**
	 * Update routines for version 3.0.0
	 *
	 * @access     private
	 * @return     void
	 */
	private function _v300()
	{
		// Install Groups and Shortcuts table
		ee()->mtt_search_group_model->install();
		ee()->mtt_search_shortcut_model->install();

		// Insert another action
		$this->_add_action($this->actions[3]);
	}

	/**
	 * Update routines for version 3.0.2
	 *
	 * @access     private
	 * @return     void
	 */
	private function _v302()
	{
		// Increase priority number for first hook
		ee()->db->where('class', $this->class_name.'_ext')
		        ->where('hook', $this->hooks[0])
		        ->update('extensions', array('priority' => '101'));
	}

	/**
	 * Update routines for version 3.1.4
	 *
	 * @access     private
	 * @return     void
	 */
	private function _v314()
	{
		// Register channel fields hooks
		$this->_add_hook($this->hooks[5]);
	}

} // End class

/* End of file upd.mtt_search.php */