<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Mtt Search Params class, to handle parameters
 *
 * @package        mtt_search
 * @author         MyTopTeacher - MTT <contact@mytopteacher.com>
 * @link           http://mytopteacher.com/addons/mtt-search
 * @copyright      Copyright (c) 2014, Mtt
 */
class Mtt_search_params {

	// --------------------------------------------------------------------
	// PROPERTIES
	// --------------------------------------------------------------------

	/**
	 * Params to "forget"
	 *
	 * @access     public
	 * @var        array
	 */
	public $forget = array();

	/**
	 * Parameters
	 *
	 * @access     private
	 * @var        array
	 */
	private $_params = array();

	/**
	 * Given query
	 *
	 * @access     private
	 * @var        array
	 */
	private $_query;

	/**
	 * Original tagparams
	 *
	 * @access     private
	 * @var        array
	 */
	private $_tagparams = array();

	/**
	 * Site ids
	 *
	 * @access     private
	 * @var        array
	 */
	private $_site_ids = array();

	// --------------------------------------------------------------------
	// METHODS
	// --------------------------------------------------------------------

	/**
	 * Set an internal parameter, or set all if none given
	 *
	 * @access     public
	 * @param      mixed
	 * @param      mixed
	 * @return     void
	 */
	public function set($key = NULL, $val = NULL)
	{
		if (empty($key))
		{
			$this->_set_all();
		}
		elseif (is_array($key))
		{
			$this->_params = array_merge($this->_params, $key);
		}
		elseif (is_string($key) && is_null($val))
		{
			$this->_params = array_merge($this->_params, mtt_search_decode($key));
		}
		elseif ( ! is_null($key))
		{
			$this->_params[$key] = $val;
		}

		$this->_filter();
	}

	/**
	 * Delete a parameter
	 */
	public function delete($key)
	{
		unset($this->_params[$key]);
		unset(ee()->TMPL->tagparams[$key]);
	}

	/**
	 * Read all parameters from query param or GET
	 *
	 * @access     private
	 * @return     void
	 */
	private function _set_all()
	{
		// --------------------------------------
		// Reset
		// --------------------------------------

		$this->_params    = array();
		$this->_query     = NULL;
		$this->_tagparams = array();

		// --------------------------------------
		// Check for given query
		// --------------------------------------

		if (ee()->mtt_search_settings->get('encode_query') == 'y')
		{
			// Read the query parameter
			$query_param = ee()->TMPL->fetch_param('query');

			// If query is given (not FALSE or empty string), try and decode it
			// Also ignore pagination segment
			if ( ! empty($query_param) && ! preg_match('/^P\d+$/', $query_param))
			{
				$query_val = mtt_search_decode($query_param);
				$this->_query = (empty($query_val)) ? FALSE : $query_val;
			}
		}
		else
		{
			// Or else get it from the GET vars
			foreach ($_GET AS $key => $val)
			{
				// Skip arrays
				if (is_array($val)) continue;

				// Strip slashes if < PHP 5.4
				if (version_compare(PHP_VERSION, '5.4', '<'))
				{
					$val = stripslashes($val);
				}

				$this->_query[$key] = $val;
			}
		}

		// --------------------------------------
		// Combine query with default custom parameters
		// --------------------------------------

		if (is_array($this->_query))
		{
			$this->_params = $this->_query;
		}

		// --------------------------------------
		// Remember current tagparams
		// --------------------------------------

		if (is_array(ee()->TMPL->tagparams))
		{
			$this->_tagparams = ee()->TMPL->tagparams;

			// but not the query param
			unset($this->_tagparams['query']);
		}

		// --------------------------------------
		// Also set the site IDs
		// --------------------------------------

		$this->_set_site_ids();
	}

	/**
	 * Completely overwrite current params with given array
	 */
	public function overwrite($array, $query = FALSE)
	{
		$this->_params = $array;
		if ($query) $this->_query = $array;
		$this->_filter();
	}

	/**
	 * Combine tagparams and other params
	 */
	public function combine()
	{
		$this->_params = array_merge($this->_params, $this->_tagparams);
		$this->_set_site_ids();
		$this->_filter();
	}

	/**
	 * Is given query valid?
	 */
	public function valid_query()
	{
		return ! ($this->_query === FALSE OR $this->_query === array());
	}

	/**
	 * Is there a given query, ie. not NULL
	 */
	public function query_given()
	{
		return ! is_null($this->_query);
	}

	/**
	 * Filter params to remove array values
	 */
	private function _filter()
	{
		foreach ($this->_params AS $key => $val)
		{
			if (is_array($val))
			{
				unset($this->_params[$key]);
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Set defaults
	 */
	public function set_defaults()
	{
		$pfx = 'default:';

		if ($defaults = $this->get_prefixed($pfx, TRUE))
		{
			foreach ($defaults AS $key => $val)
			{
				// Set the default only if not already there
				if (empty($this->_params[$key]))
				{
					$this->_params[$key] = $val;
				}

				// And forget it again
				$this->delete($pfx.$key);
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Return site ids
	 */
	public function site_ids()
	{
		if (empty($this->_site_ids))
		{
			$this->_set_site_ids();
		}

		return $this->_site_ids;
	}

	/**
	 * Get site ids by parameter
	 *
	 * @access      private
	 * @return      void
	 */
	private function _set_site_ids()
	{
		// Reset
		$this->_site_ids = array();

		if (empty($this->_params['site']))
		{
			// No site param? limit to current site only
			$this->_site_ids[] = ee()->config->item('site_id');
		}
		else
		{
			// Read sites from parameter
			list($sites, $in) = mtt_explode_param($this->_params['site']);

			// Shortcut to all sites
			$all_sites = ee()->TMPL->sites;

			// Numeric?
			$check = mtt_array_is_numeric($sites) ? 'key' : 'val';

			// Loop through all sites and add some of them
			foreach ($all_sites AS $key => $val)
			{
				if ($in === in_array($$check, $sites))
				{
					$this->_site_ids[$val] = $key;
				}
			}

			// And set to global TMPL
			ee()->TMPL->site_ids = $this->_site_ids;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Get parameter from $this->_params with fallback
	 *
	 * @access     public
	 * @param      string
	 * @param      mixed
	 * @return     mixed
	 */
	public function get($key = NULL, $fallback = NULL)
	{
		if (is_null($key))
		{
			return $this->_params;
		}
		else
		{
			return empty($this->_params[$key])
				? $fallback
				: $this->_params[$key];
		}
	}

	/**
	 * Get prefixed parameters
	 *
	 * @access     public
	 * @param      string
	 * @return     array
	 */
	public function get_prefixed($prefix = '', $strip = FALSE)
	{
		return mtt_array_get_prefixed($this->_params, $prefix, $strip);
	}

	/**
	 * Get vars
	 */
	public function get_vars($prefix = '')
	{
		$this->_filter();
		$vars = array();

		foreach ($this->_params AS $key => $val)
		{
			// force string
			$val = (string) $val;
			$vars[$prefix.$key.':raw'] = $val;
			$vars[$prefix.$key] = mtt_format($val);
		}

		return $vars;
	}

	/**
	 * Magic getter
	 */
	public function __get($key)
	{
		$key = '_'.$key;
		return isset($this->$key) ? $this->$key : NULL;
	}

	// --------------------------------------------------------------------

	/**
	 * Apply parameter key and value to TMPL tagparams
	 *
	 * @access     public
	 * @param      mixed
	 * @param      string
	 * @return     void
	 */
	public function apply($key = NULL, $val = NULL)
	{
		// What are we applying to the TMPL tagparams?
		if (empty($key))
		{
			$vars = $this->_params;
		}
		elseif (is_array($key))
		{
			$vars = $key;
		}
		else
		{
			$vars = array($key => $val);
		}

		// Loop through vars and set the tagparam
		foreach ($vars AS $k => $v)
		{
			// If forget, set to NULL, else prep it
			$v = in_array($k, $this->forget)
			   ? NULL
			   : $this->prep($k, $v);

			// Set it
			$this->_set_tagparam($k, $v);
		}
	}

	/**
	 * Set TMPL tagparam
	 *
	 * @access     private
	 * @param      string
	 * @param      string
	 * @param      bool
	 * @return     void
	 */
	private function _set_tagparam($key, $val, $overwrite = TRUE)
	{
		// Check for search fields and add parameter to either tagparams or search_fields
		if (substr($key, 0, 7) == 'search:')
		{
			$key = substr($key, 7);
			$array = 'search_fields';

		}
		else
		{
			$array = 'tagparams';
		}

		if ($overwrite || empty(ee()->TMPL->{$array}[$key]))
		{
			if (is_null($val))
			{
				unset(ee()->TMPL->{$array}[$key]);
			}
			else
			{
				ee()->TMPL->{$array}[$key] = $val;
			}
		}
	}



	// --------------------------------------------------------------------

	/**
	 * Check if a value is present in a parameter
	 */
	public function in_param($val, $param)
	{
		$it = FALSE;

		if ($param = $this->get($param))
		{
			list($fields, $in) = mtt_explode_param($param);

			$it = in_array($val, $fields);
		}

		return $it;
	}

	/**
	 * Prep param value
	 */
	public function prep($key, $val)
	{
		$val = $this->_require_all_value($key, $val);
		$val = $this->_exclude_value($key, $val);
		$val = $this->_exact_value($key, $val);
		$val = $this->_starts_with_value($key, $val);
		$val = $this->_ends_with_value($key, $val);
		return $val;
	}

	/**
	 * Check if given key is in the require_all="" parameter
	 */
	private function _require_all_value($key, $val)
	{
		if ($this->in_param($key, 'require_all'))
		{
			$amp = (substr($key, 0, 7) == 'search:') ? '&&' : '&';
			$val = str_replace('|', $amp, $val);
		}

		return $val;
	}

	/**
	 * Check if given key is in the exclude="" parameter
	 */
	private function _exclude_value($key, $val)
	{
		if ($this->in_param($key, 'exclude') && substr($val, 0, 4) != 'not ')
		{
			$val = 'not '.$val;
		}

		return $val;
	}

	/**
	 * Check if given key is in the exact="" parameter
	 */
	private function _exact_value($key, $val)
	{
		if ($this->in_param($key, 'exact') && substr($val, 0, 1) != '=')
		{
			$val = '='.$val;
		}

		return $val;
	}

	/**
	 * Check if given key is in the starts_with="" parameter
	 */
	private function _starts_with_value($key, $val)
	{
		if ($this->in_param($key, 'starts_with') && substr($val, 0, 1) != '^')
		{
			$val = '^'.$val;
		}

		return $val;
	}

	/**
	 * Check if given key is in the ends_with="" parameter
	 */
	private function _ends_with_value($key, $val)
	{
		if ($this->in_param($key, 'ends_with') && substr($val, -1) != '$')
		{
			$val = $val.'$';
		}

		return $val;
	}
}
// End of file Mtt_search_params.php