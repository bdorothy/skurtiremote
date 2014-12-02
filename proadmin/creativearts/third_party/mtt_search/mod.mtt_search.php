<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// include base class
if ( ! class_exists('Mtt_search_base'))
{
	require_once(PATH_THIRD.'mtt_search/base.mtt_search.php');
}

/**
 * Mtt Search Module class
 *
 * @package        mtt_search
 * @author         MyTopTeacher <contact@mytopteacher.com>
 * @link           http://mytopteacher.com/addons/mtt-search
 * @copyright      Copyright (c) 2014, Mtt
 */
class Mtt_search extends Mtt_search_base {

	// --------------------------------------------------------------------
	// PROPERTIES
	// --------------------------------------------------------------------

	/**
	 * Shortcut to Mtt_search_params object
	 *
	 * @access      private
	 * @var         object
	 */
	private $params;

	/**
	 * Shortcut to Mtt_search_settings object
	 *
	 * @access      private
	 * @var         object
	 */
	private $settings;

	/**
	 * Shortcut used
	 *
	 * @access      private
	 * @var         mixed
	 */
	private $shortcut;

	// --------------------------------------------------------------------
	// METHODS
	// --------------------------------------------------------------------

	public function __construct()
	{
		parent::__construct();

		ee()->load->library('Mtt_search_params');

		$this->params   =& ee()->mtt_search_params;
		$this->settings =& ee()->mtt_search_settings;
	}

	/**
	 * Filters
	 *
	 * @access      public
	 * @return      string
	 */
	public function filters()
	{
		// --------------------------------------
		// Load up language file
		// --------------------------------------

		ee()->lang->loadfile($this->package);

		// --------------------------------------
		// Read parameters
		// --------------------------------------

		$this->params->set();

		// --------------------------------------
		// Overwrite with shortcut?
		// --------------------------------------

		$this->_get_shortcut();

		// --------------------------------------
		// Prep params for template variables
		// --------------------------------------

		$vars = array();

		foreach ($this->params->get() AS $key => $val)
		{
			$vars[$this->settings->prefix.$key.':raw'] = $val;
			$vars[$this->settings->prefix.$key] = mtt_format($val);
		}

		// --------------------------------------
		// Add shortcut data to vars
		// --------------------------------------

		if ($this->shortcut)
		{
			foreach (ee()->mtt_search_shortcut_model->get_template_attrs() AS $key)
			{
				$vars[$this->settings->prefix.$key] = $this->shortcut[$key];
			}
		}

		// --------------------------------------
		// Get search collections for this site
		// --------------------------------------

		// Check to see whether we actually need to get collections
		$get_collections    = FALSE;
		$collections        = array();
		$active_collections = array();

		// Get them only if the var pair exists
		foreach (mtt_array_get_prefixed(ee()->TMPL->var_pair, 'collections') AS $val)
		{
			$this->_log('Collections variable pair found');

			$get_collections = TRUE;

			// Is the 'show' parameter set?
			// If so, get the ids
			if (isset($val['show']))
			{
				$get_collections = $val['show'];
			}
		}

		// Get the collections if necessary
		if ($get_collections !== FALSE)
		{
			$this->_log('Getting search collection details');

			// --------------------------------------
			// Possibly limit by ids
			// --------------------------------------

			$collections = array();
			$site_collections = ee()->mtt_search_collection_model->get_by_site(
				array_values(ee()->TMPL->site_ids)
			);

			if ($get_collections !== TRUE)
			{
				list($show, $in) = mtt_explode_param($get_collections);
				$key = mtt_array_is_numeric($show) ? 'collection_id' : 'collection_name';
				$site_collections = mtt_associate_results($site_collections, $key);

				foreach ($site_collections AS $id => $row)
				{
					if ($in === in_array($id, $show))
					{
						$collections[] = $row;
					}
				}
			}
			else
			{
				$collections = array_values($site_collections);
			}

			// --------------------------------------
			// Define collection meta data
			// --------------------------------------

			$meta = array(
				'collection_count'  => 0,
				'total_collections' => count($collections)
			);

			// --------------------------------------
			// Get array of active collections
			// --------------------------------------

			$col = $this->params->get('collection');

			if ( ! empty($col))
			{
				if (is_string($col))
				{
					list($active_collections, $in) = mtt_explode_param($col);
				}
				elseif (is_array($col))
				{
					$active_collections = $col;
				}
			}

			// --------------------------------------
			// Loop thru collections, modify rows
			// --------------------------------------

			// Numeric collections?
			$attr = mtt_array_is_numeric($active_collections) ? 'collection_id' : 'collection_name';

			foreach ($collections AS &$row)
			{
				// Forget some
				unset($row['site_id'], $row['settings']);

				// Make strings html-safe
				$row = array_map('htmlspecialchars', $row);

				// Is collection selected?
				$row['collection_is_active'] = in_array($row[$attr], $active_collections) ? 'y' : '';

				// Increment collection count
				$meta['collection_count']++;

				// Merge meta with row
				$row = array_merge($row, $meta);
			}
		}

		// --------------------------------------
		// Add collections to vars array
		// --------------------------------------

		$vars['collections'] = $vars[$this->settings->prefix.'collections'] = $collections;

		// --------------------------------------
		// Handle error messages
		// --------------------------------------

		// Main error message
		$vars['error_message'] = ee()->session->flashdata('error_message');

		// Errors per field
		if (($errors = ee()->session->flashdata('errors')) && is_array($errors))
		{
			foreach ($errors AS $field)
			{
				$vars[$this->settings->prefix.$field.'_missing'] = TRUE;
			}
		}

		// --------------------------------------
		// Parse it now
		// --------------------------------------

		$tagdata = ee()->TMPL->parse_variables_row(ee()->TMPL->tagdata, $vars);
		$tagdata = $this->_post_parse($tagdata);

		// --------------------------------------
		// Return output
		// --------------------------------------

		return $tagdata;
	}

	/**
	 * Call $this->filters and wrap a form around it
	 *
	 * @access     public
	 * @return     string
	 */
	public function form()
	{
		// --------------------------------------
		// Initiate data arrays for form creation
		// --------------------------------------

		$data = $params = $form_params = array();

		// --------------------------------------
		// Collect form params
		// --------------------------------------

		foreach (array_keys(mtt_array_get_prefixed(ee()->TMPL->tagparams, 'form_', TRUE)) AS $key)
		{
			$form_params[$key] = $this->_extract_tagparam('form_'.$key);
		}

		// --------------------------------------
		// Read parameters
		// --------------------------------------

		$this->params->set();

		// --------------------------------------
		// Set parameters to shortcut?
		// --------------------------------------

		if ($shortcut = $this->_get_shortcut())
		{
			$params = array_merge($params, $shortcut['parameters']);
		}

		// --------------------------------------
		// Are we remembering parameters?
		// --------------------------------------

		if ($remember = ee()->TMPL->fetch_param('remember'))
		{
			foreach (explode('|', $remember) AS $key)
			{
				$params[$key] = $this->params->get($key);
			}
		}

		// --------------------------------------
		// Overwrite-able params
		// --------------------------------------

		foreach (array('result_page') AS $key)
		{
			$params[$key] = ee()->TMPL->fetch_param($key);
		}

		// --------------------------------------
		// One-off params
		// --------------------------------------

		foreach (array('required', 'force_protocol') AS $key)
		{
			$params[$key] = $this->_extract_tagparam($key);
		}

		// --------------------------------------
		// Encode and put parameters in hidden form field
		// --------------------------------------

		if ($params = array_filter($params, 'mtt_not_empty'))
		{
			$data['hidden_fields']['params'] = mtt_search_encode($params);
		}

		// --------------------------------------
		// Define the action ID
		// --------------------------------------

		$data['hidden_fields']['ACT'] = ee()->functions->fetch_action_id($this->class_name, 'catch_search');

		// --------------------------------------
		// Get opening form tag
		// --------------------------------------

		$form = ee()->functions->form_declaration($data);

		// --------------------------------------
		// Add form params to it
		// --------------------------------------

		if ($form_params)
		{
			$form = str_replace('<form ', '<form '.mtt_param_string($form_params).' ', $form);
		}

		// --------------------------------------
		// Return output
		// --------------------------------------

		return $form . $this->filters() . '</form>';
	}

	// --------------------------------------------------------------------

	/**
	 * Save Search form
	 *
	 * @access     public
	 * @return     string
	 */
	public function save()
	{
		// --------------------------------------
		// Check permissions
		// --------------------------------------

		if ( ! $this->_can_manage_shortcuts())
		{
			$this->_log('Member not allowed to save shortcuts, returning empty string');
			return;
		}

		// --------------------------------------
		// Put parameters in params value
		// --------------------------------------

		$this->params->set();

		// --------------------------------------
		// If no valid query is given, bail out - no use to saving it
		// --------------------------------------

		if ( ! $this->params->valid_query())
		{
			$this->_log('Invalid query given, returning no results');
			return ee()->TMPL->no_results();
		}

		// --------------------------------------
		// Initiate data array for form creation
		// --------------------------------------

		$data = array();

		// Define hidden fields
		$data['hidden_fields']['ACT'] = ee()->functions->fetch_action_id($this->class_name, 'save_search');
		$data['hidden_fields']['params'] = mtt_search_encode($this->params->get());
		$data['hidden_fields']['group_id'] = ee()->TMPL->fetch_param('group_id');

		// --------------------------------------
		// Get opening form tag
		// --------------------------------------

		$form = ee()->functions->form_declaration($data);

		// --------------------------------------
		// Collect form params
		// --------------------------------------

		$form_params = mtt_array_get_prefixed(ee()->TMPL->tagparams, 'form_', TRUE);

		// --------------------------------------
		// Add form params to it
		// --------------------------------------

		if ($form_params)
		{
			$form = str_replace('<form ', '<form '.mtt_param_string($form_params).' ', $form);
		}

		// --------------------------------------
		// Return output
		// --------------------------------------

		return $form . ee()->TMPL->tagdata . '</form>';
	}

	/**
	 * Save a search
	 */
	public function save_search()
	{
		// --------------------------------------
		// Check permissions
		// --------------------------------------

		if ( ! $this->_can_manage_shortcuts())
		{
			show_error('not_authorized');
		}

		// --------------------------------------
		// Check security
		// --------------------------------------

		if (method_exists(ee()->security, 'restore_xid') &&
			version_compare(APP_VER, '2.8.0', '<'))
		{
			ee()->security->restore_xid();
		}

		// --------------------------------------
		// Get Group ID from post
		// --------------------------------------

		if ( ! ($group_id = ee()->input->post('group_id')))
		{
			// @todo: fallback to default group?
		}

		// --------------------------------------
		// Compose data to insert
		// --------------------------------------

		$data = array(
			'site_id'        => $this->site_id,
			'group_id'       => $group_id,
			'shortcut_name'  => ee()->input->post('shortcut_name'),
			'shortcut_label' => ee()->input->post('shortcut_label'),
			'parameters'     => ee()->input->post('params')
		);

		// --------------------------------------
		// Validate the shortcut data
		// --------------------------------------

		if (($validated = ee()->mtt_search_shortcut_model->validate($data)) === FALSE)
		{
			// Load the language file so we can show an error
			ee()->lang->loadfile($this->package);

			// Pass errors through lang()
			$errors = array_map('lang', ee()->mtt_search_shortcut_model->errors());

			// and show 'em
			show_error($errors);
		}

		// --------------------------------------
		// And insert it
		// --------------------------------------

		$shortcut_id = ee()->mtt_search_shortcut_model->insert($validated);

		// --------------------------------------
		// Return to previous
		// --------------------------------------

		$this->_go_back(NULL);
	}

	/**
	 * Display shorts
	 *
	 * @access     public
	 * @return     string
	 */
	public function shortcuts()
	{
		// --------------------------------------
		// Fields to select, reused for ordering
		// --------------------------------------

		$select = array('shortcut_id', 'shortcut_name', 'shortcut_label', 'parameters', 'sort_order');

		// --------------------------------------
		// Start query
		// --------------------------------------

		ee()->db->select($select)->from(ee()->mtt_search_shortcut_model->table());

		// --------------------------------------
		// Filter by shortcut ID
		// --------------------------------------

		if ($shortcut_id = ee()->TMPL->fetch_param('shortcut_id'))
		{
			list($items, $in) = mtt_explode_param($shortcut_id);

			ee()->db->{($in ? 'where_in' : 'where_not_in')}('shortcut_id', $items);
		}

		// --------------------------------------
		// Filter by site
		// --------------------------------------

		if ($sites = array_values(ee()->TMPL->site_ids))
		{
			ee()->db->where_in('site_id', $sites);
		}

		// --------------------------------------
		// Filter by group ID
		// --------------------------------------

		if ($group_id = ee()->TMPL->fetch_param('group_id'))
		{
			list($items, $in) = mtt_explode_param($group_id);

			ee()->db->{($in ? 'where_in' : 'where_not_in')}('group_id', $items);
		}

		// --------------------------------------
		// Filter by short name
		// --------------------------------------

		if ($shortcut_name = ee()->TMPL->fetch_param('shortcut_name'))
		{
			list($items, $in) = mtt_explode_param($shortcut_name);

			ee()->db->{($in ? 'where_in' : 'where_not_in')}('shortcut_name', $items);
		}

		// --------------------------------------
		// Order by
		// --------------------------------------

		if (($orderby = ee()->TMPL->fetch_param('orderby', 'sort_order')) &&
			in_array($orderby, $select))
		{
			$sort = strtoer(ee()->TMPL->fetch_param('sort', 'asc'));

			if ( ! in_array($sort, array('asc', 'desc')))
			{
				$sort = 'asc';
			}

			ee()->db->order_by($orderby, $sort);
		}

		// --------------------------------------
		// Limit, offset
		// --------------------------------------

		if (($limit = ee()->TMPL->fetch_param('limit', 100)) && is_numeric($limit))
		{
			$offset = (int) ee()->TMPL->fetch_param('offset', 0);

			ee()->db->limit($limit, $offset);
		}

		// --------------------------------------
		// Get the rows
		// --------------------------------------

		$rows = ee()->db->get()->result_array();

		// --------------------------------------
		// Nothing? No results
		// --------------------------------------

		if (empty($rows))
		{
			$this->_log('No shortcuts found');
			return ee()->TMPL->no_results();
		}

		// --------------------------------------
		// Are there {shortcut_url result_page=""} vars?
		// --------------------------------------

		$urls = array();

		foreach (mtt_array_get_prefixed(ee()->TMPL->var_single, 'shortcut_url') AS $var)
		{
			$urls[$var] = array();

			// Read out the parameters so we can override them
			if (preg_match_all("/([\w\-:]+)\s*=\s*('|\")(.+?)\\2/", $var, $matches))
			{
				foreach ($matches[0] AS $i => $val)
				{
					$urls[$var][$matches[1][$i]] = $matches[3][$i];
				}
			}
		}

		// --------------------------------------
		// Modify the rows
		// --------------------------------------

		foreach ($rows AS &$row)
		{
			$params = mtt_search_decode($row['parameters'], FALSE);

			foreach ($urls AS $key => $val)
			{
				$row[$key] = $this->_create_url(array_merge($params, $val));
			}

			// Don't need the raw json
			unset($row['parameters']);
		}

		// --------------------------------------
		// Parse it, dawg
		// --------------------------------------

		$tagdata = ee()->TMPL->parse_variables(ee()->TMPL->tagdata, $rows);

		return $tagdata;
	}

	// --------------------------------------------------------------------

	/**
	 * Show search results
	 *
	 * @access      public
	 * @return      string
	 */
	public function results()
	{
		// --------------------------------------
		// Avoid no_results conflict
		// --------------------------------------

		$this->_prep_no_results();

		// --------------------------------------
		// Set the parameters
		// --------------------------------------

		$this->params->set();

		// --------------------------------------
		// Are we using a shortcut?
		// --------------------------------------

		$this->_get_shortcut();

		// --------------------------------------
		// If query parameter is set but empty or invalid,
		// show no_results and abort
		// --------------------------------------

		if ( ! $this->params->query_given() && ee()->TMPL->fetch_param('require_query') == 'yes')
		{
			$this->_log('Query required but none given, returning no results');
			return $this->_no_results();
		}

		// Query given but not valid == no results
		if ($this->params->query_given() && ! $this->params->valid_query())
		{
			$this->_log('Returning no results due to invalid query');
			return $this->_no_results();
		}

		// --------------------------------------
		// Merge tagparams into all params, set default params
		// --------------------------------------

		$this->params->combine();
		$this->params->set_defaults();

		// -------------------------------------
		// 'mtt_search_pre_search' hook.
		//  - Do something just before the search is executed
		// -------------------------------------

		if (ee()->extensions->active_hook('mtt_search_pre_search') === TRUE)
		{
			$params = $this->params->get();
			$params = ee()->extensions->call('mtt_search_pre_search', $params);
			if (ee()->extensions->end_script === TRUE) return ee()->TMPL->tagdata;
			$this->params->overwrite($params);
		}

		// --------------------------------------
		// Optionally log search
		// --------------------------------------

		if ($this->params->get('log_search') == 'yes' &&
			! preg_match('#/P\d+/?$#', ee()->uri->uri_string()))
		{
			$this->_log_search($this->params->get());
		}

		// --------------------------------------
		// Check orderby_sort=""
		// --------------------------------------

		if ($this->params->get('orderby_sort') &&
			strpos($this->params->get('orderby_sort'), '|') !== FALSE)
		{
			list($a, $b) = explode('|', $this->params->get('orderby_sort'), 2);
			$this->params->set('orderby', $a);
			$this->params->set('sort', $b);
		}

		// --------------------------------------
		// Load and apply all available filters
		// --------------------------------------

		ee()->load->library('Mtt_search_filters');

		ee()->mtt_search_filters->filter();

		// --------------------------------------
		// What entry IDs do we have as a result?
		// --------------------------------------

		$entry_ids = ee()->mtt_search_filters->entry_ids();

		// --------------------------------------
		// If entry_ids is an array, some filters fired
		// --------------------------------------

		if (is_array($entry_ids))
		{
			// Empty array -> No results
			if (empty($entry_ids))
			{
				$this->_log('Filters found no matches, returning no results');
				return $this->_no_results();
			}
			// Yay! We have results! Now, which param should we populate?
			else
			{
				if ($fixed_order_param = $this->params->get('fixed_order'))
				{
					$entry_ids = mtt_merge_params($entry_ids, $fixed_order_param);
				}

				if ($entry_id_param = $this->params->get('entry_id'))
				{
					$entry_ids = mtt_merge_params($entry_ids, $entry_id_param);
				}

				if (empty($entry_ids))
				{
					$this->_log('No results after entry_id/fixed_order');
					return $this->_no_results();
				}

				$param = (ee()->mtt_search_filters->fixed_order() || $fixed_order_param)
					? 'fixed_order'
					: 'entry_id';

				// Still here: set the entry_id param
				$this->_log("Setting {$param} param");
				$this->params->set($param, mtt_implode_param($entry_ids));
			}
		}

		// -------------------------------------
		// 'mtt_search_post_search' hook.
		//  - Do something just after the search is executed
		// -------------------------------------

		if (ee()->extensions->active_hook('mtt_search_post_search') === TRUE)
		{
			$params = $this->params->get();
			$params = ee()->extensions->call('mtt_search_post_search', $params);
			if (ee()->extensions->end_script === TRUE) return ee()->TMPL->tagdata;
			$this->params->overwrite($params);
		}

		// --------------------------------------
		// Set misc tagparams
		// --------------------------------------

		$this->params->apply();

		// --------------------------------------
		// Log the set parameters
		// --------------------------------------

		$this->_log('Parameters set: '. mtt_param_string(array_merge(
			ee()->TMPL->tagparams,
			ee()->TMPL->search_fields
		)));

		// --------------------------------------
		// Pre-apply parameters as vars
		// --------------------------------------

		$this->_log('Pre-applying search vars to tagdata');

		ee()->TMPL->tagdata = ee()->TMPL->parse_variables_row(
			ee()->TMPL->tagdata,
			$this->params->get_vars($this->settings->prefix)
		);

		// --------------------------------------
		// Set parameter so extension kicks in
		// --------------------------------------

		$this->params->apply('mtt_search', 'yes');

		// --------------------------------------
		// Remember the shortcut, if there
		// --------------------------------------

		mtt_set_cache(MTT_SEARCH_PACKAGE, 'shortcut', $this->shortcut);

		// -------------------------------------
		// 'mtt_search_channel_entries' hook.
		//  - Call your own channel:entries, fall back to default
		// -------------------------------------

		$tagdata = (ee()->extensions->active_hook('mtt_search_channel_entries') === TRUE)
			? ee()->extensions->call('mtt_search_channel_entries')
			: $this->_channel_entries();

		// --------------------------------------
		// Don't post_parse no_results
		// --------------------------------------

		$tagdata = ($tagdata == ee()->TMPL->no_results)
			? $this->_no_results($tagdata)
			: $this->_post_parse($tagdata);

		return $tagdata;
	}

	// --------------------------------------------------------------------

	/**
	 * Display search collections
	 *
	 * @access      public
	 * @return      string
	 */
	public function collections()
	{
		// --------------------------------------
		// Check site
		// --------------------------------------

		$site_ids = ee()->TMPL->site_ids;

		// --------------------------------------
		// Get collections
		// --------------------------------------

		$rows = array_values(ee()->mtt_search_collection_model->get_by_site($site_ids));

		// --------------------------------------
		// Parse template
		// --------------------------------------

		return $rows
			? ee()->TMPL->parse_variables(ee()->TMPL->tagdata, $rows)
			: ee()->TMPL->no_results();
	}

	// --------------------------------------------------------------------

	/**
	 * Display given keywords (shortcut for param tag)
	 *
	 * @access      public
	 * @return      string
	 */
	public function keywords()
	{
		return $this->param('keywords');
	}

	/**
	 * Display any parameter outside of tags
	 *
	 * @access      public
	 * @return      string
	 */
	public function param($which = FALSE)
	{
		// --------------------------------------
		// Set the parameters
		// --------------------------------------

		$this->params->set();

		// --------------------------------------
		// Do we have a parameter to get?
		// --------------------------------------

		if ( ! ($which = ee()->TMPL->fetch_param('get', $which)))
		{
			$this->_log("Parameter {$which} not found");
			return ee()->TMPL->no_results();
		}

		// --------------------------------------
		// What's the parameter value?
		// --------------------------------------

		$it = (string) $this->params->get($which);

		// --------------------------------------
		// Get the format in which to return the value
		// --------------------------------------

		$format = ee()->TMPL->fetch_param('format', 'html');

		// --------------------------------------
		// Check if we need to return the value in a loop
		// --------------------------------------

		if ($as = ee()->TMPL->fetch_param('as'))
		{
			// Init vars array
			$vars = array();

			// Get values
			list($vals, $in) = mtt_explode_param($it);

			// Loop through param values
			foreach ($vals AS $val)
			{
				$vars[] = array($as => mtt_format($val, $format));
			}

			$it = ee()->TMPL->parse_variables(ee()->TMPL->tagdata, $vars);
		}
		else
		{
			$it = mtt_format($it, $format);
		}

		// Please
		return $it;
	}

	// --------------------------------------------------------------------

	/**
	 * Display popular keywords
	 *
	 * @access      public
	 * @return      string
	 */
	public function popular()
	{
		// --------------------------------------
		// Filter by site
		// --------------------------------------

		ee()->db->where('site_id', $this->site_id);

		// --------------------------------------
		// Limiting?
		// --------------------------------------

		if ( ! ($limit = (int) ee()->TMPL->fetch_param('limit')))
		{
			$limit = 10;
		}

		ee()->db->limit($limit);

		// --------------------------------------
		// Get terms
		// --------------------------------------

		if ($rows = ee()->mtt_search_log_model->get_popular_keywords())
		{
			// Get orderby and sort params
			$orderby = ee()->TMPL->fetch_param('orderby', 'search_count');
			$sort    = ee()->TMPL->fetch_param('sort', (($orderby == 'search_count') ? 'desc' : 'asc'));

			foreach ($rows AS &$row)
			{
				$kw = $row['keywords'];
				$row['keywords_raw']   = $kw;
				$row['keywords']       = mtt_format($kw, 'html');
				$row['keywords_url']   = mtt_format($kw, 'url');
				$row['keywords_clean'] = mtt_format($kw, 'clean');
				$row['keywords_param'] = mtt_format($kw, 'ee-encode');
			}

			// Different orderby?
			switch (ee()->TMPL->fetch_param('orderby'))
			{
				case 'keywords':
					usort($rows, 'mtt_by_keywords');
					if ($sort == 'desc') $rows = array_reverse($rows);
				break;

				case 'random':
					shuffle($rows);
				break;

				default:
					if ($sort == 'asc') $rows = array_reverse($rows);
			}
		}

		return $rows
			? ee()->TMPL->parse_variables(ee()->TMPL->tagdata, $rows)
			: ee()->TMPL->no_results();
	}

	// --------------------------------------------------------------------

	/**
	 * Generate Open Search URL
	 *
	 * @access      public
	 * @return      string
	 */
	public function url()
	{
		// --------------------------------------
		// Set internal params
		// --------------------------------------

		$this->params->set();

		// --------------------------------------
		// Shortcut?
		// --------------------------------------

		$this->_get_shortcut();

		// --------------------------------------
		// Params to ignore
		// --------------------------------------

		$ignore = array('query', 'query_string', 'encode', 'cache', 'refresh', 'parse', 'shortcut');

		// --------------------------------------
		// Is there a query_string parameter?
		// This helps passing through params with an encoded string
		// while using GET vars
		// --------------------------------------

		if ($qs = $this->_extract_tagparam('query_string'))
		{
			$this->params->set($qs);
		}

		// --------------------------------------
		// Loop through tagparams and add them to the query string
		// --------------------------------------

		// init toggle array
		$toggle = array();

		// Override with tagparams
		foreach (ee()->TMPL->tagparams AS $key => $val)
		{
			if (in_array($key, $ignore) || ! is_string($val)) continue;

			// Decode value
			$val = mtt_format($val, 'ee-decode');

			// Check for toggle values
			if (substr($key, 0, 7) == 'toggle:')
			{
				$toggle[substr($key, 7)] = $val;
				continue;
			}

			// Add to query string
			$this->params->set($key, $val);
		}

		// --------------------------------------
		// Handle toggle values
		// --------------------------------------

		foreach ($toggle AS $key => $val)
		{
			if ($current_val = $this->params->get($key))
			{
				// Read current value
				list($values, $in) = mtt_explode_param($current_val);

				// check if value is there
				if (($i = array_search($val, $values)) === FALSE)
				{
					// Not there, add it
					$values[] = $val;
				}
				else
				{
					// Is there, remove it
					unset($values[$i]);
				}

				$val = mtt_implode_param($values, $in);
			}

			// Add the new value to the parameter array (could be NULL)
			$this->params->set($key, $val);
		}

		// --------------------------------------
		// Clean up the parameters before making the URL
		// --------------------------------------

		$params = array_filter($this->params->get(), 'mtt_not_empty');

		// --------------------------------------
		// Then compose the URL, encoded or not
		// --------------------------------------

		if (ee()->TMPL->fetch_param('encode', 'yes') == 'no')
		{
			// Build non-encoded URL
			$url = ee()->functions->fetch_site_index()
			     . QUERY_MARKER.'ACT='
			     . ee()->functions->fetch_action_id($this->package, 'catch_search')
			     . AMP.http_build_query($params, '', AMP);
		}
		else
		{
			// Get the result page from the params
			$url = $this->_create_url($params);
		}

		return $url;
	}

	// --------------------------------------------------------------------
	// ACT METHODS
	// --------------------------------------------------------------------

	/**
	 * Build collection index
	 *
	 * @access      public
	 * @return      string
	 */
	public function build_index()
	{
		// License key must be given
		$license_key = $this->settings->get('license_key');
		$given_key   = ee()->input->get_post('key');

		if ($given_key && $license_key == $given_key)
		{
			ee()->load->library('mtt_search_index');
			return ee()->mtt_search_index->build();
		}
		else
		{
			show_error(ee()->lang->line('not_authorized'));
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Catch search form submission
	 *
	 * @access      public
	 * @return      void
	 */
	public function catch_search()
	{
		// --------------------------------------
		// Initiate data array; will be encrypted
		// and put in the URI later
		// --------------------------------------

		$data = array();

		if ($params = ee()->input->post('params'))
		{
			$data = mtt_search_decode($params);
		}

		// --------------------------------------
		// Check other data
		// --------------------------------------

		foreach (array_merge($_GET, $_POST) AS $key => $val)
		{
			// Keys to skip
			if (in_array($key, array('ACT', 'XID', 'csrf_token', 'params', 'site_id'))) continue;

			// Add post var to data
			$data[$key] = is_array($val)
				? implode('|', array_filter($val, 'mtt_not_empty'))
				: $val;
		}

		// --------------------------------------
		// Clean up the data array
		// --------------------------------------

		$data = array_filter($data, 'mtt_not_empty');

		// --------------------------------------
		// 'mtt_search_catch_search' extension hook
		//  - Check incoming data and optionally change it
		// --------------------------------------

		if (ee()->extensions->active_hook('mtt_search_catch_search') === TRUE)
		{
			$data = ee()->extensions->call('mtt_search_catch_search', $data);
			if (ee()->extensions->end_script === TRUE) return;

			// Clean again to be sure
			$data = array_filter($data, 'mtt_not_empty');
		}

		// --------------------------------------
		// Check for required parameter
		// --------------------------------------

		if (isset($data['required']))
		{
			// Init errors
			$errors = array();

			// Get required as array
			list($required, $in) = mtt_explode_param($data['required']);

			foreach ($required AS $req)
			{
				// Break out when empty
				// @TODO: enhance for multiple fields
				if (empty($data[$req]))
				{
					$errors[] = $req;
				}
			}

			// Go back
			if ($errors)
			{
				ee()->session->set_flashdata('errors', $errors);
				$this->_go_back('fields_missing');
			}

			// remove from data
			unset($data['required']);
		}


		// --------------------------------------
		// Optionally log search query
		// --------------------------------------

		$this->_log_search($data);

		// --------------------------------------
		// Result URI: result page & cleaned up data, encoded
		// --------------------------------------

		$url = $this->_create_url($data, '&');

		// --------------------------------------
		// Redirect to result page
		// --------------------------------------

		// Empty out flashdata to avoid serving of JSON for ajax request
		if (AJAX_REQUEST && count(ee()->session->flashdata))
		{
			ee()->session->flashdata = array();
		}

		ee()->functions->redirect($url);
	}

	// --------------------------------------------------------------------
	// PRIVATE METHODS
	// --------------------------------------------------------------------

	/**
	 * Create URL for given page and encoded query
	 *
	 * @access     private
	 * @param      array
	 * @param      string
	 * @return     string
	 */
	private function _create_url($query = array(), $amp = AMP)
	{
		// --------------------------------------
		// If no page, get default
		// --------------------------------------

		$page = isset($query['result_page'])
		      ? $query['result_page']
		      : $this->settings->get('default_result_page');

		// Remove trailing slash
		$page = rtrim($page, '/');

		// --------------------------------------
		// Force a protocol?
		// --------------------------------------

		$protocol = FALSE;

		if (isset($query['force_protocol']) &&
			in_array($query['force_protocol'], array('http', 'https')))
		{
			$protocol = $query['force_protocol'];
			unset($query['force_protocol']);
		}

		// --------------------------------------
		// Encode the query or not?
		// --------------------------------------

		if ($this->settings->get('encode_query') == 'y')
		{
			// Custom query position?
			if (strpos($page, '%s') === FALSE)
			{
				$page .= '/%s';
			}

			$url = sprintf($page, mtt_search_encode($query));
			$qs  = '';
		}
		else
		{
			unset($query['result_page']);

			$url = $page;
			$qs = http_build_query($query, '', $amp);

			// Clean up and remove 'dangerous' chars
			foreach (array('?', ';', ':', '|') AS $i => $char)
			{
				$replacement = ($i < 2) ? '' : $char;
				$qs = str_replace(urlencode($char), $replacement, $qs);
			}

			if ($qs) $qs = '?'.$qs;
		}

		// --------------------------------------
		// If URI isn't a full url, make it so
		// --------------------------------------

		if ( ! preg_match('/^https?:\/\//', $url))
		{
			$url = ee()->functions->create_url($url);
		}

		// --------------------------------------
		// Make sure the protocol is set according to force_protocol param
		// --------------------------------------

		if ($protocol)
		{
			$url = preg_replace('/^https?/', $protocol, $url);
		}

		return $url.$qs;
	}

	// --------------------------------------------------------------------

	/**
	 * Read a shortcut param, set it and return it
	 *
	 * @access     private
	 * @return     mixed
	 */
	private function _get_shortcut()
	{
		if (is_null($this->shortcut))
		{
			$row = FALSE;

			if ($shortcut = ee()->TMPL->fetch_param('shortcut'))
			{
				$attr = is_numeric($shortcut) ? 'shortcut_id' : 'shortcut_name';

				$msg = ($row = ee()->mtt_search_shortcut_model->get_one($shortcut, $attr))
					? 'Shortcut found'
					: "Shortcut {$shortcut} not found";

				$this->_log($msg);
			}

			// Overwrite the query? Defaults to Yes
			if ($row && ee()->TMPL->fetch_param('overwrite_query', 'yes') == 'yes')
			{
				$this->_log('Overwriting given query with shortcut search');
				$this->params->overwrite($row['parameters'], TRUE);
			}

			$this->shortcut = $row;
		}

		return $this->shortcut;
	}

	/**
	 * Check to see if current member can manage shortcuts
	 */
	private function _can_manage_shortcuts()
	{
		// Check permissions; only allowed members can save searches
		$allowed_groups = $this->settings->get('can_manage_shortcuts');
		$member_group   = ee()->session->userdata('group_id');

		// Force array
		if ( ! is_array($allowed_groups))
		{
			$allowed_groups = array();
		}

		// SuperAdmins are always okay
		$allowed_groups[] = 1;

		return in_array($member_group, $allowed_groups);
	}


	// --------------------------------------------------------------------

	/**
	 * Log given search parameters
	 *
	 * @access      private
	 * @param       array
	 * @return      void
	 */
	private function _log_search($data = array())
	{
		if (($search_log_size = $this->settings->get('search_log_size')) !== '0' && is_numeric($search_log_size))
		{
			$keywords = isset($data['keywords']) ? $data['keywords'] : '';

			// Don't add keywords to log parameters
			unset($data['keywords']);

			// Log search
			ee()->mtt_search_log_model->insert(array(
				'site_id'     => $this->site_id,
				'member_id'   => ee()->session->userdata['member_id'],
				'search_date' => ee()->localize->now,
				'ip_address'  => ee()->session->userdata['ip_address'],
				'keywords'    => $keywords,
				'parameters'  => mtt_search_encode($data, FALSE)
			));

			// Prune log
			// Rand trick borrowed from native search module
			if ((rand() % 100) < 5)
			{
				ee()->mtt_search_log_model->prune($this->site_id, $search_log_size);
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Check for {if mtt_search_no_results}
	 *
	 * @access      private
	 * @return      void
	 */
	private function _prep_no_results()
	{
		// Shortcut to tagdata
		$td =& ee()->TMPL->tagdata;
		$open = "if {$this->package}_no_results";
		$close = '/if';

		// Check if there is a custom no_results conditional
		if (strpos($td, $open) !== FALSE && preg_match('#'.LD.$open.RD.'(.*?)'.LD.$close.RD.'#s', $td, $match))
		{
			$this->_log("Prepping {$open} conditional");

			// Check if there are conditionals inside of that
			if (stristr($match[1], LD.'if'))
			{
				$match[0] = ee()->functions->full_tag($match[0], $td, LD.'if', LD.'\/if'.RD);
			}

			// Set template's no_results data to found chunk
			ee()->TMPL->no_results = substr($match[0], strlen(LD.$open.RD), -strlen(LD.$close.RD));

			// Remove no_results conditional from tagdata
			$td = str_replace($match[0], '', $td);
		}
	}

	/**
	 * Process no_results
	 *
	 * @access      private
	 * @return      string
	 */
	private function _no_results($tagdata = NULL)
	{
		// Set to default no_results data by default
		if (is_null($tagdata))
		{
			$tagdata = ee()->TMPL->no_results;
		}

		// Check if there are mtt_search vars present
		if (strpos($tagdata, $this->settings->prefix) !== FALSE)
		{
			$this->_log('Found mtt_search variables in no_results block, calling filters to parse');

			// TMPL lib says this is legacy code. For over 5 years now. Feck it, using it.
			$vars = ee()->functions->assign_variables($tagdata);

			ee()->TMPL->var_single = $vars['var_single'];
			ee()->TMPL->var_pair   = $vars['var_pair'];
			ee()->TMPL->tagdata    = $tagdata;

			$tagdata = $this->filters();
		}
		else
		{
			$tagdata = ee()->TMPL->no_results();
		}

		return $tagdata;
	}

	// --------------------------------------------------------------------

	/**
	 * Loads the Channel module and runs its entries() method
	 *
	 * @access      private
	 * @return      void
	 */
	private function _channel_entries()
	{
		// --------------------------------------
		// Make sure the foling params are set
		// --------------------------------------

		$set_params = array(
			'dynamic'  => 'no',
			'paginate' => 'bottom'
		);

		foreach ($set_params AS $key => $val)
		{
			if ( ! ee()->TMPL->fetch_param($key))
			{
				$this->params->apply($key, $val);
			}
		}

		// --------------------------------------
		// Take care of related entries
		// --------------------------------------

		if (version_compare(APP_VER, '2.6.0', '<'))
		{
			// We must do this, 'cause the template engine only does it for
			// channel:entries or search:search_results. The bastard.
			ee()->TMPL->tagdata = ee()->TMPL->assign_relationship_data(ee()->TMPL->tagdata);

			// Add related markers to single vars to trigger replacement
			foreach (ee()->TMPL->related_markers AS $var)
			{
				ee()->TMPL->var_single[$var] = $var;
			}
		}

		// --------------------------------------
		// Get channel module
		// --------------------------------------

		$this->_log('Calling the channel module');

		if ( ! class_exists('channel'))
		{
			require_once PATH_MOD.'channel/mod.channel'.EXT;
		}

		// --------------------------------------
		// Create new Channel instance
		// --------------------------------------

		$channel = new Channel;

		// --------------------------------------
		// Let the Channel module do all the heavy lifting
		// --------------------------------------

		return $channel->entries();
	}

	// --------------------------------------------------------------------

	/**
	 * Redirect to referrer with some flashdata
	 *
	 * @access      private
	 * @param       string
	 * @return      void
	 */
	private function _go_back($with_message)
	{
		ee()->session->set_flashdata('error_message', $with_message);
		ee()->functions->redirect($_SERVER['HTTP_REFERER']);
	}

	// --------------------------------------------------------------------

	/**
	 * Transform {mtt_search:url} to {exp:mtt_search:url query=""}
	 *
	 * @access     private
	 * @param      string
	 * @return     string
	 */
	private function _rewrite_url_vars($haystack)
	{
		$needle  = LD.'mtt_search:url';
		$replace = LD.'exp:mtt_search:url %s="%s"';
		$param   = ($this->settings->get('encode_query') == 'y') ? 'query' : 'query_string';

		if (strpos($haystack, $needle) !== FALSE)
		{
			// Make sure the query's an array
			$query = is_array($this->params->query) ? $this->params->query : array();

			// For Form and Filters tag, add the tagparams to the query, too
			// The Results tag might have other params assigned or hard-coded
			// parameters, which needn't be added to the query
			if (ee()->TMPL->tagparts[1] != 'results' && is_array($this->params->tagparams))
			{
				$query = array_merge($query, $this->params->tagparams);
			}

			// Encode it
			$query = empty($query) ? '' : mtt_search_encode($query);

			// And replace it in the template
			$haystack = str_replace($needle, sprintf($replace, $param, $query), $haystack);
		}

		return $haystack;
	}

	/**
	 * Replace query_string var, automatically fix pagination links
	 *
	 * @access     private
	 * @param      string
	 * @return     string
	 */
	private function _maintain_query_string($tagdata)
	{
		// Don't do anything if query's encoded
		if ($this->settings->get('encode_query') == 'y') return $tagdata;

		// Query string var
		$var = LD.$this->settings->prefix.'query_string'.RD;

		// Fix pagination for Results tag
		if (ee()->TMPL->fetch_param('mtt_search') == 'yes')
		{
			// Load up URL helper
			ee()->load->helper('url');

			// Get current URL
			$url = current_url();

			// Strip away pagination segment
			$url = preg_replace('#/P\d+/?$#', '', $url);

			// Make it safe
			$url = preg_quote($url, '#');

			// Now find all similar URLs in tagdata without the var next to it
			$tagdata = preg_replace("#(['\"])({$url}(/P\d+)?/?)\\1#", "$1$2{$var}$1", $tagdata);
		}

		// Get the query string
		if ($qs = (string) ee()->input->server('QUERY_STRING'))
		{
			$qs = '?' . $qs;
		}

		// Replace {mtt_search_query_string} vars
		$tagdata = str_replace($var, $qs, $tagdata);

		return $tagdata;
	}

	/**
	 * Post parse tagdata
	 *
	 * @access      private
	 * @param       string
	 * @return      string
	 */
	private function _post_parse($tagdata)
	{
		// If we're not encoding, maintain query string vars/URLs
		$tagdata = $this->_maintain_query_string($tagdata);

		// CLean up prefixed variables
		$tagdata = preg_replace('#'.LD.$this->settings->prefix.'.*?'.RD.'#i', '', $tagdata);

		// Prep {if foo IN (bar)} conditionals
		$tagdata = mtt_prep_in_conditionals($tagdata);

		// Transform {mtt_search:url ...} to their tag syntax equivalents
		// to avoid parse order woes
		$tagdata = $this->_rewrite_url_vars($tagdata);

		return $tagdata;
	}

	// --------------------------------------------------------------------

	/**
	 * Extract a parameter from tagparams (get and unset)
	 *
	 * @access     private
	 * @param      string
	 * @param      mixed
	 * @return     mixed
	 */
	private function _extract_tagparam($key, $fallback = NULL)
	{
		$val = ee()->TMPL->fetch_param($key, $fallback);
		unset(ee()->TMPL->tagparams[$key]);
		return $val;
	}

	// --------------------------------------------------------------------

	/**
	 * Log message to Template Logger
	 *
	 * @access     private
	 * @param      string
	 * @return     void
	 */
	private function _log($msg)
	{
		ee()->TMPL->log_item("Mtt Search: {$msg}");
	}

} // End Class

/* End of file mod.mtt_search.php */