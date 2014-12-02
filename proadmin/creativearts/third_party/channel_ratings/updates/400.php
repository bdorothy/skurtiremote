<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ChannelRatingsUpdate_400
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
		$this->EE->dbforge->drop_table('channel_ratings_formparams');

		//----------------------------------------
		// EXP_CHANNEL_RATINGS_COLLECTIONS
		//----------------------------------------
		$ci = array(
			'collection_id'	=> array('type' => 'INT',		'unsigned' => TRUE,	'auto_increment' => TRUE),
			'site_id'		=> array('type' => 'TINYINT',	'unsigned' => TRUE,	'default' => 1),
			'collection_label'	=> array('type' => 'VARCHAR',	'constraint' => 250, 'default' => ''),
			'collection_name'	=> array('type' => 'VARCHAR',	'constraint' => 250, 'default' => ''),
			'default'		=> array('type' => 'TINYINT',	'unsigned' => TRUE, 'default' => 0),
		);

		$this->EE->dbforge->add_field($ci);
		$this->EE->dbforge->add_key('collection_id', TRUE);
		$this->EE->dbforge->create_table('channel_ratings_collections', TRUE);

		$data = array('collection_id' => 1, 'collection_label' => 'Default', 'collection_name' => 'default', 'default' => '1');
		$this->EE->db->insert('channel_ratings_collections', $data);

		// Add the item_id column to channel_ratings_stats
		if ($this->EE->db->field_exists('rating_bayesian_overall', 'channel_ratings_stats') == FALSE)
		{
			$fields = array('rating_bayesian_overall' => array('type' => 'FLOAT', 'unsigned' => TRUE, 'default' => 0) );
			$this->EE->dbforge->add_column('channel_ratings_stats', $fields, 'rating_bayesian');
		}

		//----------------------------------------
		// Grab all known collections
		//----------------------------------------
		$collections = array();

		$query = $this->EE->db->query("SELECT DISTINCT(collection) FROM exp_channel_ratings");
		foreach ($query->result() as $row) $collections[] = $row->collection;

		$query = $this->EE->db->query("SELECT DISTINCT(collection) FROM exp_channel_ratings_fields");
		foreach ($query->result() as $row) $collections[] = $row->collection;

		$collections = array_unique($collections);

		//----------------------------------------
		// Create an entry for them!
		//----------------------------------------
		foreach ($collections as $coll)
		{
			// Does it exist?
			$query = $this->EE->db->select('collection_id')->from('exp_channel_ratings_collections')->where('collection_name', $coll)->get();

			if ($query->num_rows() == 0)
			{
				// Lets create it!
				$data = array('collection_label' => ucfirst($coll), 'collection_name' => $coll);
				$this->EE->db->insert('channel_ratings_collections', $data);
				$collection_id = $this->EE->db->insert_id();
			}
			else $collection_id = $query->row('collection_id');

			//----------------------------------------
			// Search and replace!
			//----------------------------------------
			$this->EE->db->set('collection', $collection_id);
			$this->EE->db->where('collection', $coll);
			$this->EE->db->update('exp_channel_ratings');

			$this->EE->db->set('collection', $collection_id);
			$this->EE->db->where('collection', $coll);
			$this->EE->db->update('exp_channel_ratings_fields');

			$this->EE->db->set('collection', $collection_id);
			$this->EE->db->where('collection', $coll);
			$this->EE->db->update('exp_channel_ratings_likes');

			$this->EE->db->set('collection', $collection_id);
			$this->EE->db->where('collection', $coll);
			$this->EE->db->update('exp_channel_ratings_stats');
		}

		// collection => collection_id
		if ($this->EE->db->field_exists('collection_id', 'channel_ratings') == FALSE)
		{
			$fields = array('collection' => array('name' => 'collection_id', 'type' => 'TINYINT',	'unsigned' => TRUE, 'default' => 1));
			$this->EE->dbforge->modify_column('channel_ratings', $fields);
		}

		// collection => collection_id
		if ($this->EE->db->field_exists('collection_id', 'channel_ratings_fields') == FALSE)
		{
			$fields = array('collection' => array('name' => 'collection_id', 'type' => 'TINYINT',	'unsigned' => TRUE, 'default' => 1));
			$this->EE->dbforge->modify_column('channel_ratings_fields', $fields);
		}

		// collection => collection_id
		if ($this->EE->db->field_exists('collection_id', 'channel_ratings_likes') == FALSE)
		{
			$fields = array('collection' => array('name' => 'collection_id', 'type' => 'TINYINT',	'unsigned' => TRUE, 'default' => 1));
			$this->EE->dbforge->modify_column('channel_ratings_likes', $fields);
		}

		// collection => collection_id
		if ($this->EE->db->field_exists('collection_id', 'channel_ratings_stats') == FALSE)
		{
			$fields = array('collection' => array('name' => 'collection_id', 'type' => 'TINYINT',	'unsigned' => TRUE, 'default' => 1));
			$this->EE->dbforge->modify_column('channel_ratings_stats', $fields);
		}

		$module = array( 'class' => ucfirst(CHANNELRATINGS_CLASS_NAME), 'method' => 'bayesian' );
		$this->EE->db->insert('actions', $module);

	}

	// ********************************************************************************* //

}

/* End of file 400.php */
/* Location: ./system/expressionengine/third_party/channel_ratings/updates/400.php */