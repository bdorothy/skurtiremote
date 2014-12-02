<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

abstract class Cartthrob_settings_ft extends EE_Fieldtype
{
	public $has_array_data = TRUE;
	
	/**
	 * @var array list of settings fields
	 */
	protected $fields = array();
	
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Display Field on Publish
	 *
	 * @access	public
	 * @param	$data
	 * @return	field html
	 */
	public function display_field($data)
	{
		$this->data = $this->pre_process($data);
		
		if (empty($this->EE->session->cache['Cartthrob_settings_ft']['display_field']))
		{	
			if (REQ != 'CP')
			{
				$this->EE->cp->add_to_head('<link rel="stylesheet" href="'.URL_THIRD_THEMES.'/cartthrob/css/cartthrob_matrix_table.css" type="text/css" media="screen" />');
			}
			
			$this->EE->session->cache['Cartthrob_settings_ft']['display_field'] = TRUE;
		}
		
		$settings = array();
		
		foreach ($this->fields as $setting)
		{
			$settings[] = $this->setting_metadata($setting);
		}
		
		if (version_compare(APP_VER, '2.2', '<'))
		{
			$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
			$orig_view_path = $this->EE->load->_ci_view_path;
			
			$this->EE->load->_ci_view_path = PATH_THIRD.'cartthrob/views/';
			
			$output = $this->EE->load->view('cartthrob_settings_display_field', array('settings' => $settings), TRUE);
			
			$this->EE->load->_ci_view_path = $orig_view_path;
			$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
		}
		else
		{
			$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
			
			$output = $this->EE->load->view('cartthrob_settings_display_field', array('settings' => $settings), TRUE);
			$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
		}
		
		return $output;
	}
	
	protected function setting_metadata($setting, $plugin_type = FALSE)
	{
		if ($plugin_type === FALSE)
		{
			$plugin_type = isset($setting['plugin_type']) ? $setting['plugin_type'] : 'global';
		}
		
		//retrieve the current set value of the field
		$current_value = (isset($this->data[$setting['short_name']])) ? $this->data[$setting['short_name']] : NULL;
		
		//set the value to the default value if there is no set value and the default value is defined
		$current_value = ($current_value === NULL && isset($setting['default'])) ? $setting['default'] : $current_value;
		
		$setting['current_value'] = $current_value;
		
		$setting['plugin_type'] = $plugin_type;
		
		if (method_exists($this, 'setting_'.$setting['short_name']))
		{
			$setting['display_field'] = $this->{'setting_'.$setting['short_name']}($setting);
		}
		else if (method_exists($this, 'setting_'.$setting['type']))
		{
			$setting['display_field'] = $this->{'setting_'.$setting['type']}($setting);
		}
		else
		{
			$setting['display_field'] = '';
		}
		
		return $setting;
	}
	
	public function save($data)
	{
		return (is_array($data)) ? base64_encode(serialize($data)) : '';
	}
	
	public function save_settings($data)
	{
		$data['field_fmt'] = 'none';
		
		return $data;
	}
	
	public function pre_process($data)
	{
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		$this->EE->load->helper('data_formatting');
		$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
		
		return _unserialize($data, TRUE);
	}
	
	/**
	 * Replace tag
	 *
	 * @access	public
	 * @param	field contents
	 * @return	replacement text
	 *
	 */
	public function replace_tag($data, $params = array(), $tagdata = FALSE)
	{
		return '';
	}
	
	protected function setting_text($setting)
	{
		$input_data = array('name' => $this->field_name.'['.$setting['short_name'].']', 'value' => $setting['current_value']);
		
		if (isset($setting['size']))
		{
			$input_data['style'] = 'width:'.$setting['size'].';';
		}
		
		return form_input($input_data);
	}
	
	protected function setting_date($setting)
	{
		if (empty($this->EE->session->cache['Cartthrob_settings_ft']['datepicker']))
		{
			$this->EE->cp->add_js_script('ui', 'datepicker');
			
			$this->EE->javascript->output('
			$(".ct_datepicker").datepicker({dateFormat: $.datepicker.W3C + EE.date_obj_time, defaultDate: new Date('.( $this->EE->localize->now  * 1000).')});
			');
			
			$this->EE->session->cache['Cartthrob_settings_ft']['datepicker'] = TRUE;
		}
		
		return form_input(array(
			'name' => $this->field_name.'['.$setting['short_name'].']',
			'value' => $setting['current_value'],
			'class' => 'ct_datepicker',
		));
	}
	
	protected function setting_textarea($setting)
	{
		return form_textarea(array('name' => $this->field_name.'['.$setting['short_name'].']', 'value' => $setting['current_value'], 'rows' => 2));
	}
	
	protected function setting_hidden($setting)
	{
		return form_hidden($this->field_name.'['.$setting['short_name'].']', $setting['current_value']);
	}
	
	protected function setting_select($setting)
	{
		if (array_values($setting['options']) === $setting['options'])
		{
			foreach($setting['options'] as $key => $value)
			{
				unset($setting['options'][$key]);
				
				$setting['options'][$value] = $value;
			}
		}
		
		return form_dropdown($this->field_name.'['.$setting['short_name'].']', $setting['options'], $setting['current_value'], @$setting['extra']);
	}
	
	protected function setting_multiselect($setting)
	{
		switch($setting['short_name'])
		{
			case 'categories':
				$this->EE->load->model('category_model');
				
				$query = $this->EE->category_model->get_category_groups('', $this->EE->config->item('site_id'));
				
				$category_groups = array();
				
				foreach ($query->result() as $row)
				{
					$category_groups[$row->group_id] = $row->group_name;
				}
				
				$this->EE->api->instantiate('channel_categories');
				
				$category_form_tree = $this->EE->api_channel_categories->category_form_tree(TRUE);
				
				$setting['options'] = array();
				
				if ($category_form_tree)
				{
					foreach ($category_form_tree as $key => $value)
					{
						$optgroup = isset($category_groups[$value[0]]) ? $category_groups[$value[0]] : '';
						
						if ( ! isset($setting['options'][$optgroup]))
						{
							$setting['options'][$optgroup] = array();
						}
						
						$previous_key = $key - 1;
						
						$next_key = $key +1;
						
						if ($previous_key < 0 || ! isset($category_form_tree[$previous_key]))
						{
							$category_options['NULL_'.$next_key] = '-------';
						}
						
						$setting['options'][$optgroup][$value[1]] = str_replace('!-!!-!!-!!-!!-!!-!','-', $value[2]);
						
						if (isset($category_form_tree[$next_key]) && $category_form_tree[$next_key][0] != $value[0])
						{
							$category_options['NULL_'.$next_key] = '-------';
						}
					}
				}
				
				break;
		}
		if (array_values($setting['options']) === $setting['options'])
		{
			foreach($setting['options'] as $key => $value)
			{
				unset($setting['options'][$key]);
				
				$setting['options'][$value] = $value;
			}
		}
		
		return form_multiselect($this->field_name.'['.$setting['short_name'].'][]', $setting['options'], $setting['current_value'], @$setting['extra']);
	}
	
	protected function setting_checkbox($setting)
	{
		if ( ! isset($setting['options']) || ! is_array($setting['options']))
		{
			$display_field = form_label(form_checkbox($this->field_name.'['.$setting['short_name'].']', 1, $setting['current_value'], 'id="'.$this->field_name.'['.$setting['short_name'].']'.'"').NBS.$this->EE->lang->line('yes'), $this->field_name.'['.$setting['short_name'].']');
		}
		else
		{
			$display_field = '';
			
			//if is index array
			if (array_values($setting['options']) === $setting['options'])
			{
				foreach($setting['options'] as $value)
				{
					$display_field .= form_label(form_checkbox($this->field_name.'['.$setting['short_name'].'][]', $value, ($setting['current_value'] == $value)).NBS.$value, $this->field_name.'['.$setting['short_name'].'][]');
				}
			}
			//if associative array
			else
			{
				foreach($setting['options'] as $key => $value)
				{
					$display_field .= form_label(form_checkbox($this->field_name.'['.$setting['short_name'].'][]', $key, ($setting['current_value'] == $key)).NBS.$value, $this->field_name.'['.$setting['short_name'].'][]');
				}
			}
		}
		
		return $display_field;
	}
	
	protected function setting_radio($setting)
	{
		if ( ! isset($setting['options']) || ! is_array($setting['options']))
		{
			$display_field = form_label(form_radio($this->field_name.'['.$setting['short_name'].']', 1, $setting['current_value']).NBS.$this->EE->lang->line('yes'), $this->field_name.'['.$setting['short_name'].']');
			
			$display_field .= form_label(form_radio($this->field_name.'['.$setting['short_name'].']', 0, ! $setting['current_value']).NBS.$this->EE->lang->line('no'), $this->field_name.'['.$setting['short_name'].']');
		}
		else
		{
			$display_field = '';
			
			//if is index array
			if (array_values($setting['options']) === $setting['options'])
			{
				foreach($setting['options'] as $value)
				{
					$display_field .= form_label(form_radio($this->field_name.'['.$setting['short_name'].']', $value, ($setting['current_value'] == $value)).NBS.$value, $this->field_name.'['.$setting['short_name'].']');
				}
			}
			//if associative array
			else
			{
				foreach($setting['options'] as $key => $value)
				{
					$display_field .= form_label(form_radio($this->field_name.'['.$setting['short_name'].']', $key, ($setting['current_value'] == $key)).NBS.$value, $this->field_name.'['.$setting['short_name'].']');
				}
			}
		}
		
		return $display_field;
	}
}

/* End of file ft.cartthrob_discount.php */
/* Location: ./system/expressionengine/third_party/cartthrob_discount/ft.cartthrob_discount.php */