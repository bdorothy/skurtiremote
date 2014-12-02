<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Filter by search:title="foo"
 *
 * @package        mtt_search
 * @author         MyTopTeacher - MTT <contact@mytopteacher.com>
 * @link           http://mytopteacher.com/addons/mtt-search
 * @copyright      Copyright (c) 2014, Mtt
 */
class Mtt_search_filter_field_search extends Mtt_search_filter {

	/**
	 * Prefix
	 */
	private $_pfx = 'search:';

	/**
	 * Channel IDs
	 */
	private $_channel_ids = array();

	// --------------------------------------------------------------------

	/**
	 * Als for search:title="foo|bar" parameter
	 *
	 * @access     private
	 * @return     void
	 */
	public function filter($entry_ids)
	{
		// --------------------------------------
		// Check if search:title is there
		// --------------------------------------

		$params = $this->params->get_prefixed($this->_pfx, TRUE);

		// --------------------------------------
		// Don't do anything if nothing's there
		// --------------------------------------

		if (empty($params)) return $entry_ids;

		// --------------------------------------
		// Log it
		// --------------------------------------

		$this->_log('Applying '.__CLASS__);

		// --------------------------------------
		// Set channel IDs
		// --------------------------------------

		$this->_channel_ids = ee()->mtt_search_collection_model->get_channel_ids(
			$this->params->get('collection')
		);

		// --------------------------------------
		// Loop through search filters and prep queries accordingly
		// --------------------------------------

		$queries = array();

		foreach ($params AS $key => $val)
		{
			// Make sure value is prepped correctly with exact/exclude/require_all values
			$val = $this->params->prep($this->_pfx.$key, $val);

			// Search channel title
			if (in_array($key, array('title', 'url_title')))
			{
				// (URL) Title search
				$queries['channel_titles'][] = $this->_get_where($key, $val);
			}
			// Search grid or matrix cols
			elseif (strpos($key, ':'))
			{
				list($field_name, $col_name) = explode(':', $key, 2);

				// Skip invalid fields
				if ( ! ($field_id = $this->_get_field_id($field_name))) continue;

				$table = FALSE;

				// Make sure it's an omelette!
				if ($this->_is_grid_field($field_name) &&
					$col_id = $this->_get_grid_col_id($field_id, $col_name))
				{
					$table = 'channel_grid_field_'.$field_id;
					$field = 'col_id_'.$col_id;
				}
				elseif ($this->_is_matrix_field($field_id) &&
					$col_id = $this->_get_matrix_col_id($field_id, $col_name))
				{
					$table = 'matrix_data';
					$field = 'col_id_'.$col_id;
				}

				if ($table)
				{
					$queries[$table][] = $this->_get_where($field, $val);
				}
			}
			// Search channel fields
			elseif ($field_id = $this->_get_field_id($key))
			{
				// Regular fields
				$queries['channel_data'][] = $this->_get_where('field_id_'.$field_id, $val);
			}

			// Don't set it
			$this->params->forget[] = $this->_pfx.$key;
		}

		// --------------------------------------
		// Where now contains a list of clauses
		// --------------------------------------

		if (empty($queries)) return $entry_ids;

		// --------------------------------------
		// Query the lot!
		// --------------------------------------

		foreach ($queries AS $table => $wheres)
		{
			// Start this query
			ee()->db->distinct()->select('entry_id')->from($table);

			// Add wheres
			foreach ($wheres AS $sql)
			{
				ee()->db->where($sql);
			}

			// Limit by given entry ids?
			if ( ! empty($entry_ids))
			{
				ee()->db->where_in('entry_id', $entry_ids);
			}

			// Limit only for non-grid tables
			if (in_array($table, array('channel_titles', 'channel_data')))
			{
				// Limit by channel
				if ($this->_channel_ids)
				{
					ee()->db->where_in('channel_id', $this->_channel_ids);
				}

				// Limit by site
				if ($site_ids = $this->params->site_ids())
				{
					ee()->db->where_in('site_id', $site_ids);
				}
			}

			// Execute!
			$query = ee()->db->get();

			// Get entry IDs
			$entry_ids = mtt_flatten_results($query->result_array(), 'entry_id');
			$entry_ids = array_unique($entry_ids);

			// Return immediately when no results are there
			if (empty($entry_ids)) break;
		}

		return $entry_ids;
	}

	// --------------------------------------------------------------------

	/**
	 * Get WHERE clause for given field and parameter value
	 *
	 * @access     private
	 * @return     void
	 */
	public function _get_where($field, $val)
	{
		// Initiate some vars
		$exact = $all = $starts = $ends = FALSE;

		// Exact matches
		if (substr($val, 0, 1) == '=')
		{
			$val   = substr($val, 1);
			$exact = TRUE;
		}

		// Starts with matches
		if (substr($val, 0, 1) == '^')
		{
			$val    = substr($val, 1);
			$starts = TRUE;
		}

		// Ends with matches
		if (substr($val, -1) == '$')
		{
			$val  = rtrim($val, '$');
			$ends = TRUE;
		}

		// All items? -> && instead of |
		if (strpos($val, '&&') !== FALSE)
		{
			$all = TRUE;
			$val = str_replace('&&', '|', $val);
		}

		// Convert parameter to bool and array
		list($items, $in) = mtt_explode_param($val);

		// Init sql for where clause
		$sql = array();

		// Loop through each sub-item of the filter an create sub-clause
		foreach ($items AS $item)
		{
			// Convert IS_EMPTY constant to empty string
			$empty = ($item == 'IS_EMPTY');
			$item  = str_replace('IS_EMPTY', '', $item);

			// whole word? Regexp search
			if (substr($item, -2) == '\W')
			{
				$operand = $in ? 'REGEXP' : 'NOT REGEXP';
				$item = preg_quote(substr($item, 0, -2));
				$item = str_replace("'", "\'", $item);
				$item = "'[[:<:]]{$item}[[:>:]]'";
			}
			else
			{
				if (preg_match('/^([<>]=?)([\d\.]+)$/', $item, $match))
				{
					// Numeric operator!
					$operand = $match[1];
					$item    = $match[2];
				}
				elseif ($exact || $empty || ($starts && $ends))
				{
					// Use exact operand if empty or = was the first char in param
					$operand = $in ? '=' : '!=';
					$item = "'".ee()->db->escape_str($item)."'";
				}
				else
				{
					// Use like operand in all other cases
					$operand = $in ? 'LIKE' : 'NOT LIKE';
					$item = '%'.ee()->db->escape_like_str($item).'%';

					// Al for starts/ends with matching
					if ($starts) $item = ltrim($item, '%');
					if ($ends)   $item = rtrim($item, '%');

					$item = "'{$item}'";
				}
			}

			// Add sub-clause to this statement
			$sql[] = sprintf("(%s %s %s)", $field, $operand, $item);
		}

		// Inclusive or exclusive
		$andor = $all ? ' AND ' : ' OR ';

		// Get complete clause, with parenthesis and everything
		$where = (count($sql) == 1) ? $sql[0] : '('.implode($andor, $sql).')';

		return $where;
	}

	// --------------------------------------------------------------------

	/**
	 * Check whether given string is a grid field
	 */
	private function _is_grid_field($str)
	{
		$it = FALSE;

		if ($fields = mtt_get_cache('channel', 'grid_fields'))
		{
			$it = (bool) $this->_get_field_id($str, $fields);
		}

		return $it;
	}

	/**
	 * Check whether given string is a grid field
	 */
	private function _is_matrix_field($id)
	{
		$it = FALSE;

		if ($fields = mtt_get_cache('channel', 'pair_custom_fields'))
		{
			$it = ($this->_get_field_id($id, $fields) == 'matrix');
		}

		return $it;
	}

	// --------------------------------------------------------------------

	/**
	 * Check whether given string is a grid field
	 */
	private function _get_grid_col_id($field_id, $col_name)
	{
		$grid_cols = mtt_get_cache(MTT_SEARCH_PACKAGE, 'grid_cols');

		if ( ! isset($grid_cols[$field_id]))
		{
			$query = ee()->db->select('col_id, col_name')
			       ->from('grid_columns')
			       ->where('field_id', $field_id)
			       ->get();

			foreach ($query->result() AS $row)
			{
				$grid_cols[$field_id][$row->col_id] = $row->col_name;
			}

			mtt_set_cache(MTT_SEARCH_PACKAGE, 'grid_cols', $grid_cols);
		}

		return array_search($col_name, $grid_cols[$field_id]);
	}

	/**
	 * Check whether given string is a grid field
	 */
	private function _get_matrix_col_id($field_id, $col_name)
	{
		$matrix_cols = mtt_get_cache(MTT_SEARCH_PACKAGE, 'matrix_cols');

		if ( ! isset($matrix_cols[$field_id]))
		{
			$query = ee()->db->select('col_id, col_name')
			       ->from('matrix_cols')
			       ->where('field_id', $field_id)
			       ->get();

			foreach ($query->result() AS $row)
			{
				$matrix_cols[$field_id][$row->col_id] = $row->col_name;
			}

			mtt_set_cache(MTT_SEARCH_PACKAGE, 'matrix_cols', $matrix_cols);
		}

		return array_search($col_name, $matrix_cols[$field_id]);
	}

	// --------------------------------------------------------------------

	/**
	 * Results: remove rogue {mtt_search_search:...} vars
	 */
	public function results($query)
	{
		$this->_remove_rogue_vars($this->_pfx);
		return $query;
	}

}
// End of file lsf.field_search.php