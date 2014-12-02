<?php if (!defined('BASEPATH')) die('No direct script access allowed');

// include config file
include PATH_THIRD.'channel_ratings/config'.EXT;

/**
 * Channel Ratings Module Extension File
 *
 * @package			DevDemon_ChannelRatings
 * @version			3.1
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com
 * @see				http://expressionengine.com/user_guide/development/module_tutorial.html#core_module_file
 */
class Channel_ratings_ext
{
	public $version			= CHANNELRATINGS_VERSION;
	public $name			= 'Channel Ratings Extension';
	public $description		= 'Supports the Channel Ratings Module in various functions.';
	public $docs_url		= 'http://www.devdemon.com';
	public $settings_exist	= FALSE;
	public $settings		= array();
	public $hooks			= array('delete_entries_loop', 'comment_form_hidden_fields', 'comment_form_end',
									'insert_comment_start','insert_comment_end',
									'update_comment_additional', 'delete_comment_additional',
									'relationships_query', 'cp_js_end');

	// ********************************************************************************* //

	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
		$this->site_id = $this->EE->config->item('site_id');

		// Load Rating Types
		$this->EE->load->add_package_path(PATH_THIRD . 'channel_ratings/');
		$this->EE->config->load('ratings');
		$this->TYPES = $this->EE->config->item('cr_rating_types');
		$this->TYPES_INV = array_flip($this->TYPES);
	}

	// ********************************************************************************* //

	/**
	 * Executed in the loop that deletes each entry, after deletion, prior to stat recounts.
	 *
	 * @param int $entry_id - entry_id of the entry being deleted
	 * @param int $channel_id - channel_id of the entry being deleted
	 * @access public
	 * @see http://expressionengine.com/user_guide/development/extension_hooks/cp/content_edit/index.html#delete_entries_loop
	 * @return void
	 */
	public function delete_entries_loop($entry_id, $channel_id)
	{
		// Delete All Ratings!
		$this->EE->db->where('entry_id', $entry_id);
		$this->EE->db->delete('exp_channel_ratings');

		$this->EE->db->where('entry_id', $entry_id);
		$this->EE->db->delete('exp_channel_ratings_stats');
	}

	// ********************************************************************************* //

	/**
	 * Add/Remove Hidden Fields for Comment Form
	 *
	 * @param array $hidden_fields The current array of hidden fields for the comment form
	 * @access public
	 * @see http://expressionengine.com/user_guide/development/extension_hooks/module/comment/index.html#comment_form_hidden_fields
	 * @return array
	 */
	public function comment_form_hidden_fields($hidden_fields)
	{
		// Check if we're not the only one using this hook
		if($this->EE->extensions->last_call !== FALSE)
		{
			$hidden_fields = $this->EE->extensions->last_call;
		}


		// Load Library
		if (class_exists('ChannelRatings_ACT') != TRUE) include 'act.channel_ratings.php';

		$ACT = new ChannelRatings_ACT();

		$hidden_fields = $ACT->rating_comment_form_hidden_fields($hidden_fields);

		return $hidden_fields;
	}

	// ********************************************************************************* //

	/**
	 * Modify, add, replace anything in the Comment Form tag
	 *
	 * @param string $tagdata The tag data for the Comment Form tag
	 * @access public
	 * @see http://expressionengine.com/user_guide/development/extension_hooks/module/comment/index.html#insert_comment_end
	 * @return string
	 */
	public function comment_form_end($tagdata)
	{
		// Check if we're not the only one using this hook
		if($this->EE->extensions->last_call !== FALSE)
		{
			$tagdata = $this->EE->extensions->last_call;
		}

		// Load Library
		if (class_exists('ChannelRatings_ACT') != TRUE) include 'act.channel_ratings.php';

		$ACT = new ChannelRatings_ACT();

		$tagdata = $ACT->rating_comment_form_end($tagdata);

		return $tagdata;
	}

	// ********************************************************************************* //

	/**
	 * Allows complete rewrite of comment submission routine, or could be used to modify the POST data before processing
	 *
	 * @access public
	 * @see http://expressionengine.com/user_guide/development/extension_hooks/module/comment/index.html#insert_comment_start
	 * @return void
	 */
	public function insert_comment_start()
	{
		// Load Library
		if (class_exists('ChannelRatings_ACT') != TRUE) include 'act.channel_ratings.php';

		$ACT = new ChannelRatings_ACT();

		$ACT->rating_comment_precheck();

		return;
	}

	// ********************************************************************************* //

	/**
	 * Allows additional processing when a comment is updated, executed after the comment is updated.
	 *
	 * @param int $comment_id comment_id of the comment being modified
	 * @param array $data Array of data used to update the comment.
	 * @access public
	 * @see http://expressionengine.com/user_guide/development/extension_hooks/module/comment/index.html#update_comment_additional
	 * @return void
	 */
	public function update_comment_additional($comment_id, $data)
	{
		if (isset($data['status']) == FALSE) return;

		$this->EE->load->model('ratings_model');

		$query = $this->EE->db->select('*')->from('exp_channel_ratings')->where('item_id', $comment_id)->where_in('rating_type', array($this->TYPES['comment_review'], $this->TYPES['comment_entry']))->get();

		if ($query->num_rows() == 0) return;

		// Update Rating Status
		$this->EE->db->set('rating_status', (($data['status'] == 'o') ? '1' : '0'));
		$this->EE->db->where('item_id', $comment_id);
		$this->EE->db->where_in('rating_type', array($this->TYPES['comment_review'], $this->TYPES['comment_entry']));
		$this->EE->db->update('exp_channel_ratings');

		// Update Entry Stats
		$data = $query->row_array();
		$data['rating_type_name'] = $this->TYPES_INV[$data['rating_type']];
		$this->EE->ratings_model->update_stats($data);

		return;
	}

	// ********************************************************************************* //

	/**
	 * Allows additional processing after a comment is deleted.
	 *
	 * @param array $comment_ids Array of comment ids being deleted
	 * @access public
	 * @see http://expressionengine.com/user_guide/development/extension_hooks/module/comment/index.html#delete_comment_additional
	 * @return void
	 */
	public function delete_comment_additional($comment_ids)
	{
		if (is_array($comment_ids) != TRUE OR empty($comment_ids) == TRUE) return;

		$this->EE->load->model('ratings_model');

		foreach ($comment_ids as $comment_id)
		{
			$query = $this->EE->db->select('*')->from('exp_channel_ratings')->where('item_id', $comment_id)->where_in('rating_type', array($this->TYPES['comment_review'], $this->TYPES['comment_entry']))->get();

			if ($query->num_rows() == 0) continue;

			$this->EE->db->where('item_id', $comment_id);
			$this->EE->db->where_in('rating_type', array($this->TYPES['comment_review'], $this->TYPES['comment_entry']));
			$this->EE->db->delete('exp_channel_ratings');


			// Update Entry Stats
			$data = $query->row_array();
			$data['rating_type_name'] = $this->TYPES_INV[$data['rating_type']];
			$this->EE->ratings_model->update_stats($data);
		}

		return;
	}

	// ********************************************************************************* //

	/**
	 * More emails, more processing, different redirect at the end of the comment inserting routine
	 *
	 * @param array $data Array of the data for the new comment
	 * @param bool $comment_moderate Whether the comment is going to be moderated
	 * @param int $comment_id the ID of the comment (added 1.6.1)
	 * @access public
	 * @see http://expressionengine.com/user_guide/development/extension_hooks/module/comment/index.html#insert_comment_end
	 * @return void
	 */
	public function insert_comment_end($data, $comment_moderate, $comment_id)
	{
		// Load Library
		if (class_exists('ChannelRatings_ACT') != TRUE) include 'act.channel_ratings.php';

		$ACT = new ChannelRatings_ACT();

		$ACT->rating_comment_insert($data, $comment_moderate, $comment_id);

		return;
	}

	// ********************************************************************************* //

	/**
	 * Allows you add javascript to every Control Panel page.
	 *
	 * @access public
	 * @see http://expressionengine.com/user_guide/development/extension_hooks/cp/javascript/index.html
	 * @return string
	 */
	public function cp_js_end()
	{
		$js = '';
		$out = '';

		// Check if we're not the only one using this hook
		if( $this->EE->extensions->last_call !== FALSE )
		{
			$js = $this->EE->extensions->last_call;
		}

		$this->EE->load->library('ratings_helper');

		// Load Library
		if (class_exists('ChannelRatings_ACT') != TRUE) include 'act.channel_ratings.php';
		$ACT = new ChannelRatings_ACT();

		$ACT_URL = $this->EE->ratings_helper->get_router_url();
		$THEME_URL = $this->EE->ratings_helper->define_theme_url() . "themes/default/";

		$out .= $ACT->CP_rating_comment_moderation();
		$out .= $ACT->CP_rating_edit_comment();

		$out = str_replace('%CHANNELRATINGS_ACT%', $ACT_URL, $out);
		$out = str_replace('%CHANNELRATINGS_THEMEURL%', $THEME_URL, $out);

		$js .= $out;

		return $js;
	}

	// ********************************************************************************* //

	/**
	 * Allows you add javascript to every Control Panel page.
	 *
	 * @param string $field_name – Name of current node being parsed.
	 * @param int $entry_ids – Entry IDs of entries being queried for.
	 * @param array $depths – Depth of branches.
	 * @param string $sql – Compiled SQL about to be run to gather related entries.
	 * @access public
	 * @see http://ellislab.com/expressionengine/user-guide/development/extension_hooks/global/relationships/index.html#relationships-query
	 * @return array
	 */
	public function relationships_query($field_name, $entry_ids, $depths, $sql)
	{
		if ($field_name == '__root__') {
			return $this->returnRelationshipsQuery(false, $sql);
		}

		$orderby = false;

		if (isset($this->EE->TMPL->tagparams['relationship:'.$field_name.':orderby']) === false) {
			return $this->returnRelationshipsQuery(false, $sql);
		}

		if ($this->EE->TMPL->tagparams['relationship:'.$field_name.':orderby'] == 'likes') {
			$orderby = 'likes';
		} else {
			$orderby = 'rating';
		}

		if (!$orderby) {
			return $this->returnRelationshipsQuery(false, $sql);
		}

		if ($orderby == 'likes') {
			$this->EE->db->join('exp_channel_ratings_likes cl', 'cl.entry_id = L0.parent_id AND cl.stats_row=1', 'left');
		} else {
			$this->EE->db->join('exp_channel_ratings_stats cs', 'cs.entry_id = L0.parent_id AND cs.field_id=0', 'left');
		}


		if (isset($this->EE->TMPL->tagparams['relationship:'.$field_name.':rating_required']) === true && $this->EE->TMPL->tagparams['relationship:'.$field_name.':rating_required'] == 'yes') {

			if ($orderby == 'likes') {
				$this->EE->db->where('cl.like_type', 0);
			} else {
				$this->EE->db->where('cs.rating_type', 0);
			}
		}

		return $this->returnRelationshipsQuery($orderby, $sql);
	}

	// ********************************************************************************* //

	private function returnRelationshipsQuery($type='rating', $sql) {
		if ($type === false) {
			$this->EE->db->_reset_select();
			return $this->EE->db->query($sql)->result_array();
		}

		$this->EE->db->ar_orderby = array();

		if ($type == 'likes') {
			//$this->EE->db->where('cl.stats_row', 1);
			$this->EE->db->order_by("cl.liked DESC");
		} else {
			$this->EE->db->order_by("cs.rating_avg DESC, cs.rating_total DESC, cs.rating_last_date DESC");
		}

		$sql = $this->EE->db->_compile_select(FALSE, FALSE);
		//print_r($sql);
		$this->EE->db->_reset_select();
		return $this->EE->db->query($sql)->result_array();
	}

	// ********************************************************************************* //

	/**
	 * Called by ExpressionEngine when the user activates the extension.
	 *
	 * @access		public
	 * @return		void
	 **/
	public function activate_extension()
	{
		foreach ($this->hooks as $hook)
		{
			 $data = array(	'class'		=>	__CLASS__,
			 				'method'	=>	$hook,
							'hook'      =>	$hook,
							'settings'	=>	serialize($this->settings),
							'priority'	=>	4,
							'version'	=>	$this->version,
							'enabled'	=>	'y'
      			);

			// insert in database
			$this->EE->db->insert('exp_extensions', $data);
		}
	}

	// ********************************************************************************* //

	/**
	 * Called by ExpressionEngine when the user disables the extension.
	 *
	 * @access		public
	 * @return		void
	 **/
	public function disable_extension()
	{
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->delete('exp_extensions');
	}

	// ********************************************************************************* //

	/**
	 * Called by ExpressionEngine updates the extension
	 *
	 * @access public
	 * @return void
	 **/
	public function update_extension($current=FALSE)
	{
		if($current == $this->version) return false;

		// Delete them all!!
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->delete('exp_extensions');

		// Add them back :)
		$this->activate_extension();
	}

	// ********************************************************************************* //

} // END CLASS

/* End of file ext.channel_ratings.php */
/* Location: ./system/expressionengine/third_party/channel_ratings/ext.channel_ratings.php */