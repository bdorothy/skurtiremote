<?php if (! defined('BASEPATH')) exit('Invalid file request');

/**
 * Color Picker Plus Module CP Class for EE2
 *
 * @package   Color Picker Plus
 * @author    Shoe Shine Design & Development
 */
class Color_picker_plus_mcp {

	/**
	 * Constructor
	 */

	function __construct()
	{
		$this->EE =& get_instance();
		if (REQ == 'CP')
		{
			$this->base = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=color_picker_plus';
			// Set the right nav
			$this->EE->cp->set_right_nav(array(
				'color_picker_plus_settings' => BASE.AMP.$this->base.AMP.'method=index'
			));
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Update QuickColor choice
	 */
	function qcupdate()
	{

		$dbTableName = $this->EE->db->dbprefix . "color_picker_plus";
		$query = $this->EE->db->query("SHOW TABLES LIKE '".$dbTableName."'");
		if ($query->num_rows() > 0) {
			// if row_id and quickcolor GET paramaters are set
			// just process this one (shift-click to save one quickcolor)
			$row_id = $this->EE->input->get('row_id');
			$quickcolor = $this->EE->input->get('quickcolor');
			if ($row_id && ($quickcolor !== FALSE)) {
				$data = array('quickcolor' => $quickcolor);
				$sql = $this->EE->db->update_string($dbTableName, $data, "row_id = '".$row_id."'");
				$this->EE->db->query($sql);		
			}
			// if quickListDefault GET parameter is set
			// process everything (reset all to defaults)
			$quickListDefault = $this->EE->input->get('quickListDefault');			

			if ($quickListDefault !== FALSE) {

				$quickListDefaultArray = explode(",", $quickListDefault);
				// for each element in quickListDefault
				foreach ($quickListDefaultArray as $key => $value){
					$leadingZero = '';
					if ($key < 10) {
						$leadingZero = "0";
					}
					$row_id = "qc" . $leadingZero . $key;
					if ($value == 'null') {
						$value = NULL;
					}
					$data = array('quickcolor' => $value);
					$sql = $this->EE->db->update_string($dbTableName, $data, "row_id = '".$row_id."'");

					$this->EE->db->query($sql);
				}
			}
			echo "1";
		} else {
			// table doesn't exist
		}
		exit;
	}

	// --------------------------------------------------------------------
	/**
	 * Set Page Title
	 */
	private function _set_page_title($line = 'color_picker_plus_module_name')
	{
		if ($line != 'color_picker_plus_module_name')
		{
			$this->EE->cp->set_breadcrumb(BASE.AMP.$this->base, $this->EE->lang->line('color_picker_plus_module_name'));
		}

		// $this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line($line));
		// $this->EE->cp->set_variable was deprecated in 2.6
        if (version_compare(APP_VER, '2.6', '>=')) {
            ee()->view->cp_page_title = $this->EE->lang->line($line);
        } else {
            $this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line($line));
        }  
	}

	// --------------------------------------------------------------------

	/**
	 * Index
	 */
	function index()
	{
		$this->EE->load->library('table');
		$this->_set_page_title();
		$vars['base'] = $this->base;
		$defaults = array(
			'qc00'      => 'ffaaaa',
			'qc01'      => 'ff5656',
			'qc02'      => 'ff0000',
			'qc03'      => 'bf0000',
			'qc04'      => '7f0000',
			'qc05'      => 'ffffff',
			'qc06'      => 'ffd4aa',
			'qc07'      => 'ffaa56',
			'qc08'      => 'ff7f00',
			'qc09'      => 'bf5f00',
			'qc10'      => '7f3f00',
			'qc11'      => 'e5e5e5',
			'qc12'      => 'ffffaa',
			'qc13'      => 'ffff56',
			'qc14'      => 'ffff00',
			'qc15'      => 'bfbf00',
			'qc16'      => '7f7f00',
			'qc17'      => 'cccccc',
			'qc18'      => 'd4ffaa',
			'qc19'      => 'aaff56',
			'qc20'      => '7fff00',
			'qc21'      => '5fbf00',
			'qc22'      => '3f7f00',
			'qc23'      => 'b2b2b2',
			'qc24'      => 'aaffaa',
			'qc25'      => '56ff56',
			'qc26'      => '00ff00',
			'qc27'      => '00bf00',
			'qc28'      => '007f00',
			'qc29'      => '999999',
			'qc30'      => 'aaffd4',
			'qc31'      => '56ffaa',
			'qc32'      => '00ff7f',
			'qc33'      => '00bf5f',
			'qc34'      => '007f3f',
			'qc35'      => '7f7f7f',
			'qc36'      => 'aaffff',
			'qc37'      => '56ffff',
			'qc38'      => '00ffff',
			'qc39'      => '00bfbf',
			'qc40'      => '007f7f',
			'qc41'      => '666666',
			'qc42'      => 'aad4ff',
			'qc43'      => '56aaff',
			'qc44'      => '007fff',
			'qc45'      => '005fbf',
			'qc46'      => '003f7f',
			'qc47'      => '4c4c4c',
			'qc48'      => 'aaaaff',
			'qc49'      => '5656ff',
			'qc50'      => '0000ff',
			'qc51'      => '0000bf',
			'qc52'      => '00007f',
			'qc53'      => '333333',
			'qc54'      => 'd4aaff',
			'qc55'      => 'aa56ff',
			'qc56'      => '7f00ff',
			'qc57'      => '5f00bf',
			'qc58'      => '3f007f',
			'qc59'      => '191919',
			'qc60'      => 'ffaaff',
			'qc61'      => 'ff56ff',
			'qc62'      => 'ff00ff',
			'qc63'      => 'bf00bf',
			'qc64'      => '7f007f',
			'qc65'      => '000000',
			'qc66'      => 'ffaad4',
			'qc67'      => 'ff56aa',
			'qc68'      => 'ff007f',
			'qc69'      => 'bf005f',
			'qc70'      => '7f003f',
			'qc71'      => ''
		);
		$query = get_instance()->db->select('settings')
			                       ->where('name', 'color_picker_plus')
			                       ->get('fieldtypes');

		$settings = unserialize(base64_decode($query->row('settings')));

		$dbTableName = $this->EE->db->dbprefix . "color_picker_plus";
		$groupsdbTableName = $dbTableName . "_member_groups";
		
		// get list of Member Groups that already have a row in exp_color_picker_plus_member_groups
		$cpmgArr = array();
		$cpmgResetArr = array();
		$cpmgSql = 'SELECT group_id, can_save_changes, can_reset_defaults FROM '.$groupsdbTableName;
		$cpmgResults = $this->EE->db->query($cpmgSql);
		if ($cpmgResults->num_rows() > 0) {
			foreach ($cpmgResults->result_array() as $cpmgRow) {
				$cpmgArr[$cpmgRow['group_id']] = $cpmgRow['can_save_changes'];
				$cpmgResetArr[$cpmgRow['group_id']] = $cpmgRow['can_reset_defaults'];
			}
		}

		// get list of Member Groups
		$groupsList = '<ul><strong>Member Group</strong><br /><br />';
				
		$groupsSettingList = '<ul><strong>' . $this->EE->lang->line('color_picker_plus_group_save') . '</strong><br /><br />';
		$groupsResetSettingList = '<ul><strong>' . $this->EE->lang->line('color_picker_plus_group_reset') . '</strong><br /><br />';
		
		$sql = 'SELECT group_id, group_title FROM '.$this->EE->db->dbprefix.'member_groups';
		$results = $this->EE->db->query($sql);
		if ( $results->num_rows() > 0 ) {
			foreach ($results->result_array() as $row) {
				$group_id = $row['group_id'];
				if (!array_key_exists($group_id, $cpmgArr)) {
					$cpmgArr["$group_id"] = 'y';
					$addSql = 'INSERT INTO '.$groupsdbTableName.' (group_id, can_save_changes, can_reset_defaults) VALUES ('.$group_id.', \'y\', \'y\') ON DUPLICATE KEY UPDATE can_save_changes=\'y\',can_reset_defaults=\'y\';'; 
					$addResults = $this->EE->db->query($addSql);
				}
				$yChecked = $cpmgArr["$group_id"] == 'y' ? 'checked' : '';
				$nChecked = $cpmgArr["$group_id"] != 'y' ? 'checked' : '';
				if (array_key_exists($group_id, $cpmgResetArr)) {
					$yResChecked = $cpmgResetArr["$group_id"] == 'y' ? 'checked' : '';
				} else {
					$yResChecked = 'checked';
				}
				if (array_key_exists($group_id, $cpmgResetArr)) {
					$nResChecked = $cpmgResetArr["$group_id"] != 'y' ? 'checked' : '';
				}else {
					$nResChecked = '';
				}
				$groupsList.= '<li class="ssdcpPrefs">' . $row['group_title'] . '</li>';
				
				$groupsSettingList.= '<li class="ssdcpPrefs"><input type="radio" name="group_id_'.$row['group_id'].'" value="y" ' . $yChecked . '/>&nbsp;Yes&nbsp;&nbsp;&nbsp;<input type="radio" name="group_id_'.$row['group_id'].'" value="n" ' . $nChecked . '/>&nbsp;No</li>';
				
				$groupsResetSettingList.= '<li class="ssdcpPrefs"><input type="radio" name="group_id_reset_'.$row['group_id'].'" value="y" ' . $yResChecked . '/>&nbsp;Yes&nbsp;&nbsp;&nbsp;<input type="radio" name="group_id_reset_'.$row['group_id'].'" value="n" ' . $nResChecked . '/>&nbsp;No</li>';
			}
		}
		$groupsList.= '</ul>';
		$groupsSettingList.= '</ul>';
		$vars['groupsList'] = $groupsList;
		$vars['groupsSettingList'] = $groupsSettingList;
		$vars['groupsResetSettingList'] = $groupsResetSettingList;		

		$vars = array_merge($vars, $defaults, $settings);

		return $this->EE->load->view('index', $vars, TRUE);
	}

	/**
	 * Save Settings
	 */
	function save_settings()
	{
		$settings = array(
			'qc00'      => $this->EE->input->post('qc00'),
			'qc01'      => $this->EE->input->post('qc01'),
			'qc02'      => $this->EE->input->post('qc02'),
			'qc03'      => $this->EE->input->post('qc03'),
			'qc04'      => $this->EE->input->post('qc04'),
			'qc05'      => $this->EE->input->post('qc05'),
			'qc06'      => $this->EE->input->post('qc06'),
			'qc07'      => $this->EE->input->post('qc07'),
			'qc08'      => $this->EE->input->post('qc08'),
			'qc09'      => $this->EE->input->post('qc09'),
			'qc10'      => $this->EE->input->post('qc10'),
			'qc11'      => $this->EE->input->post('qc11'),
			'qc12'      => $this->EE->input->post('qc12'),
			'qc13'      => $this->EE->input->post('qc13'),
			'qc14'      => $this->EE->input->post('qc14'),
			'qc15'      => $this->EE->input->post('qc15'),
			'qc16'      => $this->EE->input->post('qc16'),
			'qc17'      => $this->EE->input->post('qc17'),
			'qc18'      => $this->EE->input->post('qc18'),
			'qc19'      => $this->EE->input->post('qc19'),
			'qc20'      => $this->EE->input->post('qc20'),
			'qc21'      => $this->EE->input->post('qc21'),
			'qc22'      => $this->EE->input->post('qc22'),
			'qc23'      => $this->EE->input->post('qc23'),
			'qc24'      => $this->EE->input->post('qc24'),
			'qc25'      => $this->EE->input->post('qc25'),
			'qc26'      => $this->EE->input->post('qc26'),
			'qc27'      => $this->EE->input->post('qc27'),
			'qc28'      => $this->EE->input->post('qc28'),
			'qc29'      => $this->EE->input->post('qc29'),
			'qc30'      => $this->EE->input->post('qc30'),
			'qc31'      => $this->EE->input->post('qc31'),
			'qc32'      => $this->EE->input->post('qc32'),
			'qc33'      => $this->EE->input->post('qc33'),
			'qc34'      => $this->EE->input->post('qc34'),
			'qc35'      => $this->EE->input->post('qc35'),
			'qc36'      => $this->EE->input->post('qc36'),
			'qc37'      => $this->EE->input->post('qc37'),
			'qc38'      => $this->EE->input->post('qc38'),
			'qc39'      => $this->EE->input->post('qc39'),
			'qc40'      => $this->EE->input->post('qc40'),
			'qc41'      => $this->EE->input->post('qc41'),
			'qc42'      => $this->EE->input->post('qc42'),
			'qc43'      => $this->EE->input->post('qc43'),
			'qc44'      => $this->EE->input->post('qc44'),
			'qc45'      => $this->EE->input->post('qc45'),
			'qc46'      => $this->EE->input->post('qc46'),
			'qc47'      => $this->EE->input->post('qc47'),
			'qc48'      => $this->EE->input->post('qc48'),
			'qc49'      => $this->EE->input->post('qc49'),
			'qc50'      => $this->EE->input->post('qc50'),
			'qc51'      => $this->EE->input->post('qc51'),
			'qc52'      => $this->EE->input->post('qc52'),
			'qc53'      => $this->EE->input->post('qc53'),
			'qc54'      => $this->EE->input->post('qc54'),
			'qc55'      => $this->EE->input->post('qc55'),
			'qc56'      => $this->EE->input->post('qc56'),
			'qc57'      => $this->EE->input->post('qc57'),
			'qc58'      => $this->EE->input->post('qc58'),
			'qc59'      => $this->EE->input->post('qc59'),
			'qc60'      => $this->EE->input->post('qc60'),
			'qc61'      => $this->EE->input->post('qc61'),
			'qc62'      => $this->EE->input->post('qc62'),
			'qc63'      => $this->EE->input->post('qc63'),
			'qc64'      => $this->EE->input->post('qc64'),
			'qc65'      => $this->EE->input->post('qc65'),
			'qc66'      => $this->EE->input->post('qc66'),
			'qc67'      => $this->EE->input->post('qc67'),
			'qc68'      => $this->EE->input->post('qc68'),
			'qc69'      => $this->EE->input->post('qc69'),
			'qc70'      => $this->EE->input->post('qc70'),
			'qc71'      => $this->EE->input->post('qc71')
		);

		$data['settings'] = base64_encode(serialize($settings));

		$this->EE->db->where('name', 'color_picker_plus');
		$this->EE->db->update('fieldtypes', $data);
		
		$dbTableName = $this->EE->db->dbprefix . "color_picker_plus";
		
		foreach ($settings as $row=>$color) {
			//$this->EE->db->where('row_id', $row);
			$colSQL = "UPDATE ".$dbTableName." SET quickcolor = '".$color."' WHERE row_id = '".$row."'";
			$this->EE->db->query($colSQL);
		}	

		

		$groupsdbTableName = $dbTableName . "_member_groups";

		$sql = 'SELECT group_id FROM '.$this->EE->db->dbprefix.'member_groups';
		$results = $this->EE->db->query($sql);
		if ( $results->num_rows() > 0 ) {
			foreach ($results->result_array() as $row) {
				$group_id = $row['group_id'];
				$postName = 'group_id_'.$group_id;
				$resetPostName = 'group_id_reset_'.$group_id;
				$postValue = $this->EE->input->post($postName)=='y' ? 'y' : 'n';
				$resetPostValue = $this->EE->input->post($resetPostName)=='y' ? 'y' : 'n';
				$addSql = 'INSERT INTO '.$groupsdbTableName.' (group_id, can_save_changes, can_reset_defaults) VALUES ('.$group_id.', \''.$postValue.'\', \''.$resetPostValue.'\') ON DUPLICATE KEY UPDATE can_save_changes=\''.$postValue.'\',can_reset_defaults=\''.$resetPostValue.'\' ;'; 
				$addResults = $this->EE->db->query($addSql);
			}
		}
		// redirect to Index
		$this->EE->session->set_flashdata('message_success', lang('global_settings_saved'));
		$this->EE->functions->redirect(BASE.AMP.$this->base);
	}

}
