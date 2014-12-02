<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once PATH_THIRD.'cartthrob/config.php';
require_once PATH_THIRD.'cartthrob/fieldtypes/ft.cartthrob_matrix.php';

/**
 * @property CI_Controller $EE
 * @property Cartthrob_core_ee $cartthrob;
 * @property Cartthrob_cart $cart
 * @property Cartthrob_store $store
 */
class Cartthrob_price_modifiers_configurator_ft extends Cartthrob_matrix_ft
{
	public $info = array(
		'name' => 'CartThrob Price Modifiers Configurator',
		'version' => CARTTHROB_VERSION,
	);
	
	// make sure the last element has no extra comma, or it will introduce empty stuff
	public $default_row = array(
		'all_values'	=> '',
		'option_value' => '',
		'option_name' => '',
		'price' => '',
		'inventory'=> '', 
	);
	public $primary_row = array(
		'all_values'	=> '',
		'option_value' => '',
		'option_name' => '',
		'price' => '', 
		'inventory' => '',
		'weight'	=> ''
	);
	// make sure the last element has no extra comma, or it will introduce empty stuff
	public $secondary_row = array(
		'option_group'	=> '',
		'option_group_label'	=> '',
		'field_type'	=> '',
		'options'		=> ''
	); 
	public function __construct()
	{
		parent::__construct();
	}
	public function pre_process($data)
	{
		$data = parent::pre_process($data);
		
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		
		$this->EE->load->library('cartthrob_loader');
		
		$this->EE->load->library('number');
		
		foreach ($data as &$row)
		{
			if (isset($row['price']) && $row['price'] !== '')
			{	
				$row['price_plus_tax']  = $row['price'];
 				
				if ($plugin = $this->EE->cartthrob->store->plugin($this->EE->cartthrob->store->config('tax_plugin')))
				{
					$row['price_plus_tax'] = $plugin->get_tax($row['price']) + $row['price'];
 				}
				
				$row['price_numeric'] = $row['price'];
				$row['price_plus_tax_numeric'] = $row['price:plus_tax_numeric'] = $row['price_numeric:plus_tax'] = $row['price_plus_tax'];
				
				$row['price'] = $this->EE->number->format($row['price']);
				$row['price_plus_tax'] = $row['price:plus_tax'] = $this->EE->number->format($row['price_plus_tax']);
			}
		}
		$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
		
		return $data;
	}
	public function split_options($data, $option_values = TRUE, $check_inventory = FALSE)
	{
		$array_keys = $this->primary_row; 
		$array_keys['field_type'] = FALSE;
		
		$list_data = array(); 
		
		if ($data && is_array($data))
		{
			if ($option_values)
			{
				foreach ($data as $key => $value)
				{
					if (elements(array('option_name', 'option_value'), $value))
					{
						if ($check_inventory)
						{
							if (array_key_exists('inventory', $value))
							{
								if ( $value['inventory'] !== FALSE  &&  $value['inventory']  >= "0" )
								{
									$list_data[]= elements(array_keys($array_keys), $value);
								}
								elseif ($value['inventory'] === FALSE)
								{
									$list_data[]= elements(array_keys($array_keys), $value);
								}
							}
						}
						else
						{
							$list_data[]= elements(array_keys($array_keys), $value);
						}
					}

				}
			}
			else
			{
				foreach ($data as $key => $value)
				{
					if (element('option_group', $value))
					{
						$list_data[]= elements(array_keys($this->secondary_row), $value);
					}
				}
			}
		}
		return $list_data; 
	}
 
	public function option_groups($data, $params = array(), $tagdata = FALSE, $field_short_name = NULL)
	{
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		$this->EE->load->library('cartthrob_loader');
 		$this->EE->load->helper('array');
 		$item_option_labels = $this->EE->cartthrob->cart->meta('item_option_labels'); 

 		$data = $this->split_options($data, FALSE); 
		
 		$option_groups = array(); 
 		foreach ($data as $key => $value)
		{
 			$options = element('options', $value, array()); 

			$option_output = array(); 
			foreach (element('option', $options, array() ) as $k => $v)
			{
				$prices = element('price', $options); 
				
				// skip anything without a sku... cuz we can't use it
				if ($v)
				{
					// currently label is not used. not to be confused with option_name
					if (element('option_group_label', $data[$key]))
					{
						$label = $data[$key]['option_group_label']; 
					}
					else
					{
						$label = ucwords(str_replace("_", " ", $data[$key]['option_group'] )); 
					}

					$item_option_labels["configuration:".$field_short_name.":".$data[$key]['option_group']] = $label; 
					
 					$option_output[] = array(
						'option_value' 	=> $v,
						'option_name'	=> ucwords(str_replace("_", " ", $v)),
						'price'			=> element($k, $prices),
						'field_type'	=> element('field_type', $value, "options")
						//'inventory'		=> element($key, $inventory), // inventory can't really be pulled down INTO this option, because it coul apply to multiple final skus
					);				
				}
			}
			$option_groups[$data[$key]['option_group']] = $option_output; 
		}
		
		$this->EE->cartthrob->cart->set_meta('item_option_labels', $item_option_labels);
		$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
		return $option_groups;
	}
	
	public function compare($data, $configured_options, $check_inventory = FALSE)
	{
		$this->EE->load->helper("array"); 
		
		$saved_options = $this->split_options($data, TRUE); 
		
		
		foreach ($data as $key => $value)
		{
			if (element('field_type', $value) != "text")
			{
				continue; 
			}
			if (array_key_exists(element('option_group', $value), $configured_options))
			{
				$configured_options[element('option_group', $value)] = "text";
				
				/*
				if (!empty($configured_options[element('option_group', $value)]))
				{
					$configured_options[element('option_group', $value)] = "text";
				}
				else
				{
					$configured_options[element('option_group', $value)] = "not_selected";
				}
				*/
			}
		}

		$option_value= array(); 
		$option_name = array(); 
		$inventory = array(); 
		$all_values = array(); 

		foreach ($saved_options as $key => $value)
		{
			$option_value[] = element("option_value", $value, array()); 
			$inventory[] = element("inventory", $value, array()); 
			$all_values[] = element("all_values", $value, array()); 
		}

 		if ($all_values && is_array($all_values))
		{
 			foreach ($all_values as $key => $value)
			{
				
				
				$opt =  @unserialize(base64_decode($value)); 
 				$opt_count = count($opt); 
				if (is_array($configured_options) && is_array($opt))
				{
					$temp_arr = array_intersect_assoc($configured_options, $opt); 

					if (count($temp_arr) == $opt_count)
					{
						if ($check_inventory)
						{
							if (array_key_exists($key, $inventory))
							{
								return $inventory[$key];
							}
						}
						return element($key, $option_value); 
					}
				}
	 		}
		}
		return FALSE; 
	}
	
	public function replace_tag($data, $params = array(), $tagdata = FALSE)
	{
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		$this->EE->load->helper(array('html', 'array', 'data_formatting'));
		
		$data = $this->split_options($data, TRUE); 
		
		if (isset($params['orderby']) && $params['orderby'] === 'price')
		{
			$params['orderby'] = 'price_numeric';
		}
		$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
		
		return parent::replace_tag($data, $params, $tagdata);
	}
	
	// this function pre-processes data before being output in the item-options dropdown
	public function item_options($data = array())
	{
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		
		$this->EE->load->library('cartthrob_loader');
		
		$this->EE->load->library('number');
		$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');

  		return $data; 
	}
	
	public function item_option_groups($data = array(), $field_short_name)
	{
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		$this->EE->load->library('cartthrob_loader');
 		$this->EE->load->helper('array');

		$item_option_labels = $this->EE->cartthrob->cart->meta('item_option_labels'); 
		$option_groups = $this->option_groups($data, $params = array(), $tagdata = FALSE, $field_short_name); 
		$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');

		return $option_groups; 
	}
	
	
	public function display_field($data, $replace_tag = FALSE)
	{
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');

		$this->EE->lang->loadfile('cartthrob', 'cartthrob');
		
		$this->EE->load->model('cartthrob_settings_model');
		
		$this->EE->load->helper(array('html', 'array', 'data_formatting'));
 
		// default row's going to change from time to time, so we need a backup. 
		$this->primary_row = $this->default_row; 
		if ( ! is_array($data))
		{
			$data = _unserialize($data, TRUE);
		}
		
		$subdata = $data;
		
		$options = array('' => $this->EE->lang->line('select_preset'));
		
  		// just the data 
		$list_data = array(); 
		$subdata = array(); 
		
		
		$options = array(); 
		$prices = array(); 
		$saved_all_values = array(); 
		$saved_option_value = array(); 
		$saved_option_label = array(); 
		$saved_price = array(); 
		$saved_inventory = array(); 
		$final_options = array(); 
		
		
		if ($data && is_array($data))
		{
			foreach ($data as $key => $value)
			{
				if (elements(array('option_name', 'option_value'), $value))
				{
					$list_data[$key]= elements(array_keys($this->primary_row), $value);
					$saved_all_values[] =  element('all_values', $value); 
					$saved_option_value[] = element('option_value', $value); 
					$saved_option_label[] = element('option_name', $value); 
					$saved_price[] = element('price', $value); 
			 		$saved_inventory[] = element('inventory', $value);
				}
				if (element('option_group', $value))
				{
					$subdata[$key]= elements(array_keys($this->secondary_row), $value);
					
					foreach (element('option', $value['options']) as $k => $v)
					{
						$p = element('price', $value['options']); 
 						if ( $v !="" && $v !== FALSE && $v !==NULL)
						{
	 						$options[element('option_group', $value)][] = $v; 
	 						$prices[element('option_group', $value)][] = element($k, $p); 
							
						}
					}
				}
			}
		}
 
 		$final_options = cartesian($options);
		
  		$data = $list_data; 

		// just the subdata
		$vars = array(
			'field_id'	=> $this->field_id,
		);
		
		
		unset($this->default_row['inventory']);
		unset($this->default_row['weight']);
		
		
		$channel_id = $this->EE->input->get('channel_id'); 

		if ( ! $channel_id  && isset($this->EE->channel_form))
		{
 			$channel_id = $this->EE->channel_form->channel('channel_id');
		}
		
		if ($channel_id && $this->field_id == array_value($this->EE->config->item('cartthrob:product_channel_fields'), $channel_id, 'inventory'))
		{
			$this->default_row['inventory'] = '';
		}
		
		if ($channel_id && $this->field_id == array_value($this->EE->config->item('cartthrob:product_channel_fields'), $channel_id, 'weight'))
		{
			$this->default_row['weight'] = '';
		}
		
		if (empty($this->EE->session->cache['cartthrob_price_modifiers']['head']))
		{
			//always use action
			$url = (REQ === 'CP') ? 'EE.BASE+"&C=addons_modules&M=show_module_cp&module=cartthrob&method=save_price_modifier_presets_action"'
					     : 'EE.BASE+"ACT="+'.$this->EE->functions->fetch_action_id('Cartthrob_mcp', 'save_price_modifier_presets_action');
 
			$this->EE->session->cache['cartthrob_price_modifiers']['head'] = TRUE;
		}

		$this->EE->cp->add_to_foot('<script type="text/javascript" src="'.URL_THIRD_THEMES.'/cartthrob/scripts/jquery.form.js"></script>');

		$this->EE->cp->add_to_foot('<script type="text/javascript" src="'.URL_THIRD_THEMES.'/cartthrob_option_configurator/js/optionConfigurator_ajax.js"></script>');

		$this->EE->cp->add_to_foot('
			<script type="text/javascript">
				'.(isset($this->default_row['inventory']) ? "var show_inventory='1'" : "var show_inventory='0'").'
				var configurator_id = "'.$this->field_id.'"
			</script>
		');
		$this->EE->cp->add_to_head('
			<style type="text/css">

			table.cartthrobMatrix table.cartthrobOptionConfigurator td {
				border: 0 !important;
				padding: 0 !important;
				height: 28px;
				overflow: hidden;
				white-space: nowrap;
			}
 
			</style>
		');

		
		$this->buttons_temp = $this->buttons; 
		$this->buttons = array(); // getting rid of the add_row buttons
 
		$vars = array(
			'all_values'	=> $saved_all_values,
			'option_value'  => $saved_option_value, 
			'option_label'  => $saved_option_label, 
			'price'  		=> $saved_price,
			'inventory' 	=> $saved_inventory,
			'options' 		=> $final_options,
			'show_inventory'	=> (isset($this->default_row['inventory']) ? 1 : 0 ),
			'field_id'		=> $this->field_id,
			'field_id_name' => "field_id_".$this->field_id,
		); 
		$this->additional_controls = "<br><br>".$this->EE->load->view('configurator', $vars, TRUE);


		$this->default_row = array(); 
		$this->default_row = $this->secondary_row;
		$this->buttons = $this->buttons_temp; 
		$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');

		return parent::display_field($subdata, $replace_tag);
	}
	public function display_field_all_values($name, $value, $row, $index, $blank = FALSE)
	{
		$all_values = array(
			'readonly'		=> TRUE,
			'name'        => $name,
			'value'       => $value,
		);
		$data = array(); 
		
		if ( ! is_array($value))
		{
			$data = _unserialize($value, TRUE);
		}
		else
		{
			$data = $value; 
		}
		$details = NULL; 
		foreach ($data as $attr=>$val)
		{
			$details.="<strong>{$attr}:</strong> {$val}<br />";
		}

		return $details."<span style='display:none'>".form_input($all_values)."</span>"; 
		
	}
	public function display_field_field_type($name, $value, $row, $index, $blank = FALSE)
	{
		$this->EE->load->helper('form'); 
		
		$options = array(
			'options'	=> 'options', 
			'text'	=> 'text',
			); 
			
		return form_dropdown($name, $options, $value, 'class="cartthrob_configurator_field_type"');
	}
	public function display_field_options($name, $value, $row, $index, $blank = FALSE)
	{
		$modifiers = array(
			"option"=> (!empty($value['option']) ? $value['option']: array(NULL)), 
			"price" => (!empty($value['price']) ? $value['price']: array(NULL))
		); 
		
  		// count number of modifiers add that count as a JS variable.
		$vars = array(
			'field_id'	=> $this->field_id, 
			'name'		=> $name,
			'modifiers'	=> $modifiers,
			'count'		=> count($modifiers),
			'minus_graphic' => $this->EE->config->slash_item('theme_folder_url').'third_party/cartthrob_option_configurator/images/fe_icon_minus.png',
			'plus_graphic'	=> $this->EE->config->slash_item('theme_folder_url').'third_party/cartthrob_option_configurator/images/fe_icon_plus.png',
		);
 
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');

		// this view stores the option/price +- box content
		$view_data =  $this->EE->load->view('price_modifiers_field_options', $vars, TRUE);
		
		$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
		
		return $view_data; 
	}
}

/* End of file ft.cartthrob_discount.php */
/* Location: ./system/expressionengine/third_party/cartthrob_discount/ft.cartthrob_discount.php */