<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Filter by distance:foo="bar"
 *
 * @package        mtt_search
 * @author         MyTopTeacher - MTT <contact@mytopteacher.com>
 * @link           http://mytopteacher.com/addons/mtt-search
 * @copyright      Copyright (c) 2014, Mtt
 */
class Mtt_search_filter_distance extends Mtt_search_filter {

	// Usage:
	// distance:from="lat|long"
	// distance:to="lat_field|long_field"
	// distance:radius="10"
	// distance:unit="km"

	/**
	 * Results cached
	 */
	private $_results;

	/**
	 * Search parameters for distance: params
	 *
	 * @access      private
	 * @return      void
	 */
	public function filter($entry_ids)
	{
		// --------------------------------------
		// Reset
		// --------------------------------------

		$this->_results = array();

		// --------------------------------------
		// Get distance params
		// --------------------------------------

		$params = $this->params->get_prefixed('distance:', TRUE);
		$params = array_filter($params, 'mtt_not_empty');

		// --------------------------------------
		// Check required params
		// --------------------------------------

		foreach (array('from', 'to') AS $key)
		{
			if (empty($params[$key])) return $entry_ids;
		}

		// --------------------------------------
		// Log it
		// --------------------------------------

		$this->_log('Applying '.__CLASS__);

		// --------------------------------------
		// Get channel IDs before starting the query
		// --------------------------------------

		$channel_ids = ee()->mtt_search_collection_model->get_channel_ids($this->params->get('collection'));

		// --------------------------------------
		// Get from coords
		// --------------------------------------

		list($lat, $long) = preg_split('/[|,]/', $params['from'], 2, PREG_SPLIT_NO_EMPTY);

		// --------------------------------------
		// Unit
		// --------------------------------------

		if (empty($params['unit']))
		{
			$params['unit'] = 'km';
		}

		// Radius of the earth
		switch ($params['unit'])
		{
			case 'mi':
				$R = 3959;
			break;

			default:
				$R = 6371;
		}

		// --------------------------------------
		// Get reference to fields from params
		// --------------------------------------

		list($lat_field, $long_field) = explode('|', $params['to'], 2);

		$lat_field_id  = $this->_get_field_id($lat_field);
		$long_field_id = $this->_get_field_id($long_field);

		if ( ! ($lat_field_id && $long_field_id)) return $entry_ids;

		$lat_field  = 'field_id_' . $lat_field_id;
		$long_field = 'field_id_' . $long_field_id;

		// --------------------------------------
		// The distance SQL statement: Haversine formula
		// --------------------------------------

		$haversine
			= '(%d * acos(cos(radians(%2$f)) * cos(radians(%4$s)) * cos(radians(%5$s) - radians(%3$f))'
			. ' + sin(radians(%2$f)) * sin(radians(%4$s)))) AS distance';

		// Get entry IDs and their distances
		ee()->db->select('entry_id')
		        ->select(sprintf($haversine, $R, $lat, $long, $lat_field, $long_field), FALSE)
		        ->from('channel_data')
		        ->where("({$lat_field} OR {$long_field})")
		        ->order_by('distance', 'asc');

		// --------------------------------------
		// Limit by site ids?
		// --------------------------------------

		if ($site_ids = $this->params->site_ids())
		{
			ee()->db->where_in('site_id', $site_ids);
		}

		// --------------------------------------
		// Limit by channel ids?
		// --------------------------------------

		if ($channel_ids)
		{
			ee()->db->where_in('channel_id', $channel_ids);
		}

		// --------------------------------------
		// Limit by entry ids?
		// --------------------------------------

		if ($entry_ids)
		{
			ee()->db->where_in('entry_id', $entry_ids);
		}

		// --------------------------------------
		// Limit by radius?
		// --------------------------------------

		if ( ! empty($params['radius']))
		{
			ee()->db->having('distance <=', $params['radius']);
		}

		// --------------------------------------
		// Execute!
		// --------------------------------------

		$query = ee()->db->get();

		$this->_results = mtt_flatten_results($query->result_array(), 'distance', 'entry_id');

		return array_keys($this->_results);
	}

	// --------------------------------------------------------------------

	/**
	 * Fixed order?
	 */
	public function fixed_order()
	{
		$fixed = FALSE;

		// --------------------------------------
		// Is there a custom sort order?
		// If so, set the entry_id param instead of the fixed_order
		// --------------------------------------

		if ($this->_results)
		{
			$orderby = $this->params->get('orderby', 'mtt_search_distance');

			if (substr($orderby, 0, 19) == 'mtt_search_distance')
			{
				$fixed = TRUE;

				// Unset so they don't get re-set
				$this->params->set('orderby', '');
				$this->params->set('sort', '');
			}
		}

		return $fixed;
	}

	// --------------------------------------------------------------------

	/**
	 * Modify rows for a search result for this filter
	 */
	public function results($rows)
	{
		if ($this->_results)
		{
			// Populate rows with the distance value
			$pfx = ee()->mtt_search_settings->prefix;

			foreach ($rows AS &$row)
			{
				$row[$pfx.'distance'] = isset($this->_results[$row['entry_id']])
					? round($this->_results[$row['entry_id']])
					: '';
			}
		}
		else
		{
			// Remove any distance-specific vars
			$this->_remove_rogue_vars('distance');
		}

		return $rows;
	}

	// --------------------------------------------------------------------

}