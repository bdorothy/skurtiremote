<?php if (! defined('BASEPATH')) exit('No direct script access allowed');


if (! defined('PATH_THIRD')) define('PATH_THIRD', EE_APPPATH.'third_party/');
require_once PATH_THIRD.'color_picker_plus/config.php';


/**
 * Color Picker Plus Update Class for EE2
 *
 * @package   Color Picker Plus
 * @author    Shoe Shine Design & Development
 */
class Color_picker_plus_upd {

	var $version = COLOR_PICKER_PLUS_VER;

	/**
	 * Constructor
	 */
	function __construct()
	{
		$this->EE =& get_instance();
	}

	// --------------------------------------------------------------------

	/**
	 * Install
	 */
	function install()
	{
		$this->EE->load->dbforge();
		
		$name_no_space = str_replace(' ', '_', COLOR_PICKER_PLUS_NAME);
		$this->EE->db->insert('modules', array(
			'module_name'        => $name_no_space,
			'module_version'     => COLOR_PICKER_PLUS_VER,
			'has_cp_backend'     => 'y',
			'has_publish_fields' => 'n'
		));
		
		// -------------------------------------------
		//  Create the exp_color_picker_plus table and populate it
		// -------------------------------------------

		$dbTableName = $this->EE->db->dbprefix . "color_picker_plus";
		if (! $this->EE->db->table_exists($dbTableName)) {
			$query = $this->EE->db->query("CREATE TABLE `" . $dbTableName . "` (
			`row_id` varchar(6) NOT NULL,
			`quickcolor` varchar(8) DEFAULT NULL,
			PRIMARY KEY (`row_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
			
			$query = $this->EE->db->query(
			"INSERT INTO " . $dbTableName . " (row_id, quickcolor)
			VALUES
			('qc00', 'ffaaaa'),
			('qc01', 'ff5656'),
			('qc02', 'ff0000'),
			('qc03', 'bf0000'),
			('qc04', '7f0000'),
			('qc05', 'ffffff'),
			('qc06', 'ffd4aa'),
			('qc07', 'ffaa56'),
			('qc08', 'ff7f00'),
			('qc09', 'bf5f00'),
			('qc10', '7f3f00'),
			('qc11', 'e5e5e5'),
			('qc12', 'ffffaa'),
			('qc13', 'ffff56'),
			('qc14', 'ffff00'),
			('qc15', 'bfbf00'),
			('qc16', '7f7f00'),
			('qc17', 'cccccc'),
			('qc18', 'd4ffaa'),
			('qc19', 'aaff56'),
			('qc20', '7fff00'),
			('qc21', '5fbf00'),
			('qc22', '3f7f00'),
			('qc23', 'b2b2b2'),
			('qc24', 'aaffaa'),
			('qc25', '56ff56'),
			('qc26', '00ff00'),
			('qc27', '00bf00'),
			('qc28', '007f00'),
			('qc29', '999999'),
			('qc30', 'aaffd4'),
			('qc31', '56ffaa'),
			('qc32', '00ff7f'),
			('qc33', '00bf5f'),
			('qc34', '007f3f'),
			('qc35', '7f7f7f'),
			('qc36', 'aaffff'),
			('qc37', '56ffff'),
			('qc38', '00ffff'),
			('qc39', '00bfbf'),
			('qc40', '007f7f'),
			('qc41', '666666'),
			('qc42', 'aad4ff'),
			('qc43', '56aaff'),
			('qc44', '007fff'),
			('qc45', '005fbf'),
			('qc46', '003f7f'),
			('qc47', '4c4c4c'),
			('qc48', 'aaaaff'),
			('qc49', '5656ff'),
			('qc50', '0000ff'),
			('qc51', '0000bf'),
			('qc52', '00007f'),
			('qc53', '333333'),
			('qc54', 'd4aaff'),
			('qc55', 'aa56ff'),
			('qc56', '7f00ff'),
			('qc57', '5f00bf'),
			('qc58', '3f007f'),
			('qc59', '191919'),
			('qc60', 'ffaaff'),
			('qc61', 'ff56ff'),
			('qc62', 'ff00ff'),
			('qc63', 'bf00bf'),
			('qc64', '7f007f'),
			('qc65', '000000'),
			('qc66', 'ffaad4'),
			('qc67', 'ff56aa'),
			('qc68', 'ff007f'),
			('qc69', 'bf005f'),
			('qc70', '7f003f'),
			('qc71', '');"
			);
		} else {
			// table already exists, what needs to be done?
			// if future versions use a different database table structure
			//   may need an update function to add tables/columns or adjust
			//   the table structure, but if the install function is running
			//   the table/s shouldn't exist
		}
		
		// -------------------------------------------
		//  Create the exp_color_picker_plus_member_groups table
		// -------------------------------------------

		$groupsdbTableName = $dbTableName . "_member_groups";
		if (! $this->EE->db->table_exists($groupsdbTableName)) {
			$query = $this->EE->db->query("CREATE TABLE `" . $groupsdbTableName . "` (
			`group_id` smallint(4) unsigned NOT NULL,
			`can_save_changes` char(1) DEFAULT 'y',
			`can_reset_defaults` char(1) DEFAULT 'y',
			PRIMARY KEY (`group_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
		} else {
			$result = mysql_query("SHOW COLUMNS FROM `".$groupsdbTableName."` LIKE 'can_save_changes'");
            $exists = (mysql_num_rows($result))?TRUE:FALSE;
			if (!$exists){
                $query = $this->EE->db->query("ALTER TABLE `".$groupsdbTableName."` ADD can_reset_defaults char(1) DEFAULT 'y'");
			}
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Update
	 */
	function update($current = '')
	{
        if ($current=="1.1"){
            $query = $this->EE->db->query("ALTER TABLE `".$groupsdbTableName."` ADD can_reset_defaults char(1) DEFAULT 'y'");
        }
		
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Uninstall
	 */
	function uninstall()
	{
		// remove row from exp_modules
		$name_no_space = str_replace(' ', '_', COLOR_PICKER_PLUS_NAME);
		$this->EE->db->delete('modules', array('module_name' => $name_no_space));

		// drop the exp_color_picker_plus and exp_color_picker_plus_member_groups tables
		$dbTableName = $this->EE->db->dbprefix . "color_picker_plus";
		$groupsdbTableName = $dbTableName . "_member_groups";
		$query = $this->EE->db->query("DROP TABLE IF EXISTS `".$dbTableName."`");
		$query = $this->EE->db->query("DROP TABLE IF EXISTS `".$groupsdbTableName."`");

		return TRUE;
	}

}
