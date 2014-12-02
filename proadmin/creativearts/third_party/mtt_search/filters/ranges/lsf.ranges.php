<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Filter by search:title="foo"
 *
 * @package        mtt_search
 * @author         MyTopTeacher - MTT <contact@mytopteacher.com>
 * @link           http://mytopteacher.com/addons/mtt-search
 * @copyright      Copyright (c) 2014, Mtt
 */
class Mtt_search_filter_ranges extends Mtt_search_filter {

	/**
	 * Separator character for ranges
	 */
	private $_sep = '|';

	/**
	 * Native date fields
	 */
	private $_native_dates = array(
		'entry_date',
		'expiration_date',
		'comment_expiration_date',
		'recent_comment_date',
		'edit_date' // Which is in YYYYMMDDHHMMSS format. Obviously.
	);

	/**
	 * Native numeric fields
	 */
	private $_native_numeric = array(
		'view_count_one',
		'view_count_two',
		'view_count_thee',
		'view_count_four',
		'comment_total'
	);

	// --------------------------------------------------------------------
	// METHODS
	// --------------------------------------------------------------------

	/**
	 * Search parameters for range:field params and return set of ids that match it
	 *
	 * @access      private
	 * @return      void
	 */
	public function filter($entry_ids)
	{
		// --------------------------------------
		// Get ranges params
		// --------------------------------------

		$params = array_filter(array_merge(
			$this->params->get_prefixed('r:'),
			$this->params->get_prefixed('rf:'),
			$this->params->get_prefixed('rt:')
		), 'mtt_not_empty');

		// --------------------------------------
		// Don't do anything if nothing's there
		// --------------------------------------

		if (empty($params)) return $entry_ids;

		// --------------------------------------
		// Log it
		// --------------------------------------

		$this->_log('Applying '.__CLASS__);

		// --------------------------------------
		// Validate params
		// --------------------------------------

		$ranges = array();

		foreach ($params AS $key => $val)
		{
			// Split key
			list($prefix, $field) = explode(':', $key, 2);

			// Skip invalid fields
			if ( ! ($field_name = $this->_get_field_name($field))) continue;

			// Init this range
			$from = $to = NULL;

			// Check prefix and get from/to values accordingly
			switch ($prefix)
			{
				case 'r':
					// Fallback to ;
					$char = strpos($val, ';') ? ';' : $this->_sep;
					if (strpos($val, $char)) list($from, $to) = explode($char, $val, 2);
				break;

				case 'rf':
					$from = $val;
				break;

				case 'rt':
					$to = $val;
				break;
			}

			// Make sure the values are numeric
			$from = $this->_validate_value($from, $field);
			$to   = $this->_validate_value($to, $field);

			// If both are invalid, skip it
			if (is_null($from) && is_null($to)) continue;

			// Add from value to field
			if ( ! is_null($from))
			{
				$ranges[$field]['from'] = $from;
			}

			// Add to value to field
			if ( ! is_null($to))
			{
				$ranges[$field]['to'] = $to;
			}

			// Add sql field name to field
			$ranges[$field]['field'] = $field_name;
		}

		// --------------------------------------
		// No ranges, bail out
		// --------------------------------------

		if (empty($ranges))
		{
			$this->_log('No valid ranges found');
			return $entry_ids;
		}

		// --------------------------------------
		// Get channel IDs before starting the query
		// --------------------------------------

		$channel_ids = ee()->mtt_search_collection_model->get_channel_ids($this->params->get('collection'));

		// --------------------------------------
		// Start query
		// --------------------------------------

		ee()->db->select('t.entry_id')
		        ->from('channel_titles t')
		        ->join('channel_data d', 't.entry_id = d.entry_id');

		// --------------------------------------
		// Limit by channel ids?
		// --------------------------------------

		if ($channel_ids)
		{
			ee()->db->where_in('t.channel_id', $channel_ids);
		}

		// --------------------------------------
		// Limit by site ids?
		// --------------------------------------

		if ($site_ids = $this->params->site_ids())
		{
			ee()->db->where_in('t.site_id', $site_ids);
		}

		// --------------------------------------
		// Limit by given entry ids?
		// --------------------------------------

		if ( ! empty($entry_ids))
		{
			ee()->db->where_in('t.entry_id', $entry_ids);
		}

		// --------------------------------------
		// And filter by the ranges
		// --------------------------------------

		foreach ($ranges AS $field => $range)
		{
			// Exclude values from range?
			$exclude = $this->params->in_param("range:{$field}", 'exclude');

			// Prefix the field name
			$sql_field = ($this->_is_native_field($range['field']) ? 't.' : 'd.') . $range['field'];

			// Limit by Greater Than option
			if (isset($range['from']))
			{
				$range['from'] = intval($range['from']);
				$gt = ($exclude || $this->params->in_param("range-from:{$field}", 'exclude'))
					? ' >'
					: ' >=';

				ee()->db->where($sql_field.$gt, $range['from']);
			}

			// Limit by Lesser Than option
			if (isset($range['to']))
			{
				$range['to'] = intval($range['to']);
				$lt = ($exclude || $this->params->in_param("range-to:{$field}", 'exclude'))
					? ' <'
					: ' <=';

				ee()->db->where($sql_field.$lt, $range['to']);
			}
		}

		// --------------------------------------
		// Execute!
		// --------------------------------------

		$query = ee()->db->get();

		// --------------------------------------
		// And get the entry ids
		// --------------------------------------

		$entry_ids = mtt_flatten_results($query->result_array(), 'entry_id');
		$entry_ids = array_unique($entry_ids);

		return $entry_ids;
	}

	// --------------------------------------------------------------------

	/**
	 * Get field name
	 */
	private function _get_field_name($field)
	{
		if ($this->_is_native_field($field))
		{
			return $field;
		}
		elseif ($id = $this->_get_field_id($field))
		{
			return 'field_id_' . $id;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Validate range value
	 */
	private function _validate_value($val, $field)
	{
		if (is_numeric($val) || is_null($val))
		{
			return $val;
		}
		elseif ($this->_is_date_field($field))
		{
			// @todo: add support for EE < 2.6?
			ee()->load->library('localize');
			$val = ee()->localize->string_to_timestamp($val);

			// Oh, edit_date, you so cray-cray!
			if ($field == 'edit_date')
			{
				$val = date('YmdHis', $val);
			}

			return $val;
		}
		else
		{
			return NULL;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Check whether given string is a date field
	 */
	private function _is_date_field($str)
	{
		$it = FALSE;

		if (in_array($str, $this->_native_dates))
		{
			$it = TRUE;
		}
		elseif ($fields = mtt_get_cache('channel', 'date_fields'))
		{
			$it = (bool) $this->_get_field_id($str, $fields);
		}

		return $it;
	}

	/**
	 * Check if field is a native field
	 */
	private function _is_native_field($str)
	{
		return in_array($str, array_merge($this->_native_dates, $this->_native_numeric));
	}

	// --------------------------------------------------------------------

	/**
	 * Results: remove rogue {mtt_search_range...:...} vars
	 */
	public function results($query)
	{
		$this->_remove_rogue_vars(array('range:', 'range-from:', 'range-to:'));
		return $query;
	}

}
// End of file lsf.ranges.php