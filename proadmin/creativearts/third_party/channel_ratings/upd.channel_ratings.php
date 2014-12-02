<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// include config file
include PATH_THIRD.'channel_ratings/config'.EXT;

/**
 * Install / Uninstall and updates the modules
 *
 * @package			DevDemon_ChannelRatings
 * @version			3.2
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2010 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com
 * @see				http://expressionengine.com/user_guide/development/module_tutorial.html#update_file
 */
class Channel_ratings_upd
{
	/**
	 * Module version
	 *
	 * @var string
	 * @access public
	 */
	public $version		=	CHANNELRATINGS_VERSION;

	/**
	 * Module Short Name
	 *
	 * @var string
	 * @access private
	 */
	private $module_name	=	CHANNELRATINGS_CLASS_NAME;

	/**
	 * Has Control Panel Backend?
	 *
	 * @var string
	 * @access private
	 */
	private $has_cp_backend = 'y';

	/**
	 * Has Publish Fields?
	 *
	 * @var string
	 * @access private
	 */
	private $has_publish_fields = 'n';


	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
	}

	// ********************************************************************************* //

	/**
	 * Installs the module
	 *
	 * Installs the module, adding a record to the exp_modules table,
	 * creates and populates and necessary database tables,
	 * adds any necessary records to the exp_actions table,
	 * and if custom tabs are to be used, adds those fields to any saved publish layouts
	 *
	 * @access public
	 * @return boolean
	 **/
	public function install()
	{
		// Load dbforge
		$this->EE->load->dbforge();

		//----------------------------------------
		// EXP_MODULES
		//----------------------------------------
		$module = array(	'module_name' => ucfirst($this->module_name),
							'module_version' => $this->version,
							'has_cp_backend' => 'y',
							'has_publish_fields' => 'n' );

		$this->EE->db->insert('modules', $module);

		//----------------------------------------
		// EXP_CHANNEL_RATINGS
		//----------------------------------------
		$ci = array(
			'rating_id'		=> array('type' => 'INT',		'unsigned' => TRUE,	'auto_increment' => TRUE),
			'site_id'		=> array('type' => 'TINYINT',	'unsigned' => TRUE,	'default' => 1),
			'entry_id'		=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 0),
			'item_id'		=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 0),
			'channel_id'	=> array('type' => 'TINYINT',	'unsigned' => TRUE, 'default' => 0),
			'field_id'		=> array('type' => 'TINYINT',	'unsigned' => TRUE, 'default' => 0), // 0=Avg Rating
			'ip_address'	=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 0),
			'collection_id'	=> array('type' => 'TINYINT',	'unsigned' => TRUE, 'default' => 1),
			'rating'		=> array('type' => 'FLOAT',		'unsigned' => TRUE, 'default' => 0),
			'rating_author_id'	=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 0),
			'rating_date'	=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 0),
			'rating_type'	=> array('type' => 'TINYINT',	'unsigned' => TRUE, 'default' => 0), // 1 = Channel Entry, 2 = Comment
			'rating_status'	=> array('type' => 'TINYINT',	'unsigned' => TRUE, 'default' => 1),
		);

		$this->EE->dbforge->add_field($ci);
		$this->EE->dbforge->add_key('rating_id', TRUE);
		$this->EE->dbforge->add_key('entry_id');
		$this->EE->dbforge->add_key('item_id');
		$this->EE->dbforge->add_key('rating_author_id');
		$this->EE->dbforge->create_table('channel_ratings', TRUE);

		//----------------------------------------
		// EXP_CHANNEL_RATINGS_STATS
		//----------------------------------------
		$ci = array(
			'rstat_id'		=> array('type' => 'INT',		'unsigned' => TRUE,	'auto_increment' => TRUE),
			'site_id'		=> array('type' => 'TINYINT',	'unsigned' => TRUE,	'default' => 1),
			'entry_id'		=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 0),
			'item_id'		=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 0),
			'field_id'		=> array('type' => 'TINYINT',	'unsigned' => TRUE, 'default' => 0),
			'channel_id'	=> array('type' => 'TINYINT',	'unsigned' => TRUE, 'default' => 0),
			'collection_id'	=> array('type' => 'TINYINT',	'unsigned' => TRUE, 'default' => 1),
			'rating_avg'	=> array('type' => 'FLOAT',		'unsigned' => TRUE, 'default' => 0),
			'rating_last_date'	=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 0),
			'rating_sum'	=> array('type' => 'FLOAT',		'unsigned' => TRUE, 'default' => 0),
			'rating_total'	=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 0),
			'rating_bayesian'=>  array('type' => 'FLOAT',		'unsigned' => TRUE, 'default' => 0),
			'rating_bayesian_overall'	=> array('type' => 'FLOAT',		'unsigned' => TRUE, 'default' => 0),
			'rating_type'	=> array('type' => 'TINYINT',	'unsigned' => TRUE, 'default' => 0), // 1 = Channel Entry, 2 = Comment
		);

		$this->EE->dbforge->add_field($ci);
		$this->EE->dbforge->add_key('rstat_id', TRUE);
		$this->EE->dbforge->add_key('entry_id');
		$this->EE->dbforge->add_key('item_id');
		$this->EE->dbforge->create_table('channel_ratings_stats', TRUE);

		//----------------------------------------
		// EXP_CHANNEL_RATINGS_LIKES
		//----------------------------------------
		$ci = array(
			'rlike_id'		=> array('type' => 'INT',		'unsigned' => TRUE,	'auto_increment' => TRUE),
			'site_id'		=> array('type' => 'TINYINT',	'unsigned' => TRUE,	'default' => 1),
			'entry_id'		=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 0),
			'item_id'		=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 0),
			'channel_id'	=> array('type' => 'TINYINT',	'unsigned' => TRUE, 'default' => 0),
			'collection_id'	=> array('type' => 'TINYINT',	'unsigned' => TRUE, 'default' => 1),
			'liked'			=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 0),
			'disliked'		=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 0),
			'ip_address'	=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 0),
			'like_author_id'	=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 0),
			'like_date'		=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 0), // In "Stats Row", this is the last like date
			'like_type'		=> array('type' => 'TINYINT',	'unsigned' => TRUE, 'default' => 0), // 1 = Channel Entry, 2 = Comment
			'like_bayesian'	=> array('type' => 'FLOAT',		'unsigned' => TRUE, 'default' => 0),
			'stats_row'		=> array('type' => 'TINYINT',	'unsigned' => TRUE, 'default' => 0), // If 1, it's the stats row
		);

		$this->EE->dbforge->add_field($ci);
		$this->EE->dbforge->add_key('rlike_id', TRUE);
		$this->EE->dbforge->add_key('entry_id');
		$this->EE->dbforge->add_key('item_id');
		$this->EE->dbforge->create_table('channel_ratings_likes', TRUE);

		//----------------------------------------
		// EXP_CHANNEL_RATINGS_FIELDS
		//----------------------------------------
		$ci = array(
			'field_id'		=> array('type' => 'INT',		'unsigned' => TRUE,	'auto_increment' => TRUE),
			'site_id'		=> array('type' => 'TINYINT',	'unsigned' => TRUE,	'default' => 1),
			'title'			=> array('type' => 'VARCHAR',	'constraint' => 250, 'default' => ''),
			'short_name'	=> array('type' => 'VARCHAR',	'constraint' => 250, 'default' => ''),
			'collection_id'	=> array('type' => 'TINYINT',	'unsigned' => TRUE, 'default' => 1),
			'required'		=> array('type' => 'TINYINT',	'unsigned' => TRUE, 'default' => 1),
		);

		$this->EE->dbforge->add_field($ci);
		$this->EE->dbforge->add_key('field_id', TRUE);
		$this->EE->dbforge->create_table('channel_ratings_fields', TRUE);

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

		//----------------------------------------
		// Insert Default Data
		//----------------------------------------
		$data = array('collection_id' => 1, 'collection_label' => 'Default', 'collection_name' => 'default', 'default' => '1');
		$this->EE->db->insert('channel_ratings_collections', $data);

		$data = array('field_id' => 1, 'title' => 'Default Rating Field', 'short_name' => 'default', 'collection_id' => '1');
		$this->EE->db->insert('channel_ratings_fields', $data);

		//----------------------------------------
		// EXP_ACTIONS
		//----------------------------------------
		$module = array( 'class' => ucfirst($this->module_name), 'method' => $this->module_name . '_router' );
		$this->EE->db->insert('actions', $module);
		$module = array( 'class' => ucfirst($this->module_name), 'method' => 'insert_rating' );
		$this->EE->db->insert('actions', $module);
		$module = array( 'class' => ucfirst($this->module_name), 'method' => 'insert_like' );
		$this->EE->db->insert('actions', $module);
		$module = array( 'class' => ucfirst($this->module_name), 'method' => 'bayesian' );
		$this->EE->db->insert('actions', $module);

		//----------------------------------------
		// EXP_MODULES
		// The settings column, Ellislab should have put this one in long ago.
		// No need for a seperate preferences table for each module.
		//----------------------------------------
		if ($this->EE->db->field_exists('settings', 'modules') == FALSE)
		{
			$this->EE->dbforge->add_column('modules', array('settings' => array('type' => 'TEXT') ) );
		}

		// Do we need to enable the extension
        //if ($this->uses_extension === TRUE) $this->extension_handler('enable');

		return TRUE;
	}

	// ********************************************************************************* //

	/**
	 * Uninstalls the module
	 *
	 * @access public
	 * @return Boolean FALSE if uninstall failed, TRUE if it was successful
	 **/
	function uninstall()
	{
		// Load dbforge
		$this->EE->load->dbforge();

		// Remove
		$this->EE->dbforge->drop_table('channel_ratings');
		$this->EE->dbforge->drop_table('channel_ratings_stats');
		$this->EE->dbforge->drop_table('channel_ratings_fields');
		$this->EE->dbforge->drop_table('channel_ratings_likes');
		$this->EE->dbforge->drop_table('channel_ratings_collections');


		/*
		$this->EE->dbforge->drop_column('channel_titles', 'rating_last_date');
		$this->EE->dbforge->drop_column('channel_titles', 'rating_total');
		$this->EE->dbforge->drop_column('channel_titles', 'rating_avg');
		$this->EE->dbforge->drop_column('channel_titles', 'rating_sum');
		$this->EE->dbforge->drop_column('channel_titles', 'comment_rating_last_date');
		$this->EE->dbforge->drop_column('channel_titles', 'comment_rating_total');
		$this->EE->dbforge->drop_column('channel_titles', 'comment_rating_avg');
		$this->EE->dbforge->drop_column('channel_titles', 'comment_rating_sum');
		*/

		$this->EE->db->where('module_name', ucfirst($this->module_name));
		$this->EE->db->delete('modules');
		$this->EE->db->where('class', ucfirst($this->module_name));
		$this->EE->db->delete('actions');

		// $this->EE->cp->delete_layout_tabs($this->tabs(), 'points');

		return TRUE;
	}

	// ********************************************************************************* //

	/**
	 * Updates the module
	 *
	 * This function is checked on any visit to the module's control panel,
	 * and compares the current version number in the file to
	 * the recorded version in the database.
	 * This allows you to easily make database or
	 * other changes as new versions of the module come out.
	 *
	 * @access public
	 * @return Boolean FALSE if no update is necessary, TRUE if it is.
	 **/
	public function update($current = '')
	{
		// Are they the same?
		if ($current >= $this->version)
		{
			return FALSE;
		}

		$current = str_replace('.', '', $current);

		// Two Digits? (needs to be 3)
		if (strlen($current) == 2) $current .= '0';

		$update_dir = PATH_THIRD.strtolower($this->module_name).'/updates/';

		// Does our folder exist?
		if (@is_dir($update_dir) === TRUE)
		{
			// Loop over all files
			$files = @scandir($update_dir);

			if (is_array($files) == TRUE)
			{
				foreach ($files as $file)
				{
					if ($file == '.' OR $file == '..' OR strtolower($file) == '.ds_store') continue;

					// Get the version number
					$ver = substr($file, 0, -4);

					// We only want greater ones
					if ($current >= $ver) continue;

					require $update_dir . $file;
					$class = 'ChannelRatingsUpdate_' . $ver;
					$UPD = new $class();
					$UPD->do_update();
				}
			}
		}

		// Upgrade The Module
		$this->EE->db->set('module_version', $this->version);
		$this->EE->db->where('module_name', ucfirst($this->module_name));
		$this->EE->db->update('exp_modules');

		return TRUE;
	}

} // END CLASS

/* End of file upd.channel_ratings.php */
/* Location: ./system/expressionengine/third_party/channel_ratings/upd.channel_ratings.php */