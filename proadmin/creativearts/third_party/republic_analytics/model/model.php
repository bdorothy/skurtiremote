<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once PATH_THIRD.'republic_analytics/libraries/helper.php';

/**
* Republic Analytics model
*
* @author     Ragnar Frosti Frostason - Republic Factory
* @link       http://www.republiclabs.com
* @license
*/

class Republic_analytics_model {

  // --------------------------------------------------------------------
  /**
  * PHP4 Constructor
  *
  * @see  __construct()
  */
function Republic_analytics_model()
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
  /** -------------------------------------
  /**  Get global instance
  /** -------------------------------------*/

$this->EE =& get_instance();


if ( ! isset($this->EE->session->cache['republic_analytics']))
{
  $this->EE->session->cache['republic_analytics'] = array();
}
$this->cache =& $this->EE->session->cache['republic_analytics'];

}

/**
* Get all configurations
*
* @return array
*/
function get_configurations()
{
  $this->EE->db->select('settings');
  $this->EE->db->where('site_id', $this->EE->config->item('site_id'));
  $result = $this->EE->db->get('republic_analytics');

  if ($result->num_rows() == 0)
  {
    $default_settings = $this->get_default_settings();

    $this->EE->db->insert('republic_analytics', array(
      'site_id'  => $this->EE->config->item('site_id'),
      'settings' => base64_encode(serialize($default_settings))
    ));

    $this->EE->db->select('settings');
    $this->EE->db->where('site_id', $this->EE->config->item('site_id'));
    $result = $this->EE->db->get('republic_analytics');
  }
  $row = $result->row_array();
  $this->cache['settings'] = unserialize(base64_decode($row['settings']));
}

function get_default_settings()
{
  $this->EE->load->model('member_model');
  $member_groups = $vars['member_groups'] = $this->EE->member_model->get_member_groups(array(), array(array('can_access_cp' => 'y')))->result_array();
  $member_group_redirect_on_login = array();
  foreach($member_groups AS $member_group)
  {
    $member_group_redirect_on_login[] = $member_group['group_id'];
  }

  return array(
    'redirect_on_login'              => 'n',
    'override_homepage_icon'         => 'n',
    'override_homepage_page'         => 'n',
    'member_group_redirect_on_login' => $member_group_redirect_on_login,
    'graph_type'                     => 'bar',
    'visits_color'                   => '#CCCCCC',
    'visitors_color'                 => '#95A4AF',
    'pages_view_color'               => '#EEEEEE',
    'addon_access'                   => array(),
    'google_token'                   => '',
    'access_token'                   => '',
    'access_token_expires_in'        => '',
    'access_token_created'           => '',
    'google_account'                 => '',
    'client_id'                      => '',
    'client_secret'                  => '',
    'redirect_url'                   => '',
    'cache'                          => array(),
    'google_allow_profile_switch'    => array(),
    'google_allow_member_groups'     => 'n',
    'group_google_account'           => array(),
    'update_frequency'               => '-30 minutes',
    'show_monthly_view'              => 'y',
    'show_today_view'                => 'y',
    'show_yesterday_view'            => 'y',
    'show_week_view'                 => 'y',
    'show_month_view'                => 'y',
    'show_source_view'               => 'y',
    'show_pages_view'                => 'y',
    'show_browser_view'              => 'y',
    'show_operativsystem_view'       => 'y',
    'show_pages_view_in_table'       => 'n',
    'exclude_hosts'                  => '',
    'include_hosts'                  => ''
  );
}

function update_google_credentials($credentials)
{
  $this->cache['settings']['client_id']     = $credentials['client_id'];
  $this->cache['settings']['client_secret'] = $credentials['client_secret'];
  $this->cache['settings']['redirect_url']  = $credentials['redirect_url'];

  $data = array(
    'settings' => base64_encode(serialize($this->cache['settings'])),
  );

  $this->EE->db->where('site_id', $this->EE->config->item('site_id'));
  $this->EE->db->update('republic_analytics', $data);

  $this->get_configurations();
}

function update_google_token($google_token)
{
  $this->cache['settings']['google_token']            = $google_token['refresh_token'];
  $this->cache['settings']['access_token']            = $google_token['access_token'];
  $this->cache['settings']['access_token_expires_in'] = $google_token['expires_in'];
  $this->cache['settings']['access_token_created']    = time();

  $data = array(
    'settings' => base64_encode(serialize($this->cache['settings'])),
  );

  $this->EE->db->where('site_id', $this->EE->config->item('site_id'));
  $this->EE->db->update('republic_analytics', $data);

  $this->get_configurations();
}

function update_access_token($access_token_data)
{
  $this->cache['settings']['access_token']            = $access_token_data['access_token'];
  $this->cache['settings']['access_token_expires_in'] = $access_token_data['expires_in'];
  $this->cache['settings']['access_token_created']    = time();

  $data = array(
    'settings' => base64_encode(serialize($this->cache['settings'])),
  );
  $this->EE->db->where('site_id', $this->EE->config->item('site_id'));
  $this->EE->db->update('republic_analytics', $data);

  $this->get_configurations();
}

function clear_google_account_data_from_db()
{
  $config = $this->cache['settings'];

  $config['google_token']            = '';
  $config['google_account']          = '';
  $config['access_token']            = '';
  $config['access_token_expires_in'] = '';
  $config['access_token_created']    = '';
  $config['client_id']               = '';
  $config['client_secret']           = '';
  $config['redirect_url']            = '';
  $config['cache']                   = array();
  $config['group_google_account']    = array();

  $data = array(
    'settings' => base64_encode(serialize($config)),
  );

  $this->EE->db->where('site_id', $this->EE->config->item('site_id'));
  $this->EE->db->update('republic_analytics', $data);

  $this->get_configurations();
}

/**
* save the configurations
*
* @return array
*/
function save_configurations($google_accounts)
{
  $row_array = $this->EE->db->select('*')->where('site_id', $this->EE->config->item('site_id'))->get('republic_analytics')->row_array();
  $old_settings = unserialize(base64_decode($row_array['settings']));

  $addon_access = $this->EE->input->post('addon_access') != "" ? $this->EE->input->post('addon_access') : array();

  $exclude_hosts = $this->EE->input->post('exclude_hosts');
  $include_hosts = $this->EE->input->post('include_hosts');
  $data = array(
    'redirect_on_login'              => $this->EE->input->post('redirect_on_login'),
    'member_group_redirect_on_login' => $this->EE->input->post('member_group_redirect_on_login'),
    'override_homepage_icon'         => $this->EE->input->post('override_homepage_icon'),
    'override_homepage_page'         => $this->EE->input->post('override_homepage_page'),
    'addon_access'                   => $addon_access,
    'graph_type'                     => $this->EE->input->post('graph_type'),
    'visits_color'                   => '#' . ltrim(trim($this->EE->input->post('visits_color')), '#'),
    'visitors_color'                 => '#' . ltrim(trim($this->EE->input->post('visitors_color')), '#'),
    'pages_view_color'               => '#' . ltrim(trim($this->EE->input->post('pages_view_color')), '#'),
    'google_token'                   => isset($old_settings['google_token']) ? $old_settings['google_token'] : '',
    'access_token'                   => isset($old_settings['access_token']) ? $old_settings['access_token'] : '',
    'access_token_expires_in'        => isset($old_settings['access_token_expires_in']) ? $old_settings['access_token_expires_in'] : '',
    'access_token_created'           => isset($old_settings['access_token_created']) ? $old_settings['access_token_created'] : '',
    'client_id'                      => isset($old_settings['client_id']) ? $old_settings['client_id'] : '',
    'client_secret'                  => isset($old_settings['client_secret']) ? $old_settings['client_secret'] : '',
    'redirect_url'                   => isset($old_settings['redirect_url']) ? $old_settings['redirect_url'] : '',
    'google_account'                 => $this->combine_profile_id_with_name($google_accounts, $this->EE->input->post('google_account')),
    'cache'                          => array(),
    'google_allow_profile_switch'    => $this->EE->input->post('google_allow_profile_switch'),
    'google_allow_member_groups'     => $this->EE->input->post('google_allow_member_groups'),
    'group_google_account'           => array(),
    'update_frequency'               => $this->EE->input->post('update_frequency'),
    'show_monthly_view'              => $this->EE->input->post('show_monthly_view'),
    'show_today_view'                => $this->EE->input->post('show_today_view'),
    'show_yesterday_view'            => $this->EE->input->post('show_yesterday_view'),
    'show_week_view'                 => $this->EE->input->post('show_week_view'),
    'show_month_view'                => $this->EE->input->post('show_month_view'),
    'show_source_view'               => $this->EE->input->post('show_source_view'),
    'show_pages_view'                => $this->EE->input->post('show_pages_view'),
    'show_browser_view'              => $this->EE->input->post('show_browser_view'),
    'show_operativsystem_view'       => $this->EE->input->post('show_operativsystem_view'),
    'show_pages_view_in_table'       => $this->EE->input->post('show_pages_view_in_table'),
    'exclude_hosts'                  => trim($exclude_hosts),
    'include_hosts'                  => trim($include_hosts)
  );

  // Prepare different profiles for different member groups
  if ($this->EE->input->post('google_allow_member_groups') == 'y')
  {
    $member_groups = array();
    $groups        = $this->EE->input->post('group_google_account');

    foreach ($groups AS $group => $profile_id)
    {
      if ( ! empty($profile_id))
      {
        $member_groups[$group] = $this->combine_profile_id_with_name($google_accounts, $profile_id);
      }
    }

    $data['group_google_account'] = $member_groups;
  }

  $data = array(
    'settings' => base64_encode(serialize($data)),
  );

  if (sizeof($row_array) === 0)
  {
    $data['site_id']   = $this->EE->config->item('site_id');
    $this->EE->db->insert('republic_analytics', $data);
  }
  else
  {
    $this->EE->db->where('site_id', $this->EE->config->item('site_id'));
    $this->EE->db->update('republic_analytics', $data);
  }

  $this->get_configurations();
}

private function combine_profile_id_with_name($google_accounts, $profile_id)
{
  if ($profile_id == "" OR sizeof($google_accounts) == 0 OR !isset($google_accounts[$profile_id]))
  {
    return array();
  }

  return array('profile_id' => $profile_id, 'profile_name' => $google_accounts[$profile_id]['title']);
}

function update_statistics_cache($name, $statistics, $profile_id)
{
  $this->cache['settings']['cache'][$profile_id][$name] = $statistics;
  $this->cache['settings']['cache'][$profile_id]['last_updated'] = time();

  $data = array(
    'settings' => base64_encode(serialize($this->cache['settings'])),
    'site_id'  => $this->EE->config->item('site_id')
    );
  $this->EE->db->where('site_id', $this->EE->config->item('site_id'));
  $this->EE->db->update('republic_analytics', $data);

  $this->get_configurations();
}


function module_is_installed()
{
  // get module id
  $this->EE->db->select('module_id');
  $this->EE->db->from('modules');
  $this->EE->db->where('module_name', 'republic_analytics');
  $query = $this->EE->db->get();

  if ($query->num_rows() == 0)
  {
    return FALSE;
  }

  return TRUE;
}

function is_extension_installed()
{
  // get module id
  $this->EE->db->select('*');
  $this->EE->db->from('extensions');
  $this->EE->db->where('class', 'Republic_analytics_ext');
  $this->EE->db->where('enabled', 'y');
  $query = $this->EE->db->get();

  if ($query->num_rows() === 0)
  {
    return FALSE;
  }

  return TRUE;
}

function get_member_group_id($username)
{
  $this->EE->db->select('group_id');
  $this->EE->db->where('username', $username);
  $result = $this->EE->db->get('members')->row_array();

  return $result['group_id'];
}

function get_member_groups()
{
  $this->EE->load->model('member_model');
  $member_groups = $this->EE->member_model->get_member_groups(array(), array(array('can_access_cp' => 'y')))->result_array();

  $module_id = $this->get_module_id();
  $this->EE->db->select('group_id');
  $this->EE->db->where('module_id', $module_id);
  $module_access = $this->EE->db->get('module_member_groups')->result_array();

  if ( ! is_array($module_access) )
  {
    $module_access = array();
  }
  else
  {
    $tmp_array = array();
    foreach ($module_access AS $group)
    {
      $tmp_array[] = $group['group_id'];
    }
    $module_access = $tmp_array;
  }

  foreach ($member_groups AS $key => $member_group)
  {
    if ( $member_group['group_id'] != '1' && ! in_array($member_group['group_id'], $module_access))
    {
      unset($member_groups[$key]);
    }
  }

  return $member_groups;
}

function get_module_id()
{
  $this->EE->db->select('module_id');
  $this->EE->db->where('module_name', 'Republic_analytics');
  $module_info = $this->EE->db->get('modules')->row_array();

  return $module_info['module_id'];
}

}
// END CLASS

/* End of file model.php */
