<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once PATH_THIRD.'cartthrob/config.php';

class Cartthrob_price_simple_ft extends EE_Fieldtype
{
	public $info = array(
		'name' => 'CartThrob Price - Simple',
		'version' => CARTTHROB_VERSION,
	);
	
	public function __construct()
	{
		parent::__construct();
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		$this->EE->load->helper('data_formatting');
		$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
	}
	function install()
	{
	    return array(
	        'field_prefix'  => '$',
	    );
	}
	
	public function validate($data)
	{
		if ($data && ! $this->EE->form_validation->numeric($data))
		{
			return $this->EE->lang->line('numeric');
		}
		
		return TRUE;
	}
	
	public function get_prefix()
	{
 		if (empty($this->settings['field_prefix']))
		{
			$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
			
			$this->EE->load->model('cartthrob_settings_model');
 			$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');

			return  $this->EE->config->item('cartthrob:number_format_defaults_prefix');
		}
		else
		{
			return $this->settings['field_prefix']; 
		}
	}
	public function pre_process($data)
	{
		if (isset($this->row['channel_id']))
		{
			$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');

			$this->EE->load->model('cartthrob_settings_model');
 			$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
			
			$product_channel_fields = $this->EE->config->item('cartthrob:product_channel_fields');

			if (isset($product_channel_fields[$this->row['channel_id']]['global_price']))
			{
				$global_price = $product_channel_fields[$this->row['channel_id']]['global_price'];
				
				if ($global_price !== '')
				{
					$data = $product_channel_fields[$this->row['channel_id']]['global_price'];
				}
			}
		}

		return $data;
	}
	public function replace_tag($data, $params = '', $tagdata = '')
	{
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');

		$this->EE->load->library('number');
		
		$this->EE->number->set_prefix($this->get_prefix() ); 
 		$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
		return $this->EE->number->format($data);
	}
	
	public function replace_no_tax($data, $params = '', $tagdata = '')
	{
		return $this->replace_tag($data, $params, $tagdata); 
	}
	
	public function replace_plus_tax($data, $params = '', $tagdata = '')
	{
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		
		$this->EE->load->library('cartthrob_loader');
		
		$this->EE->load->library('number');

		if ($plugin = $this->EE->cartthrob->store->plugin($this->EE->cartthrob->store->config('tax_plugin')))
		{
			$data = $plugin->get_tax($data) + $data;
		}
		$this->EE->number->set_prefix( $this->get_prefix() ); 
 		$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
		return $this->EE->number->format($data);
	}
	public function replace_plus_tax_numeric($data, $params = '', $tagdata = '')
	{
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		
		$this->EE->load->library('cartthrob_loader');
		
		$this->EE->load->library('number');

		if ($plugin = $this->EE->cartthrob->store->plugin($this->EE->cartthrob->store->config('tax_plugin')))
		{
			$data = $plugin->get_tax($data) + $data;
		}
 		$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
		return $data; 
	}
	public function replace_numeric($data, $params = '', $tagdata = '')
	{
		return $data;
	}
	
	public function display_field($data)
	{
		$prefix = $this->get_prefix();
			
		$field_id = $this->settings['field_id']; 
 
		$span = '<span style="position:absolute;padding:5px 0 0 5px;">'.$prefix.'</span>';
			
			$this->EE->javascript->output('
				var span = $(\''.$span.'\').appendTo("body").css({top:-9999});
				var indent = span.width()+4;
				span.remove();
			
			$("#field_id_'.$field_id.'").before(\''.$span.'\');
			$("#field_id_'.$field_id.'").css({paddingLeft: indent});
			');
			
		return form_input(array(
			'name' => $this->field_name,
			'id' => $this->field_name,
			'class' => 'cartthrob_price_simple',
			'value' =>   $data,
			'maxlength' => $this->settings['field_maxl']
		));
	}

	public function display_settings($data)
	{
		$field_maxl = (empty($data['field_maxl'])) ? 12 : $data['field_maxl'];
		
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		
		$this->EE->load->library('cartthrob_loader');
		
		$field_prefix = (empty($data['field_prefix'])) ? $this->EE->cartthrob->store->config('number_format_defaults_prefix') : $data['field_prefix'];
		
		$this->EE->table->add_row(
			lang('field_max_length', 'field_maxl'),
			form_input(
				array('id' => 'cartthrob_price_simple_field_maxl', 'name' => 'field_maxl_cps', 'size' => 4, 'value' => $field_maxl)
				)
		);
		
		$this->EE->table->add_row(
			lang('number_format_defaults_prefix', 'field_prefix'),
			form_input(
				array('id' => 'cartthrob_price_simple_field_prefix', 'name' => 'field_prefix_cps', 'size' => 4, 'value' => $field_prefix)
				)
		);
 		$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
		
	}
	
	public function settings_modify_column($data)
	{
		$fields = parent::settings_modify_column($data);
		
		$fields['field_id_'.$data['field_id']]['type'] = 'FLOAT';
		$fields['field_id_'.$data['field_id']]['default'] = 0;
		
		return $fields;
	}
	
	public function save_settings($data)
	{
		return array(
			'field_maxl' => $this->EE->input->post('field_maxl_cps'),
			'field_prefix' => $this->EE->input->post('field_prefix_cps'),
			'field_fmt' => 'none',
		);
	}
}

/* End of file ft.cartthrob_price.php */
/* Location: ./system/expressionengine/third_party/cartthrob_discount/ft.cartthrob_price.php */