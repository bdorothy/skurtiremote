<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Mtt Search Index class
 *
 * @package        mtt_search
 * @author         MyTopTeacher - MTT <contact@mytopteacher.com>
 * @link           http://mytopteacher.com/addons/mtt-search
 * @copyright      Copyright (c) 2014, Mtt
 */
class Mtt_search_index {

	// --------------------------------------------------------------------

	/**
	 * Build collection index
	 *
	 * @access      protected
	 * @return      array
	 */
	public function build($collection_id = FALSE, $entry_ids = FALSE, $start = FALSE)
	{
		// --------------------------------------
		// Check for collection_id or entry_id
		// --------------------------------------

		$collection_id = ($collection_id !== FALSE) ? $collection_id : ee()->input->get_post('collection_id');
		$entry_ids     = ($entry_ids !== FALSE) ? $entry_ids : ee()->input->get_post('entry_id');

		// --------------------------------------
		// Either collection_id or entry_id or both must be given
		// --------------------------------------

		if ( ! ($collection_id || $entry_ids)) show_error(ee()->lang->line('not_authorized'));

		// --------------------------------------
		// Start building query to get collection details
		// --------------------------------------

		ee()->db->select('lsc.collection_id, lsc.channel_id, lsc.settings, lsc.site_id');
		ee()->db->from('mtt_search_collections lsc');

		// --------------------------------------
		// If there's a collection id, limit query by that one
		// --------------------------------------

		if ($collection_id)
		{
			ee()->db->where('lsc.collection_id', $collection_id);
		}

		// --------------------------------------
		// If there's an entry_id, limit query by those
		// --------------------------------------

		if ($entry_ids)
		{
			// Force array
			if ( ! is_array($entry_ids))
			{
				$entry_ids = preg_split('/\D+/', $entry_ids);
			}

			// Get collections for given entries
			ee()->db->select('GROUP_CONCAT(ct.entry_id) AS entries');
			ee()->db->join('channel_titles ct', 'lsc.channel_id = ct.channel_id');
			ee()->db->where_in('entry_id', $entry_ids);
			ee()->db->group_by('lsc.collection_id');
		}

		// --------------------------------------
		// Execute query and get results. Bail out if none
		// --------------------------------------

		if ( ! ($collections = ee()->db->get()->result_array()))
		{
			return FALSE;
		}

		$collections = mtt_associate_results($collections, 'collection_id');
		$channel_ids = array_unique(mtt_flatten_results($collections, 'channel_id'));

		// --------------------------------------
		// Get batch size
		// --------------------------------------

		$batch_size = ee()->mtt_search_settings->get('batch_size');

		// --------------------------------------
		// Get total number of entries that need to be indexed
		// --------------------------------------

		if ($entry_ids)
		{
			$num_entries = count($entry_ids);
		}
		else
		{
			ee()->db->where_in('channel_id', $channel_ids);
			$num_entries = ee()->db->count_all_results('channel_titles');
		}

		// --------------------------------------
		// Get weighted field settings only, keep track of field ids
		// --------------------------------------

		$fields  = array();
		$entries = array();

		foreach ($collections AS &$col)
		{
			$col['settings'] = array_filter(mtt_search_decode($col['settings'], FALSE));

			// Add field ids to fields array
			$fields = array_merge($fields, array_keys($col['settings']));

			if (isset($col['entries']))
			{
				foreach (explode(',', $col['entries']) AS $eid)
				{
					$entries[$eid][] = $col['collection_id'];
				}
			}
		}

		// Get rid of duplicate field ids
		$fields = array_unique($fields);
		sort($fields);

		// --------------------------------------
		// Let an extension take over?
		// --------------------------------------

		if (ee()->extensions->active_hook('mtt_search_get_index_entries') === TRUE)
		{
			$index_entries = ee()->extensions->call('mtt_search_get_index_entries',
				$fields, $channel_ids, $entry_ids, $start, $batch_size);
		}
		else
		{
			// --------------------------------------
			// Create select list
			// --------------------------------------

			$select = array('t.entry_id', 't.channel_id');

			foreach ($fields AS $field_id)
			{
				// Skip non-numeric settings
				if ( ! is_numeric($field_id)) continue;

				$select[] = ($field_id == '0') ? 't.title AS field_id_0' : 'd.field_id_'.$field_id;
			}

			// --------------------------------------
			// Start building query
			// --------------------------------------

			ee()->db->select($select)
				->from('channel_titles t')
				->join('channel_data d', 't.entry_id = d.entry_id', 'inner')
				->where_in('t.channel_id', $channel_ids)
				->order_by('entry_id', 'asc');

			// --------------------------------------
			// Optional: Limit to given entries
			// --------------------------------------

			if ($entry_ids)
			{
				ee()->db->where_in('t.entry_id', $entry_ids);
			}

			// --------------------------------------
			// Optional: Limit entries by batch size
			// --------------------------------------

			if ($start !== FALSE && is_numeric($start))
			{
				ee()->db->limit($batch_size, $start);
			}

			// --------------------------------------
			// Query it!
			// --------------------------------------

			$query = ee()->db->get();

			// Make sure the rows are keyed by their entry_id
			$index_entries = mtt_associate_results($query->result_array(), 'entry_id');

			// --------------------------------------
			// Get category info for these entries
			// --------------------------------------

			if ($entry_cats = $this->get_entry_categories(array_keys($index_entries)))
			{
				// add the categories to the index_entries rows
				foreach ($entry_cats AS $entry_id => $cats)
				{
					$index_entries[$entry_id] += $cats;
				}
			}
		}

		// --------------------------------------
		// Loop thru the entries to index
		// --------------------------------------

		foreach ($index_entries AS $row)
		{
			// If it's a given entry, loop thru its collections and rebuild index
			if (isset($entries[$row['entry_id']]))
			{
				foreach ($entries[$row['entry_id']] AS $col_id)
				{
					// Collection details
					$col = $collections[$col_id];

					// Build index for this entry/collection combo
					ee()->mtt_search_index_model->build($col, $row);
				}
			}
			// If it's not a given entry, loop thru all collections (which should be 1) and rebuild index
			else
			{
				foreach ($collections AS $col_id => $col)
				{
					if ($row['channel_id'] == $col['channel_id'])
					{
						ee()->mtt_search_index_model->build($col, $row);
					}
				}
			}
		}

		// Determine new start
		$new_start = $start + $batch_size;

		// Are we done?
		$done = ($new_start >= $num_entries);

		// --------------------------------------
		// Prep response
		// --------------------------------------

		$response = array(
			'status'        => $done ? 'done' : 'building',
			'start'         => (int) $new_start,
			'total_entries' => (int) $num_entries,
			'processed'     => count($index_entries)
		);

		return $response;
	}

	// --------------------------------------------------------------------

	/**
	 * Get categories for entries
	 *
	 * @access     public
	 * @param      mixed [int|array]
	 * @param      mixed [null|array]
	 * @return     array
	 */
	public function get_entry_categories($entry_ids, $cat_ids = NULL)
	{
		// Prep output
		$cats = array();

		// --------------------------------------
		// Two options: either get cats by their entry id,
		// or get details for given cat ids.
		// Compose query based on those two options.
		// --------------------------------------

		$ok     = FALSE;
		$select = array('c.cat_id', 'c.group_id', 'c.cat_name', 'c.cat_description', 'fd.*');
		$joins  = array(array('category_field_data fd', 'c.cat_id = fd.cat_id', 'left'));
		$where  = array();

		if (is_array($entry_ids) && ! empty($entry_ids))
		{
			// Option 1: get categories by given entry_ids
			$ok = TRUE;
			$select[] = 'cp.entry_id';
			$joins[] = array('category_posts cp', 'c.cat_id = cp.cat_id', 'inner');
			$where['cp.entry_id'] = $entry_ids;
		}
		elseif (is_array($cat_ids) && ! empty($cat_ids))
		{
			// Option 2: get categories by given cat_ids,
			// hardcode entry ID to be compatible
			$ok = TRUE;
			$select[] = "'{$entry_ids}' AS `entry_id`";
			$where['c.cat_id'] = $cat_ids;
		}

		// Not ok? Bail out
		if ( ! $ok) return $cats;

		// Start query
		ee()->db->select($select, FALSE);
		ee()->db->from('categories c');

		// Process joins
		foreach ($joins AS $join)
		{
			list($table, $on, $type) = $join;
			ee()->db->join($table, $on, $type);
		}

		// Process wheres
		foreach ($where AS $key => $val)
		{
			ee()->db->where_in($key, $val);
		}

		// Execute query
		$query = ee()->db->get();

		// --------------------------------------
		// Done with the query; loop through results
		// --------------------------------------

		// Relevant non-custom fields
		$fields = array('cat_name', 'cat_description');

		foreach ($query->result_array() AS $row)
		{
			// Loop through each result and populate the output
			foreach ($row AS $key => $val)
			{
				// Skip non-valid fields
				if ( ! in_array($key, $fields) && ! preg_match('/^field_id_(\d+)$/', $key, $match)) continue;

				// We're OK! Go on with composing the right key:
				// Either the name or description or custom field ID
				$cat_field = $match ? $match[1] : $key;

				// Use that as the key in the array to return
				$cats[$row['entry_id']]["{$row['group_id']}:{$cat_field}"][$row['cat_id']] = $val;
			}
		}

		// --------------------------------------
		// Focus on the single one if one entry_id is given
		// --------------------------------------

		if ( ! is_array($entry_ids))
		{
			$cats = $cats[$entry_ids];
		}

		return $cats;
	}

}
// End of file Mtt_search_index.php