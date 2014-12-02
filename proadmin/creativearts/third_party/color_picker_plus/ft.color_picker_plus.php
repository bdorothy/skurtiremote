<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
ft.color_picker_plus.php
A color picker ExpressionEngine Field Type
*/

class Color_picker_plus_ft extends EE_Fieldtype {


	

	public $info = array(
	    'name'    => 'Color Picker Plus',
	    'version' => '1.3'
	);

	

	 /**
     * Include the CSS styles, but only once
     */
    private function _include_css_js()
    {
	
	$theme_url	= defined( 'URL_THIRD_THEMES' )
					? URL_THIRD_THEMES . 'color_picker_plus/'
					: $this->EE->config->item('theme_folder_url').'third_party/color_picker_plus/';
					
					
        if (!ee()->session->cache(__CLASS__, 'color_picker_assets_loaded'))
		{
			ee()->cp->add_to_foot('<link rel="stylesheet" media="screen" href="' . $theme_url . 'css/jPicker-1.1.6.min.css" />');
			ee()->cp->add_to_foot('<link rel="stylesheet" media="screen" href="' . $theme_url . 'css/jPicker.css" />');
			//$this->EE->cp->add_to_head('<script type="text/javascript" src="'.$this->EE->config->item('theme_folder_url').'javascript/compressed/jquery/jquery.js"></script>');
			ee()->cp->add_to_foot('<script type="text/javascript" src="' . $theme_url . 'jpicker-1.1.6.min.js"></script>');
			
			ee()->session->set_cache(__CLASS__, 'color_picker_assets_loaded', TRUE);
		}
    }

	/**
	 * Display Field
	 * 
	 * @return string  The field's HTML
	 */
	function display_field($data)
	{
		$this->EE =& get_instance();
		$this->EE->load->helper('form');

		$theme_url	= defined( 'URL_THIRD_THEMES' )
					? URL_THIRD_THEMES . 'color_picker_plus/'
					: $this->EE->config->item('theme_folder_url').'third_party/color_picker_plus/';
					
					
		
    	$this->_include_css_js();

		$sql = 'SELECT field_label FROM '.$this->EE->db->dbprefix.'channel_fields WHERE field_id="'.$this->field_id.'"';
		$results = $this->EE->db->query($sql);
		if ( $results->num_rows() == 0 ) {
			$field_label = $this->field_id;
		} else {
			$result_array = $results->result_array();
			$field_label = $result_array[0]['field_label'];
		}

		/* Get colors stored in color_picker_plus table.
		*  These are overwritten by the popup - not the admin.
		*  Initially, this table is filled with a copy of the default colors.
		*  These are then overwritten by the popup when a preset is saved.
		*/
		$dbTableName = $this->EE->db->dbprefix . "color_picker_plus";
		$query = $this->EE->db->query("SHOW TABLES LIKE '".$dbTableName."'");
		if ($query->num_rows() > 0) {
			$query = $this->EE->db->query("SELECT row_id, quickcolor FROM " . $dbTableName . " ORDER BY row_id;");
			$quicklist_str = '';
			foreach ($query->result_array() as $row) {
				if (!($row['quickcolor']=='')) {
					$quicklist_str        .= 'new $.jPicker.Color({ ahex: "'.$row['quickcolor']      .'" }),';
				} else {
					$quicklist_str        .= 'new $.jPicker.Color(),';
				}
			}		

			// drop trailing comma
			$quicklist_str = substr($quicklist_str, 0, -1);
		} else {
			// settings db table doesn't exist or can't be accessed, fill in with hard-coded jPicker presets
			$quicklist_str = '';
		}

		//get the admin set colors - these are *not* overwritten by the popup and can only be set by the admin
		$quicklistdefault_str = '';
		$query = get_instance()->db->select('settings')
			                       ->where('name', 'color_picker_plus')
			                       ->get('fieldtypes');
		$settings = unserialize(base64_decode($query->row('settings')));
		for ($i=0; $i<=71; $i++) {
			$istr = substr('00'.$i, -2);
			$ikey = 'qc'.$istr;
			if (!($settings[$ikey]=='')) {
				$quicklistdefault_str .= 'new $.jPicker.Color({ ahex: "'.$settings[$ikey] .'" }),';
			} else {
				$quicklistdefault_str .= 'new $.jPicker.Color(),';
			}
			
		}

		// drop trailing comma
		$quicklistdefault_str = substr($quicklistdefault_str, 0, -1);

		$usersMemberGroupId = $this->EE->session->userdata('group_id');
		$groupsdbTableName = $dbTableName . "_member_groups";
	    $sql = 'SELECT can_save_changes, can_reset_defaults FROM '.$groupsdbTableName.' WHERE group_id = '.$usersMemberGroupId.';';
		$results = $this->EE->db->query($sql);
		if ( $results->num_rows() > 0 ) {
			$resultArray = $results->result_array();
			$can_save_changes = $resultArray[0]['can_save_changes'];
			$can_reset_defaults = $resultArray[0]['can_reset_defaults'];
		} else {
			$can_save_changes = 'n';
			$can_reset_defaults = 'n';
		}
		
		

		/* plain pop-up color picker
		*/
		$imagesPath = $theme_url . 'images/';
		$this->EE->javascript->output('
			$("input[name='.$this->field_name.']").jPicker(	
				{
					window:
					{
						title:"'.$field_label.'"
					},
					color:
					{
						quickList: [ '.$quicklist_str.' ],
						quickListDefault: [ '.$quicklistdefault_str.' ]
					},
					images:
					{
						clientPath:"'.$imagesPath.'"
					},
					updateurl:"'.str_replace('&amp;', '&', BASE).'",
					cansavechanges:"'.$can_save_changes.'",
					canreset:"'.$can_reset_defaults.'"
				}
			);
		');
		

		$form = form_input (array(
			'name'      => $this->field_name,
			'value'     => $data,
			'size'      => '8',
			'maxlength' => '8',
			'class'     => 'color_picker_plus',
			'style'     => 'width:60px; position: relative; height: 23px; margin-right: 4px; padding-top: 5px; top: 4px;'
		));

		return $form;

	}

	// --------------------------------------------------------------------

	/**
	 * Display Global Settings
	 */
	function display_global_settings()
	{
		if ($this->EE->addons_model->module_installed('color_picker_plus')) {
			$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=color_picker_plus');
		} else {
			$this->EE->lang->loadfile('color_picker_plus');
			$this->EE->session->set_flashdata('message_failure', lang('color_picker_plus_no_module'));
			$this->EE->functions->redirect(BASE.AMP.'C=addons_modules');
		}
	}

	// --------------------------------------------------------------------
	
	/**
	 * Install Fieldtype
	 *
	 * @access	public
	 * @return	default global settings
	 *
	 */
	function install()
	{		
		// -------------------------------------------
		// Create the default preferences
		// -------------------------------------------
		$settings = array(
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
		return $settings;
	}

	// --------------------------------------------------------------------

	/**
	 * Uninstall Fieldtype
	 *
	 * @access	public
	 * 
	 */
	function uninstall()
	{
		parent::uninstall();
	}

	// --------------------------------------------------------------------

	/**
	 * Display Cell
	 * 
	 * @return string  The field's HTML
	 *
	 */
	function display_cell($data)
	{
		$theme_url	= defined( 'URL_THIRD_THEMES' )
					? URL_THIRD_THEMES . 'color_picker_plus/'
					: $this->EE->config->item('theme_folder_url') . 'third_party/color_picker_plus/';

    	$this->_include_css_js();

		$cell_name = $this->cell_name;

		$fieldId = str_replace( '[', '', $cell_name);
		$fieldId = str_replace( ']', '', $fieldId);

		$r['class'] = 'color_picker_plus';
		$r['data'] = '<input type="text" class="jPicker jPickerCell" id="'.$fieldId.'" name="'.$cell_name.'" value="'.$data.'" />';
		
		$field_label = 'Pick a Color';
		
		$dbTableName = $this->EE->db->dbprefix . "color_picker_plus";
		$query = $this->EE->db->query("SHOW TABLES LIKE '".$dbTableName."'");
		if ($query->num_rows() > 0) {
			$query = $this->EE->db->query("SELECT row_id, quickcolor FROM " . $dbTableName . " ORDER BY row_id;");
			$quicklist_str = '';
			foreach ($query->result_array() as $row) {
				if (!($row['quickcolor']=='')) {
					$quicklist_str        .= 'new $.jPicker.Color({ ahex: "'.$row['quickcolor']      .'" }),';
				} else {
					$quicklist_str        .= 'new $.jPicker.Color(),';
				}
			}		
			// drop trailing comma
			$quicklist_str = substr($quicklist_str, 0, -1);
		} else {
			// settings db table doesn't exist or can't be accessed, fill in with hard-coded jPicker presets
			$quicklist_str = '';
		}

		$quicklistdefault_str = '';
		$query = get_instance()->db->select('settings')
			                       ->where('name', 'color_picker_plus')
			                       ->get('fieldtypes');
		$settings = unserialize(base64_decode($query->row('settings')));
		for ($i=0; $i<=71; $i++) {
			$istr = substr('00'.$i, -2);
			$ikey = 'qc'.$istr;
			if (!($settings[$ikey]=='')) {
				$quicklistdefault_str .= 'new $.jPicker.Color({ ahex: "'.$settings[$ikey] .'" }),';
			} else {
				$quicklistdefault_str .= 'new $.jPicker.Color(),';
			}
		}
		// drop trailing comma
		$quicklistdefault_str = substr($quicklistdefault_str, 0, -1);

		$usersMemberGroupId = $this->EE->session->userdata('group_id');
		$groupsdbTableName = $dbTableName . "_member_groups";
		$sql = 'SELECT can_save_changes, can_reset_defaults FROM '.$groupsdbTableName.' WHERE group_id = '.$usersMemberGroupId.';';
		$results = $this->EE->db->query($sql);
		if ( $results->num_rows() > 0 ) {
			$resultArray = $results->result_array();
			$can_save_changes = $resultArray[0]['can_save_changes'];
			$can_reset_defaults = $resultArray[0]['can_reset_defaults'];
		} else {
			$can_save_changes = 'n';
			$can_reset_defaults = 'n';
		}

		/* plain pop-up color picker 
		*/
		$imagesPath = $theme_url . 'images/';
		$this->EE->javascript->output('
			Matrix.bind("color_picker_plus", "display", function(cell){
				$(\'.jPickerCell\').each(function() {
					if (!$(this).next().hasClass(\'jPicker\')) {
						$(this).jPicker(
							{
								window:
								{
									title: "'.$field_label.'"
								},
								color:
								{
									quickList: [ '.$quicklist_str.' ],
									quickListDefault: [ '.$quicklistdefault_str.' ]
								},
								images:
								{
									clientPath:"'.$imagesPath.'"
								},
								updateurl:"'.str_replace('&amp;', '&', BASE).'",
								cansavechanges:"'.$can_save_changes.'",
								canreset:"'.$can_reset_defaults.'"
							}
						);
					}
				});
				$(\'.jPickerCell\').css(\'width\',\'60px\').css(\'position\',\'relative\').css(\'height\',\'23px\').css(\'margin-right\',\'4px\').css(\'top\',\'5px\').css(\'border-color\',\'#C8CFD8\');
				
			});
		');
		
		return $r;
	}

	// --------------------------------------------------------------------
	
    /**
     * Display for Low Variables
     */
    function display_var_field($data)
    {


		$this->EE =& get_instance();
		$this->EE->load->helper('form');

		$theme_url	= defined( 'URL_THIRD_THEMES' )
					? URL_THIRD_THEMES . 'color_picker_plus/'
					: $this->EE->config->item('theme_folder_url') . 'third_party/color_picker_plus/';

    	$this->_include_css_js();

		
		$cell_name = $this->field_id;

		
		$field_label = 'Select a color';
		
		
		$dbTableName = $this->EE->db->dbprefix . "color_picker_plus";
		$query = $this->EE->db->query("SHOW TABLES LIKE '".$dbTableName."'");
		if ($query->num_rows() > 0) {
			$query = $this->EE->db->query("SELECT row_id, quickcolor FROM " . $dbTableName . " ORDER BY row_id;");
			$quicklist_str = '';
			foreach ($query->result_array() as $row) {
				if (!($row['quickcolor']=='')) {
					$quicklist_str        .= 'new $.jPicker.Color({ ahex: "'.$row['quickcolor']      .'" }),';
				} else {
					$quicklist_str        .= 'new $.jPicker.Color(),';
				}
			}		
			// drop trailing comma
			$quicklist_str = substr($quicklist_str, 0, -1);
		} else {
			// settings db table doesn't exist or can't be accessed, fill in with hard-coded jPicker presets
			$quicklist_str = '';
		}
		
		$quicklistdefault_str = '';
		$query = get_instance()->db->select('settings')
			                       ->where('name', 'color_picker_plus')
			                       ->get('fieldtypes');
		$settings = unserialize(base64_decode($query->row('settings')));
		for ($i=0; $i<=71; $i++) {
			$istr = substr('00'.$i, -2);
			$ikey = 'qc'.$istr;
			if (!($settings[$ikey]=='')) {
				$quicklistdefault_str .= 'new $.jPicker.Color({ ahex: "'.$settings[$ikey] .'" }),';
			} else {
				$quicklistdefault_str .= 'new $.jPicker.Color(),';
			}
		}
		// drop trailing comma
		$quicklistdefault_str = substr($quicklistdefault_str, 0, -1);

		$usersMemberGroupId = $this->EE->session->userdata('group_id');
		$groupsdbTableName = $dbTableName . "_member_groups";
		$sql = 'SELECT can_save_changes, can_reset_defaults FROM '.$groupsdbTableName.' WHERE group_id = '.$usersMemberGroupId.';';
		$results = $this->EE->db->query($sql);
		if ( $results->num_rows() > 0 ) {
			$resultArray = $results->result_array();
			$can_save_changes = $resultArray[0]['can_save_changes'];
			$can_reset_defaults = $resultArray[0]['can_reset_defaults'];
		} else {
			$can_save_changes = 'n';
			$can_reset_defaults = 'n';
		}
		
		/* plain pop-up color picker
		*/
		$imagesPath = $theme_url . 'images/';
		$this->EE->javascript->output('
			$("input[name=\'var['.$this->var_id.']\']").jPicker(
				{
					window:
					{
						title:"'.$field_label.'"
					},
					color:
					{
						quickList: [ '.$quicklist_str.' ],
						quickListDefault: [ '.$quicklistdefault_str.' ]
					},
					images:
					{
						clientPath:"'.$imagesPath.'"
					},
					updateurl:"'.str_replace('&amp;', '&', BASE).'",
					cansavechanges:"'.$can_save_changes.'",
					canreset:"'.$can_reset_defaults.'"
				}
			);
		');

		$form = form_input (array(
			'name'      => 'var['.$this->var_id.']',
			'value'     => $data,
			'size'      => '8',
			'maxlength' => '8',
			'class'     => 'color_picker_plus',
			'style'     => 'width:60px; position: relative; height: 23px; margin-right: 4px; padding-top: 5px; top: 5px;'
		));

		return $form;

    }
 
 	// --------------------------------------------------------------------
    
 	/**
	 * Save Variable Field - for Low Variables
	 */
	function save_var_field($data)
	{
        return $this->save($data);
	}
	
	// --------------------------------------------------------------------
	
    /**
     * Save Field - for Low Variables
     */
    function save($data)
    {
    	$data = str_replace("#", "", $data);
     	//return "#".$data;
	 	return $data;
    }
    
    // --------------------------------------------------------------------
	
    /**
     * Accept content type - for Grid compatibility
     */
    
    public function accepts_content_type($name)
	{
	    return ($name == 'grid');
	}
	
	
	
	function grid_display_field($data)
	{
	
		$this->EE =& get_instance();
		$this->EE->load->helper('form');
		
		$this->_include_css_js();

		$cell_name = $this->field_name;

		//$fieldId = str_replace( '[', '', $cell_name);
		//$fieldId = str_replace( ']', '', $fieldId);
		
		$field_label = 'Pick a Colors';
		
		$dbTableName = $this->EE->db->dbprefix . "color_picker_plus";
		$query = $this->EE->db->query("SHOW TABLES LIKE '".$dbTableName."'");
		if ($query->num_rows() > 0) {
			$query = $this->EE->db->query("SELECT row_id, quickcolor FROM " . $dbTableName . " ORDER BY row_id;");
			$quicklist_str = '';
			foreach ($query->result_array() as $row) {
				if (!($row['quickcolor']=='')) {
					$quicklist_str        .= 'new $.jPicker.Color({ ahex: "'.$row['quickcolor']      .'" }),';
				} else {
					$quicklist_str        .= 'new $.jPicker.Color(),';
				}
			}		
			// drop trailing comma
			$quicklist_str = substr($quicklist_str, 0, -1);
		} else {
			// settings db table doesn't exist or can't be accessed, fill in with hard-coded jPicker presets
			$quicklist_str = '';
		}

		$quicklistdefault_str = '';
		$query = get_instance()->db->select('settings')
			                       ->where('name', 'color_picker_plus')
			                       ->get('fieldtypes');
		$settings = unserialize(base64_decode($query->row('settings')));
		for ($i=0; $i<=71; $i++) {
			$istr = substr('00'.$i, -2);
			$ikey = 'qc'.$istr;
			if (!($settings[$ikey]=='')) {
				$quicklistdefault_str .= 'new $.jPicker.Color({ ahex: "'.$settings[$ikey] .'" }),';
			} else {
				$quicklistdefault_str .= 'new $.jPicker.Color(),';
			}
		}
		// drop trailing comma
		$quicklistdefault_str = substr($quicklistdefault_str, 0, -1);

		$usersMemberGroupId = $this->EE->session->userdata('group_id');
		$groupsdbTableName = $dbTableName . "_member_groups";
		$sql = 'SELECT can_save_changes, can_reset_defaults FROM '.$groupsdbTableName.' WHERE group_id = '.$usersMemberGroupId.';';
		$results = $this->EE->db->query($sql);
		if ( $results->num_rows() > 0 ) {
			$resultArray = $results->result_array();
			$can_save_changes = $resultArray[0]['can_save_changes'];
			$can_reset_defaults = $resultArray[0]['can_reset_defaults'];
		} else {
			$can_save_changes = 'n';
			$can_reset_defaults = 'n';
		}

		/* plain pop-up color picker 
		*/
$theme_url	= defined( 'URL_THIRD_THEMES' )
					? URL_THIRD_THEMES . 'color_picker_plus/'
					: $this->EE->config->item('theme_folder_url').'third_party/color_picker_plus/';


		$imagesPath = $theme_url . 'images/';
		$this->EE->javascript->output('
			Grid.bind("color_picker_plus", "display", function(cell){
				$(\'.jPickerGridCell\').each(function() {
					var td = $(this).closest("td");
					if (isNaN(td.data("row-id"))){
						var curCol = td.data("column-id");
						$(this).addClass("no-row");
						$(this).addClass("col-is-"+curCol);
					}
					if(!isNaN(td.data("row-id"))){
						if (!$(this).next().hasClass(\'jPicker\')) {
							$(this).jPicker(
								{
									window:
									{
										title: "'.$field_label.'"
									},
									color:
									{
										quickList: [ '.$quicklist_str.' ],
										quickListDefault: [ '.$quicklistdefault_str.' ]
									},
									images:
									{
										clientPath:"'.$imagesPath.'"
									},
									updateurl:"'.str_replace('&amp;', '&', BASE).'",
									cansavechanges:"'.$can_save_changes.'",
									canreset:"'.$can_reset_defaults.'"
								}
							);
						}
					}
				});
				$(\'.no-row\').each(function() {
					if($(this).is(":visible")&&$(this).siblings("span.jPicker").length<1){
						$(this).jPicker(						
							{
								window:
								{
									title: "'.$field_label.'"
								},
								color:
								{
									quickList: [ '.$quicklist_str.' ],
									quickListDefault: [ '.$quicklistdefault_str.' ]
								},
								images:
								{
									clientPath:"'.$imagesPath.'"
								},
								updateurl:"'.str_replace('&amp;', '&', BASE).'",
								cansavechanges:"'.$can_save_changes.'",
								canreset:"'.$can_reset_defaults.'"
							}
						);
					}
				});				
			});
		');
				
				//$r['class'] = 'color_picker_plus';
		//$r['data'] = '<input type="text" class="jPicker jPickerCell" id="'.$fieldId.'" name="'.$cell_name.'" value="'.$data.'" />';
		$form = form_input (array(
			'name'      => $this->field_name,
			'value'     => $data,
			'size'      => '8',
			'maxlength' => '8',
			'class'     => 'jPicker jPickerGridCell',
			'style'     => 'width:60px; position: relative; height: 23px; margin-right: 4px; padding-top: 5px; top: 4px;'
		));
		
		
		return $form;
	}
	
	
	
		// --------------------------------------------------------------------

	/**
	 * Display Cell for Content Elementx
	 * 
	 * @return string  The field's HTML
	 *
	 */
	function display_element($data)
	{
		$this->EE =& get_instance();
		$this->EE->load->helper('form');
		
    	$this->_include_css_js();

		$sql = 'SELECT field_label FROM '.$this->EE->db->dbprefix.'channel_fields WHERE field_id="'.$this->field_id.'"';
		$results = $this->EE->db->query($sql);
		if ( $results->num_rows() == 0 ) {
			$field_label = $this->field_id;
		} else {
			$result_array = $results->result_array();
			$field_label = $result_array[0]['field_label'];
		}

		/* Get colors stored in color_picker_plus table.
		*  These are overwritten by the popup - not the admin.
		*  Initially, this table is filled with a copy of the default colors.
		*  These are then overwritten by the popup when a preset is saved.
		*/
		$dbTableName = $this->EE->db->dbprefix . "color_picker_plus";
		$query = $this->EE->db->query("SHOW TABLES LIKE '".$dbTableName."'");
		if ($query->num_rows() > 0) {
			$query = $this->EE->db->query("SELECT row_id, quickcolor FROM " . $dbTableName . " ORDER BY row_id;");
			$quicklist_str = '';
			foreach ($query->result_array() as $row) {
				if (!($row['quickcolor']=='')) {
					$quicklist_str        .= 'new $.jPicker.Color({ ahex: "'.$row['quickcolor']      .'" }),';
				} else {
					$quicklist_str        .= 'new $.jPicker.Color(),';
				}
			}		

			// drop trailing comma
			$quicklist_str = substr($quicklist_str, 0, -1);
		} else {
			// settings db table doesn't exist or can't be accessed, fill in with hard-coded jPicker presets
			$quicklist_str = '';
		}

		//get the admin set colors - these are *not* overwritten by the popup and can only be set by the admin
		$quicklistdefault_str = '';
		$query = get_instance()->db->select('settings')
			                       ->where('name', 'color_picker_plus')
			                       ->get('fieldtypes');
		$settings = unserialize(base64_decode($query->row('settings')));
		for ($i=0; $i<=71; $i++) {
			$istr = substr('00'.$i, -2);
			$ikey = 'qc'.$istr;
			if (!($settings[$ikey]=='')) {
				$quicklistdefault_str .= 'new $.jPicker.Color({ ahex: "'.$settings[$ikey] .'" }),';
			} else {
				$quicklistdefault_str .= 'new $.jPicker.Color(),';
			}
			
		}

		// drop trailing comma
		$quicklistdefault_str = substr($quicklistdefault_str, 0, -1);

		$usersMemberGroupId = $this->EE->session->userdata('group_id');
		$groupsdbTableName = $dbTableName . "_member_groups";
	    $sql = 'SELECT can_save_changes, can_reset_defaults FROM '.$groupsdbTableName.' WHERE group_id = '.$usersMemberGroupId.';';
		$results = $this->EE->db->query($sql);
		if ( $results->num_rows() > 0 ) {
			$resultArray = $results->result_array();
			$can_save_changes = $resultArray[0]['can_save_changes'];
			$can_reset_defaults = $resultArray[0]['can_reset_defaults'];
		} else {
			$can_save_changes = 'n';
			$can_reset_defaults = 'n';
		}



		$form = form_input (array(
			'name'      => $this->field_name,
			'value'     => $data,
			'size'      => '8',
			'maxlength' => '8',
			'class'     => 'color_picker_plus jPickerElement',
			'style'     => 'width:60px; position: relative; height: 23px; margin-right: 4px; padding-top: 5px; top: 4px;'
		));
		
		$imagesPath = $theme_url . 'images/';
		
		//Content Elements requires a different approach - need to do a check on page load to add pickers to the inputs
		
		$this->EE->javascript->output('
		$(\'.jPickerElement\').each(function() {
			var closediv = $(this).closest("div.content_elements_tile_item");
			if (closediv.data("el-id")!=undefined){
				$(this).addClass("el-id");
			}
			if (!$(this).next().hasClass(\'jPicker\')&&$(this).is(":visible")&&closediv.data("el-id")==undefined) {
				$(this).jPicker(
					{
						window:
						{
							title: "'.$field_label.'"
						},
						color:
						{
							quickList: [ '.$quicklist_str.' ],
							quickListDefault: [ '.$quicklistdefault_str.' ]
						},
						images:
						{
							clientPath:"'.$imagesPath.'"
						},
						updateurl:"'.str_replace('&amp;', '&', BASE).'",
						cansavechanges:"'.$can_save_changes.'",
						canreset:"'.$can_reset_defaults.'"
					}
				);
				$(this).addClass("pick-done");
			}
		});
		');
		
		/* plain pop-up color picker - after checking preloaded pickers/inputs, now need to add binding in to add pickers to newly created inputs
		*/

		$this->EE->javascript->output('
		ContentElements.bind("color_picker_plus", "display", function(data){
			$(\'.jPickerElement\').each(function() {
				var closediv = $(this).closest("div.content_elements_tile_item");
				if(closediv.data("el-id")!=undefined&&!$(this).hasClass("bind-pick-done")){
					$(this).jPicker(
						{
							window:
							{
								title: "'.$field_label.'"
							},
							color:
							{
								quickList: [ '.$quicklist_str.' ],
								quickListDefault: [ '.$quicklistdefault_str.' ]
							},
							images:
							{
								clientPath:"'.$imagesPath.'"
							},
							updateurl:"'.str_replace('&amp;', '&', BASE).'",
							cansavechanges:"'.$can_save_changes.'",
							canreset:"'.$can_reset_defaults.'"
						}
					);
					$(this).addClass("bind-pick-done");
				}
			});
			});
		');
		

		return $form;
	}
	
	

	/**
	 * Render the element.
	 *
	 * @param $data
	 * @param array $params
	 * @param $tagdata
	 * @return bool
	 */
	function replace_tag($data, $params = array(), $tagdata = FALSE)
	{
		return $data;
	}

	function replace_rgb($data, $params = array(), $tagdata = FALSE)
	{
		if(strlen($data) == 3) {
		      $r = hexdec(substr($data,0,1).substr($data,0,1));
		      $g = hexdec(substr($data,1,1).substr($data,1,1));
		      $b = hexdec(substr($data,2,1).substr($data,2,1));
		} else {
		      $r = hexdec(substr($data,0,2));
		      $g = hexdec(substr($data,2,2));
		      $b = hexdec(substr($data,4,2));
		}
		$rgb = array($r, $g, $b);

		return implode(",", $rgb); // returns the rgb values separated by commas
	}



} 	/* END class */
/* End of file ft.color_picker_plus.php */
/* Location: ./system/expressionengine/color_picker_plus/ft.color_picker_plus.php */ 