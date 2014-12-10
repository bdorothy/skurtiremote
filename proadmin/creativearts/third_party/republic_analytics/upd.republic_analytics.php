<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*

                                    __/---\__
                     ,___     ___  /___o--\  \
                      \_ o---/ _/          )--)
                        \-----/           ______
                                          |    |
                                          |    |
                    ---_    ---_    ---_  |    |
                    |   \__ |   \__ |   \__    |
                    |      \__     \__     \__ o
                    |         `       `      \__
                    |                          |
                    |                          |
                    |__________________________|

                    | ) |_´ | ) | | |_) |  | / '
                    | \ |_, |´  \_/ |_) |_,| \_,
                            F A C T O R Y

Republic Analytics made by Republic Factory AB <http://www.republic.se> and is
licensed under a Creative Commons Attribution-NoDerivs 3.0 Unported License
<http://creativecommons.org/licenses/by-nd/3.0/>.

You can use it for free, both in personal and commercial projects as long as
this attribution in left intact. But, by downloading this add-on you also take
full responsibility for anything that happens while using it. The add-on is
made with love and passion, and is used by us on daily basis, but we cannot
guarantee that it works equally well for you.

See Republic Labs site <http://republiclabs.com> for more information.

*/


require_once PATH_THIRD.'republic_analytics/config.php';
require_once PATH_THIRD.'republic_analytics/model/model.php';
require_once PATH_THIRD.'republic_analytics/libraries/helper.php';

/**
* Republic Analytics MCP
*
* @author     Ragnar Frosti Frostason - Republic Factory
* @link       http://www.republiclabs.com
* @license
*/
class Republic_analytics_upd {

  /**
  * Version number
  *
  * @var  string
  */
  var $version = REPUBLIC_ANALYTICS_VERSION;

  // --------------------------------------------------------------------

  /**
  * PHP4 Constructor
  *
  * @see  __construct()
  */
  function Republic_analytics_upd()
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

    // set module name
    $this->name = str_replace('_upd', '', ucfirst(get_class($this)));
  }

  // --------------------------------------------------------------------

  /**
  * Install the module
  *
  * @return bool
  */
  function install()
  {
    $this->EE->db->insert('modules', array(
      'module_name'        => $this->name,
      'module_version'     => $this->version,
      'has_cp_backend'     => 'y',
      'has_publish_fields' => 'n'
    ));

    $this->EE->load->dbforge();

    // Create the variable parent table
    $fields = array(
    'id'       => array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => TRUE),
    'site_id'  => array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE),
    'settings' => array('type' => 'longtext')
    );

    $this->EE->dbforge->add_field($fields);
    $this->EE->dbforge->add_key('id', TRUE);
    $this->EE->dbforge->create_table('republic_analytics');

    $model = new Republic_analytics_model();
    $default_settings = $model->get_default_settings();

    $this->EE->db->insert('republic_analytics', array(
      'site_id'  => $this->EE->config->item('site_id'),
      'settings' => base64_encode(serialize($default_settings))
    ));

    unset($fields);

    return TRUE;
  }

  // --------------------------------------------------------------------

  /**
  * Uninstall the module
  *
  * @return bool
  */
  function uninstall()
  {
    $this->EE->load->dbforge();

    $model = new Republic_analytics_model();
    $helper = new Republic_analytics_helper();
    $model->get_configurations();
    $helper->logout();

    // get module id
    $this->EE->db->select('module_id');
    $this->EE->db->from('exp_modules');
    $this->EE->db->where('module_name', $this->name);
    $query = $this->EE->db->get();

    // remove references from module_member_groups
    $this->EE->db->where('module_id', $query->row('module_id'));
    $this->EE->db->delete('module_member_groups');

    // remove references from modules
    $this->EE->db->where('module_name', $this->name);
    $this->EE->db->delete('modules');

    // Drop the module tables
    $this->EE->dbforge->drop_table('republic_analytics');

    return TRUE;
  }

  // --------------------------------------------------------------------

  /**
  * Update the module
  *
  * @return bool
  */
  function update($current = '')
  {

    if ($current === '' OR version_compare($current, $this->version) === 0)
    {
      return FALSE;
    }

    if (version_compare($current, '1.1.1', '<'))
    {
      $this->EE->db->select('settings');
      $this->EE->db->where('site_id', $this->EE->config->item('site_id'));
      $row = $this->EE->db->get('republic_analytics')->row_array();

      $settings = unserialize(base64_decode($row['settings']));

      $settings['browser_view'] = array('show' => 'y');
      $settings['operativsystem_view'] = array('show' => 'y');

      $data = array(
        'settings' => base64_encode(serialize($settings)),
        'site_id'  => $this->EE->config->item('site_id')
      );

      $this->EE->db->update('republic_analytics', $data);
    }

    if (version_compare($current, '1.4', '<'))
    {
      $this->EE->db->select('site_id, settings');
      $result = $this->EE->db->get('republic_analytics')->result_array();

      foreach ($result AS $row)
      {
        $settings = unserialize(base64_decode($row['settings']));
        $settings['google_token'] = "";
        unset($settings['google_username']);
        unset($settings['google_password']);
        unset($settings['cache']);
        $data = array(
          'settings' => base64_encode(serialize($settings)),
        );
        $this->EE->db->where('site_id', $row['site_id']);
        $this->EE->db->update('republic_analytics', $data);
      }
    }

    if (version_compare($current, '1.6', '<'))
    {
      $this->EE->db->select('site_id, settings');
      $result = $this->EE->db->get('republic_analytics')->result_array();

      foreach ($result AS $row)
      {
        $settings = unserialize(base64_decode($row['settings']));
        $settings['group_google_account'] = $settings['google_allow_member_groups'];
        $settings['google_allow_member_groups'] = (is_array($settings['google_allow_member_groups']) && sizeof($settings['google_allow_member_groups']) > 0) ? 'y' : 'n';
        $settings['google_allow_profile_switch'] = array();

        $data = array(
          'settings' => base64_encode(serialize($settings)),
        );
        $this->EE->db->where('site_id', $row['site_id']);
        $this->EE->db->update('republic_analytics', $data);
      }
    }


    if (version_compare($current, '1.6.5', '<'))
    {
      $this->EE->db->select('site_id, settings');
      $result = $this->EE->db->get('republic_analytics')->result_array();

      foreach ($result AS $row)
      {
        $settings = unserialize(base64_decode($row['settings']));

        // Clear the Google data to force new login using authSub
        $settings['google_allow_profile_switch'] = array();
        $settings['google_token']                = '';
        $settings['google_account']              = '';
        $settings['cache']                       = array();
        $settings['group_google_account']        = array();
        $settings['update_frequency']            = '-30 minutes';
        $settings['exclude_hosts']               = '';
        $settings['include_hosts']               = '';
        $settings['show_monthly_view']           = $settings['monthly_view']['show'];
        $settings['show_today_view']             = $settings['today_view']['show'];
        $settings['show_yesterday_view']         = $settings['yesterday_view']['show'];
        $settings['show_week_view']              = $settings['week_view']['show'];
        $settings['show_month_view']             = $settings['month_view']['show'];
        $settings['show_source_view']            = $settings['source_view']['show'];
        $settings['show_pages_view']             = $settings['pages_view']['show'];
        $settings['show_browser_view']           = $settings['browser_view']['show'];
        $settings['show_operativsystem_view']    = $settings['operativsystem_view']['show'];

        unset($settings['google_username']);
        unset($settings['google_password']);
        unset($settings['monthly_view']);
        unset($settings['today_view']);
        unset($settings['yesterday_view']);
        unset($settings['week_view']);
        unset($settings['month_view']);
        unset($settings['source_view']);
        unset($settings['pages_view']);
        unset($settings['browser_view']);
        unset($settings['operativsystem_view']);

        $data = array(
          'settings' => base64_encode(serialize($settings)),
        );

        $this->EE->db->where('site_id', $row['site_id']);
        $this->EE->db->update('republic_analytics', $data);
      }
    }


    if (version_compare($current, '2.1', '<'))
    {
      $this->EE->db->select('site_id, settings');
      $result = $this->EE->db->get('republic_analytics')->result_array();

      foreach ($result AS $row)
      {
        $settings = unserialize(base64_decode($row['settings']));

        // Empty cache
        $settings['cache']                       = array();

        // Add new fields
        $settings['override_homepage_icon']   = 'n';
        $settings['override_homepage_page']   = 'n';
        $settings['show_pages_view_in_table'] = 'n';
        $settings['pages_view_color']         = '#EEEEEE';

        $data = array(
          'settings' => base64_encode(serialize($settings)),
        );

        $this->EE->db->where('site_id', $row['site_id']);
        $this->EE->db->update('republic_analytics', $data);
      }
    }

    if (version_compare($current, '2.2', '<'))
    {
      $this->EE->db->select('site_id, settings');
      $result = $this->EE->db->get('republic_analytics')->result_array();

      foreach ($result AS $row)
      {
        $settings = unserialize(base64_decode($row['settings']));

        // Empty cache
        $settings['cache']                   = array();

        // Add new fields
        $settings['google_token']            = '';
        $settings['access_token']            = '';
        $settings['access_token_expires_in'] = '';
        $settings['access_token_created']    = '';
        $settings['client_id']               = '';
        $settings['client_secret']           = '';
        $settings['redirect_url']            = '';

        $data = array(
          'settings' => base64_encode(serialize($settings)),
        );

        $this->EE->db->where('site_id', $row['site_id']);
        $this->EE->db->update('republic_analytics', $data);
      }
    }

    return TRUE;
  }
}
// END CLASS

/* End of file upd.republic_analytics.php */
