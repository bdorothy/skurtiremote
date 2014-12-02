<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once('config.php');

/**
 * Field Suggest_ft class.
 * 
 * Fieldtype will auto suggest previous content saved with this fieldtype
 *
 * @extends EE_Fieldtype
 */
class Ls_fieldsuggest_ft extends EE_Fieldtype 
{
    public $info = array(
        'name'      => LS_FIELDSUGGEST_NAME,
        'version'   => LS_FIELDSUGGEST_VERSION
    );
    public $EE;
    public $settings = array(
    	'hover_color_bg'		=> '#0088cc',
    	'enable_autosuggest'	=> 'yes',
    	'field_maxl' 			=> '128',
		'field_content_type' 	=> 'all',
		'field_text_direction'	=> 'ltr'
    );
    public $field_id;
    public $field_name;
    
    // --------------------------------------------------------------------
    
    public function __construct()
    {
    	parent::__construct();
    	
		if (! isset($this->EE->session->cache[LS_FIELDSUGGEST_SHORTNAME])) {
			$this->EE->session->cache[LS_FIELDSUGGEST_SHORTNAME] = array('includes' => array());
		}
		$this->cache =& $this->EE->session->cache[LS_FIELDSUGGEST_SHORTNAME];
    }

    // --------------------------------------------------------------------

	
	public function display_cell($data) {
		return $this->display_field($data);
	}
	
	
	// GRID FIELD TYPE
	public function grid_display_field($data)	{
	$enable_autosuggest	= $this->_get_setting('enable_autosuggest');
    $hover_color_bg	= $this->_get_setting('hover_color_bg', array(), true);
   	$type = (isset($this->settings['field_content_type'])) ? $this->settings['field_content_type'] : 'all';
	$attributes = array(
            'name'  => $this->field_name,
            'id'    => $this->field_id,
			'value' => $this->_format_number($data, $type),
            'field_content_type' => $type,
//			'dir' => $this->settings['field_text_direction'],
        );
		
	if ($this->settings['field_maxl']) {
			$field['maxlength'] = $this->settings['field_maxl'];
		}	
		
	if ($enable_autosuggest === 'yes') {
	    	$this->_include_theme_css('ls_fieldsuggest.css');
	    	$this->_insert_css($this->_create_color_css($hover_color_bg));
	    	$this->_include_theme_js('typeahead.js');		
        	$grid_datasource = json_encode($this->_grid_get_datasource());
	    	$this->_insert_js($this->_create_typeahead_js($grid_datasource));
        	$attributes['autocomplete'] = 'off';
			$attributes['class'] = 'dropdown ls_fieldsuggest_autosuggest';
    	}	
		
		
    	$input = form_input($attributes);
    	
        return $input;
	}
    
	
    public function display_field($data)
    {    
    	$enable_autosuggest	= $this->_get_setting('enable_autosuggest');
    	$hover_color_bg	= $this->_get_setting('hover_color_bg', array(), true);
    	$type = (isset($this->settings['field_content_type'])) ? $this->settings['field_content_type'] : 'all';
    	
    	$attributes = array(
            'name'  => $this->field_name,
            'id'    => $this->field_id,
			'value' => $this->_format_number($data, $type),
            'field_content_type' => $type,
//			'dir' => $this->settings['field_text_direction'],
        );
        
        // maxlength attribute should only appear if its value is > 0
		if ($this->settings['field_maxl']) {
			$field['maxlength'] = $this->settings['field_maxl'];
		}
        
        if ($enable_autosuggest === 'yes') {
	    	$this->_include_theme_css('ls_fieldsuggest.css');
	    	$this->_insert_css($this->_create_color_css($hover_color_bg));
	    	$this->_include_theme_js('typeahead.js');		
        	$datasource = json_encode($this->_get_datasource());	
	    	$this->_insert_js($this->_create_typeahead_js($datasource));
        	$attributes['autocomplete'] = 'off';
			$attributes['class'] = 'dropdown ls_fieldsuggest_autosuggest';
    	}
    	
    	$input = form_input($attributes);
    	
        return $input;
    }
    
    private function _get_datasource()
    {
    	$datasource = array();
    	
    	$field = 'field_id_'.$this->field_id;
    	$this->EE->db->select($field)
    				 ->distinct()
    				 ->from('exp_channel_data')
    				 ->where($field . ' != ""');
    	$field_data = $this->EE->db->get();
    	
    	if ($field_data->num_rows() > 0) {
    		foreach ($field_data->result_array() as $row) {
    			$datasource[] = $row[$field];
    		}
    	}
    	
    	return $datasource;
    }
	
	
	
	private function _grid_get_datasource()
    {
		
    	$datasource = array();
		
    	$col = 'col_id_'.$this->field_id;
    	$grid_table = 'channel_grid_field_'.$this->settings['grid_field_id'];
    	$this->EE->db->select($col)
    				 ->distinct()
    				 ->from($grid_table);
					// ->where($col.' != ',"");
    				 
    	$field_data = $this->EE->db->get();
    	
    	if ($field_data->num_rows() > 0) {
    		foreach ($field_data->result_array() as $row) {
    			$datasource[] = $row[$col];
    		}
			
    	}
    	
    	return $datasource;
    }
    
    private function _create_color_css($hover_color_bg)
    {
    	
    	return <<<CSS
.dropdown-menu li > a:hover, .dropdown-menu .active > a, .dropdown-menu .active > a:hover {
    background-color: {$hover_color_bg} !important;
}
CSS;
    }
    
    private function _create_typeahead_js($datasource)
    {
    return <<<JS
jQuery(document).ready(function($) {

	
	$('input[name="field_id_{$this->field_id}"]').typeahead({
		'source': {$datasource},
		'items': 10
	});

	Grid.bind("ls_fieldsuggest", "display", function(cell)
	{
	$('.ls_fieldsuggest_autosuggest').typeahead({
	'source': {$datasource},
	'items': 10
	});
	});
		
});
JS;
    }
    
    // --------------------------------------------------------------------
    
    public function validate($data)
    {
		if ($data == '') {
			return true;
		}
		
		if ( ! isset($this->field_content_types)) {
			$this->EE->load->model('field_model');
			$this->field_content_types = $this->EE->field_model->get_field_content_types();
		}

		if ( ! isset($this->settings['field_content_type'])) {
			return true;
		}

		$content_type = $this->settings['field_content_type'];
		
		if (in_array($content_type, $this->field_content_types['text']) && $content_type !== 'any') {
			if ($content_type === 'decimal') {
				if ( ! $this->EE->form_validation->numeric($data)) {
					return $this->EE->lang->line($content_type);
				}
				
				// Check if number exceeds mysql limits
				if ($data >= 999999.9999) {
					return $this->EE->lang->line('number_exceeds_limit');
				}
				
				return true;
			}

			if ( ! $this->EE->form_validation->$content_type($data)) {
				return $this->EE->lang->line($content_type);
			}
			
			// Check if number exceeds mysql limits			
			if ($content_type == 'integer') {
				if (($data < -2147483648) OR ($data > 2147483647)) {
					return $this->EE->lang->line('number_exceeds_limit');
				}
			}
		}
		
		return true;
	}
    
    // --------------------------------------------------------------------
	
	public function replace_tag($data, $params = '', $tagdata = '')
	{
		$type		= isset($this->settings['field_content_type']) ? $this->settings['field_content_type'] : 'all';
		$decimals	= isset($params['decimal_place']) ? (int) $params['decimal_place'] : FALSE;
		
		$data = $this->_format_number($data, $type, $decimals);

		return $this->EE->typography->parse_type(
			$this->EE->functions->encode_ee_tags($data),
			array(
				'text_format'	=> $this->row['field_ft_'.$this->field_id],
				'html_format'	=> $this->row['channel_html_formatting'],
				'auto_links'	=> $this->row['channel_auto_link_urls'],
				'allow_img_url' => $this->row['channel_allow_img_urls']
			)
		);
	}
    
	
	
	
    // --------------------------------------------------------------------
    
    public function display_settings($data)
    {
		$prefix = 'ls_fieldsuggest';
		$extra = '';
		
		$this->EE->lang->loadfile('ls_fieldsuggest');
		
		// Set up autosuggest
    	$enable_autosuggest	= $this->_get_setting('enable_autosuggest', $data);    
    	$this->EE->table->add_row(
    		form_label($this->EE->lang->line('enable_autosuggest'), 'enable_autosuggest'),
			form_checkbox('enable_autosuggest', $enable_autosuggest, ($enable_autosuggest === 'yes'))
		);
		
		if ($data['field_id'] != '') {
			$extra .= '<div class="notice update_content_type js_hide">';
			$extra .= '<p>'.sprintf(lang('content_type_changed'), $data['field_content_type']).'</p>';
			$extra .= '</div>';
		}
		
		// Setup Max length
		$field_maxl = ($data['field_maxl'] == '') ? 128 : $data['field_maxl'];
		$field_content_options = array(
			'all' => lang('all'),
			'numeric' => lang('type_numeric'),
			'integer' => lang('type_integer'),
			'decimal' => lang('type_decimal')
		);
		$this->EE->table->add_row(
			lang('field_max_length', 'field_max1'),
			form_input(array('id' => 'field_maxl', 'name' => 'field_maxl', 'size' => 4,'value' => $field_maxl))
		);

		// Set up text options
		$this->field_formatting_row($data, $prefix);
		$this->text_direction_row($data, $prefix);

		$this->EE->table->add_row(
			lang('field_content_text', 'field_content_text'),
			form_dropdown('text_field_content_type', $field_content_options, $data['field_content_type'], 'id="text_field_content_type"').$extra
		);

		$this->field_show_smileys_row($data, $prefix);
		$this->field_show_glossary_row($data, $prefix);
		$this->field_show_spellcheck_row($data, $prefix);
		$this->field_show_file_selector_row($data, $prefix);
		
		$this->EE->javascript->output('$("#text_field_content_type").change(function() {$(this).nextAll(".update_content_type").show();});');
    }
	
	
	
	/**
	 * Save a cell's settings
	 */
	public function save_cell_settings($data) {
		return $this->save_settings($data);
	}
	
    
    /**
	 * Save Settings
	 *
	 * @access	public
	 * @return	field settings
	 */
	public function save_settings($data)
	{
		return array(
			'enable_autosuggest'	=> $this->EE->input->post('enable_autosuggest'),
			'field_maxl'			=> $this->EE->input->post('field_maxl'),
			'field_content_type'	=> $this->EE->input->post('text_field_content_type')
		);
	}
	
	// --------------------------------------------------------------------
	
	public function display_global_settings()
	{
		$val = array_merge($this->settings, $_POST);
		
		$this->EE->lang->loadfile('ls_fieldsuggest');
		
		$form = form_label($this->EE->lang->line('hover_color_bg'), 'hover_color_bg').NBS.
				form_input('hover_color_bg', $val['hover_color_bg']).NBS.NBS.NBS.' ';
		
		return $form;
	}
	
	public function save_global_settings()
	{
		unset($_POST['submit']);
	
		return array_merge($this->settings, $_POST);
	}
	
	// --------------------------------------------------------------------
	
	public function settings_modify_column($data)
	{

		$settings = unserialize(base64_decode($data['field_settings']));

		switch($settings['field_content_type'])
		{
			case 'numeric':
				$fields['field_id_'.$data['field_id']]['type'] = 'FLOAT';
				$fields['field_id_'.$data['field_id']]['default'] = 0;
				break;
			case 'integer':
				$fields['field_id_'.$data['field_id']]['type'] = 'INT';
				$fields['field_id_'.$data['field_id']]['default'] = 0;
				break;
			case 'decimal':
				$fields['field_id_'.$data['field_id']]['type'] = 'DECIMAL(10,4)';
				$fields['field_id_'.$data['field_id']]['default'] = 0;
				break;
			default:
				$fields['field_id_'.$data['field_id']]['type'] = 'text';
				$fields['field_id_'.$data['field_id']]['null'] = TRUE;
		}
		
		return $fields;
	}
	
	// --------------------------------------------------------------------
	
	private function _format_number($data, $type = 'all', $decimals = FALSE)
	{
		switch($type)
		{
			case 'numeric':	$data = rtrim(rtrim(sprintf('%F', $data), '0'), '.'); // remove trailing zeros up to decimal point and kill decimal point if no trailing zeros
				break;
			case 'integer': $data = sprintf('%d', $data);
				break;
			case 'decimal':
				$parts = explode('.', sprintf('%F', $data));
				$parts[1] = isset($parts[1]) ? rtrim($parts[1], '0') : '';
				
				$decimals = ($decimals === FALSE) ? 2 : $decimals;
				$data = $parts[0].'.'.str_pad($parts[1], $decimals, '0');
				break;
			default:
				if ($decimals && ctype_digit(str_replace('.', '', $data))) {
					$data = number_format($data, $decimals);
				}
		}
		
		return $data;
	}
	
	// --------------------------------------------------------------------
    
    public function install()
	{
	    return array(
	        'hover_color_bg'		=> '#0088cc',
	    	'enable_autosuggest'	=> 'yes',
	    	'field_maxl' 			=> '128',
			'field_content_type' 	=> 'all',
			'field_text_direction'	=> 'ltr'
	    );
	}
	
	// --------------------------------------------------------------------
	
	private function _get_setting($needle, $haystack = array(), $db = false)
	{
		if ($db) {
			$query = $this->EE->db->query('SELECT settings FROM exp_fieldtypes WHERE name="ls_fieldsuggest" LIMIT 1');
			if ($query->num_rows() === 1) {
				$haystack = unserialize(base64_decode($query->row('settings')));
			}
		}
	
		if (!empty($haystack) && array_key_exists($needle, $haystack) && isset($haystack[$needle])) {
			return $haystack[$needle];
		}
		
		if (is_array($this->settings) && array_key_exists($needle, $this->settings) && isset($this->settings[$needle])) {
			return $this->settings[$needle];
		}
		
		return false;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Theme URL
	 */
	private function _theme_url()
	{
		if (! isset($this->cache['theme_url']))
		{
			$theme_folder_url = $this->EE->config->item('theme_folder_url');
			if (substr($theme_folder_url, -1) != '/') $theme_folder_url .= '/';
			$this->cache['theme_url'] = $theme_folder_url.'third_party/ls_fieldsuggest/';
		}

		return $this->cache['theme_url'];
	}

    /**
     * Include Theme CSS
     * @param $file
     */
	private function _include_theme_css($file)
	{
		if (! in_array($file, $this->cache['includes']))
		{
			$this->cache['includes'][] = $file;
			$this->EE->cp->add_to_head('<link rel="stylesheet" type="text/css" href="'.$this->_theme_url().$file.'" />');
		}
	}

    /**
     * Include Theme JS
     * @param $file
     */
	private function _include_theme_js($file)
	{
		if (! in_array($file, $this->cache['includes']))
		{
			$this->cache['includes'][] = $file;
			$this->EE->cp->add_to_foot('<script type="text/javascript" src="'.$this->_theme_url().$file.'"></script>');
		}
	}

	// --------------------------------------------------------------------

    /**
     * Insert JS
     * @param $js
     */
	private function _insert_js($js)
	{
		$this->EE->cp->add_to_foot('<script type="text/javascript">'.$js.'</script>');
	}

    /**
     * Insert JS
     * @param $css
     */
	private function _insert_css($css)
	{
		$this->EE->cp->add_to_head('<style type="text/css">'.$css.'</style>');
	}

	// --------------------------------------------------------------------
    
	
	
	// grid

	public function accepts_content_type($name)
	{
		return ($name == 'channel' || $name == 'grid');
	}
	
	public function grid_display_settings($data){
	return array(
		$this->grid_field_formatting_row($data),
		$this->grid_text_direction_row($data),
		$this->grid_max_length_row($data),
	);
	}
	
	
}
// END Field Suggest_ft class

/* End of file ft.ls_fieldsuggest.php */
/* Location: ./system/expressionengine/third_party/ls_fieldsuggest/ft.ls_fieldsuggest.php */