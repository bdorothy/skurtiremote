<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Channel Ratings Module Control Panel Class
 *
 * @package			DevDemon_ChannelRatings
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2010 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com
 * @see				http://expressionengine.com/user_guide/development/module_tutorial.html#control_panel_file
 */
class Channel_ratings_mcp
{
	/**
	 * Views Data
	 * @var array
	 * @access private
	 */
	private $vData = array();

	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		// Creat EE Instance
		$this->EE =& get_instance();

		// Load Models & Libraries & Helpers
		$this->EE->load->library('ratings_helper');
		$this->EE->load->model('ratings_mcp_model');
		$this->EE->load->model('ratings_model');
		$this->site_id = $this->EE->config->item('site_id');
		$this->EE->ratings_helper->define_theme_url();

		// Some Globals
		$this->base = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=channel_ratings';
		$this->base_short = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=channel_ratings';

		$this->collections = $this->EE->ratings_model->get_collections();
		$this->current_collection = $this->EE->ratings_helper->get_current_collection();

		// Global Views Data
		$this->vData['base_url'] = $this->base;
		$this->vData['base_url_short'] = $this->base_short;
		$this->vData['method'] = $this->EE->input->get('method');
		$this->vData['collections'] = $this->collections;
		$this->vData['current_collection'] = $this->current_collection;
		$this->vData['current_collection_label'] = '';

		foreach ($this->collections as $id => $obj)
		{
			if ($this->current_collection == $id) $this->vData['current_collection_label'] = $obj->collection_label;
		}

		// -----------------------------------------
		// Add Help!
		// -----------------------------------------
		if (isset($this->EE->session->cache['ChannelRatings']['JSON_help']) == FALSE && AJAX_REQUEST == FALSE)
		{
			$this->vData['helpjson'] = array();
			$this->vData['alertjson'] = array();

			foreach ($this->EE->lang->language as $key => $val)
			{
				if (strpos($key, 'cr:help:') === 0)
				{
					$this->vData['helpjson'][substr($key, 8)] = $val;
					unset($this->EE->lang->language[$key]);
				}

				if (strpos($key, 'cr:alert:') === 0)
				{
					$this->vData['alertjson'][substr($key, 9)] = $val;
					unset($this->EE->lang->language[$key]);
				}

			}

			$this->vData['helpjson'] = $this->EE->ratings_helper->generate_json($this->vData['helpjson']);
			$this->vData['alertjson'] = $this->EE->ratings_helper->generate_json($this->vData['alertjson']);
			$this->EE->session->cache['ChannelRatings']['JSON_help'] = TRUE;
		}

		$this->mcp_globals();
	}

	// ********************************************************************************* //

	function index()
	{
		// Are they the same?
		if (version_compare(APP_VER, '2.6.0', '>=')) {
			ee()->view->cp_page_title = $this->EE->lang->line('cr:ratings');
		} else {
			$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('cr:ratings'));
		}

		$this->vData['PageHeader'] = 'ratings';

		$this->vData['rating_types'] = $this->EE->ratings_mcp_model->get_rating_types();

		return $this->EE->load->view('mcp/index', $this->vData, TRUE);
	}

	// ********************************************************************************* //

	function likes()
	{
		// Are they the same?
		if (version_compare(APP_VER, '2.6.0', '>=')) {
			ee()->view->cp_page_title = $this->EE->lang->line('cr:likes');
		} else {
			$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('cr:likes'));
		}

		$this->vData['PageHeader'] = 'likes';

		$this->vData['rating_types'] = $this->EE->ratings_mcp_model->get_rating_types();

		return $this->EE->load->view('mcp/likes', $this->vData, TRUE);
	}

	// ********************************************************************************* //

	function fields()
	{
		// Are they the same?
		if (version_compare(APP_VER, '2.6.0', '>=')) {
			ee()->view->cp_page_title = $this->EE->lang->line('cr:fields');
		} else {
			$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('cr:fields'));
		}


		$this->vData['PageHeader'] = 'fields';

		$query = $this->EE->db->select("*")->from('exp_channel_ratings_fields')->where('collection_id', $this->current_collection)->get();
		$this->vData['fields'] = $query->result();

		return $this->EE->load->view('mcp/fields', $this->vData, TRUE);
	}

	// ********************************************************************************* //

	public function add_field()
	{
		// Page Title
		if (version_compare(APP_VER, '2.6.0', '>=')) {
			ee()->view->cp_page_title = $this->EE->lang->line('cr:field_add');
		} else {
			$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('cr:field_add'));
		}

		$this->vData['PageHeader'] = 'fields';

		$this->vData['field_id'] = '';
		$this->vData['title'] = '';
		$this->vData['short_name'] = '';
		$this->vData['collection'] = '';
		$this->vData['required'] = 1;

		// Are we editing?
		if ($this->EE->input->get('field_id') > 0)
		{
			// Get the field
			$field = $this->EE->db->select("*")->from('exp_channel_ratings_fields')->where('field_id', $this->EE->input->get('field_id'))->get();

			// Found anything?
			if ($field->num_rows() == 1)
			{
				$this->vData = array_merge($this->vData, $field->row_array());
			}

		}

		return $this->EE->load->view('mcp/fields_add', $this->vData, TRUE);
	}

	// ********************************************************************************* //

	public function update_field()
	{
		$this->EE->load->helper('url');

		//----------------------------------------
		// Create/Updating?
		//----------------------------------------
		if ($this->EE->input->get('delete') != 'yes')
		{
			$this->EE->db->set('site_id', $this->site_id);
			$this->EE->db->set('title', $this->EE->input->post('field_title'));
			$this->EE->db->set('short_name', ($this->EE->input->post('field_name') != FALSE) ? url_title(trim($this->EE->input->post('field_name')), 'underscore', TRUE) : url_title(trim($this->EE->input->post('field_title')), 'underscore', TRUE)   );
			$this->EE->db->set('collection_id', $this->EE->input->post('collection_id'));
			$this->EE->db->set('required', $this->EE->input->post('field_required'));

			// Are we updating a group?
			if ($this->EE->input->post('field_id') >= 1)
			{
				$this->EE->db->where('field_id', $this->EE->input->post('field_id'));
				$this->EE->db->update('exp_channel_ratings_fields');
			}
			else
			{
				$this->EE->db->insert('exp_channel_ratings_fields');
			}
		}
		//----------------------------------------
		// Delete
		//----------------------------------------
		else
		{
			$field_id = $this->EE->input->get('field_id');

			$this->EE->db->where('field_id', $field_id);
			$this->EE->db->delete('channel_ratings');

			$this->EE->db->where('field_id', $field_id);
			$this->EE->db->delete('channel_ratings_stats');

			$this->EE->db->where('field_id', $field_id);
			$this->EE->db->delete('channel_ratings_fields');
		}

		$this->EE->functions->redirect($this->base . '&method=fields');
	}

	// ********************************************************************************* //

	function collections()
	{
		// Page Title & BreadCumbs

		// Page Title
		if (version_compare(APP_VER, '2.6.0', '>=')) {
			ee()->view->cp_page_title = $this->EE->lang->line('cr:collections');
		} else {
			$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('cr:collections'));
		}

		$this->vData['PageHeader'] = 'collections';

		return $this->EE->load->view('mcp/collections', $this->vData, TRUE);
	}

	// ********************************************************************************* //

	public function add_collection()
	{
		// Page Title

		// Page Title
		if (version_compare(APP_VER, '2.6.0', '>=')) {
			ee()->view->cp_page_title = $this->EE->lang->line('cr:collections_add_long');
		} else {
			$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('cr:collections_add_long'));
		}

		$this->vData['PageHeader'] = 'collections';

		foreach ($this->EE->db->list_fields('exp_channel_ratings_collections') as $key => $val) $this->vData[$val] = '';

		// Are we editing?
		if ($this->EE->input->get('collection_id') > 0)
		{
			// Get the field
			$field = $this->EE->db->select("*")->from('exp_channel_ratings_collections')->where('collection_id', $this->EE->input->get('collection_id'))->get();

			// Found anything?
			if ($field->num_rows() == 1)
			{
				$this->vData = array_merge($this->vData, $field->row_array());
			}

		}

		return $this->EE->load->view('mcp/collections_add', $this->vData, TRUE);
	}

	// ********************************************************************************* //

	public function update_collection()
	{
		//----------------------------------------
		// Create/Updating?
		//----------------------------------------
		if ($this->EE->input->get('delete') != 'yes')
		{
			$data['collection_label'] = trim($this->EE->input->post('collection_label'));
			$data['collection_name'] = trim($this->EE->input->post('collection_name'));
			$data['default'] = $this->EE->input->post('default');

			$collection_id = $this->EE->ratings_model->create_update_collection($data, $this->EE->input->post('collection_id'));

			// New One? Lets Switch!
			if ($this->EE->input->post('collection_id') == FALSE)
			{
				$this->switch_collection($collection_id);
			}
		}
		//----------------------------------------
		// Delete
		//----------------------------------------
		else
		{
			$collection_id = $this->EE->input->get('collection_id');

			$this->EE->ratings_model->delete_collection($collection_id);
		}

		$this->EE->functions->redirect($this->base . '&method=collections');
	}

	// ********************************************************************************* //

	public function settings()
	{
		// Page Title & BreadCumbs

		// Page Title
		if (version_compare(APP_VER, '2.6.0', '>=')) {
			ee()->view->cp_page_title = $this->EE->lang->line('cr:settings');
		} else {
			$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('cr:settings'));
		}

		$this->vData['PageHeader'] = 'settings';

		// ACT
		$this->vData['bayesian_act_url'] = $this->EE->ratings_helper->get_router_url('url', 'bayesian');
		$this->vData['ajax_act_url'] = $this->EE->ratings_helper->get_router_url('url');

		return $this->EE->load->view('mcp/settings', $this->vData, TRUE);
	}

	// ********************************************************************************* //

	public function import()
	{
		// Page Title & BreadCumbs

		// Page Title
		if (version_compare(APP_VER, '2.6.0', '>=')) {
			ee()->view->cp_page_title = $this->EE->lang->line('cr:import');
		} else {
			$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('cr:import'));
		}

		$this->vData['PageHeader'] = 'import';

		// Load model
		$this->EE->load->model('ratings_import');

		// Grab Solspace Rating Data
		$this->vData['candidates'] = $this->EE->ratings_import->get_import_candidates();



		return $this->EE->load->view('mcp_import', $this->vData, TRUE);
	}

	// ********************************************************************************* //

	public function import_solspace_rating()
	{
		// Load model
		$this->EE->load->model('ratings_import');

		/** ----------------------------------------
		/** Ratings Fields?
		/** ----------------------------------------*/
		if ($this->EE->input->get('action') == 'fields')
		{
			$this->EE->ratings_import->import_ss_rating_fields();
		}

		/** ----------------------------------------
		/** Ratings
		/** ----------------------------------------*/
		if ($this->EE->input->get('action') == 'ratings')
		{
			$this->EE->ratings_import->import_ss_ratings( $this->EE->input->get('channel_id') );
		}

		exit();
	}

	// ********************************************************************************* //

	public function recount()
	{
		// Page Title & BreadCumbs

		// Page Title
		if (version_compare(APP_VER, '2.6.0', '>=')) {
			ee()->view->cp_page_title = $this->EE->lang->line('cr:recount');
		} else {
			$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('cr:recount'));
		}

		$this->vData['PageHeader'] = 'recount';

		$this->EE->load->model('ratings_recount');

		// Grab Recount Candidates
		$this->vData['candidates'] = $this->EE->ratings_recount->get_recount_candidates();


		return $this->EE->load->view('mcp_recount', $this->vData, TRUE);
	}

	// ********************************************************************************* //

	public function switch_collection($coll_id=FALSE)
	{
		if ($coll_id == FALSE) $collection_id = $this->EE->input->get_post('collection_id');
		else $collection_id = $coll_id;

		// Which collection did we choose?
		$coll = explode('|', $this->EE->input->cookie('cr_mcp_collection'));
		if ($coll === FALSE) $coll = array();

		foreach ($coll as $str)
		{
			$str = explode('-', $str);
			if (isset($str[1]) == TRUE)
			{
				$collections[$str[0]] = $str[1];
			}
		}

		if (isset($collections[$this->site_id]) == FALSE)
		{
			$first = reset($this->EE->ratings_model->get_collections());
			$collections[$this->site_id] = $first->collection_id;
		}
		else
		{
			$collections[$this->site_id] = $collection_id;
		}

		foreach ($collections as $site => &$col)
		{
			$col = $site.'-'.$col;
		}

		$cookie = implode('|', $collections);

		$this->EE->functions->set_cookie('cr_mcp_collection', $cookie, 1728000);

		if ($coll_id == FALSE) $this->EE->functions->redirect($this->base . '&method=index');
	}

	// ********************************************************************************* //

	function mcp_globals()
	{
		$this->EE->cp->set_breadcrumb($this->base, $this->EE->lang->line('channel_ratings'));

		$this->EE->cp->add_js_script(array('ui' => array('datepicker') ));

		// Add Global JS & CSS & JS Scripts
		$this->EE->ratings_helper->mcp_meta_parser('gjs', '', 'ChannelRatings');
		$this->EE->ratings_helper->mcp_meta_parser('css', CHANNELRATINGS_THEME_URL . 'channel_ratings_mcp.css', 'cr-mcp');
		$this->EE->ratings_helper->mcp_meta_parser('css', CHANNELRATINGS_THEME_URL . 'chosen.css', 'chosen');
		//$this->EE->ratings_helper->mcp_meta_parser('js', CHANNELRATINGS_THEME_URL . 'jquery.editable.js', 'jquery.editable', 'jquery');
		$this->EE->ratings_helper->mcp_meta_parser('js',  CHANNELRATINGS_THEME_URL . 'jquery.datatables.min.js', 'jquery.dataTables', 'jquery');
		$this->EE->ratings_helper->mcp_meta_parser('js',  CHANNELRATINGS_THEME_URL . 'jquery.datatables.colreorder.min.js', 'jquery.dataTables.colreorder', 'jquery');
		$this->EE->ratings_helper->mcp_meta_parser('js',  CHANNELRATINGS_THEME_URL . 'jquery.expander.js', 'jquery.expander', 'jquery');
		$this->EE->ratings_helper->mcp_meta_parser('js',  CHANNELRATINGS_THEME_URL . 'jquery.stringtoslug.min.js', 'jquery.stringtoslug', 'jquery');
		$this->EE->ratings_helper->mcp_meta_parser('js',  CHANNELRATINGS_THEME_URL . 'jquery.inputhint.min.js', 'jquery.inputhint', 'jquery');
		$this->EE->ratings_helper->mcp_meta_parser('js',  CHANNELRATINGS_THEME_URL . 'chosen.jquery.min.js', 'chosen', 'chosen');
		$this->EE->ratings_helper->mcp_meta_parser('js',  CHANNELRATINGS_THEME_URL . 'bootstrap-tooltip.js', 'bootstrap-tooltip', 'bootstrap');
		$this->EE->ratings_helper->mcp_meta_parser('js',  CHANNELRATINGS_THEME_URL . 'bootstrap-popover.js', 'bootstrap-popover', 'bootstrap');
		$this->EE->ratings_helper->mcp_meta_parser('js',  CHANNELRATINGS_THEME_URL . 'bootstrap-dropdown.js', 'bootstrap-dropdown', 'bootstrap');
		$this->EE->ratings_helper->mcp_meta_parser('js',  CHANNELRATINGS_THEME_URL . 'bootstrap-modal.js', 'bootstrap-modal', 'bootstrap');
		$this->EE->ratings_helper->mcp_meta_parser('js',  CHANNELRATINGS_THEME_URL . 'channel_ratings_mcp.js', 'cr-mcp');
	}

	// ********************************************************************************* //

	public function ajax_router()
	{
		// -----------------------------------------
		// Ajax Request?
		// -----------------------------------------
		if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
		{
			// Load Library
			if (class_exists('Channel_Ratings_AJAX') != TRUE) include 'ajax.channel_ratings.php';

			$AJAX = new Channel_Ratings_AJAX();

			// Shoot the requested method
			$method = $this->EE->input->get_post('ajax_method');
			echo $AJAX->$method();
			exit();
		}
	}

	// ********************************************************************************* //

} // END CLASS

/* End of file mcp.channel_ratings.php */
/* Location: ./system/expressionengine/third_party/tagger/mcp.channel_ratings.php */
