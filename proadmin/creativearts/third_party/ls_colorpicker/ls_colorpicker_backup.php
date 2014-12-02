<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once('config.php');
class Ls_colorpicker_ft extends EE_Fieldtype {

	
	var $info = array(
		'name'    => 'LS ColorPicker Fieldtype',
		'version' => '3.0'
	);

	
	
	/**
	 * Fieldtype Constructor
	 */
	function __construct()
	{
		parent::__construct();
		if (! isset($this->EE->session->cache[LS_COLORPICKER_SHORTNAME])) {
			$this->EE->session->cache[LS_COLORPICKER_SHORTNAME] = array('includes' => array());
		}
		$this->cache =& $this->EE->session->cache[LS_COLORPICKER_SHORTNAME];
		
	}

	// --------------------------------------------------------------------	
	
	/**
	 * INSTALL
	 */
	function install(){
	
	}
	
	public function accepts_content_type($name)
	{
		return ($name == 'channel' || $name == 'grid');
	}
	
	
	
	
	/**
	 * GLOBAL SETTINGS
	 */
	function display_global_settings(){
	
		return ('Global settings here');
	}
	
	
	
	
	/**
	 * SAVE SETTINGS
	 */
	function save_global_settings(){
	return array_merge($this->settings, $_POST);
	}
	
	
	
	/**
	 * DISPLAY FIELD TYPE (SETTINGS PAGE OF EE)
	 */
	function display_settings(){
    
	}
	
	
	/**
	 * GRID DISPLAY SETTINGS ON EE SETTINGS PAGE
	*/	
	public function grid_display_settings($data){
	return array(
		$this->grid_field_formatting_row($data),
		$this->grid_text_direction_row($data),
		$this->grid_max_length_row($data),			
	);
	}
	
	
	/**
	 * SAVE SETTINGS
	 */
	function save_settings($data){
   	}
	
	/**
	 * DISPLAYING FIELD ON PUBLISH PAGE
	 */
	function display_field($data){
	$this->_include_theme_css('ls_colorpicker.css');
	$options = array(
                  '#ff0'  => 'Yellow',
                  '#f00'    => 'Red',
                  '#00f'   => 'Green',
               
                );
	return form_dropdown('colors', $options, '');
	
	}
	
	private function _include_theme_css($file)
	{
		if (! in_array($file, $this->cache['includes']))
		{
			$this->cache['includes'][] = $file;
			$this->EE->cp->add_to_head('<link rel="stylesheet" type="text/css" href="'.$this->_theme_url().$file.'" />');
		}
	}

	private function _theme_url()
	{
		if (! isset($this->cache['theme_url']))
		{
			$theme_folder_url = $this->EE->config->item('theme_folder_url');
			if (substr($theme_folder_url, -1) != '/') $theme_folder_url .= '/';
			$this->cache['theme_url'] = $theme_folder_url.'third_party/ls_colorpicker/';
		}

		return $this->cache['theme_url'];
	}
	

	
	
	
	public function zenbu_display($entry_id, $channel_id, $data, $table_data, $field_id, $settings, $rules, $upload_prefs, $installed_addons){
	return '<div style="width:20px; height:20px; background:#'.$data.'"></div>';
	}
	
	
	
	function save($data){
	return $data;
	}
	
	function grid_display_field($data)
	{	
	
	$this->_include_theme_css('ls_colorpicker.css');
	$options = array(	
					''  			=> 'Select Color',
					'FFFFFF'    	=> 'White',
					'C0C0C0'  		=> 'Silver',
					'FFD700'		=> 'Gold',
					'F5F5DC'		=> 'Beige',
					'808080'    	=> 'Gray',
					'000000'  		=> 'Black',
					'FF0000'    	=> 'Red',
					'800000'  		=> 'Marooon',
					'FFFF00'    	=> 'Yellow',
					'808000'  		=> 'Olive',
					'00FF00'    	=> 'Lime',
					'008000'  		=> 'Green',
					'00FFFF'    	=> 'Aqua',
					'008080'  		=> 'Teal',
					'0000FF'    	=> 'Blue',
					'000080'  		=> 'Navy',
					'FF00FF'    	=> 'Pink',
					'800080'  		=> 'Purple',
					'FFFFE0'		=> 'Cream',
					'FFDAB9'		=> 'Peach',
                );
				
				
	return form_dropdown($this->field_name, $options, $data,'class="colorpicker"');
	}
	
	
	
	
	

}
