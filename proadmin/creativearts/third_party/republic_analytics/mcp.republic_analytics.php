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

require_once PATH_THIRD.'republic_analytics/libraries/helper.php';
require_once PATH_THIRD.'republic_analytics/model/model.php';
/**
* Republic Analytics MCP
*
* @author			Ragnar Frosti Frostason - Republic Factory
* @link				http://www.republiclabs.com
* @license
*/

class Republic_analytics_mcp
{
    // --------------------------------------------------------------------
    /**
    * PHP4 Constructor
    *
    * @see	__construct()
    */
function Republic_analytics_mcp()
{
    $this->__construct();
}

// --------------------------------------------------------------------

/**
* PHP 5 Constructor
*
* @return	void
*/
function __construct()
{
    /** -------------------------------------
    /**  Get global instance
    /** -------------------------------------*/

$this->EE =& get_instance();

// Republic variable theme folder
//$this->theme_url = $this->EE->config->item('theme_folder_url');

$this->name = str_replace('_mcp', '', strtolower(get_class($this)));

// module url
$this->module_url = $this->data['mod_url'] = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->name;
$this->theme_url = $this->EE->config->item('theme_folder_url').'third_party/'.$this->name.'/';

if ( ! isset($this->EE->session->cache['republic_analytics'])) {
    $this->EE->session->cache['republic_analytics'] = array();
}
$this->cache =& $this->EE->session->cache['republic_analytics'];

$this->model  = new Republic_analytics_model();
$this->helper = new Republic_analytics_helper();
$this->model->get_configurations();
//echo $this->helper->get_profile();exit;
$this->addon_access = ($this->EE->session->userdata['group_id'] == '1' OR in_array($this->EE->session->userdata['group_id'],$this->cache['settings']['addon_access'])) ? TRUE : FALSE;
}


// --------------------------------------------------------------------


function index()
{

    // Load CSS
    $this->EE->cp->add_to_head('<link rel="stylesheet" href="'.$this->theme_url.'css/republic_analytics_default.css" type="text/css" media="screen" />');
    $this->EE->cp->add_to_head('<link rel="stylesheet" href="'.$this->theme_url.'css/jquery.jqplot.css" type="text/css" media="screen" />');

    // Load Javascript
    $this->EE->load->library('javascript');
    $this->EE->cp->load_package_js('jqplot/excanvas.min');
    $this->EE->cp->load_package_js('jqplot/jquery.jqplot.min');
    $this->EE->cp->load_package_js('jqplot/plugins/jqplot.barRenderer.min');
    $this->EE->cp->load_package_js('jqplot/plugins/jqplot.categoryAxisRenderer.min');
    $this->EE->cp->load_package_js('jqplot/plugins/jqplot.pointLabels.min');
    $this->EE->cp->load_package_js('jqplot/plugins/jqplot.dateAxisRenderer.min');
    $this->EE->cp->load_package_js('jqplot/plugins/jqplot.canvasTextRenderer.min');
    $this->EE->cp->load_package_js('jqplot/plugins/jqplot.canvasAxisTickRenderer.min');
    $this->EE->cp->load_package_js('jqplot/plugins/jqplot.highlighter');
    $this->EE->cp->load_package_js('jqplot/plugins/jqplot.cursor.min');
    $this->EE->cp->load_package_js('jqplot.custom');
    $this->EE->cp->load_package_js('spin.min');

    if (function_exists('ee')) {
    ee()->view->cp_page_title = $this->EE->lang->line('republic_analytics_main_name');
  } else {
    $this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('republic_analytics_main_name'));
  }
    $this->EE->cp->set_breadcrumb(BASE.AMP.$this->module_url, $this->EE->lang->line('republic_analytics_module_name'));

    $vars['profile_id'] = $this->EE->input->get('profile');

    // Do we have cached data?
    $vars['is_data_cached'] = FALSE;
    if ( ! empty($this->cache['settings']['cache']) && isset($this->cache['settings']['cache'][$this->helper->get_profile_id()])) {
        $vars['is_data_cached'] = TRUE;
    }

    return $this->_render('index_empty', $vars);
}

/**
* Home page for module, lists all variables
*
* @return	View
*/
function load_data()
{
    $vars[''] = array();

    // Title tag + breadcrumb
    if (function_exists('ee')) {
    ee()->view->cp_page_title = $this->EE->lang->line('republic_analytics_module_name');
  } else {
        $this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('republic_analytics_module_name'));
  }
    $this->EE->cp->set_breadcrumb(BASE.AMP.$this->module_url, $this->EE->lang->line('republic_analytics_module_name'));

    // Check if we've authenticated to Google Analytics
    if ( empty($this->cache['settings']['google_token'])) {
        return $this->_render('index_google_login', $vars);
    }

    // Get the current profile id
    $profile_id = $this->helper->get_profile_id();

    // If no profile id we return information page about that problem
    if ( empty($profile_id)) {
        return $this->_render('index_no_google_profile', $vars);
    }

    // Get settings and current profile name
    $vars['settings']             = $this->cache['settings'];
    $vars['google_profile_title'] = $this->helper->get_profile_name();

  $current_data_cache = $this->helper->get_cache();
    $this->cache['saved_last_update'] = isset($current_data_cache['last_updated']) ? $current_data_cache['last_updated'] : 0;

    // Get Source table view if it is used
    if ($this->cache['settings']['show_source_view'] === 'y') {
        $vars['sources'] = $this->helper->get_sources_statistics();
    }

    // Get Pages table view if it is used
    if ($this->cache['settings']['show_pages_view'] === 'y') {
        $vars['pages'] = $this->helper->get_pages_statistics();
    }

    // Get todays, yesterdays, last weeks, last months and graph data
    $data = $this->helper->get_table_statistics();

    $vars['table_data'] = isset($data['table_data']) ? $data['table_data'] : array();
    $vars['today']      = isset($data['today']) ? $data['today'] : array();
    $vars['yesterday']  = isset($data['yesterday']) ? $data['yesterday'] : array();
    $vars['week']       = isset($data['week']) ? $data['week'] : array();
    $vars['month']      = isset($data['month']) ? $data['month'] : array();

    $vars['is_google_connection_error'] = $this->helper->google->is_google_connection_error;
    $vars['settings'] 	= $this->cache['settings'];

    if ($vars['is_google_connection_error'] && ! isset($this->cache['settings']['cache'][$this->helper->get_profile_id()])) {
        return $this->_render('index_connection_error', $vars);
    }

    // Allow Super Admin or others to fast switch between profiles in statistics view
    $vars['google_accounts'] = array();
    $vars['current_profile'] = "";
    if ($this->EE->session->userdata['group_id'] === '1' OR in_array($this->EE->session->userdata['group_id'], $this->cache['settings']['google_allow_profile_switch'])) {
        $vars['google_accounts'] = $this->helper->get_google_profiles();
        $vars['current_profile'] = $profile_id;
    }

    return $this->_render('index', $vars);

}

function configurations()
{

    $this->EE->cp->add_to_head('<link rel="stylesheet" href="'.$this->theme_url.'css/jPicker-1.1.6.min.css" type="text/css" media="screen" />');
    $this->EE->load->library('javascript');
    $this->EE->cp->load_package_js('jpicker-1.1.6.min');
    $this->EE->cp->load_package_js('republic_analytics_configs');

    $s = (APP_VER < '2.6.0') ? "" : "S=" . $this->EE->input->get('S') . AMP;
    $vars['return_url']          = $this->EE->config->item('cp_url') . '?' . $s . 'D=cp' . AMP . $this->module_url.AMP.'method=authenticate';

    $vars['authenticate_url']    = $this->module_url.AMP.'method=login';
    $vars['action_url']          = $this->module_url.AMP.'method='.__FUNCTION__;
    $vars['logout_url']          = BASE.AMP.$this->module_url.AMP.'method=logout';
    $vars['google_url']			 = ""; //https://accounts.google.com/o/oauth2/auth?client_id=772822877733.apps.googleusercontent.com&redirect_uri=" . $google_return_url . "&response_type=code&scope=https://www.googleapis.com/auth/analytics.readonly&approval_prompt=force&access_type=offline";
    $vars['settings']            = $this->cache['settings'];
    $vars['theme_url']           = $this->theme_url;
    $vars['extension_installed'] = $this->model->is_extension_installed();

    if (function_exists('ee')) {
        ee()->view->cp_page_title = $this->EE->lang->line('republic_analytics_configurations');
    } else {
        $this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('republic_analytics_configurations'));
    }

    $this->EE->cp->set_breadcrumb(BASE.AMP.$this->module_url, $this->EE->lang->line('republic_analytics_module_name'));

    $member_groups = $vars['member_groups'] = $this->model->get_member_groups();

    $vars['google_accounts']    = array();
    $google_token               = (isset($this->cache['settings']['google_token']) && $this->cache['settings']['google_token'] != "") ? $this->cache['settings']['google_token'] : "";
    $google_account             = (!empty($this->cache['settings']['google_account'])) ? $this->cache['settings']['google_account'] : "";
    $google_allow_member_groups = (isset($this->cache['settings']['google_allow_member_groups']) && sizeof($this->cache['settings']['google_allow_member_groups']) > 0) ? 'y' : 'n';

    if ( ! empty($this->cache['settings']['google_token'])) {
        $vars['google_accounts'] = $this->helper->get_google_profiles();
        asort($vars['google_accounts']);
    }

    $vars['google_error']								= $this->helper->google->error;
    $vars['is_google_connection_error'] = $this->helper->google->is_google_connection_error;

    $vars['update_frequency'] = array(
        "-1 minutes"  => "1 minute",
        "-10 minutes" => "10 minutes",
        "-30 minutes" => "30 minutes",
        "-1 hour"     => "1 hour",
        "-2 hour"     => "2 hours",
        "-6 hour"     => "6 hours",
        "-12 hour"    => "12 hours",
        "-1 day"      => "1 day"
    );

    $vars['google_member_groups'] = (isset($this->cache['settings']['google_member_groups'])) ? $this->cache['settings']['google_member_groups'] : array();

    if ($this->EE->input->post('submit')) {
        // Load libraries
        $this->EE->load->library('form_validation');

        // Set validation rules
        $this->EE->form_validation->set_rules('addon_access[]', lang('republic_analytics_configuration_access'), 'trim');
        $this->EE->form_validation->set_rules('google_account', lang('republic_analytics_configuration_google_account'), 'trim');

        if ($this->EE->form_validation->run() === TRUE) {

            $this->model->save_configurations($vars['google_accounts']);
            $google_accounts = array();

            $this->EE->session->set_flashdata('message_success', $this->EE->lang->line('republic_analytics_configurations_saved'));
            $this->EE->functions->redirect(BASE.AMP.$this->module_url.AMP.'method='.__FUNCTION__);
        } else {
            $this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('republic_analytics_configurations_failure'));
            $this->EE->functions->redirect(BASE.AMP.$this->module_url.AMP.'method='.__FUNCTION__);
        }
    }

    return $this->_render('configurations', $vars);
}


function login()
{
    $client_id     = $this->EE->input->post('client_id');
    $client_secret = $this->EE->input->post('client_secret');
    $redirect_url  = urlencode($this->EE->input->post('redirect_url'));

    $this->model->update_google_credentials(array(
        'client_id'     => $client_id,
        'client_secret' => $client_secret,
        'redirect_url'  => $redirect_url
    ));

    $url = "https://accounts.google.com/o/oauth2/auth?client_id=" . $client_id . "&redirect_uri=" . $redirect_url . "&response_type=code&scope=https://www.googleapis.com/auth/analytics.readonly&approval_prompt=force&access_type=offline";

    $this->EE->functions->redirect($url);
}

function authenticate()
{
    if ($this->EE->input->get('code') === FALSE) {
        $this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('republic_analytics_authentication_error_no_code'));
        $this->EE->functions->redirect(BASE.AMP.$this->module_url.AMP.'method=configurations');
    }

    $this->helper->receiveTokens($this->EE->input->get('code'));

    if ($this->helper->google->error !== FALSE || $this->cache['settings']['google_token'] === '') {
        $error_message = $this->EE->lang->line('republic_analytics_authentication_error_no_refresh_token');

        if (isset($this->helper->google->error['code'])) {
            $error_message .= '<br />' . $this->EE->lang->line('republic_analytics_configuration_google_error_code') . $this->helper->google->error['code'];
        }
        if (isset($this->helper->google->error['message'])) {
            $error_message .= '<br />' . $this->EE->lang->line('republic_analytics_configuration_google_error_message') . $this->helper->google->error['message'];
        }
        $this->EE->session->set_flashdata('message_failure', $error_message);
        $this->EE->functions->redirect(BASE.AMP.$this->module_url.AMP.'method=configurations');
    }

    $this->EE->session->set_flashdata('message_success', $this->EE->lang->line('republic_analytics_configurations_google_success_no_profile'));
    $this->EE->functions->redirect(BASE.AMP.$this->module_url.AMP.'method=configurations');
}

function logout()
{
    $this->helper->logout();

    $this->EE->session->set_flashdata('message_success', $this->EE->lang->line('republic_analytics_logged_out'));
    $this->EE->functions->redirect(BASE.AMP.$this->module_url.AMP.'method=configurations');
}


/***************************
* RENDERING
****************************/
/**
* Render the view files, set the right navigation
*
* @return	void
*/
function _render($view = "", $vars = array())
{
    if ($this->addon_access) {
        $navigation = array(
            $this->EE->lang->line('republic_analytics_main_name')      => BASE.AMP.$this->module_url,
            $this->EE->lang->line('republic_analytics_configurations') => BASE.AMP.$this->module_url.AMP.'method=configurations'
        );
        $this->EE->cp->set_right_nav($navigation);
    }

    return $this->EE->load->view($view, $vars, TRUE);
}

}
// END CLASS

/* End of file mcp.republic_analytics.php */
