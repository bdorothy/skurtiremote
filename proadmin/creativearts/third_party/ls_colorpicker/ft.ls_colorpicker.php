<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once('config.php');
class Ls_colorpicker_ft extends EE_Fieldtype {

    var $info = array(
        'name'      => 'Ls Color Picker',
        'version'   => '3.2'
    );
	
	
	function __construct()
	{
		parent::__construct();
		if (! isset($this->EE->session->cache[LS_COLORPICKER_SHORTNAME])) {
			$this->EE->session->cache[LS_COLORPICKER_SHORTNAME] = array('includes' => array());
		}
		$this->cache =& $this->EE->session->cache[LS_COLORPICKER_SHORTNAME];
		
	}


    // --------------------------------------------------------------------

	// install
	function install()
	{    
    
	}
	
	public function accepts_content_type($name)
	{
    return ($name == 'channel' || $name == 'grid');
	}
	
	// display field
	function display_field($data){
	
    return $data;
	}

	
	// grid field display
	public function grid_display_settings($data)
	{
    return array(
        $this->grid_field_formatting_row($data),
        $this->grid_text_direction_row($data),
        $this->grid_max_length_row($data)
    );
	}
	
	
	// Only called when being rendered in a Grid field cell:
	public function grid_display_field($data)
	{	
	$this->_include_theme_css('ls_colorpicker.css');
	if(is_array($data)){
	$data = implode('|',$data);
	};
	$output = '<div style="height:150px; overflow-y:scroll">';
	$options = array(
				'multicolor'=>' Multi Color',
				'white'=>' White',
				'silver'=>' Silver',
				'beige'=>' Beige',
				'grey'=>' Grey',
				'black'=>' Black',
				'red'=>' Red',
				'maroon'=>' Maroon',
				'yellow'=>' Yellow',
				'gold'=>' Gold',
				'cream'=>' Cream',
				'peach'=>' Peach',
				'olive'=>' Olive',
				'lime'=>' Lime',
				'green'=>' Green',
				'aqua'=>' Aqua',
				'teal'=>' Teal',
				'blue'=>' Blue',
				'navy'=>' Navy',
				'pink'=>' Pink',
				'purple'=>' Purple',
				
	);
	
	foreach($options as $k => $v){
	//$output .= '<input type="checkbox" name="'.$this->field_name.'[]" value="'.$k.'" />'.$v.'<br />';
	$colors = array(
	'name'        => $this->field_name.'[]',
    'class'          => $this->field_name,
    'value'       => $k,
    'checked'     => (in_array($k,explode('|',$data))?TRUE:FALSE),
    'style'       => 'margin:2px; float:left',
	);
	$output .= '<div class="color '.$k.'" style="float:left; margin-top:5px"></div><div style="float:left; margin-top:5px; margin-left:10px">'.form_checkbox($colors).$v.'</div><br>';
    $output .= '<div class="clear"></div>';
	}
	$output .= "</div>";
	return $output;
    // Display code for Grid cell
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
	
	

	
    
}
// END Google_maps_ft class

/* End of file ft.google_maps.php */
/* Location: ./system/expressionengine/third_party/google_maps/ft.google_maps.php */