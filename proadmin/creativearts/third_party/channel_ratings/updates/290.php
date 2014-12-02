<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ChannelRatingsUpdate_290
{

	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * Calls the parent constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();

		// Load dbforge
		$this->EE->load->dbforge();
	}

	// ********************************************************************************* //

	public function do_update()
	{
		// comment_id => item_id
		if ($this->EE->db->field_exists('item_id', 'channel_ratings') == FALSE)
		{
			$fields = array('comment_id' => array('name' => 'item_id', 'type' => 'INT',	'unsigned' => TRUE, 'default' => 0));
			$this->EE->dbforge->modify_column('channel_ratings', $fields);
		}

		// comment_id => item_id
		if ($this->EE->db->field_exists('item_id', 'channel_ratings_likes') == FALSE)
		{
			$fields = array('comment_id' => array('name' => 'item_id', 'type' => 'INT',	'unsigned' => TRUE, 'default' => 0));
			$this->EE->dbforge->modify_column('channel_ratings_likes', $fields);
		}

		// Add the item_id column to channel_ratings_stats
		if ($this->EE->db->field_exists('item_id', 'channel_ratings_stats') == FALSE)
		{
			$fields = array('item_id' => array('type' => 'INT',	'unsigned' => TRUE, 'default' => 0) );
			$this->EE->dbforge->add_column('channel_ratings_stats', $fields, 'entry_id');
		}

		// new Like ACT
		$query = $this->EE->db->select('action_id')->from('exp_actions')->where('method', 'insert_like')->where('class', ucfirst($this->module_name))->get();
		if ($query->num_rows() == 0)
		{
			$module = array( 'class' => ucfirst($this->module_name), 'method' => 'insert_like' );
			$this->EE->db->insert('actions', $module);
		}

		// Add index
		$query = $this->EE->db->query("SHOW INDEX FROM exp_channel_ratings WHERE Key_name = 'item_id'");
		if ($query->num_rows() == 0) $this->EE->db->query("CREATE INDEX item_id ON exp_channel_ratings(item_id)");

		$query = $this->EE->db->query("SHOW INDEX FROM exp_channel_ratings_stats WHERE Key_name = 'item_id'");
		if ($query->num_rows() == 0) $this->EE->db->query("CREATE INDEX item_id ON exp_channel_ratings_stats(item_id)");

		$query = $this->EE->db->query("SHOW INDEX FROM exp_channel_ratings_likes WHERE Key_name = 'item_id'");
		if ($query->num_rows() == 0) $this->EE->db->query("CREATE INDEX item_id ON exp_channel_ratings_likes(item_id)");
	}

	// ********************************************************************************* //

}

/* End of file 200.php */
/* Location: ./system/expressionengine/third_party/channel_ratings/updates/290.php */