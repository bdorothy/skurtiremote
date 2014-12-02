<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// include base class
if ( ! class_exists('Mtt_search_base'))
{
	require_once(PATH_THIRD.'mtt_search/base.mtt_search.php');
}

/**
 * Mtt Search Module Control Panel class
 *
 * @package        mtt_search
 * @author         MyTopTeacher <contact@mytopteacher.com>
 * @link           http://mytopteacher.com/addons/mtt-search
 * @copyright      Copyright (c) 2014, Mtt
 */
class Mtt_search_mcp extends Mtt_search_base {

	// --------------------------------------------------------------------
	// CONSTANTS
	// --------------------------------------------------------------------

	const VIEW_LOG_LIMIT   = 50;
	const PREVIEW_PAD      = 50;
	const PREVIEW_LIMIT    = 100;

	// --------------------------------------------------------------------
	// PROPERTIES
	// --------------------------------------------------------------------

	/**
	 * Holder for error messages
	 *
	 * @access     private
	 * @var        string
	 */
	private $error_msg = '';

	/**
	 * Allowed field types for replacing
	 *
	 * @access     private
	 * @var        array
	 */
	private $allowed_types = array(
		'text',
		'textarea',
		'rte',
		'wygwam',
		'matrix',
		'grid',
		'nsm_tiny_mce',
		'wyvern',
		'expresso',
		'editor',
		'reedactor',
		'redactee',
		'illuminated',
		'wb_markitup'
	);

	/**
	 * Data array for views
	 *
	 * @var        array
	 * @access     private
	 */
	private $data = array();

	/**
	 * Shortcut to current member group
	 *
	 * @var        int
	 * @access     private
	 */
	private $member_group;

	/**
	 * Model shortcuts
	 *
	 * @var        object
	 * @access     private
	 */
	private $collection;
	private $shortcuts;
	private $groups;

	// --------------------------------------------------------------------
	// METHODS
	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @return      void
	 */
	public function __construct()
	{
		// -------------------------------------
		//  Call parent constructor
		// -------------------------------------

		parent::__construct();

		// -------------------------------------
		//  Get member group shortcut
		// -------------------------------------

		$this->member_group = (int) @ee()->session->userdata['group_id'];

		// -------------------------------------
		//  Model shortcuts
		// -------------------------------------

		$this->collection =& ee()->mtt_search_collection_model;
		$this->shortcuts  =& ee()->mtt_search_shortcut_model;
		$this->groups     =& ee()->mtt_search_group_model;
	}

	// --------------------------------------------------------------------

	/**
	 * Module home page
	 *
	 * @access      public
	 * @return      string
	 */
	public function index()
	{
		// --------------------------------------
		// Add settings to view
		// --------------------------------------

		$this->data['settings'] = ee()->mtt_search_settings->get();

		// --------------------------------------
		// Get action ID for open search URL
		// --------------------------------------

		$this->data['search_url']
			= ee()->functions->fetch_site_index(0, 0)
			. QUERY_MARKER.'ACT='
			. ee()->cp->fetch_action_id($this->class_name, 'catch_search')
			. AMP.'qt={searchTerms}';

		// --------------------------------------
		// Get action ID for building an index
		// --------------------------------------

		$this->data['build_url']
			= ee()->functions->fetch_site_index(0, 0)
			. QUERY_MARKER.'ACT='
			. ee()->cp->fetch_action_id($this->class_name, 'build_index')
			. AMP.'key='
			. ee()->mtt_search_settings->get('license_key');

		// --------------------------------------
		// For EE 2.7.0, make sure csrf_exempt is activated.
		// Can't do it in the install or update routine,
		// 'cause someone could have the latest LS version when
		// upgrading to EE 2.7. And EE doesn't broadcast when
		// it's updated, so I just need to set it here,
		// on each page load. :-/
		// --------------------------------------

		if (version_compare(APP_VER, '2.7.0', '>='))
		{
			ee()->db->update(
				'actions',
				array('csrf_exempt' => '1'),
				array('class' => $this->class_name, 'method' => 'catch_search')
			);
		}

		// --------------------------------------
		// Set title and breadcrumb and view page
		// --------------------------------------

		$this->_set_cp_var('cp_page_title', lang('mtt_search_module_name'));

		return $this->view('mcp_index');
	}

	// --------------------------------------------------------------------

	/**
	 * Extension settings
	 *
	 * @access      public
	 * @return      string
	 */
	public function settings()
	{
		// --------------------------------------
		// Determine min_word_len
		// --------------------------------------

		// $query = ee()->db->query("SHOW VARIABLES LIKE 'ft_min_word_len'");

		// if ($row = $query->row_array() && isset($row['Value']))
		// {
		// 	$this->default_settings['min_word_length'] = $row['Value'];
		// }

		// --------------------------------------
		// Merge default and current settings
		// --------------------------------------

		$this->data = array_merge($this->data, ee()->mtt_search_settings->get());

		// --------------------------------------
		// Get URI protocol
		// --------------------------------------

		$this->data['uri_protocol'] = ee()->config->item('uri_protocol');

		// --------------------------------------
		// Add search modes to data array
		// --------------------------------------

		$this->data['search_modes'] = ee()->mtt_search_settings->search_modes;

		// --------------------------------------
		// Add hilite tags to data array
		// --------------------------------------

		$this->data['hilite_tags'] = ee()->mtt_search_settings->hilite_tags;

		// --------------------------------------
		// Format stop words and ignore words
		// --------------------------------------

		$this->data['stop_words']   = preg_replace('/\s+/', "\n", $this->data['stop_words']);
		$this->data['ignore_words'] = preg_replace('/\s+/', "\n", $this->data['ignore_words']);

		// -------------------------------------
		//  Get member groups; exclude superAdmins, guests, pending and banned
		// -------------------------------------

		$query = ee()->db->select('group_id, group_title')
		       ->from('member_groups')
		       ->where('can_access_cp', 'y')
		       ->where_not_in('group_id', array(1, 2, 3, 4))
		       ->order_by('group_title', 'asc')
		       ->get();

		$this->data['groups'] = mtt_flatten_results($query->result_array(), 'group_title', 'group_id');
		$this->data['permissions'] = ee()->mtt_search_settings->permissions();

		// --------------------------------------
		// Set breadcrumb
		// --------------------------------------

		$this->_set_cp_var('cp_page_title', lang('settings'));
		ee()->cp->set_breadcrumb($this->mcp_url(), lang('mtt_search_module_name'));

		// --------------------------------------
		// Load view
		// --------------------------------------

		return $this->view('ext_settings');
	}

	/**
	 * Save settings
	 */
	public function save_settings()
	{
		// --------------------------------------
		// Initiate settings array
		// --------------------------------------

		$settings = array();

		// --------------------------------------
		// Loop through default settings, check
		// for POST values, fallback to default
		// --------------------------------------

		foreach (ee()->mtt_search_settings->default_settings AS $key => $val)
		{
			if (($settings[$key] = ee()->input->post($key)) === FALSE)
			{
				$settings[$key] = $val;
			}
		}

		// --------------------------------------
		// Convert stop/ignore words
		// --------------------------------------

		$settings['stop_words']   = mtt_prep_word_list($settings['stop_words']);
		$settings['ignore_words'] = mtt_prep_word_list($settings['ignore_words']);

		// --------------------------------------
		// Save serialized settings
		// --------------------------------------

		ee()->db->where('class', $this->class_name.'_ext');
		ee()->db->update('extensions', array('settings' => serialize($settings)));

		// --------------------------------------
		// Set feedback message
		// --------------------------------------

		ee()->session->set_flashdata('msg', 'changes_saved');

		// --------------------------------------
		// Redirect back to overview
		// --------------------------------------

		ee()->functions->redirect($this->mcp_url('settings'));
	}

	// --------------------------------------------------------------------

	/**
	 * List collections screen
	 *
	 * @access      public
	 * @return      string
	 */
	public function collections()
	{
		// --------------------------------------
		// Get all collections
		// --------------------------------------

		$this->data['collections'] = $this->collection->get_by_site($this->site_id);

		// --------------------------------------
		// Get entry count for each collection
		// Plus oldest index for each collection
		// --------------------------------------

		if ($this->data['collections'])
		{
			// Get channel ids
			$channels = array_unique(mtt_flatten_results($this->data['collections'], 'channel_id'));

			// Query DB to get totals
			$query = ee()->db->select('channel_id, COUNT(*) AS num_entries')
			       ->from('channel_titles')
			       ->where_in('channel_id', $channels)
			       ->group_by('channel_id')
			       ->get();

			// Set totals in data array
			foreach ($query->result() AS $row)
			{
				$this->data['totals'][$row->channel_id] = $row->num_entries;
			}

			// Get oldest index dates
			$index_dates = ee()->mtt_search_index_model->get_oldest_index();

			// Get all channel names
			$query = ee()->db->select('channel_id, channel_title')
			       ->from('channels')
			       ->where_in('channel_id', $channels)
			       ->get();

			$channels = mtt_flatten_results($query->result_array(), 'channel_title', 'channel_id');

			// Get index url (action)
			// $index_url
			// 	= ee()->functions->fetch_site_index(0, 0)
			// 	. QUERY_MARKER.'ACT='
			// 	. ee()->cp->fetch_action_id($this->class_name.'_mcp', 'build_index')
			// 	. AMP.'collection_id=%s'
			// 	. AMP.'start=0';

			// Get index url (MCP)
			$index_url = $this->mcp_url('build_index', 'collection_id=%s');

			foreach ($this->data['collections'] AS &$row)
			{
				// Add channel name to row
				$row['channel'] = array_key_exists($row['channel_id'], $channels)
					? $channels[$row['channel_id']]
					: '--';

				if ( ! isset($index_dates[$row['collection_id']]))
				{
					// Index doesn't exist for this collection
					$row['index_status'] = 'empty';
				}
				else
				{
					// If oldest index in collection is older than the collection edit date,
					// then we need to suggest to rebuild the index.
					$row['index_status'] = ($index_dates[$row['collection_id']] < $row['edit_date']) ? 'old' : 'ok';
				}

				// Add update index url to collection
				$row['index_url'] = sprintf($index_url, $row['collection_id']);

				// Force rebuild when old
				if ($row['index_status'] == 'old')
				{
					$row['index_url'] .= '&amp;rebuild=yes';
				}
			}
		}

		// --------------------------------------
		// Set title and breadcrumb and view page
		// --------------------------------------

		$this->data['new_collection_url'] = $this->mcp_url('edit_collection');

		$this->_set_cp_var('cp_page_title', lang('collections'));
		ee()->cp->set_breadcrumb($this->mcp_url(), lang('mtt_search_module_name'));

		return $this->view('mcp_list_collections');
	}

	// --------------------------------------------------------------------

	/**
	 * Create new collection or edit existing one
	 *
	 * @access      public
	 * @return      string
	 */
	public function edit_collection()
	{
		// --------------------------------------
		// Get collection by id or empty row
		// --------------------------------------

		$collection_id = ee()->input->get('collection_id');
		$collection    = ($collection_id === FALSE)
		               ? $this->collection->empty_row()
		               : $this->collection->get_one($collection_id);

		// --------------------------------------
		// Get settings for this collection
		// --------------------------------------

		if (strlen($collection['settings']))
		{
			$collection['settings'] = mtt_search_decode($collection['settings'], FALSE);
		}

		// --------------------------------------
		// Set default excerpt data
		// --------------------------------------

		if ( ! strlen($collection['excerpt']))
		{
			$collection['excerpt'] = '0';
		}

		// --------------------------------------
		// Set default modifier data
		// --------------------------------------

		if ( ! strlen($collection['modifier']))
		{
			$collection['modifier'] = '1';
		}

		// --------------------------------------
		// Merge collection data with view data
		// --------------------------------------

		$this->data = array_merge($this->data, $collection);

		// --------------------------------------
		// Get searchable channel fields
		// --------------------------------------

		$query = ee()->db->select('field_id, group_id, field_label')
		       ->from('channel_fields')
		       ->where('site_id', $this->site_id)
		       ->where('field_search', 'y')
		       ->order_by('field_order')
		       ->get();
		$fields = array();

		foreach ($query->result() AS $row)
		{
			$fields[$row->group_id][$row->field_id] = $row->field_label;
		}

		// --------------------------------------
		// Get channels
		// --------------------------------------

		$query = ee()->db->select('channel_id, field_group, cat_group, channel_name, channel_title')
		       ->from('channels')
		       ->where('site_id', $this->site_id)
		       ->order_by('channel_title')
		       ->get();
		$channels = mtt_associate_results($query->result_array(), 'channel_id');

		// Add simple channel data to global JS object for later reference
		ee()->javascript->set_global('mtt_search_channels', $channels);

		// Add fields to channels
		foreach ($channels AS &$row)
		{
			$row['cat_group'] = array_filter(explode('|', $row['cat_group']));
			$row['fields'] = array(0 => lang('title'));

			if (array_key_exists($row['field_group'], $fields))
			{
				$row['fields'] += $fields[$row['field_group']];
			}
		}

		// --------------------------------------
		// Get category groups
		// --------------------------------------

		ee()->lang->loadfile('admin_content');
		ee()->load->model('category_model');
		$query = ee()->category_model->get_category_groups();
		$cat_groups = mtt_associate_results($query->result_array(), 'group_id');

		if ( ! empty($cat_groups))
		{
			// Add default fields to groups
			foreach ($cat_groups AS &$group)
			{
				$group['fields'] = array(
					'cat_name'        => lang('category_name'),
					'cat_description' => lang('category_description')
				);
			}

			// Get fields for category groups
			$query = ee()->db->select('field_id, group_id, field_label')
		    	   ->from('category_fields')
		    	   ->where_in('group_id', array_keys($cat_groups))
		    	   ->order_by('field_order', 'asc')
		    	   ->get();

			foreach ($query->result() AS $field)
			{
				$cat_groups[$field->group_id]['fields'][$field->field_id] = $field->field_label;
		   	}
		}

		// --------------------------------------
		// Add to view data
		// --------------------------------------

		$this->data['channels'] = $channels;
		$this->data['cat_groups'] = $cat_groups;

		// --------------------------------------
		// Set title and breadcrumb
		// --------------------------------------

		$this->_set_cp_var('cp_page_title', lang($collection_id === FALSE ? 'create_new_collection' : 'edit_collection'));
		ee()->cp->set_breadcrumb($this->mcp_url(), lang('mtt_search_module_name'));
		ee()->cp->set_breadcrumb($this->mcp_url('collections'), lang('collections'));

		return $this->view('mcp_edit_collection');
	}

	// --------------------------------------------------------------------

	/**
	 * Save changes to given collection
	 *
	 * @access      public
	 * @return      void
	 */
	public function save_collection()
	{
		// --------------------------------------
		// Set return url
		// --------------------------------------

		$return_url = $this->mcp_url('collections');

		// --------------------------------------
		// Get collection id
		// --------------------------------------

		if (($collection_id = ee()->input->post('collection_id')) === FALSE)
		{
			ee()->functions->redirect($return_url);
		}

		// --------------------------------------
		// Set site id to current site
		// --------------------------------------

		$_POST['site_id'] = $this->site_id;

		// --------------------------------------
		// Title shouldn't be empty
		// --------------------------------------

		if ( ! strlen($_POST['collection_label']))
		{
			$_POST['collection_label'] = lang('new_collection');
		}

		// --------------------------------------
		// Check channel
		// --------------------------------------

		if ( ! ($channel_id = ee()->input->post('channel_id')))
		{
			show_error(lang('channel_cannot_be_empty'));
		}

		// --------------------------------------
		// Check collection name
		// --------------------------------------

		// It should be filled in
		if ( ! ($collection_name = trim(ee()->input->post('collection_name'))))
		{
			show_error(lang('collection_name_cannot_be_empty'));
		}

		// It should be formatted correctly
		if ( ! preg_match('#^[\-a-zA-Z0-9_]+$#', $collection_name))
		{
			show_error(lang('collection_name_has_wrong_chars'));
		}

		// And it should be unique
		if (ee()->db->where(array(
				'site_id'          => $this->site_id,
				'collection_name'  => $collection_name,
				'collection_id !=' => $collection_id
			))->from(ee()->mtt_search_collection_model->table())->count_all_results())
		{
			show_error(lang('collection_name_exists'));
		}

		// --------------------------------------
		// Check modifier
		// --------------------------------------

		$mod = (float) ee()->input->post('modifier');

		// Check modifier validity
		if ($mod <= 0) $mod = 1;
		if ($mod > 10) $mod = 10;

		$_POST['modifier'] = $mod;

		// --------------------------------------
		// Check Excerpt
		// --------------------------------------

		$excerpts = ee()->input->post('excerpt');

		$_POST['excerpt'] = isset($excerpts[$channel_id]) ? $excerpts[$channel_id] : 0;

		// --------------------------------------
		// Check Settings
		// --------------------------------------

		$settings = (array) ee()->input->post('settings');

		// Check field weights
		if (isset($settings[$channel_id]))
		{
			$settings = $settings[$channel_id];
		}
		else
		{
			$settings = array();
		}

		// Clean it
		$settings = array_filter($settings);

		// It's nicer to sort the settings
		ksort($settings);

		// Encode the settings to JSON
		$settings = mtt_search_encode($settings, FALSE);

		// Set settings in POST so model can handle it
		$_POST['settings'] = $settings;

		// --------------------------------------
		// Add edit date to POST vars if new or settings changed
		// --------------------------------------

		// Initiate edit date
		$edit_date = ee()->localize->now;

		// Check old settings
		if (is_numeric($collection_id))
		{
			$old_collection = ee()->mtt_search_collection_model->get_one($collection_id);

			// if the new encoded settings are the same as the settings on record,
			// we don't need to change the edit date
			if ($old_collection['channel_id'] == $channel_id AND
				$old_collection['settings'] == $settings)
			{
				$edit_date = FALSE;
			}
		}

		if ($edit_date)
		{
			$_POST['edit_date'] = $edit_date;
		}

		// --------------------------------------
		// Insert or update record
		// --------------------------------------

		if (is_numeric($collection_id))
		{
			ee()->mtt_search_collection_model->update($collection_id);
		}
		else
		{
			$collection_id = ee()->mtt_search_collection_model->insert();
		}

		// --------------------------------------
		// Set feedback message
		// --------------------------------------

		ee()->session->set_flashdata('msg', 'changes_saved');

		// --------------------------------------
		// Redirect back to overview
		// --------------------------------------

		ee()->functions->redirect($return_url);
	}

	// --------------------------------------------------------------------

	/**
	 * Confirm deletion of a collection
	 *
	 * @access      public
	 * @return      string
	 */
	public function delete_collection_confirm()
	{
		// --------------------------------------
		// Redirect back to module home if no collection is given
		// --------------------------------------

		if ( ! ($collection_id = ee()->input->get('collection_id')))
		{
			ee()->functions->redirect($this->mcp_url());
		}

		// --------------------------------------
		// Get collection from DB
		// --------------------------------------

		if ( ! ($collection = ee()->mtt_search_collection_model->get_one($collection_id)))
		{
			ee()->functions->redirect($this->mcp_url());
		}

		// --------------------------------------
		// Compose data
		// --------------------------------------

		$data = array(
			'form_action'   => $this->mcp_url('delete_collection'),
			'hidden_fields' => array('collection_id' => $collection_id),
			'are_you_sure'  => lang('delete_collection_confirm_message'),
			'confirm'       => lang('delete_collection_confirm')." &ldquo;".htmlspecialchars($collection['collection_label'])."&rdquo;",
			'cancel_url'    => $this->mcp_url('collections')
		);

		$this->data = array_merge($this->data, $data);

		// --------------------------------------
		// Title and Crumbs
		// --------------------------------------

		$this->_set_cp_var('cp_page_title', lang('delete_collection_confirm'));
		ee()->cp->set_breadcrumb($this->mcp_url(), lang('mtt_search_module_name'));

		// --------------------------------------
		// Load up view
		// --------------------------------------

		return $this->view('mcp_delete_confirm');
	}

	/**
	 * Delete a collection
	 *
	 * @access      public
	 * @return      void
	 */
	public function delete_collection()
	{
		// --------------------------------------
		// Check collection id
		// --------------------------------------

		if ($collection_id = ee()->input->post('collection_id'))
		{
			// --------------------------------------
			// Delete in 2 tables
			// --------------------------------------

			ee()->mtt_search_collection_model->delete($collection_id);
			ee()->mtt_search_index_model->delete($collection_id, 'collection_id');

			// --------------------------------------
			// Set feedback message
			// --------------------------------------

			ee()->session->set_flashdata('msg', 'collection_deleted');
		}

		// --------------------------------------
		// Go home
		// --------------------------------------

		ee()->functions->redirect($this->mcp_url('collections'));
	}

	// --------------------------------------------------------------------

	/**
	 * List Groups
	 *
	 * @access      public
	 * @return      string
	 */
	public function groups()
	{
		// --------------------------------------
		// Get all groups
		// --------------------------------------

		$groups   = $this->groups->get_by_site($this->site_id);
		$edit_url = $this->mcp_url('edit_group', 'group_id=%s');

		// --------------------------------------
		// Modify rows
		// --------------------------------------

		foreach ($groups AS &$row)
		{
			$row['show_url']   = $this->mcp_url('shortcuts', 'group_id='.$row['group_id']);
			$row['edit_url']   = sprintf($edit_url, $row['group_id']);
			$row['delete_url'] = $this->mcp_url('delete_group_confirm', 'group_id='.$row['group_id']);
		}

		// --------------------------------------
		// Add groups to view data
		// --------------------------------------

		$this->data['groups'] = $groups;
		$this->data['new_group_url'] = sprintf($edit_url, 'new');

		// --------------------------------------
		// Shortcut count per group
		// --------------------------------------

		if ($groups)
		{
			$this->data['counts'] = $this->shortcuts->get_group_counts($this->site_id);
		}

		// --------------------------------------
		// Set title and breadcrumb and view page
		// --------------------------------------

		$this->_set_cp_var('cp_page_title', lang('groups'));
		ee()->cp->set_breadcrumb($this->mcp_url(), lang('mtt_search_module_name'));

		return $this->view('mcp_list_groups');
	}

	/**
	 * Edit short group
	 *
	 * @access      public
	 * @return      string
	 */
	public function edit_group()
	{
		// --------------------------------------
		// Get short group
		// --------------------------------------

		$group_id = ee()->input->get('group_id');

		if ($group_id == 'new')
		{
			$group = $this->groups->empty_row();
			$group['group_id'] = $group_id;
		}
		else
		{
			$group = $this->groups->get_one($group_id);
		}

		$this->data = array_merge($this->data, $group);

		// --------------------------------------
		// Set title and breadcrumb and view page
		// --------------------------------------

		$this->_set_cp_var('cp_page_title', lang('edit_group'));
		ee()->cp->set_breadcrumb($this->mcp_url(), lang('mtt_search_module_name'));
		ee()->cp->set_breadcrumb($this->mcp_url('groups'), lang('groups'));

		return $this->view('mcp_edit_group');
	}

	/**
	 * Save short group
	 *
	 * @access      public
	 * @return      string
	 */
	public function save_group()
	{
		// --------------------------------------
		// Data to save
		// --------------------------------------

		$data = array();

		// --------------------------------------
		// Get short group
		// --------------------------------------

		$data['site_id'] = $this->site_id;
		$data['group_label'] = trim(ee()->input->post('group_label'));

		// --------------------------------------
		// Insert/update
		// --------------------------------------

		if (($group_id = ee()->input->post('group_id')) == 'new')
		{
			$this->groups->insert($data);
		}
		else
		{
			$this->groups->update($group_id, $data);
		}

		// --------------------------------------
		// Set feedback message
		// --------------------------------------

		ee()->session->set_flashdata('msg', 'changes_saved');

		// --------------------------------------
		// Return to the group list page
		// --------------------------------------

		ee()->functions->redirect($this->mcp_url('groups'));
	}

	/**
	 * Delete group confirmation
	 */
	public function delete_group_confirm()
	{
		// --------------------------------------
		// Redirect back to module home if no collection is given
		// --------------------------------------

		if ( ! ($group_id = ee()->input->get('group_id')))
		{
			ee()->functions->redirect($this->mcp_url());
		}

		// --------------------------------------
		// Get collection from DB
		// --------------------------------------

		if ( ! ($group = ee()->mtt_search_group_model->get_one($group_id)))
		{
			ee()->functions->redirect($this->mcp_url());
		}

		// --------------------------------------
		// Compose data
		// --------------------------------------

		$data = array(
			'form_action'   => $this->mcp_url('delete_group'),
			'hidden_fields' => array('group_id' => $group_id),
			'are_you_sure'  => lang('delete_group_confirm_message'),
			'confirm'       => lang('delete_group_confirm')." &ldquo;".htmlspecialchars($group['group_label'])."&rdquo;",
			'cancel_url'    => $this->mcp_url('groups')
		);

		$this->data = array_merge($this->data, $data);

		// --------------------------------------
		// Title and Crumbs
		// --------------------------------------

		$this->_set_cp_var('cp_page_title', lang('delete_group_confirm'));
		ee()->cp->set_breadcrumb($this->mcp_url(), lang('mtt_search_module_name'));
		ee()->cp->set_breadcrumb($this->mcp_url('groups'), lang('groups'));

		// --------------------------------------
		// Load up view
		// --------------------------------------

		return $this->view('mcp_delete_confirm');
	}

	/**
	 * Delete group and its shortcuts
	 */
	public function delete_group()
	{
		// Make sure an ID is posted
		if ($group_id = ee()->input->post('group_id'))
		{
			// Delete it
			$this->groups->delete($group_id);
			$this->shortcuts->delete_by_group($group_id);

			// And set feedback message
			ee()->session->set_flashdata('msg', 'group_deleted');
		}

		// Go back
		ee()->functions->redirect($this->mcp_url('groups'));
	}

	// --------------------------------------------------------------------

	/**
	 * List shortcuts for given group
	 *
	 * @access      public
	 * @return      string
	 */
	public function shortcuts()
	{
		// --------------------------------------
		// Get group, get out when not given
		// --------------------------------------

		if ( ! ($group_id = ee()->input->get('group_id')))
		{
			ee()->functions->redirect($this->mcp_url('groups'));
		}

		// --------------------------------------
		// Compose edit url
		// --------------------------------------

		$edit_url = $this->mcp_url('edit_shortcut', 'group_id='.$group_id.AMP.'shortcut_id=%s');

		// --------------------------------------
		// Get shortcuts for this group
		// --------------------------------------

		$rows = $this->shortcuts->get_by_group($group_id);

		foreach ($rows AS &$row)
		{
			$row['edit_url'] = sprintf($edit_url, $row['shortcut_id']);
			$row['delete_url'] = $this->mcp_url('delete_shortcut_confirm', 'shortcut_id='.$row['shortcut_id']);
		}

		$this->data['shortcuts'] = $rows;
		$this->data['new_shortcut_url'] = sprintf($edit_url, 'new');

		// --------------------------------------
		// Group details
		// --------------------------------------

		$group = $this->groups->get_one($group_id);

		// --------------------------------------
		// Set title and breadcrumb and view page
		// --------------------------------------

		$this->_set_cp_var('cp_page_title', $group['group_label']);
		ee()->cp->set_breadcrumb($this->mcp_url(), lang('mtt_search_module_name'));
		ee()->cp->set_breadcrumb($this->mcp_url('groups'), lang('groups'));

		return $this->view('mcp_list_shortcuts');
	}

	/**
	 * Edit shortcut
	 *
	 * @access      public
	 * @return      string
	 */
	public function edit_shortcut()
	{
		// --------------------------------------
		// Get IDs
		// --------------------------------------

		$shortcut_id = ee()->input->get('shortcut_id');
		$group_id = ee()->input->get('group_id');

		if ($shortcut_id == 'new')
		{
			$row = $this->shortcuts->empty_row();
			$row['group_id'] = $group_id;
			$title = lang('new_shortcut');
		}
		else
		{
			$row = $this->shortcuts->get_one($shortcut_id);
			$title = lang('edit_shortcut');
		}

		// --------------------------------------
		// Are we getting it from the log?
		// --------------------------------------

		if ($log_id = ee()->input->get('log_id'))
		{
			if ($log = ee()->mtt_search_log_model->get_one($log_id))
			{
				$params = mtt_search_decode($log['parameters'], FALSE);

				// Add keywords to params
				if ($log['keywords'])
				{
					$params['keywords'] = $log['keywords'];
				}

				$row['parameters'] = $params;
			}
		}

		$this->data = array_merge($this->data, $row);

		// --------------------------------------
		// Get all groups
		// --------------------------------------

		$groups = mtt_flatten_results($this->groups->get_by_site($this->site_id), 'group_label', 'group_id');
		$this->data['groups'] = $groups;
		$this->data['params_json'] = json_encode($row['parameters']);

		// --------------------------------------
		// Current group name?
		// --------------------------------------

		$group_name = $group_id ? $groups[$group_id] : FALSE;

		// --------------------------------------
		// Set title and breadcrumb and view page
		// --------------------------------------

		$this->_set_cp_var('cp_page_title', $title);
		ee()->cp->set_breadcrumb($this->mcp_url(), lang('mtt_search_module_name'));
		ee()->cp->set_breadcrumb($this->mcp_url('groups'), lang('groups'));
		ee()->cp->set_breadcrumb($this->mcp_url('shortcuts', 'group_id='.$group_id), $group_name);

		return $this->view('mcp_edit_shortcut');
	}

	/**
	 * Save shortcut
	 *
	 * @access      public
	 * @return      void
	 */
	public function save_shortcut()
	{
		// --------------------------------------
		// Read parameters
		// --------------------------------------

		$keys = ee()->input->post('param-key');
		$vals = ee()->input->post('param-val');

		$params = (is_array($keys) && is_array($vals))
			? array_combine($keys, $vals)
			: array();

		// --------------------------------------
		// Compose data to insert
		// --------------------------------------

		$data = array(
			'shortcut_id'    => NULL,
			'site_id'        => $this->site_id,
			'group_id'       => ee()->input->post('group_id'),
			'shortcut_name'  => ee()->input->post('shortcut_name'),
			'shortcut_label' => ee()->input->post('shortcut_label'),
			'parameters'     => mtt_search_encode($params, FALSE)
		);

		if (is_numeric(($shortcut_id = ee()->input->post('shortcut_id'))))
		{
			$data['shortcut_id'] = $shortcut_id;
		}

		// --------------------------------------
		// Validate the saved_search data
		// --------------------------------------

		if (($validated = $this->shortcuts->validate($data)) === FALSE)
		{
			show_error(array_map('lang', $this->shortcuts->errors()));
		}

		// --------------------------------------
		// And insert/update it
		// --------------------------------------

		if (empty($validated['shortcut_id']))
		{
			$validated['shortcut_id'] = $this->shortcuts->insert($validated);
		}
		else
		{
			$this->shortcuts->update($validated['shortcut_id'], $validated);
		}

		// --------------------------------------
		// Set feedback message
		// --------------------------------------

		ee()->session->set_flashdata('msg', 'changes_saved');

		// --------------------------------------
		// Return to the group list page
		// --------------------------------------

		ee()->functions->redirect($this->mcp_url('shortcuts', 'group_id='.$data['group_id']));
	}

	/**
	 * Delete shortcut confirmation
	 */
	public function delete_shortcut_confirm()
	{
		// --------------------------------------
		// Redirect back to module home if no collection is given
		// --------------------------------------

		if ( ! ($shortcut_id = ee()->input->get('shortcut_id')))
		{
			ee()->functions->redirect($this->mcp_url());
		}

		// --------------------------------------
		// Get collection from DB
		// --------------------------------------

		if ( ! ($shortcut = ee()->mtt_search_shortcut_model->get_one($shortcut_id)))
		{
			ee()->functions->redirect($this->mcp_url());
		}

		// --------------------------------------
		// Compose data
		// --------------------------------------

		$data = array(
			'form_action'   => $this->mcp_url('delete_shortcut'),
			'hidden_fields' => array('shortcut_id' => $shortcut_id, 'group_id' => $shortcut['group_id']),
			'are_you_sure'  => lang('delete_shortcut_confirm_message'),
			'confirm'       => lang('delete_shortcut_confirm')." &ldquo;".htmlspecialchars($shortcut['shortcut_label'])."&rdquo;",
			'cancel_url'    => $this->mcp_url('shortcuts')
		);

		$this->data = array_merge($this->data, $data);

		// --------------------------------------
		// Title and Crumbs
		// --------------------------------------

		$this->_set_cp_var('cp_page_title', lang('delete_shortcut_confirm'));
		ee()->cp->set_breadcrumb($this->mcp_url(), lang('mtt_search_module_name'));

		// --------------------------------------
		// Load up view
		// --------------------------------------

		return $this->view('mcp_delete_confirm');
	}

	/**
	 * Delete shotcut
	 */
	public function delete_shortcut()
	{
		// Make sure an ID is posted
		if ($shortcut_id = ee()->input->post('shortcut_id'))
		{
			// Delete it
			$this->shortcuts->delete($shortcut_id);

			// And set feedback message
			ee()->session->set_flashdata('msg', 'shortcut_deleted');
		}

		// Optionally go back to group
		$group = ($group_id = ee()->input->post('group_id'))
			? 'group_id='.$group_id
			: '';

		// Go back
		ee()->functions->redirect($this->mcp_url('shortcuts', $group));
	}

	/**
	 * Order shortcuts
	 */
	public function order_shortcuts()
	{
		// Get order from POST
		if (($order = ee()->input->post('order')) && is_array($order))
		{
			foreach ($order AS $i => $id)
			{
				$this->shortcuts->update($id, array('sort_order' => $i + 1));
			}
		}

		if (AJAX_REQUEST)
		{
			die('true');
		}
		else
		{
			ee()->functions->redirect($this->mcp_url());
		}
	}

	// --------------------------------------------------------------------

	/**
	 * First half of Find & Replace
	 *
	 * @access      public
	 * @return      string
	 */
	public function find()
	{
		// --------------------------------------
		// Get this member's id and group id
		// --------------------------------------

		$member_id    = ee()->session->userdata('member_id');
		$member_group = $this->member_group;

		// --------------------------------------
		// Get allowed channels
		// --------------------------------------

		$channel_ids = ee()->functions->fetch_assigned_channels();

		// Quick & dirty error message display when there aren't any channels
		if (empty($channel_ids))
		{
			show_error('No channels found');
		}

		// --------------------------------------
		// Get hidden fields according to publish layouts
		// --------------------------------------

		$hidden = array();

		$query = ee()->db->select('channel_id, field_layout')
		       ->from('layout_publish')
		       ->where('member_group', $member_group)
		       ->where_in('channel_id', $channel_ids)
		       ->get();

		// Loop thru each publish layout
		foreach ($query->result() AS $row)
		{
			// Unserialize details and loop thru tabs
			foreach (unserialize($row->field_layout) AS $tab => $layout)
			{
				// For each tab, loop thru fields and check if they're visible
				// If not visible, add it to hidden_fields array
				foreach ($layout AS $field => $options)
				{
					if (isset($options['visible']) && $options['visible'] == FALSE && is_numeric($field))
					{
						$hidden[] = $field;
					}
				}
			}
		}

		// --------------------------------------
		// Get list of channels and fields for selection
		// --------------------------------------

		$channels = $cat_groups = array();

		ee()->db->select('c.channel_id, c.cat_group, c.channel_title, f.field_id, f.field_label')
		     ->from('channels c')
		     ->join('channel_fields f', 'c.field_group = f.group_id')
		     ->where('c.site_id', $this->site_id)
		     ->where_in('c.channel_id', $channel_ids)
		     ->where_in('f.field_type', $this->allowed_types)
		     ->order_by('c.channel_title', 'asc')
		     ->order_by('f.field_order', 'asc');

		if ($hidden)
		{
			ee()->db->where_not_in('f.field_id', $hidden);
		}

		$query = ee()->db->get();

		// Change flat resultset into nested one
		foreach ($query->result() AS $row)
		{
			if ( ! isset($channels[$row->channel_id]))
			{
				$channels[$row->channel_id] = array(
					'channel_title'   => $row->channel_title,
					'fields'          => array('title' => lang('title'))
				);

				if ($row->cat_group) $cat_groups = array_merge($cat_groups, explode('|', $row->cat_group));
			}

			$channels[$row->channel_id]['fields'][$row->field_id] = $row->field_label;
		}

		$this->data['channels'] = $channels;

		// --------------------------------------
		// Categories filter
		// --------------------------------------

		$categories = array();
		$allowed    = ($member_group == 1) ? FALSE : $this->_get_permitted_categories($member_id);

		ee()->load->library('api');
		ee()->api->instantiate('channel_categories');

		// Generate category tree array
		if ($cat_groups && $tree = ee()->api_channel_categories->category_tree($cat_groups))
		{
			// Get category group names
			$query = ee()->db->select('group_id, group_name')
			       ->from('category_groups')
			       ->where_in('group_id', $cat_groups)
			       ->order_by('group_name')
			       ->get();
			$groups = mtt_flatten_results($query->result_array(), 'group_name', 'group_id');

			// Loop thru tree
			foreach ($tree AS $row)
			{
				// Skip categories that aren't allowed
				if (is_array($allowed) && ! in_array($row[0], $allowed))
				{
					continue;
				}

				// Add category group to array
				if ( ! isset($categories[$row[2]]))
				{
					$categories[$row[2]] = array(
						'group_name' => $groups[$row[2]],
						'cats'       => array()
					);
				}

				// Indent level for child categories
				$indent = ($row[5] > 1) ? str_repeat(NBS, $row[5] - 1) : '';

				// Add category itself to array
				$categories[$row[2]]['cats'][$row[0]] = array(
					'name' => $row[1],
					'indent' => $indent
				);
			}
		}

		// Add categories array to data
		$this->data['categories'] = $categories;

		// --------------------------------------
		// Check if we need to preview
		// --------------------------------------

		if (ee()->input->get('preview') == 'yes')
		{
			// Move this bulk to different method for clarity
			$this->_show_preview();
		}

		// --------------------------------------
		// Check if we need to show feedback message
		// --------------------------------------

		if ($feedback = ee()->session->flashdata('replace_feedback'))
		{
			$this->data['feedback'] = mtt_search_decode($feedback);
		}

		// --------------------------------------
		// Set title and breadcrumb
		// --------------------------------------

		$this->_set_cp_var('cp_page_title', lang('find_replace'));
		ee()->cp->set_breadcrumb($this->mcp_url(), lang('mtt_search_module_name'));

		return $this->view('mcp_find_replace');
	}

	/**
	 * Show preview table based on given keywords and fields
	 *
	 * @access      public
	 * @return      string
	 */
	private function _show_preview()
	{
		// --------------------------------------
		// Check prerequisites
		// --------------------------------------

		$member_id = ee()->session->userdata('member_id');
		$keywords  = ee()->input->post('qt');
		$fields    = ee()->input->post('fields');
		$cats      = ee()->input->post('cats');

		if ( ! ($keywords && $fields))
		{
			if (is_ajax())
			{
				die('No keywords or fields given.');
			}
			else
			{
				return;
			}
		}

		// Save this POST data as encoded data, so we know that what has been
		// previewed, is also used for the actual replacement
		$this->data['encoded_preview'] = mtt_search_encode($_POST);
		$this->data['keywords'] = htmlspecialchars($keywords);

		// --------------------------------------
		// Get permitted categories, if it's installed
		// --------------------------------------

		$allowed_cats  = ($this->member_group == 1) ? FALSE : $this->_get_permitted_categories($member_id, TRUE);
		$selected_cats = empty($cats) ? array() : $cats;

		// --------------------------------------
		// Compose query to get the matching entries
		// --------------------------------------

		// First, define some vars to help build the SQL
		$sql_keywords     = ee()->db->escape_str($keywords);
		$sql_channel_tmpl = "(t.channel_id = '%s' AND (%s)%s)";
		$sql_field_tmpl   = "(%s LIKE '%%%s%%' COLLATE utf8_bin)";
		$sql_cat_tmpl     = " AND cp.cat_id IN (%s)";
		$sql_select       = array();
		$sql_where        = array();

		// Loop thru each channel and its fields
		foreach ($fields AS $channel_id => $field_ids)
		{
			$sql_fields = array();
			$sql_cat    = '';

			// Per field, we need to add the LIKE clause to search it
			foreach ($field_ids AS $field_id)
			{
				// Field id could be numeric (for field_id_1) or not (for title)
				$field_name   = (is_numeric($field_id) ? 'd.field_id_' : 't.') . $field_id;

				// Add field name to select clause
				$sql_select[] = $field_name;

				// Add LIKE clause to temporary
				$sql_fields[] = sprintf($sql_field_tmpl, $field_name, $sql_keywords);
			}

			// If we need to limit by category, create that clause here
			if (isset($allowed_cats[$channel_id]))
			{
				$sql_cat = sprintf($sql_cat_tmpl, implode(',', $allowed_cats[$channel_id]));
			}

			// Add the full WHERE clause per channel to the where-array
			$sql_where[] = sprintf($sql_channel_tmpl, $channel_id, implode(' OR ', $sql_fields), $sql_cat);
		}

		// Add mandatory fields to SELECT array
		$sql_select = array_unique(array_merge(array('t.entry_id', 't.title', 't.channel_id'), $sql_select));

		// Start building query
		ee()->db->select($sql_select)
		     ->from('channel_titles t')
		     ->join('channel_data d', 't.entry_id = d.entry_id')
		     ->where_in('t.channel_id', array_keys($fields))
		     ->where('('.implode(' OR ', $sql_where).')')
		     ->group_by('t.entry_id')
		     ->order_by('t.entry_id', 'desc')
		     ->limit(self::PREVIEW_LIMIT);

		// Limit to user's own entries
		if ($this->member_group != 1 && ee()->session->userdata('can_edit_other_entries') == 'n')
		{
			ee()->db->where('t.author_id', $member_id);
		}

		// Join category_posts if necessary
		if ($allowed_cats || $selected_cats)
		{
			ee()->db->join('category_posts cp', 'd.entry_id = cp.entry_id', 'left');

			// Limit by selected cats, if given
			if ($selected_cats)
			{
				ee()->db->where_in('cp.cat_id', $selected_cats);
			}
		}

		// And get the results
		$query = ee()->db->get();

		// --------------------------------------
		// Create nested array from results, with match preview
		// --------------------------------------

		$preview = array();
		$keyword_length = strlen($keywords);

		foreach ($query->result_array() AS $row)
		{
			$row['matches'] = array();

			foreach ($fields[$row['channel_id']] AS $field_id)
			{
				// Field name shortcut
				$field = (is_numeric($field_id) ? $row['field_id_'.$field_id] : $row[$field_id]);

				if ($matches = mtt_strpos_all($field, $keywords))
				{
					$subs = mtt_substr_pad($field, $matches, $keyword_length, self::PREVIEW_PAD);
					$subs = array_map('htmlspecialchars', $subs);
					foreach ($subs AS &$sub)
					{
						$sub = mtt_hilite($sub, htmlspecialchars($keywords));
					}
					$row['matches'][$field_id] = $subs;
				}
			}

			if ($row['matches']) $preview[] = $row;
		}

		$this->data['preview'] = $preview;

		// --------------------------------------
		// Create form action
		// --------------------------------------

		$this->data['form_action'] = $this->mcp_url('replace');

		// --------------------------------------
		// If Ajax request, load parial view and exit
		// --------------------------------------

		if (is_ajax())
		{
			//  Do CSRF jig
			$this->_add_csrf_tokens_to_view();

			die(ee()->load->view('ajax_preview', $this->data, TRUE));
		}
	}

	/**
	 * Perform find & replace in DB
	 *
	 * @access      public
	 * @return      void
	 */
	public function replace()
	{
		if ( ! ($data = ee()->input->post('encoded_preview')))
		{
			ee()->functions->redirect($this->mcp_url('find'));
			exit;
		}

		$data        = mtt_search_decode($data);
		$keywords    = $data['keywords'];
		$replacement = ee()->input->post('replacement');
		$entries     = ee()->input->post('entries');

		if ( ! ($data && $entries))
		{
			ee()->functions->redirect($this->mcp_url('find'));
			exit;
		}

		// --------------------------------------
		// Compose all needed queries
		// --------------------------------------

		$sql = array();
		$sql_replace_tmpl = "%s = REPLACE(%s, '%s', '%s')";
		$sql_update_tmpl  = "UPDATE %s SET %s WHERE entry_id IN (%s);";
		$sql_keywords     = ee()->db->escape_str($keywords);
		$sql_replacement  = ee()->db->escape_str($replacement);
		$all_entries      = array();

		foreach ($entries AS $channel_id => $entry_ids)
		{
			// initiate arrays per channel
			$tables = array();

			// Get field ids
			$field_ids = $data['fields'][$channel_id];

			// SQL safe entry ids
			$sql_entries = implode(',', $entry_ids);

			// Loop thru each field id and add update statement to batch
			foreach ($field_ids AS $field_id)
			{
				$field = is_numeric($field_id) ? "field_id_{$field_id}" : $field_id;
				$table = is_numeric($field_id) ? 'channel_data' : 'channel_titles';

				// Add replace statement to correct table
				$tables[$table][] = sprintf($sql_replace_tmpl, $field, $field, $sql_keywords, $sql_replacement);

				// Replace Grid/Matrix columns?
				if (($is_grid   = $this->_field_is_type($field_id, 'grid')) ||
					($is_matrix = $this->_field_is_type($field_id, 'matrix')))
				{
					$col_table  = $is_grid ? 'grid_columns' : 'matrix_cols';
					$data_table = $is_grid ? 'channel_grid_field_'.$field_id : 'matrix_data';

					// Replace SQL for searchable columns
					foreach ($this->_get_cols($field_id, $col_table) AS $col_id)
					{
						$col = 'col_id_'.$col_id;
						$tables[$data_table][] = sprintf($sql_replace_tmpl, $col, $col, $sql_keywords, $sql_replacement);
					}
				}
			}

			// Add query to change edit date, which is in a STUPID FORMAT!
			$tables['channel_titles'][] = sprintf("edit_date = '%s'",
				date('YmdHis', ee()->localize->now));

			// Compose SQL from each table and statements
			foreach ($tables AS $table => $statements)
			{
				$sql[] = sprintf(
					$sql_update_tmpl,
					ee()->db->dbprefix.$table,
					implode(', ', $statements),
					$sql_entries
				);
			}

			// Add entry_ids to all_entries
			$all_entries = array_unique(array_merge($all_entries, $entry_ids));
		}

		// --------------------------------------
		// Execute all queries!
		// --------------------------------------

		foreach ($sql AS $query)
		{
			ee()->db->query($query);
		}

		// --------------------------------------
		// Update index for affected entries
		// --------------------------------------

		ee()->load->library('Mtt_search_index');
		ee()->mtt_search_index->build(FALSE, $all_entries);

		// --------------------------------------
		// Clear cache
		// --------------------------------------

		ee()->functions->clear_caching('all', '', TRUE);

		// --------------------------------------
		// Add to replace log
		// --------------------------------------

		ee()->mtt_search_replace_log_model->insert(array(
			'site_id'      => $this->site_id,
			'member_id'    => ee()->session->userdata['member_id'],
			'replace_date' => ee()->localize->now,
			'keywords'     => $data['keywords'],
			'replacement'  => $replacement,
			'fields'       => mtt_search_encode($data['fields'], FALSE),
			'entries'      => '|'.implode('|', $all_entries).'|'
		));

		// -------------------------------------
		// 'mtt_search_post_replace' hook.
		//  - Do something after the replace action
		// -------------------------------------

		if (ee()->extensions->active_hook('mtt_search_post_replace') === TRUE)
		{
			ee()->extensions->call('mtt_search_post_replace', $all_entries);
		}

		// --------------------------------------
		// Set feedback msg
		// --------------------------------------

		$this->data['feedback'] = array(
			'keywords'      => $keywords,
			'replacement'   => $replacement,
			'total_entries' => count($all_entries)
		);

		// --------------------------------------
		// Go back to F&R home
		// --------------------------------------

		if (is_ajax())
		{
			die(ee()->load->view('ajax_replace_feedback', $this->data, TRUE));
		}
		else
		{
			ee()->session->set_flashdata('replace_feedback', mtt_search_encode($this->data['feedback']));
			ee()->functions->redirect($this->mcp_url('find'));
		}
	}

	// --------------------------------------------------------------------

	/**
	 * View replace log
	 *
	 * @access      public
	 * @return      string
	 */
	public function replace_log()
	{
		$member_id = ee()->session->userdata('member_id');

		// --------------------------------------
		// Get total rows of log
		// --------------------------------------

		if ($this->member_group != 1)
		{
			ee()->db->where('member_id', $member_id);
		}

		$total = ee()->db->where('site_id', $this->site_id)->count_all_results(ee()->mtt_search_replace_log_model->table());

		// --------------------------------------
		// Get start row
		// --------------------------------------

		if (($start = ee()->input->get('start')) === FALSE)
		{
			$start = 0;
		}

		// --------------------------------------
		// Load pagination class, if necessary
		// --------------------------------------

		if ($total > self::VIEW_LOG_LIMIT)
		{
			ee()->load->library('pagination');

			// Pagination link template
			$pagi_link = '<img src="'.ee()->cp->cp_theme_url.'images/pagination_%s_button.gif" width="13" height="13" alt="%s" />';

			// Set pagination parameters
			ee()->pagination->initialize(array(
				'base_url'             => $this->mcp_url('replace_log'),
				'total_rows'           => $total,
				'per_page'             => self::VIEW_LOG_LIMIT,
				'page_query_string'    => TRUE,
				'query_string_segment' => 'start',
				'full_tag_open'        => '<span>',
				'full_tag_close'       => '</span>',
				'prev_link'            => sprintf($pagi_link, 'prev', '&larr;'),
				'next_link'            => sprintf($pagi_link, 'next', '&rarr;'),
				'first_link'           => sprintf($pagi_link, 'first', '&ldarr;'),
				'last_link'            => sprintf($pagi_link, 'last', '&rdarr;')
			));

			// Create the links
			$this->data['pagination'] = ee()->pagination->create_links();
		}
		else
		{
			$this->data['pagination'] = FALSE;
		}

		// --------------------------------------
		// Get search log
		// --------------------------------------

		ee()->db->select('members.screen_name, mtt_search_replace_log.*')
		             ->where('site_id', $this->site_id)
		             ->order_by('replace_date', 'desc')
		             ->limit(self::VIEW_LOG_LIMIT, $start)
		             ->join('members', 'members.member_id = mtt_search_replace_log.member_id', 'left');

		// Filter by member_id if not a superadmin
		if ($this->member_group != 1)
		{
			ee()->db->where('members.member_id', $member_id);
		}

		if ($log = ee()->mtt_search_replace_log_model->get_all())
		{
			// --------------------------------------
			// Set pagination details
			// --------------------------------------

			$this->data['viewing_rows'] = sprintf(lang('viewing_rows'),
				$start + 1,
				(($to = $start + self::VIEW_LOG_LIMIT) > $total) ? $total : $to,
				$total
			);

			// --------------------------------------
			// Modify rows
			// --------------------------------------

			foreach ($log AS &$row)
			{
				// Display a nice date
				$row['replace_date'] = $this->_human_time($row['replace_date']);

				// Account for guests
				$row['member_id'] = $row['member_id'] ? $row['screen_name'] : '--';

				// Affected entries
				$row['entries'] = array_filter(explode('|', $row['entries']));
			}
		}

		// --------------------------------------
		// Add log to data array
		// --------------------------------------

		$this->data['log'] = $log;
		$this->data['is_admin'] = ($this->member_group == 1);

		// --------------------------------------
		// Set title and breadcrumb and view page
		// --------------------------------------

		$this->_set_cp_var('cp_page_title', lang('replace_log'));
		ee()->cp->set_breadcrumb($this->mcp_url(), lang('mtt_search_module_name'));

		return $this->view('mcp_list_replace_log');
	}

	/**
	 * Clear the replace log for current site
	 *
	 * @access      public
	 * @return      void
	 */
	public function clear_replace_log()
	{
		// Delete
		ee()->db->where('site_id', $this->site_id);
		ee()->db->delete(ee()->mtt_search_replace_log_model->table());

		// And go back
		ee()->functions->redirect($this->mcp_url('replace_log'));
	}

	/**
	 * View replace details, called by ajax in modal
	 *
	 * @access      public
	 * @return      string
	 */
	public function replace_details()
	{
		// --------------------------------------
		// Get the id and row of details
		// --------------------------------------

		$log_id = ee()->input->get('log_id');
		$log    = ee()->mtt_search_replace_log_model->get_one($log_id);

		// --------------------------------------
		// Get titles for entries
		// --------------------------------------

		$query = ee()->db->select('entry_id, channel_id, title')
		       ->from('channel_titles')
		       ->where_in('entry_id', array_filter(explode('|', $log['entries'])))
		       ->order_by('entry_id', 'asc')
		       ->get();

		$log['entries'] = $query->result_array();

		// --------------------------------------
		// Get Channel and Field details
		// --------------------------------------

		$query = ee()->db->select('channel_id, channel_title')
		       ->from('channels')
		       ->where('site_id', $this->site_id)
		       ->get();

		$channels = mtt_flatten_results($query->result_array(), 'channel_title', 'channel_id');

		$query = ee()->db->select('field_id, field_label')
		       ->from('channel_fields')
		       ->where('site_id', $this->site_id)
		       ->where_in('field_type', $this->allowed_types)
		       ->get();

		$fields = mtt_flatten_results($query->result_array(), 'field_label', 'field_id');

		// --------------------------------------
		// Create nested array for selected channels & fields
		// --------------------------------------

		$searched_fields = array();
		/*
		foreach (mtt_search_decode($log['fields'], FALSE) AS $channel_id => $field_ids)
		{
			$searched_fields[$channels[$channel_id]] = array();

			foreach ($field_ids AS $field_id)
			{
				$searched_fields[$channels[$channel_id]][] = is_numeric($field_id) ? $fields[$field_id] : lang($field_id);
			}
		}
		*/
		$log['fields'] = $searched_fields;

		// --------------------------------------
		// Add to data array for display purposes
		// --------------------------------------

		$this->data = array_merge($this->data, $log);
		$this->data['channels'] = $channels;

		if (is_ajax())
		{
			die(ee()->load->view('ajax_replace_details', $this->data, TRUE));
		}
	}

	// --------------------------------------------------------------------

	/**
	 * View search log
	 *
	 * @access      public
	 * @return      string
	 */
	public function search_log()
	{
		// Keep track of this URL
		$log_url = $this->mcp_url('search_log');
		$filtered = FALSE;

		// --------------------------------------
		// Check if filter form was posted
		// --------------------------------------

		if ($filter = ee()->input->post('filter'))
		{
			if ($filter = array_filter($filter, 'mtt_not_empty'))
			{
				$log_url .= AMP.'filter='.mtt_search_encode($filter);
			}

			// Go to same url with encoded filter in GET
			ee()->functions->redirect($log_url);
		}

		// --------------------------------------
		// Check if there's a GET filter
		// --------------------------------------

		if ($filter = ee()->input->get('filter'))
		{
			$log_url .= AMP.'filter='.$filter;
			$filter = mtt_search_decode($filter);
			$filtered = TRUE;
		}

		if ( ! is_array($filter)) $filter = array();

		// Add site id to it
		$filter['site_id'] = $this->site_id;

		// --------------------------------------
		// Populate filters
		// --------------------------------------

		// Get all unique members
		$members = ee()->db->select('m.member_id, m.screen_name')
			->from('members m')
			->join('mtt_search_log l', 'm.member_id = l.member_id', '')
			->where('l.site_id', $this->site_id)
			->group_by('m.member_id')
			->order_by('m.screen_name', 'asc')
			->get()
			->result_array();

		$members = mtt_flatten_results($members, 'screen_name', 'member_id');
		$this->data['members'] = $members;

		// Get all days of searching
		$dates = ee()->db->select("DISTINCT(FROM_UNIXTIME(search_date, '%Y-%m-%d')) AS search_date", FALSE)
			   ->from('mtt_search_log')
			   ->where('site_id', $this->site_id)
			   ->order_by('search_date', 'desc')
			   ->get()
			   ->result_array();

		$dates = mtt_flatten_results($dates, 'search_date');
		$this->data['dates'] = $dates;

		// --------------------------------------
		// Get total rows of log
		// --------------------------------------

		$total = ee()->mtt_search_log_model->get_filtered_count($filter);

		// Prune now?
		if (($search_log_size = ee()->mtt_search_settings->get('search_log_size')) !== '0' AND
			$total > $search_log_size)
		{
			ee()->mtt_search_log_model->prune($this->site_id, $search_log_size);
			$total = $search_log_size;
		}

		// --------------------------------------
		// Get start row
		// --------------------------------------

		if (($start = ee()->input->get('start')) === FALSE)
		{
			$start = 0;
		}

		// --------------------------------------
		// Load pagination class, if necessary
		// --------------------------------------

		if ($total > self::VIEW_LOG_LIMIT)
		{
			ee()->load->library('pagination');

			// Pagination link template
			$pagi_link = '<img src="'.ee()->cp->cp_theme_url.'images/pagination_%s_button.gif" width="13" height="13" alt="%s" />';

			// Set pagination parameters
			ee()->pagination->initialize(array(
				'base_url'             => $log_url,
				'total_rows'           => $total,
				'per_page'             => self::VIEW_LOG_LIMIT,
				'page_query_string'    => TRUE,
				'query_string_segment' => 'start',
				'full_tag_open'        => '<span>',
				'full_tag_close'       => '</span>',
				'prev_link'            => sprintf($pagi_link, 'prev', '&larr;'),
				'next_link'            => sprintf($pagi_link, 'next', '&rarr;'),
				'first_link'           => sprintf($pagi_link, 'first', '&ldarr;'),
				'last_link'            => sprintf($pagi_link, 'last', '&rdarr;')
			));

			// Create the links
			$this->data['pagination'] = ee()->pagination->create_links();
		}
		else
		{
			$this->data['pagination'] = FALSE;
		}

		// --------------------------------------
		// Get search log
		// --------------------------------------

		// pagination
		ee()->db->order_by('search_date', 'desc');
		ee()->db->limit(self::VIEW_LOG_LIMIT, $start);

		$log = ee()->mtt_search_log_model->get_filtered_rows($filter);

		if ($log)
		{
			// --------------------------------------
			// Set pagination details
			// --------------------------------------

			$this->data['viewing_rows'] = sprintf(lang('viewing_rows'),
				$start + 1,
				(($to = $start + self::VIEW_LOG_LIMIT) > $total) ? $total : $to,
				$total
			);

			// --------------------------------------
			// Shortcut URL
			// --------------------------------------

			$shortcut_url = $this->mcp_url('edit_shortcut', 'shortcut_id=new&amp;log_id=%s');

			// --------------------------------------
			// Modify rows
			// --------------------------------------

			foreach ($log AS &$row)
			{
				// Display a nice date
				$row['search_date'] = $this->_human_time($row['search_date']);

				// Account for guests
				$row['member_id'] = isset($members[$row['member_id']])
					? $members[$row['member_id']]
					: '';

				// Parameters
				$row['parameters'] = mtt_search_decode($row['parameters'], FALSE);

				// Shortcut URL
				$row['shortcut_url'] = sprintf($shortcut_url, $row['log_id']);
			}
		}

		// --------------------------------------
		// Add log to data array
		// --------------------------------------

		$this->data['log'] = $log;
		$this->data['is_admin'] = ($this->member_group == 1);
		$this->data['filter'] = $filter;
		$this->data['filtered'] = $filtered;

		// --------------------------------------
		// Set title and breadcrumb and view page
		// --------------------------------------

		$this->_set_cp_var('cp_page_title', lang('view_search_log'));
		ee()->cp->set_breadcrumb($this->mcp_url(), lang('mtt_search_module_name'));

		return $this->view('mcp_list_search_log');
	}

	/**
	 * Clear the search log for current site
	 *
	 * @access      public
	 * @return      void
	 */
	public function clear_search_log()
	{
		// Delete
		ee()->db->where('site_id', $this->site_id);
		ee()->db->delete(ee()->mtt_search_log_model->table());

		// And go back
		ee()->functions->redirect($this->mcp_url('search_log'));
	}

	/**
	 * Download/export search log
	 *
	 * @access      public
	 * @return      void
	 */
	public function export_search_log()
	{
		// --------------------------------------
		// Load download helper
		// --------------------------------------

		ee()->load->helper('download');

		// --------------------------------------
		// Table/prefix
		// --------------------------------------

		$t = ee()->mtt_search_log_model->table();

		// --------------------------------------
		// Get all log records
		// --------------------------------------

		$query = ee()->db->select(array(
				"{$t}.parameters", "{$t}.keywords", 'members.screen_name AS member',
				"{$t}.ip_address", "FROM_UNIXTIME({$t}.search_date) AS `date`"))
		       ->from($t)
		       ->join('members', "members.member_id = {$t}.member_id", 'left')
		       ->where('site_id', $this->site_id)
		       ->order_by('search_date', 'desc')
		       ->get();

		// --------------------------------------
		// Get keys
		// --------------------------------------

		$keys = $query->list_fields();
		$pk   = array_shift($keys);

		// --------------------------------------
		// Get additional keys from params
		// --------------------------------------

		foreach ($query->result_array() AS $row)
		{
			// Get the params
			$params = mtt_search_decode(array_shift($row), FALSE);

			foreach (array_keys($params) AS $key)
			{
				if (array_search($key, $keys) === FALSE)
				{
					$keys[] = $key;
				}
			}
		}

		// --------------------------------------
		// Fill the log
		// --------------------------------------

		$log = array();
		$log[] = $this->_csv_row($keys);

		foreach ($query->result_array() AS $row)
		{
			$params = array_shift($row);
			$params = mtt_search_decode($params, FALSE);
			$row = array_merge($row, $params);

			$log_row = array();

			foreach ($keys AS $k => $v)
			{
				$log_row[$k] = isset($row[$v]) ? $row[$v] : '';
			}

			$log[] = $this->_csv_row($log_row);
		}

		$log = implode("\n", $log);

		// --------------------------------------
		// File name
		// --------------------------------------

		$name = 'search_log_'.date('YmdHi').'.csv';

		// --------------------------------------
		// And download it
		// --------------------------------------

		force_download($name, $log);
	}

	/**
	 * Transform an array to a CSV row
	 *
	 * @access     private
	 * @param      array
	 * @return     string
	 */
	private function _csv_row($array)
	{
		$row = array();

		foreach ($array AS $val)
		{
			// CSV-escaping of double quotes by doubling double quotes
			$row[] = '"'.str_replace('"', '""', $val).'"';
		}

		return implode(',', $row);
	}

	// --------------------------------------------------------------------

	/**
	 * Rebuild index
	 *
	 * @access      public
	 * @return      string
	 */
	public function build_index()
	{
		if (method_exists(ee()->security, 'restore_xid') &&
			version_compare(APP_VER, '2.8.0', '<'))
		{
			ee()->security->restore_xid();
		}

		// --------------------------------------
		// Only members are allowed to do this
		// --------------------------------------

		if ( ! ee()->session->userdata('member_id'))
		{
			show_error('Operation not permitted');
		}

		// --------------------------------------
		// Load index library
		// --------------------------------------

		ee()->load->library('mtt_search_index');

		// --------------------------------------
		// Get info from Query String
		// --------------------------------------

		$col_id  = (int) ee()->input->get('collection_id');
		$start   = (int) ee()->input->post('start');
		$rebuild = (string) ee()->input->post('rebuild');

		// --------------------------------------
		// Delete existing collection if rebuild == 'yes'
		// --------------------------------------

		if ($start === 0 && $rebuild == 'yes')
		{
			ee()->mtt_search_index_model->delete($col_id, 'collection_id');
		}

		// --------------------------------------
		// Call private build_index method
		// --------------------------------------

		$response = ee()->mtt_search_index->build($col_id, NULL, $start);

		// --------------------------------------
		// Optimize table when we're done
		// --------------------------------------

		if ($response['status'] == 'done')
		{
			ee()->mtt_search_index_model->optimize();
		}

		// --------------------------------------
		// Return JSON
		// --------------------------------------

		if (is_ajax())
		{
			die(json_encode($response));
		}
	}

	// --------------------------------------------------------------------
	// PRIVATE METHODS
	// --------------------------------------------------------------------

	/**
	 * Get array of channel_id => cat_ids for this member
	 *
	 * @access      private
	 * @param       int
	 * @return      array
	 */
	private function _get_permitted_categories($member_id, $nested = FALSE)
	{
		$categories = FALSE;

		// --------------------------------------
		// Bail out if category permissions ext is not installed
		// --------------------------------------

		$package = 'category_permissions';

		if (array_key_exists($package, ee()->addons->get_installed('extensions')))
		{
			$categories = array();

			// Load CatPerm model so we can use their stuff
			ee()->load->add_package_path(PATH_THIRD.$package);
			ee()->load->model($package.'_model', $package);

			// Get array of category ids
			if (ee()->$package->member_has_category_permissions($member_id))
			{
				$cat_ids = ee()->$package->get_member_permitted_categories($member_id);
			}
			else
			{
				$cat_ids = array();
			}

			// Clean up after us
			ee()->load->remove_package_path(PATH_THIRD.$package);

			// If we have categories, associate them with group ids
			if ($cat_ids)
			{
				// $query = ee()->db->select('t.channel_id, t.entry_id')
				//        ->from('channel_titles t')
				//        ->join('category_posts cp', 't.entry_id = cp.entry_id', 'left')
				//        ->where_in('cp.cat_id', $cat_ids)
				//        ->or_where('cp.cat_id IS NULL')
				//        ->get();
				//
				// foreach ($query->result() AS $row)
				// {
				// 	$categories[$row->channel_id][] = $row->entry_id;
				// }

				if ($nested === FALSE)
				{
					return $cat_ids;
				}

				$cats_by_group = array();

				// First, organize categories by category group
				$query = ee()->db->select('cat_id, group_id')
				       ->from('categories')
				       ->where('site_id', $this->site_id)
				       ->where_in('cat_id', $cat_ids)
				       ->get();

				foreach ($query->result() AS $row)
				{
					$cats_by_group[$row->group_id][] = $row->cat_id;
				}

				// Then get channel and their cat groups
				$query = ee()->db->select('channel_id, cat_group')
				       ->from('channels')
				       ->where('site_id', $this->site_id)
				       ->get();

				// And associate channel with cat ids
				foreach ($query->result() AS $row)
				{
					foreach (array_filter(explode('|', $row->cat_group)) AS $group_id)
					{
						if ( ! isset($categories[$row->channel_id]))
						{
							$categories[$row->channel_id] = array();
						}

						$categories[$row->channel_id] = array_merge(
							$categories[$row->channel_id],
							$cats_by_group[$group_id]
						);
					}
				}
			}
		}

		return $categories;
	}

	// --------------------------------------------------------------------

	/**
	 * Is given field ID a of given type field?
	 *
	 * @access      private
	 * @param       int
	 * @param       string
	 * @return      mixed [int|bool]
	 */
	private function _field_is_type($id, $type)
	{
		static $fields = array();

		if (empty($fields))
		{
			$query = ee()->db->select('field_id, field_type')
			       ->from('channel_fields')
			       ->where('site_id', $this->site_id)
			       ->get();

			$fields = mtt_flatten_results($query->result_array(), 'field_type', 'field_id');
		}

		return (isset($fields[$id]) && $fields[$id] == $type);
	}

	/**
	 * Get array of column ids for given Grid or Matrix field
	 *
	 * @access      private
	 * @param       int
	 * @param       string
	 * @return      array
	 */
	private function _get_cols($id, $table = 'grid_columns')
	{
		static $cols = array();

		if ( ! isset($cols[$table][$id]))
		{
			$query = ee()->db->select('col_id')
			       ->from($table)
			       ->where('field_id', $id)
			       ->where('col_search', 'y')
			       ->get();

			$cols[$table][$id] = mtt_flatten_results($query->result_array(), 'col_id');
		}

		return $cols[$table][$id];
	}

	// --------------------------------------------------------------------

	/**
	 * Set cp var
	 *
	 * @access     private
	 * @param      string
	 * @param      string
	 * @return     void
	 */
	private function _set_cp_var($key, $val)
	{
		if (version_compare(APP_VER, '2.6.0', '<'))
		{
			ee()->cp->set_variable($key, $val);
		}
		else
		{
			ee()->view->$key = $val;
		}
	}

	/**
	 * Return human time
	 *
	 * @access     private
	 * @param      string
	 * @return     string
	 * @return     void
	 */
	private function _human_time($str)
	{
		$method = (version_compare(APP_VER, '2.6.0', '<'))
			? 'set_human_time'
			: 'human_time';

		return ee()->localize->$method($str);
	}

	// --------------------------------------------------------------------

	/**
	 * Permissions: can current user manage collections?
	 *
	 * @access      protected
	 * @return      bool
	 */
	protected function can_manage()
	{
		return $this->_can_i('manage');
	}

	/**
	 * Permissions: can current user manage shortcuts?
	 *
	 * @access      protected
	 * @return      bool
	 */
	protected function can_manage_shortcuts()
	{
		return $this->_can_i('manage_shortcuts');
	}

	/**
	 * Permissions: can current user find and replace?
	 *
	 * @access      protected
	 * @return      bool
	 */
	protected function can_replace()
	{
		return $this->_can_i('replace');
	}

	/**
	 * Permissions: can current user view search log?
	 *
	 * @access      protected
	 * @return      bool
	 */
	protected function can_view_search_log()
	{
		return $this->_can_i('view_search_log');
	}

	/**
	 * Permissions: can current user view replace log?
	 *
	 * @access      protected
	 * @return      bool
	 */
	protected function can_view_replace_log()
	{
		return $this->_can_i('view_replace_log');
	}

	/**
	 * Can I do what? SuperAdmins always can.
	 *
	 * @access      private
	 * @return      bool
	 */
	private function _can_i($do_what)
	{
		$can = (array) ee()->mtt_search_settings->get('can_'.$do_what);
		return ($this->member_group === 1 || in_array($this->member_group, $can));
	}

	// --------------------------------------------------------------------

	/**
	 * Adds the XID / CSRF_TOKEN data to the view
	 */
	private function _add_csrf_tokens_to_view()
	{
		$this->data['csrf_token_name'] = defined('CSRF_TOKEN') ? 'csrf_token' : 'XID';
		$this->data['csrf_token_value'] = defined('CSRF_TOKEN') ? CSRF_TOKEN : XID_SECURE_HASH;
	}

	/**
	 * View add-on page
	 *
	 * @access     protected
	 * @param      string
	 * @return     string
	 */
	private function view($file)
	{
		// -------------------------------------
		//  Add base url to view
		// -------------------------------------

		$this->data['base_url'] = $this->mcp_url();

		// -------------------------------------
		//  Do CSRF jig
		// -------------------------------------

		$this->_add_csrf_tokens_to_view();

		// -------------------------------------
		//  Load CSS and JS
		// -------------------------------------

		$version = '&amp;v=' . (MTT_SEARCH_DEBUG ? time() : MTT_SEARCH_VERSION);

		ee()->cp->load_package_css($this->package.$version);
		ee()->cp->load_package_js($this->package.$version);

		// --------------------------------------
		// Add themes url for images
		// --------------------------------------

		$this->data['themes_url'] = ee()->config->item('theme_folder_url');

		// -------------------------------------
		//  Add feedback msg to output
		// -------------------------------------

		if ($this->data['message'] = ee()->session->flashdata('msg'))
		{
			ee()->javascript->output(array(
				'$.ee_notice("'.lang($this->data['message']).'",{type:"success",open:true});',
				'window.setTimeout(function(){$.ee_notice.destroy()}, 2000);'
			));
		}

		// -------------------------------------
		//  Add permissions to data
		// -------------------------------------

		foreach (ee()->mtt_search_settings->permissions() AS $perm)
		{
			$this->data['member_'.$perm] = $this->$perm();
		}

		$this->data['member_group'] = $this->member_group;

		// -------------------------------------
		//  Add menu to page if manager
		// -------------------------------------

		$nav = array('mtt_search_module_name' => $this->mcp_url());

		if ($this->can_manage())
		{
			$nav['collections'] = $this->mcp_url('collections');
		}

		if ($this->can_manage_shortcuts())
		{
			$nav['shortcuts'] = $this->mcp_url('groups');
		}

		if ($this->can_view_search_log())
		{
			$nav['search_log'] = $this->mcp_url('search_log');
		}

		if ($this->can_replace())
		{
			$nav['find_replace'] = $this->mcp_url('find');
		}

		if ($this->can_view_replace_log())
		{
			$nav['replace_log'] = $this->mcp_url('replace_log');
		}

		ee()->cp->set_right_nav($nav);

		// -------------------------------------
		//  Add JS language object
		// -------------------------------------

		$lang = array();
		$js_lang_lines = array(
			'deleting',
			'optimizing',
			'done',
			'no_keywords_given',
			'no_fields_selected',
			'no_entries_selected',
			'working'
		);

		foreach ($js_lang_lines AS $line)
		{
			$lang[$line] = lang($line);
		}

		ee()->javascript->output(";$.MTT_lang = ".json_encode($lang).";");

		// -------------------------------------
		//  Return the view
		// -------------------------------------

		return ee()->load->view($file, $this->data, TRUE);
	}

} // End Class

/* End of file mcp.mtt_search.php */