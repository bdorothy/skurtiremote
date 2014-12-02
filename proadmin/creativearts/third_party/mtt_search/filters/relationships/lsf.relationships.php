<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Filter by relationships
 *
 * @package        mtt_search
 * @author         MyTopTeacher - MTT <contact@mytopteacher.com>
 * @link           http://mytopteacher.com/addons/mtt-search
 * @copyright      Copyright (c) 2014, Mtt
 */
class Mtt_search_filter_relationships extends Mtt_search_filter {

	/**
	 * Search parameters for (parents|children):field params and return set of ids that match it
	 *
	 * @access      public
	 * @return      void
	 */
	public function filter($entry_ids)
	{
		// --------------------------------------
		// Check prefixed parameters needed
		// --------------------------------------

		$rels = array_filter(array_merge(
			$this->params->get_prefixed('parent:'),
			$this->params->get_prefixed('child:')
		), 'mtt_param_is_numeric');

		// --------------------------------------
		// Don't do anything if nothing's there
		// --------------------------------------

		if (empty($rels)) return $entry_ids;

		// --------------------------------------
		// Log it
		// --------------------------------------

		$this->_log('Applying '.__CLASS__);

		// --------------------------------------
		// Loop through relationships
		// --------------------------------------

		foreach ($rels AS $key => $val)
		{
			// List out match
			list($type, $field) = explode(':', $key, 2);

			// Get the field id, skip if non-existent
			if ( ! ($field_id = $this->_get_field_id($field))) continue;

			// Prep the value
			$val = $this->params->prep($key, $val);

			// Get the parameter
			list($ids, $in) = mtt_explode_param($val);

			// Match all?
			$all = (bool) strpos($val, '&');

			// Init vars
			$rel_ids = $table = FALSE;
			$get_children = ($type == 'parent');

			// Native relationship field
			if ($this->_is_rel_field($field))
			{
				// Account for new EE rels
				$prefix = (version_compare(APP_VER, '2.6.0', '<')) ? 'rel_' : '';

				// Set the table & attributes
				$table  = 'relationships';
				$select = $get_children ? $prefix.'child_id' : $prefix.'parent_id';
				$where  = $get_children ? $prefix.'parent_id' : $prefix.'child_id';
			}
			elseif ($this->_is_playa_field($field_id))
			{
				// Set the table
				$table  = 'playa_relationships';
				$select = $get_children ? 'child_entry_id' : 'parent_entry_id';
				$where  = $get_children ? 'parent_entry_id' : 'child_entry_id';

				// Focus on specific field
				ee()->db->where('parent_field_id', $field_id);
			}

			// Execute query
			if ($table)
			{
				ee()->db->select($select.' AS entry_id')
				        ->from($table)
				        ->{$in ? 'where_in' : 'where_not_in'}($where, $ids);

				// Limit by already existing ids
				if ($entry_ids)
				{
					ee()->db->where_in($select, $entry_ids);
				}

				// Do the having-trick to account for *all* given entry ids
				if ($in && $all)
				{
					ee()->db->select('COUNT(*) AS num')
					        ->group_by($select)
					        ->having('num', count($ids));
				}

				// Execute query
				$query = ee()->db->get();

				// And get the entry ids
				$entry_ids = mtt_flatten_results($query->result_array(), 'entry_id');
				$entry_ids = array_unique($entry_ids);

				// Bail out if there aren't any matches
				if (is_array($entry_ids) && empty($entry_ids)) break;
			}
		}

		return $entry_ids;
	}

	/**
	 * Check whether given string is a relationship field
	 */
	private function _is_rel_field($str)
	{
		$it = FALSE;

		if ($fields = mtt_get_cache('channel', 'relationship_fields'))
		{
			$it = (bool) $this->_get_field_id($str, $fields);
		}

		return $it;
	}

	/**
	 * Check whether given field id is a playa field
	 */
	private function _is_playa_field($id)
	{
		$it = FALSE;

		if ($fields = mtt_get_cache('channel', 'pair_custom_fields'))
		{
			$it = (strpos($this->_get_field_id($id, $fields), 'playa') !== FALSE);
		}

		return $it;
	}

	/**
	 * Results: remove rogue {mtt_search_parent/child:...} vars
	 */
	public function results($query)
	{
		$this->_remove_rogue_vars(array('parent:', 'child:'));
		return $query;
	}

}