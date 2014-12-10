<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_THIRD.'republic_analytics/model/model.php';
require_once PATH_THIRD.'republic_analytics/libraries/Google.php';

/**
* Republic Analytic Helper
*
* @author     Ragnar Frosti Frostason - Republic Factory
* @link       http://www.republiclabs.com
* @license
*/

class Republic_analytics_helper
{

	// --------------------------------------------------------------------
	/**
	* PHP4 Constructor
	*
	* @see  __construct()
	*/
function Republic_analytics_helper()
{
	$this->__construct();
}

// --------------------------------------------------------------------

/**
* PHP 5 Constructor
*
* @return void
*/
function __construct()
{
	$this->EE =& get_instance();

	if ( ! isset($this->EE->session->cache['republic_analytics']))
	{
		$this->EE->session->cache['republic_analytics'] = array();
	}
	$this->cache =& $this->EE->session->cache['republic_analytics'];

	$this->name     = str_replace('_helper', '', strtolower(get_class($this)));
	$this->model    = new Republic_analytics_model();

	$this->google = new Google();
	$this->filter = $this->_create_filter();
}

function get_cache()
{
	return (isset($this->cache['settings']['cache']) && isset($this->cache['settings']['cache'][$this->get_profile_id()])) ? $this->cache['settings']['cache'][$this->get_profile_id()] : array();
}

/*
 * Get refresh/access tokens
 */
public function receiveTokens($code)
{
	$tokens = $this->google->receiveTokens($code);

	if ($this->google->error === FALSE && $this->google->is_google_connection_error === FALSE && isset($tokens['refresh_token']))
	{
		$this->model->update_google_token($tokens);
	}
}

/*
 * Get a new access token
 */
public function refreshAccessToken()
{
	$access_token = $this->google->refreshAccessToken();

	if ($this->google->error === FALSE && $this->google->is_google_connection_error === FALSE && isset($access_token['access_token']))
	{
		$this->model->update_access_token($access_token);
	}
}

/*
 * Check if the access token has expired
 */
public function check_access_key()
{
	$expired = ($this->cache['settings']['access_token_created'] + ($this->cache['settings']['access_token_expires_in'] - 30)) < time();
	if ($expired)
	{
		$this->refreshAccessToken();
	}
}

public function logout()
{
	$this->google->revokeToken();
	$this->model->clear_google_account_data_from_db();
}

public function get_google_profiles()
{

	$this->check_access_key();

	$request = $this->google->request_account_data(1, 150);

	if ($this->google->error !== FALSE OR $this->google->is_google_connection_error !== FALSE)
	{
		return array();
	}

	$google_accounts = array();
	foreach ($request['items'] AS $account)
	{
		$google_accounts[$account['id']]['title'] = $account['name'];
	}

	asort($google_accounts);
	return $google_accounts;
}

public function get_profile_id()
{
	if ($this->EE->input->get('profile') !== FALSE)
	{
		return $this->EE->input->get('profile');
	}

	if (isset($this->cache['settings']['group_google_account'][$this->EE->session->userdata['group_id']]))
	{
		return $this->cache['settings']['group_google_account'][$this->EE->session->userdata['group_id']]['profile_id'];
	}

	return isset($this->cache['settings']['google_account']['profile_id']) ? $this->cache['settings']['google_account']['profile_id'] : "";
}

public function get_profile_name()
{
	if ($this->EE->input->get('profile') !== FALSE)
	{
		$profiles = $this->get_google_profiles();
		return $profiles[$this->EE->input->get('profile')]['title'];
	}

	if (isset($this->cache['settings']['group_google_account'][$this->EE->session->userdata['group_id']]))
	{
		return $this->cache['settings']['group_google_account'][$this->EE->session->userdata['group_id']]['profile_name'];
	}
	return isset($this->cache['settings']['google_account']['profile_name']) ? $this->cache['settings']['google_account']['profile_name'] : "";
}

function get_sources_statistics($start_date = "", $end_date = "")
{
	$cache = $this->get_cache();

	if ( ! empty($cache) && isset($cache['sources_statistics']) && $this->cache['saved_last_update'] >= strtotime($this->cache['settings']['update_frequency']))
	{
		return $cache['sources_statistics'];
	}

	$this->check_access_key();

	$dimensions  = array('source');
	$metrics     = array('visits', 'bounces', 'avgTimeOnSite');
	$filter      = "";//$this->filter;
	$sort        = array('-visits');
	$end_date    = ($end_date === "") ? date('Y-m-d') : $end_date;
	$new_date     = strtotime ( '-3 months' , strtotime ( $end_date ) ) ;
	$start_date  = ($start_date === "") ? date ( 'Y-m-d' , $new_date ) : $start_date;
	$start_index = 1;
	$max_results = 100;
	$results = $this->google->requestReportData(
		$this->get_profile_id(),
		$dimensions,
		$metrics,
		$sort,
		$filter,
		$start_date,
		$end_date,
		$start_index,
		$max_results
	);

	if ($this->google->is_google_connection_error !== FALSE)
	{
		if ( ! empty($cache) && isset($cache['sources_statistics']))
		{
			return $cache['sources_statistics'];
		}

		return array();
	}

	$array = array();

	foreach ($results AS $result)
	{
		$array[$result->getSource()] =  array(
			'visits'        => $result->getVisits(),
			'bounces'       => $result->getBounces(),
			'avgTimeOnSite' => $result->getavgTimeOnSite(),
		);
	}

	// If array is empty (connection error) or and there is data in the cache, show that.
	if (empty($array) && ! empty($cache))
	{
		return (empty($cache['sources_statistics'])) ? array() : $cache['sources_statistics'];
	}

	// If array is empty (connection error) or and there is no data in the cache, return empty array.
	if (empty($array) && empty($cache))
	{
		return array();
	}

	$this->model->update_statistics_cache('sources_statistics', $array, $this->get_profile_id());

	return $array;
}

function get_pages_statistics($start_date = "", $end_date = "")
{
	$cache = $this->get_cache();


	if ( ! empty($cache) && isset($cache['page_statistics']) && $this->cache['saved_last_update'] >= strtotime($this->cache['settings']['update_frequency']))
	{
		return $cache['page_statistics'];
	}

	$this->check_access_key();

	$dimensions  = array('pagePath', 'pageTitle', 'hostname');
	$metrics     = array('uniquePageViews', 'bounces', 'avgTimeOnPage');
	$filter			 = $this->filter;
	$sort        = array('-uniquePageViews');
	$end_date    = ($end_date === "") ? date('Y-m-d') : $end_date;
  $new_date     = strtotime ( '-3 months' , strtotime ( $end_date ) ) ;
  $start_date  = ($start_date === "") ? date ( 'Y-m-d' , $new_date ) : $start_date;
	$start_index = 1;
	$max_results = 100;
	$results = $this->google->requestReportData(
		$this->get_profile_id(),
		$dimensions,
		$metrics,
		$sort,
		$filter,
		$start_date,
		$end_date,
		$start_index,
		$max_results
	);

	if ($this->google->is_google_connection_error !== FALSE)
	{
		if ( ! empty($cache) && isset($cache['page_statistics']))
		{
			return $cache['page_statistics'];
		}

		return array();
	}

	$pagePaths = array();
	$array = array();
	$i = 1;
	foreach ($results as $result)
	{
		// Do we already have this page path?
		$page_path = ($result->getPagePath() !== "/") ? rtrim($result->getPagePath(), '/') : $result->getPagePath();
		if ($path_exists = array_search($page_path, $pagePaths))
		{
			// Add the additional information for this path to the array
			$visits = $array[$path_exists]['visits'];
			$array[$path_exists]['visits']  = $array[$path_exists]['visits']  + $result->getUniquePageViews();
			$array[$path_exists]['bounces'] = $array[$path_exists]['bounces'] + $result->getBounces();
			$totalAvgTimeOnPage = ($array[$path_exists]['avgTimeOnPage'] * $visits ) + ($result->getUniquePageViews() * $result->getavgTimeOnPage());
			$array[$path_exists]['avgTimeOnPage'] = $totalAvgTimeOnPage / ($visits + $result->getUniquePageViews());
		}
		else
		{
			$array[$i] = array(
				'hostname' 			=> $result->getHostname(),
				'pagePath' 			=> $page_path,
				'pageTitle' 		=> $result->getPageTitle(),
				'visits' 				=> $result->getUniquePageViews(),
				'bounces' 			=> $result->getBounces(),
				'avgTimeOnPage' => $result->getavgTimeOnPage()
			);

			// Store the page path at the same position so we can check for dupes
			$pagePaths[$i] = $page_path;

			$i++;
		}
	}

	// If array is empty (connection error) or and there is data in the cache, show that.
	if (empty($array) && ! empty($cache) && isset($cache['page_statistics']))
	{
		return $cache['page_statistics'];
	}
	// If array is empty (connection error) or and there is no data in the cache, return empty array.
	if (empty($array) && empty($cache) && ! isset($cache['page_statistics']))
	{
		return array();
	}

	$this->model->update_statistics_cache('page_statistics', $array, $this->get_profile_id());

	return $array;
}

function decode_brower_version($browser, $version)
{
	switch ($browser)
	{
		case 'Safari':
		$version = 'All';
		break;
		case 'Internet Explorer':
		$version = explode('.', $version);
		$version = $version[0];
		break;
		case 'Chrome':
		$version = explode('.', $version);
		$version = $version[0];
		break;
		case 'Firefox':
		$version = explode('.', $version);
		$version = $version[0];
		break;
		case 'Mozilla Compatible Agent':
		$version = explode('.', $version);
		$version = $version[0];
		break;
		case 'Opera':
		$version = explode('.', $version);
		$version = $version[0];
		break;
		case 'Android Browser':
		$version = 'All';
		break;
		case 'IE with Chrome Frame':
		$version = explode('.', $version);
		$version = $version[0];
		break;
		case 'RockMelt':
		$version = 'All';
		break;
	}

	return $version;
}

function get_table_statistics($start_date = "", $end_date = "")
{
	$array = array();
	$cache = $this->get_cache();

	$last_date_from_array = "";
	if ( ! empty($cache) && isset($cache['table_statistics']))
	{
		$last_date_from_array = end(array_keys($cache['table_statistics']));
	}

	if ( ! empty($cache) && isset($cache['table_statistics']) && $this->cache['saved_last_update'] >= strtotime($this->cache['settings']['update_frequency']))
	{
		$array = $cache['table_statistics'];
	}
	else
	{

		// If there is connection error
		if ($this->google->is_google_connection_error)
		{
			// If we have cached data
			if ( ! empty($cache) && isset($cache['table_statistics']))
			{
				$array = $cache['table_statistics'];
			}
			else
			{
				return array();
			}
		}
		else
		{
			$dimensions = array();
			$dimensions[] = 'date';

			if ($this->cache['settings']['show_operativsystem_view'] === 'y')
			{
				$dimensions[] = 'operatingSystem';
			}

			if ($this->cache['settings']['show_browser_view'] === 'y')
			{
				$dimensions[] = 'browser';
				$dimensions[] = 'browserVersion';
			}

			$metrics    = array('visits', 'pageViews', 'visitors', 'bounces', 'avgTimeOnSite', 'newVisits');
			$filter     = $this->filter;
			$sort       = array('date');
			$end_date    = ($end_date === "") ? date('Y-m-d') : $end_date;
      $new_date     = strtotime ( '-3 months' , strtotime ( $end_date ) ) ;
      $start_date  = ($start_date === "") ? date ( 'Y-m-d' , $new_date ) : $start_date;

			// Only get data from the last date already caught.
			if ( ! empty($last_date_from_array)) {
				$start_date = $last_date_from_array;
			}

			$start_index = 1;
			$max_results = 10000;

			$this->check_access_key();

			$results = $this->google->requestReportData(
				$this->get_profile_id(),
				$dimensions,
				$metrics,
				$sort,
				$filter,
				$start_date,
				$end_date,
				$start_index,
				$max_results
			);

			if ($this->google->is_google_connection_error !== FALSE)
			{
				$results = array();
			}

			$test = 0;

			foreach ($results AS $result)
			{

				$year   = substr($result->getDate(), 0, 4);
				$month  = substr($result->getDate(), 4, 2);
				$day    = substr($result->getDate(), 6);
				$date   =  $year.'-'.$month.'-'.$day;
				$visits = $result->getVisits();

				$array[$date]['getavgTimeOnSiteVisits'] = isset($array[$date]['getavgTimeOnSiteVisits']) ? ($array[$date]['getavgTimeOnSiteVisits'] + $visits) : $visits;

				$array[$date]['pageViews']     = isset($array[$date]['pageViews']) ? ($array[$date]['pageViews'] + $result->getPageViews()) : $result->getPageViews();
				$array[$date]['visits']        = isset($array[$date]['visits']) ? ($array[$date]['visits'] + $visits) : $visits;
				$array[$date]['visitors']      = isset($array[$date]['visitors']) ? ($array[$date]['visitors'] + $result->getVisitors()) : $result->getVisitors();
				$array[$date]['bounces']       = isset($array[$date]['bounces']) ? ($array[$date]['bounces'] + $result->getBounces()) : $result->getBounces();
				$array[$date]['avgTimeOnSite'] = isset($array[$date]['avgTimeOnSite']) ? ($array[$date]['avgTimeOnSite'] + ($result->getavgTimeOnSite()*$visits)) : $result->getavgTimeOnSite() * $visits;
				$array[$date]['newVisits']     = isset($array[$date]['newVisits']) ? ($array[$date]['newVisits'] + $result->getNewVisits()) : $result->getNewVisits();
				if ($this->cache['settings']['show_operativsystem_view'] === 'y')
				{
					$array[$date]['operativsystems'][$result->getOperatingSystem()] = isset($array[$date]['operativsystems'][$result->getOperatingSystem()]) ? ($array[$date]['operativsystems'][$result->getOperatingSystem()] + $visits) : $visits;
				}
				if ($this->cache['settings']['show_browser_view'] === 'y')
				{
					$browser = $result->getBrowser();
					$version = $this->decode_brower_version($browser, $result->getBrowserVersion());
					$array[$date]['browser'][$browser]['total'] = isset($array[$date]['browser'][$browser]['total']) ? ($array[$date]['browser'][$browser]['total'] + $visits) : $visits;
					$array[$date]['browser'][$browser]['version'][$version] = isset($array[$date]['browser'][$result->getBrowser()]['version'][$version]) ? ($array[$date]['browser'][$browser]['version'][$version] + $visits) : $visits;
				}
			}

			$end_date;
			$start_date;
			$date_i     = $start_date;
			$date_array = array();
			$data       = array(
				'getavgTimeOnSiteVisits' => 0,
				'visits'                 => 0,
				'pageViews'              => 0,
				'visitors'               => 0,
				'bounces'                => 0,
				'avgTimeOnSite'          => 0,
				'newVisits'              => 0,
				'operativsystems'        => array(),
				'browser'                => array()
			);

			$date_array[$date_i] = $data;

			while ($date_i < $end_date)
			{
				$date_i = date( 'Y-m-d' , strtotime( '+1 day' , strtotime( $date_i ) ));
				$date_array[$date_i] = $data;
			}

			$array = array_merge($date_array, $array);

			// Calculate Average time on site for each date
			foreach ($array AS $key => $row)
			{
				if (isset($array[$key]['getavgTimeOnSiteVisits']))
				{
					$array[$key]['avgTimeOnSite'] = ($array[$key]['visits'] !== 0) ? $array[$key]['avgTimeOnSite'] / $array[$key]['visits'] : 0;
				}
			}

			// If array is empty (connection error) or and there is data in the cache, show that.
			if ( ! empty($array) && ! empty($cache) && isset($cache['table_statistics']))
			{
				$cached_array = $cache['table_statistics'];
				foreach ($array AS $key => $entry)
				{
					$cached_array[$key] = $entry;
				}

				// Remove unneccesary data older than 3months from now
				$this->EE->load->helper('date');
				$cache_last_date = date ( 'Y-m-d', strtotime ( '-3 months' , human_to_unix($end_date . ' 00:00:00')));
				foreach ($cached_array AS $key => $entry)
				{
					if ($key < $cache_last_date)
					{
						unset($cached_array[$key]);
					}
				}
				$array = $cached_array;
			}

			// If array is empty (connection error) or and there is data in the cache, show that.
			if (empty($array) && ! empty($cache) && isset($cache['table_statistics']))
			{
				$array = $cache['table_statistics'];
			}

			// If array is empty (connection error) or and there is no data in the cache, return empty array.
			if (empty($array) && empty($cache))
			{
				return array();
			}
			$this->model->update_statistics_cache('table_statistics', $array, $this->get_profile_id());
		}
	}

	$i = 1;
	$sizeOfArray = sizeof($array);
	$week_array  = array();
	$month_array = array();

	$tmp_array = $array;
	krsort($tmp_array);

	foreach ($tmp_array AS $row)
	{
		// Weekly
		if ($i < 8)
		{
			$week_array[] = $row;
		}

		// Monthly
		if ($i < 31)
		{
			$month_array[] = $row;
		}
		else
		{
			break;
		}

		$i++;
	}

	// Sums up the statistics for weekly and monthly views
	$week_array  = $this->_statistic_summary($week_array);
	$month_array = $this->_statistic_summary($month_array);

	// Sort and group operativsystem or browser data
	if ($this->cache['settings']['show_operativsystem_view'] === 'y' OR $this->cache['settings']['show_browser_view'] === 'y')
	{
		$this->sort_special_data($array);
		$this->sort_special_data($week_array, TRUE);
		$this->sort_special_data($month_array, TRUE);
	}

	$today     = date('Y-m-d',strtotime("today"));
	$today     = isset($array[$today]) ? $array[$today] : array();
	$yesterday = date('Y-m-d',strtotime("yesterday"));
	$yesterday = isset($array[$yesterday]) ? $array[$yesterday] : array();


	// The following calculates parameters used for graph
	$return['dateGoogleVisits']     = "";
	$return['countGoogleVisits']    = "";
	$return['countGooglePageViews'] = "";
	$return['maxGoogleVisits']      = 0;
	$return['maxGooglePageViews']   = 0;
	$return['maxGoogleVisitors']    = 0;
	$return['countGoogleVisitors']  = "";

	$return['dateFirst'] = "";
	$return['dateLast']  = "";

	ksort($array);
	foreach ($array AS $key => $item)
	{

		if ($return['dateFirst'] === "")
		{
			$return['dateFirst'] = $key;
		}

		$return['dateLast'] = $key;

		if ($item['visits'] > $return['maxGoogleVisits'])
		{
			$return['maxGoogleVisits'] = $item['visits'];
		}

		if ($item['visitors'] > $return['maxGoogleVisitors'])
		{
			$return['maxGoogleVisitors'] = $item['visitors'];
		}

		if ($item['pageViews'] > $return['maxGooglePageViews'])
		{
			$return['maxGooglePageViews'] = $item['pageViews'];
		}

		$return['dateGoogleVisits']     .= "'" . $key . "'" . ",";
		$return['countGoogleVisits']    .= $key.",".$item['visits'] . ";";
		$return['countGoogleVisitors']  .= $key.",".$item['visitors'] . ";";
		$return['countGooglePageViews'] .= $key.",".$item['pageViews'] . ";";
	}

	$return['dateGoogleVisits']     = substr($return['dateGoogleVisits'], 0, -1);
	$return['countGoogleVisits']    = substr($return['countGoogleVisits'], 0, -1);
	$return['countGoogleVisitors']  = substr($return['countGoogleVisitors'], 0, -1);
	$return['countGooglePageViews'] = substr($return['countGooglePageViews'], 0, -1);
	$return['maxGoogleVisitsY']     = $this->_get_y_axis_value($return['maxGoogleVisits']);
	$return['maxGoogleVisitorsY']   = $this->_get_y_axis_value($return['maxGoogleVisitors']);
	$return['maxGooglePageViewsY']  = $this->_get_y_axis_value($return['maxGooglePageViews']);

	return array(
		'table_data' => $return,
		'today'      => $today,
		'yesterday'  => $yesterday,
		'month'      => $month_array,
		'week'       => $week_array
		);
}



function sort_special_data(&$array, $single_array = FALSE)
{

	if ( ! $single_array)
	{
		function cmp($a, $b)
		{
			if ($a['total'] === $b['total'])
			{
				return 0;
			}
			return ($a['total'] > $b['total']) ? -1 : 1;
		}

		foreach ($array AS $date_key => $entry)
		{
			if ($this->cache['settings']['show_operativsystem_view'] === 'y')
			{
				arsort($array[$date_key]['operativsystems']);
			}
			if ($this->cache['settings']['show_browser_view'] === 'y')
			{
				uasort($array[$date_key]['browser'], "cmp");
				foreach ($array[$date_key]['browser'] AS $browser_key => $version)
				{
					arsort($array[$date_key]['browser'][$browser_key]['version']);
				}
			}
		}
	}
	else
	{
		if ($this->cache['settings']['show_operativsystem_view'] === 'y')
		{
			arsort($array['operativsystems']);
		}
		if ($this->cache['settings']['show_browser_view'] === 'y')
		{
			uasort($array['browser'], "cmp");
			foreach ($array['browser'] AS $browser_key => $version)
			{
				arsort($array['browser'][$browser_key]['version']);
			}
		}
	}

}

function _statistic_summary($array)
{
	$new_array['visits']        = 0;
	$new_array['visitors']      = 0;
	$new_array['bounces']       = 0;
	$new_array['avgTimeOnSite'] = 0;
	$new_array['newVisits']     = 0;
	$new_array['pageViews']     = 0;

	if ($this->cache['settings']['show_operativsystem_view'] === 'y')
	{
		$new_array['operativsystems'] = array();
	}
	if ($this->cache['settings']['show_browser_view'] === 'y')
	{
		$new_array['browser'] = array();
	}

	foreach ($array AS $row)
	{
		$new_array['visits']        += $row['visits'];
		$new_array['visitors']      += $row['visitors'];
		$new_array['bounces']       += $row['bounces'];
		$new_array['avgTimeOnSite'] += ($row['avgTimeOnSite'] * $row['visits']);
		$new_array['newVisits']     += $row['newVisits'];
		$new_array['pageViews']     += $row['pageViews'];

		if ($this->cache['settings']['show_operativsystem_view'] === 'y')
		{
			foreach ($row['operativsystems'] AS $operativsystem => $visits)
			{
				$new_array['operativsystems'][$operativsystem] = isset($new_array['operativsystems'][$operativsystem]) ? $new_array['operativsystems'][$operativsystem] + $visits: $visits;
			}
		}
		if ($this->cache['settings']['show_browser_view'] === 'y')
		{
			foreach ($row['browser'] AS $browser => $value)
			{

				$new_array['browser'][$browser]['total'] = isset($new_array['browser'][$browser]['total']) ? $new_array['browser'][$browser]['total'] + $value['total']: $value['total'];
				foreach ($value['version'] AS $version => $version_count)
				{
					$new_array['browser'][$browser]['version'][$version] = isset($new_array['browser'][$browser]['version'][$version]) ? $new_array['browser'][$browser]['version'][$version] + $version_count: $version_count;
				}
			}
		}
	}

	$size = sizeof($new_array);
	$new_array['avgTimeOnSite'] = ($new_array['visits'] !== 0) ? $new_array['avgTimeOnSite'] / $new_array['visits'] : 0;

	return $new_array;
}

function _create_filter()
{
	$filter = "";

	if ( ! empty($this->cache['settings']['exclude_hosts']))
	{
		$data 	= explode('|', $this->cache['settings']['exclude_hosts']);
		$filter = 'hostname!=' . implode('&&hostname!=', $data);
	}

	if ( ! empty($this->cache['settings']['include_hosts']))
	{
		$data 	 = explode('|', $this->cache['settings']['include_hosts']);
		$filter .= 'hostname==' . implode('||hostname==', $data);
	}

	return $filter;
}


function _get_y_axis_value($value)
{

	if ($value <= 10)
	{
		return 1;
	}

	if ($value <= 100)
	{
		return (100 / 2 < $value) ? 10 : 5;
	}

	if ($value <= 1000)
	{
		return (1000 / 2 < $value) ? 100 : 50;
	}

	if ($value <= 10000)
	{
		return (10000 / 2 < $value) ? 1000 : 500;
	}

	if ($value <= 100000)
	{
		return (100000 / 2 < $value) ? 10000 : 5000;
	}

	if ($value <= 1000000)
	{
		return (1000000 / 2 < $value) ? 100000 : 50000;
	}

	if ($value <= 10000000)
	{
		return (10000000 / 2 < $value) ? 1000000 : 500000;
	}
}

}
// END CLASS

/* End of file helper.php */
