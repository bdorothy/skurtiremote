<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Mtt Search Filter class, for inheritance
 *
 * @package        mtt_search
 * @author         MyTopTeacher - MTT <contact@mytopteacher.com>
 * @link           http://mytopteacher.com/addons/mtt-search
 * @copyright      Copyright (c) 2014, Mtt
 */
class Mtt_search_filter {

	/**
	 * Default priority for this filter
	 */
	protected $priority = 5;

	/**
	 * Shortcut to Mtt_search_params
	 */
	protected $params;

	// --------------------------------------------------------------------
	// METHODS
	// --------------------------------------------------------------------

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// Set the shortcut
		$this->params =& ee()->mtt_search_params;
	}

	/**
	 * Return the priority
	 */
	public function priority()
	{
		return $this->priority;
	}

	/**
	 * Fixed order?
	 */
	public function fixed_order()
	{
		return FALSE;
	}

	/**
	 * Get field id for given field short name
	 *
	 * @access      protected
	 * @param       string
	 * @param       array
	 * @return      int
	 */
	protected function _get_field_id($str, $fields = array())
	{
		// --------------------------------------
		// Get custom channel fields from cache
		// --------------------------------------

		if (empty($fields) && ! ($fields = mtt_get_cache('channel', 'custom_channel_fields')))
		{
			// If not present, get them from the API
			// Takes some effort, but its reusable for others this way
			$this->_log('Getting channel field info from API');

			ee()->load->library('api');
			ee()->api->instantiate('channel_fields');

			$fields = ee()->api_channel_fields->fetch_custom_channel_fields();

			foreach ($fields AS $key => $val)
			{
				mtt_set_cache('channel', $key, $val);
			}

			$fields = $fields['custom_channel_fields'];
		}

		// --------------------------------------
		// To be somewhat compatible with MSM,
		// get the first ID that matches,
		// not just for current site, but all given.
		// --------------------------------------

		// Initiate ID
		$it = 0;

		// Check active site IDs, return first match encountered
		foreach (ee()->mtt_search_params->site_ids() AS $site_id)
		{
			if (isset($fields[$site_id][$str]))
			{
				$it = $fields[$site_id][$str];
				break;
			}
		}

		// Please
		return $it;
	}

	/**
	 * Remove vars from tagdata
	 *
	 * @access     protected
	 * @param      mixed
	 * @return     void
	 */
	protected function _remove_rogue_vars($key, $prefix = TRUE)
	{
		// Force array
		if ( ! is_array($key))
		{
			$key = array($key);
		}

		foreach ($key AS $pfx)
		{
			// Append global prefix?
			if ($prefix)
			{
				$pfx = ee()->mtt_search_settings->prefix . $pfx;
			}

			// Escape
			$pfx = preg_quote($pfx);

			// Strip vars from tagdata
			ee()->TMPL->tagdata = preg_replace(
				"/\{{$pfx}[\w\-:]+?\}/", '',
				ee()->TMPL->tagdata
			);
		}
	}


	/**
	 * Log message to Template Logger
	 *
	 * @access     protected
	 * @param      string
	 * @return     void
	 */
	protected function _log($msg)
	{
		ee()->TMPL->log_item("Mtt Search: {$msg}");
	}

}
// End of file filter.mtt_search.php