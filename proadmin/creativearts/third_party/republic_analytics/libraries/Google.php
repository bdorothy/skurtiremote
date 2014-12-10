<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Google
{

  private $auth_url = 'https://accounts.google.com/o/oauth2/auth';
  private $data_url = 'https://www.googleapis.com/analytics/v3/data/ga';
  private $token_url = 'https://accounts.google.com/o/oauth2/token';
  private $redirect_uri = '';

  public $is_google_connection_error = FALSE;
  public $error = FALSE;


  public function __construct() {
    if ( ! function_exists('curl_init'))
    {
      throw new Exception('The API requires the CURL PHP extension');
    }

    if ( ! function_exists('json_decode'))
    {
      throw new Exception('The API requires the JSON PHP extension');
    }

    $this->EE =& get_instance();

    if ( ! isset($this->EE->session->cache['republic_analytics']))
    {
      $this->EE->session->cache['republic_analytics'] = array();
    }

    $this->cache =& $this->EE->session->cache['republic_analytics'];
  }

  function revokeToken()
  {
    $url = 'https://accounts.google.com/o/oauth2/revoke?token=' . $this->cache['settings']['google_token'];
    $request = $this->post($url, FALSE, FALSE);
  }

  function receiveTokens($code = '')
  {
    $parameters  = 'code=' . $code;
    $parameters .= '&client_id=' . $this->cache['settings']['client_id'];
    $parameters .= '&client_secret=' . $this->cache['settings']['client_secret'];
    $parameters .= '&redirect_uri=' . $this->cache['settings']['redirect_url'];
    $parameters .= '&grant_type=authorization_code';

    return $this->post($this->token_url, $parameters, FALSE);
  }

  function refreshAccessToken()
  {
    $parameters  = '&client_id=' . $this->cache['settings']['client_id'];
    $parameters .= '&client_secret=' . $this->cache['settings']['client_secret'];
    $parameters .= '&refresh_token=' . $this->cache['settings']['google_token'];
    $parameters .= '&grant_type=refresh_token';

    $data = $this->post($this->token_url, $parameters, FALSE);
    return $data === FALSE ? FALSE : $data;
  }

  function request_account_data($start_index = 1, $max_results = 20)
  {
    $url = 'https://www.googleapis.com/analytics/v3/management/accounts/~all/webproperties/~all/profiles?max-results=' . $max_results . '&start-index=' . $start_index . '';
    $data = $this->post($url, FALSE, TRUE);
    return $data === FALSE ? array() : $data;
  }

  function requestReportData($report_id = NULL, $dimensions = NULL, $metrics, $sort_metric = NULL, $filter = NULL, $start_date = NULL, $end_date = NULL, $start_index = 1, $max_results = 10000)
  {
    $parameters = array('ids'=>'ga:' . $report_id);

    if (is_array($dimensions)) {
      $dimensions_string = '';
      foreach ($dimensions as $dimesion) {
        $dimensions_string .= ',ga:' . $dimesion;
      }
      $parameters['dimensions'] = substr($dimensions_string, 1);
    } elseif ($dimensions !== null) {
      $parameters['dimensions'] = 'ga:'.$dimensions;
    }

    if (is_array($metrics)) {
      $metrics_string = '';
      foreach ($metrics as $metric) {
        $metrics_string .= ',ga:' . $metric;
      }
      $parameters['metrics'] = substr($metrics_string, 1);
    } else {
      $parameters['metrics'] = 'ga:'.$metrics;
    }

    if ($sort_metric==null&&isset($parameters['metrics'])) {
      $parameters['sort'] = $parameters['metrics'];
    } elseif (is_array($sort_metric)) {
      $sort_metric_string = '';

      foreach ($sort_metric as $sort_metric_value) {
        //Reverse sort - Thanks Nick Sullivan
        if (substr($sort_metric_value, 0, 1) == "-") {
          $sort_metric_string .= ',-ga:' . substr($sort_metric_value, 1); // Descending
        }
        else {
          $sort_metric_string .= ',ga:' . $sort_metric_value; // Ascending
        }
      }

      $parameters['sort'] = substr($sort_metric_string, 1);
    } else {
      if (substr($sort_metric, 0, 1) == "-") {
        $parameters['sort'] = '-ga:' . substr($sort_metric, 1);
      } else {
        $parameters['sort'] = 'ga:' . $sort_metric;
      }
    }

    if ($filter!=null) {
      $filter = $this->processFilter($filter);
      if ($filter!==false) {
        $parameters['filters'] = $filter;
      }
    }

    if ($start_date==null) {
      // Use the day that Google Analytics was released (1 Jan 2005).
      $start_date = '2005-01-01';
    } elseif (is_int($start_date)) {
      // Perhaps we are receiving a Unix timestamp.
      $start_date = date('Y-m-d', $start_date);
    }

    $parameters['start-date'] = $start_date;

    if ($end_date == null) {
      $end_date = date('Y-m-d');
    } elseif (is_int($end_date)) {
      // Perhaps we are receiving a Unix timestamp.
      $end_date = date('Y-m-d', $end_date);
    }

    $parameters['end-date'] = $end_date;


    $parameters['start-index'] = $start_index;
    $parameters['max-results'] = $max_results;

    $parameters['prettyprint'] = 'false';

    $url = 'https://www.googleapis.com/analytics/v3/data/ga?' . str_replace('&amp;', '&', urldecode(http_build_query($parameters)));
    $data = $this->post($url, FALSE, TRUE);

    if ($this->is_google_connection_error === TRUE)
    {
      return array();
    }

    return $this->generate_result($data);
  }

  /**
   * Process filter string, clean parameters and convert to Google Analytics
   * compatible format
   *
   * @param String $filter
   * @return String Compatible filter string
   */
  protected function processFilter($filter) {
    $valid_operators = '(!~|=~|==|!=|>|<|>=|<=|=@|!@)';
    $filter = preg_replace('/\s\s+/', ' ', trim($filter)); //Clean duplicate whitespace
    $filter = str_replace(array(',', ';'), array('\,', '\;'), $filter); //Escape Google Analytics reserved characters
    $filter = preg_replace('/(&&\s*|\|\|\s*|^)([a-z]+)(\s*' . $valid_operators . ')/i','$1ga:$2$3',$filter); //Prefix ga: to metrics and dimensions
    $filter = preg_replace('/[\'\"]/i', '', $filter); //Clear invalid quote characters
    $filter = preg_replace(array('/\s*&&\s*/','/\s*\|\|\s*/','/\s*' . $valid_operators . '\s*/'), array(';', ',', '$1'), $filter); //Clean up operators

    if (strlen($filter) > 0) {
      return urlencode($filter);
    }
    else {
      return false;
    }
  }

  function generate_result($data)
  {
    $results = array();

    $google_entry = array();
    $headers = array();
    $rows = array();
    foreach ($data['columnHeaders'] AS $header)
    {
      $headers[] = str_replace('ga:', '', $header['name']);
    }

    foreach ($data['rows'] AS $row)
    {
      $i = 0;
      for ($i = 0; $i < sizeof($row); $i++)
      {
        $google_entry[$headers[$i]] = $row[$i];
      }
      $results[] = new gapiAccountEntry($google_entry);
    }

    return $results;
  }

  function post($url, $parameters = FALSE, $access_token = FALSE)
  {
    $ch = curl_init();
    $timeout = 5;
    curl_setopt( $ch, CURLOPT_URL, $url );

    if ($parameters !== FALSE)
    {
      curl_setopt( $ch, CURLOPT_POST, 1);
      curl_setopt( $ch, CURLOPT_POSTFIELDS, $parameters);
    }

    if ($access_token !== FALSE)
    {
      curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Authorization: Bearer ' . $this->cache['settings']['access_token'] ));
    }

    curl_setopt( $ch, CURLOPT_RETURNTRANSFER,1 );
    curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $data = curl_exec( $ch );

    $error_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close( $ch );

    if ($error_code !== 200)
    {
      if ($error_code === 0)
      {
        $this->is_google_connection_error = TRUE;
      }
      else
      {
        $data = json_decode($data, TRUE);

        $this->error = array(
          'code'    => $error_code,
          'message' => (isset($data['error']) && isset($data['error']['message'])) ? $data['error']['message'] : ''
        );
      }

      return array();
    }

    $this->error = FALSE;
    $this->is_google_connection_error = FALSE;


    return json_decode($data, TRUE);
  }
}

/**
 * Class gapiAccountEntry
 *
 * Storage for individual gapi account entries
 *
 */
class gapiAccountEntry {
  private $properties = array();

  /**
   * Constructor function for all new gapiAccountEntry instances
   *
   * @param Array $properties
   * @return gapiAccountEntry
   */
  public function __construct($properties) {
    $this->properties = $properties;
  }

  /**
   * toString function to return the name of the account
   *
   * @return String
   */
  public function __toString() {
    return isset($this->properties['title']) ?
      $this->properties['title']: false;
  }

  /**
   * Get an associative array of the properties
   * and the matching values for the current result
   *
   * @return Array
   */
  public function getProperties() {
    return $this->properties;
  }

  /**
   * Call method to find a matching parameter to return
   *
   * @param $name String name of function called
   * @return String
   * @throws Exception if not a valid parameter, or not a 'get' function
   */
  public function __call($name, $parameters) {
    if (!preg_match('/^get/', $name)) {
      throw new Exception('No such function "' . $name . '"');
    }

    $name = preg_replace('/^get/', '', $name);

    $property_key = array_key_exists_nc($name, $this->properties);

    if ($property_key) {
      return $this->properties[$property_key];
    }

    throw new Exception('No valid property called "' . $name . '"');
  }
}

/**
 * Case insensitive array_key_exists function, also returns
 * matching key.
 *
 * @param String $key
 * @param Array $search
 * @return String Matching array key
 */
function array_key_exists_nc($key, $search) {
  if (array_key_exists($key, $search)) {
    return $key;
  }
  if (!(is_string($key) && is_array($search))) {
    return false;
  }
  $key = strtolower($key);
  foreach ($search as $k => $v) {
    if (strtolower($k) == $key) {
      return $k;
    }
  }
  return false;
}

// END CLASS

/* End of file Google.php */
