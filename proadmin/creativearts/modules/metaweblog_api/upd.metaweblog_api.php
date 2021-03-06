<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Creativearts - by Creativelab
 *
 * @package		Creativearts
 * @author		Creativelab Dev Team
 * @copyright	Copyright (c) 2003 - 2014, Creativelab, Inc.
 * @license		http://creativelab.com/creativearts/user-guide/license.html
 * @link		http://creativelab.com
 * @since		Version 2.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Creativearts Metaweblog API Module
 *
 * @package		Creativearts
 * @subpackage	Modules
 * @category	Update File
 * @author		Creativelab Dev Team
 * @link		http://creativelab.com
 */
class Metaweblog_api_upd {

	var $version = '2.2';

	function Metaweblog_api_upd()
	{
		// Make a local reference to the Creativearts super object
		$this->EE =& get_instance();
		ee()->load->dbforge();
	}


	// --------------------------------------------------------------------

	/**
	 * Module Installer
	 *
	 * @access	public
	 * @return	bool
	 */
	function install()
	{
		$data = array(
			'module_name' 	=> 'Metaweblog_api',
			'module_version' 	=> $this->version,
			'has_cp_backend' 	=> 'y'
		);
		ee()->db->insert('modules', $data);

		$data = array(
			'class' 	=> 'Metaweblog_api',
			'method' 	=> 'incoming',
			'csrf_exempt' => 1
		);
		ee()->db->insert('actions', $data);

		$fields = array(
						'metaweblog_id'	=> array(
													'type'				=> 'int',
													'constraint'		=> 5,
													'unsigned'			=> TRUE,
													'null'				=> FALSE,
													'auto_increment'	=> TRUE
												),
						'metaweblog_pref_name'  => array(
													'type' 				=> 'varchar',
													'constraint'		=> '80',
													'null'				=> FALSE,
													'default'			=> ''
												),
						'metaweblog_parse_type'  => array(
													'type' 				=> 'varchar',
													'constraint'		=> '1',
													'null'				=> FALSE,
													'default'			=> 'y'
												),
						'entry_status'  => array(
													'type' 				=> 'varchar',
													'constraint'		=> '50',
													'null'				=> FALSE,
													'default'			=> 'NULL'
												),
						'field_group_id'  => array(
													'type' 				=> 'int',
													'constraint'		=> '5',
													'unsigned'			=> TRUE,
													'null'				=> FALSE,
													'default'			=> 0
												),
						'excerpt_field_id'	=> array(
													'type'				=> 'int',
													'constraint'		=> 7,
													'unsigned'			=> TRUE,
													'null'				=> FALSE,
													'default'			=> 0
												),
						'content_field_id'	=> array(
													'type'				=> 'int',
													'constraint'		=> 7,
													'unsigned'			=> TRUE,
													'null'				=> FALSE,
													'default'			=> 0
												),
						'more_field_id'	=> array(
													'type'				=> 'int',
													'constraint'		=> 7,
													'unsigned'			=> TRUE,
													'null'				=> FALSE,
													'default'			=> 0
												),
						'keywords_field_id'	=> array(
													'type'				=> 'int',
													'constraint'		=> 7,
													'unsigned'			=> TRUE,
													'null'				=> FALSE,
													'default'			=> 0
												),
						'upload_dir'	=> array(
													'type'				=> 'int',
													'constraint'		=> 5,
													'unsigned'			=> TRUE,
													'null'				=> FALSE,
													'default'			=> 1
												),
		);

		ee()->dbforge->add_field($fields);
		ee()->dbforge->add_key('metaweblog_id', TRUE);
		ee()->dbforge->create_table('metaweblog_api', TRUE);

		$data = array(
			'metaweblog_pref_name' 	=> 'Default',
			'field_group_id' 	=> 1,
			'content_field_id' 	=> 2
		);
		ee()->db->insert('metaweblog_api', $data);

		return TRUE;
	}



	// --------------------------------------------------------------------

	/**
	 * Module Uninstaller
	 *
	 * @access	public
	 * @return	bool
	 */
	function uninstall()
	{
		ee()->db->select('module_id');
		$query = ee()->db->get_where('modules', array('module_name' => 'Metaweblog_api'));

		ee()->db->where('module_id', $query->row('module_id'));
		ee()->db->delete('module_member_groups');

		ee()->db->where('module_name', 'Metaweblog_api');
		ee()->db->delete('modules');

		ee()->db->where('class', 'Metaweblog_api');
		ee()->db->delete('actions');

		ee()->dbforge->drop_table('metaweblog_api');

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Module Updater
	 *
	 * @access	public
	 * @return	bool
	 */

	function update($version = '')
	{
		if (version_compare($version, '2', '<') && ee()->db->table_exists('exp_metaweblog_api'))
		{
			$existing_fields = array();

			$new_fields = array('entry_status' => "`entry_status` varchar(50) NOT NULL default 'null' AFTER `metaweblog_parse_type`");

			$query = ee()->db->query("SHOW COLUMNS FROM exp_metaweblog_api");

			foreach($query->result_array() as $row)
			{
				$existing_fields[] = $row['Field'];
			}

			foreach($new_fields as $field => $alter)
			{
				if ( ! in_array($field, $existing_fields))
				{
					ee()->db->query("ALTER table exp_metaweblog_api ADD COLUMN {$alter}");
				}
			}
		}

		if (version_compare($version, '2.2', '<'))
		{
			$data = array(
				'csrf_exempt' => 1
				);

			ee()->db->where('class', 'Metaweblog_api');
			ee()->db->where('method', 'incoming');
			ee()->db->update('actions', $data);
		}

		return TRUE;
	}
}


/* End of file upd.metaweblog_api.php */
/* Location: ./system/creativearts/modules/metaweblog_api/upd.metaweblog_api.php */