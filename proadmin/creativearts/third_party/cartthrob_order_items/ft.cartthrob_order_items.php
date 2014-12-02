<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once PATH_THIRD.'cartthrob/config.php';
require_once PATH_THIRD.'cartthrob/fieldtypes/ft.cartthrob_matrix.php';

/**
 * @property CI_Controller $EE
 * @property Cartthrob_core_ee $cartthrob;
 */
class Cartthrob_order_items_ft extends Cartthrob_matrix_ft
{
	public $EE, $cartthrob;
	
	public $info = array(
		'name' => 'CartThrob Order Items',
		'version' => CARTTHROB_VERSION,
	);
	
	public $variable_prefix = 'item:';
	
	public $row_nomenclature = 'item';
	
	public $default_row = array(
		'entry_id' => '',
		'title' => '',
		'quantity' => '',
		'price' => '',
	);
	
	public $default_columns = array(
		'row_id',
		'row_order',
		'order_id',
		'entry_id',
		'title',
		'quantity',
		'price',
		'price_plus_tax',
		'weight',
		'shipping',
		'no_tax',
		'no_shipping',
		'extra',
	);
	
	protected $sub_items;
	
	public function __construct()
	{
		parent::__construct();
	}
	public function pre_process($data)
	{
		if ( ! method_exists($this->EE->load, 'get_package_paths') || ! in_array(PATH_THIRD.'cartthrob/', $this->EE->load->get_package_paths()))
		{
			$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		}
		
		$this->EE->load->library('cartthrob_loader');
		
		$this->EE->load->library('number');
		
		$this->EE->load->model('order_model');
		
		if (isset($this->row['entry_id']))
		{
			$data = $this->EE->order_model->get_order_items($this->row['entry_id']);
		}
		/*
		foreach ($data as &$row)
		{
			if (isset($row['price']) && $row['price'] !== '')
			{
				$row['price_numeric'] = $row['price'];
				$row['price'] = $this->EE->number->format($row['price']);
			}
		}
		*/
		if (!is_array($data) || empty($data))
		{
			$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
			return $data;
		}
		foreach ($data as $i => $row)
		{
			//this is to set blank columns that arent in all rows
			if ( ! isset($this->EE->session->cache['cartthrob_order_items']['extra_columns'][$row['order_id']]))
			{
				$this->EE->session->cache['cartthrob_order_items']['extra_columns'][$row['order_id']] = array();
			}
			
			$this->EE->session->cache['cartthrob_order_items']['original_columns'][$row['order_id']][$i] = array_keys($row);
			
			foreach (array_keys($row) as $key)
			{
				if (in_array($key, $this->default_columns))
				{
					continue;
				}
				
				if ( ! in_array($key, $this->EE->session->cache['cartthrob_order_items']['extra_columns'][$row['order_id']]))
				{
					$this->EE->session->cache['cartthrob_order_items']['extra_columns'][$row['order_id']][] = $key;
				}
			}
			
			foreach ($this->EE->session->cache['cartthrob_order_items']['extra_columns'][$row['order_id']] as $key)
			{
				if ( ! isset($row[$key]))
				{
					$data[$i][$key] = '';
				}
			}
		}
		$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
		
		return $data;
	}
	
	public function replace_tag($data, $params = array(), $tagdata = FALSE)
	{
		//@TODO add packages sub tag pair
		if (count($data) === 0 && preg_match('/'.LD.'if '.$this->variable_prefix.'no_results'.RD.'(.*?)'.LD.'\/if'.RD.'/s', $tagdata, $match))
		{
			$tagdata = str_replace($match[0], '', $tagdata);
			
			$this->EE->TMPL->no_results = $match[1];
		}
		
		if ( ! $data)
		{
			return $this->EE->TMPL->no_results();
		}
		
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		
		$this->EE->load->helper('data_formatting', 'array');
		
		$total_results = count($data);
		
		$this->EE->load->library(array('data_filter', 'number'));
		
		$this->EE->load->model(array('product_model', 'cartthrob_entries_model', 'cartthrob_field_model'));
		
		// looking for row-id parameter
		if (isset($params['row_id']))
		{
			$row_ids = explode("|", $params['row_id']); 
 			$this->EE->data_filter->filter($data, 'row_id', $row_ids, 'in_array', TRUE);
		}
		$this->EE->data_filter->sort($data, (isset($params['orderby'])) ? $params['orderby'] : FALSE, (isset($params['sort'])) ? $params['sort'] : FALSE);
		
		$this->EE->data_filter->limit($data, (isset($params['limit'])) ? $params['limit'] : FALSE, (isset($params['offset'])) ? $params['offset'] : FALSE);
		
		$count = 1;
		
		if (preg_match_all('#{packages?(.*?)}(.*?){/packages?}#s', $this->EE->TMPL->tagdata, $matches))
		{
			$package_tagdata = array();
			
			foreach ($matches[0] as $i => $full_match)
			{
				$package_tagdata[substr($full_match, 1, -1)] = $matches[2][$i];
			}
		}
		
		foreach ($data as $i => $row)
		{
			$item_options = array();
			
			$sub_items = array();
			
			$raw_item_options = array();
			
			foreach ($row as $key => $value)
			{
				if (is_array($value))
				{
					if ($key === 'sub_items')
					{
						$sub_items = $value;
						
						continue;
					}
					else
					{
						$row[$key] = $value = implode('|', $value);
					}
				}
				
				if ( ! in_array($key, $this->default_columns)
				    && isset($this->EE->session->cache['cartthrob_order_items']['original_columns'][$row['order_id']][$i])
				    && in_array($key, $this->EE->session->cache['cartthrob_order_items']['original_columns'][$row['order_id']][$i]))
				{
					$raw_item_options[$key] = $value;
				}
			}
			
			if (isset($package_tagdata))
			{
				foreach ($package_tagdata as $full_match => $_package_tagdata)
				{
					$row[$full_match] = '';
					
					foreach ($sub_items as &$sub_item)
					{
						$sub_item_vars = array_merge($sub_item, array_key_prefix($sub_item, 'sub:'));
						
						if (isset($sub_item['entry_id']) && $sub_product = $this->EE->product_model->get_product($sub_item['entry_id']))
						{
							$sub_item_vars = array_merge($this->EE->cartthrob_entries_model->entry_vars($sub_product, $_package_tagdata, 'sub:'), $sub_item_vars );
						}
						
						$row[$full_match] .= $this->EE->TMPL->parse_variables($_package_tagdata, array($sub_item_vars));
					}
				}
			}
			
			if (count($sub_items) > 0)
			{
				$row['is_package'] = 1;
			}
			else
			{
				$row['is_package'] = 0;
			}
			
			$row['count'] = $count;
			
			$row['total_results'] = $total_results;
			
			$row['first_'.$this->row_nomenclature] = (int) ($count === 1);
			
			$row['last_'.$this->row_nomenclature] = (int) ($count === $total_results);
			
			$row[$this->row_nomenclature.'_count'] = $row['count'];
			
			$row['total_'.$this->row_nomenclature.'s'] = $row['total_results'];
		
			if (preg_match_all('/'.LD.'('.preg_quote($this->variable_prefix).'|row_)?'.'switch=([\042\047])(.+)\\2'.RD.'/', $tagdata, $matches))
			{
				foreach ($matches[0] as $j => $v)
				{
					$values = explode('|', $matches[3][$j]);
					
					$row[substr($matches[0][$j], 1, -1)] = $values[($count - 1) % count($values)];
				}
			}
			if (isset($row['price']) && $row['price'] !== '')
			{
				$row['price_numeric'] = $row['price'];
				$row['price'] = $this->EE->number->format($row['price']);
			}
			else
			{
				$row['price_numeric'] = 0; 
			}
			
			if (!isset($row['price_plus_tax'])) 
			{
				$row['price_plus_tax'] = 0; 
			}
			$row['price_numeric:plus_tax']= $row['price_plus_tax_numeric'] = $row['price_plus_tax']; 
			$row['price:plus_tax']= $row['price_plus_tax'] = $this->EE->number->format($row['price_plus_tax']); 
			
			if (isset($row['quantity']) && isset($row['price']))
			{
				$row["subtotal"] = $this->EE->number->format($row['quantity'] * $row['price_numeric']); 
				$row["subtotal_plus_tax"] = $this->EE->number->format($row['quantity'] * $row['price_plus_tax_numeric']); 
			}
			
			$row = array_merge($row, array_key_prefix($row, $this->variable_prefix));
			
			if (isset($row['entry_id']) && $product = $this->EE->product_model->get_product($row['entry_id']))
			{
				$row = array_merge($this->EE->cartthrob_entries_model->entry_vars($product, $tagdata, $this->variable_prefix), $row);

 				foreach ($this->EE->product_model->get_all_price_modifiers($row['entry_id']) as $field_name => $options)
				{
 					if (isset($raw_item_options[$field_name]))
					{
						
						if ( ! $option_label = $this->EE->cartthrob_field_model->get_field_label($this->EE->cartthrob_field_model->get_field_id($field_name)))
						{
							$option_label = $field_name;
						}
						$sub_label = NULL; 
						
						
						$option_price = NULL; 
						foreach ($options as $option_row)
						{
							if ($raw_item_options[$field_name] == element("price", $option_row))
							{
								$option_price = $option_row['price'];
								
								break;
							}
						}
						$option_name = $raw_item_options[$field_name];
						foreach ($options as $option_row)
						{
							if ($raw_item_options[$field_name] == element("option_value", $option_row))
							{
								$option_name = $option_row['option_name'];
								
								break;
							}
						}
						
						$item_options[] = array(
							'option_value' => $raw_item_options[$field_name],
							'option_name' => $option_name,
							'option_label' => $option_label,
							'sub_label'	=> $sub_label,
							'configuration_label'	=>$sub_label,
							'option_price' => $option_price,
							'dynamic' => FALSE,
						);
						
						// later on we'll add in the dynamic options
						unset($raw_item_options[$field_name]); 
 					}
					
					if ( ! isset($row[$field_name]))
					{
						continue;
					}
					
					foreach ($options as $option_row)
					{
						if ($row[$field_name] == element("option_value", $option_row))
						{
							foreach ($option_row as $key => $value)
							{
								$row[$field_name.':'.$key] = $value;
							}
							
							break;
						}
					}
				}
			}
			else
			{
 				if ( ! $option_label = $this->EE->cartthrob_field_model->get_field_label($this->EE->cartthrob_field_model->get_field_id($i)))
				{
					$option_label = $key;
 				}
				$sub_label = NULL; 
				
				$item_options[] = array(
					'option_value' => $value,
					'option_name' => $value,
					'option_label' => $option_label,
					'sub_label'		=> $sub_label,
					'configuration_label'	=> $sub_label,
					'option_price' => NULL,
					'dynamic' => TRUE,
				);
			}
			
			if (isset($raw_item_options))
			{
				foreach ($raw_item_options as $key => $value)
				{
					if ( ! $option_label = $this->EE->cartthrob_field_model->get_field_label($this->EE->cartthrob_field_model->get_field_id($key)))
					{
						$option_label = $key;
	 				}
					$sub_label = NULL; 
					$option_label = ucwords(str_replace("_", " ", $option_label)); 
					
					if (strstr($option_label,":") !== FALSE)
					{
						// get last item after breaking at : so... item_option_x:Color becomes "Color"
 						$sub_label = array_pop(explode(":", $option_label)); 
						// if for some reason it has underscores and stuff... replace them. 
						$sub_label = ucwords(str_replace(array("_", "-"), " ", $sub_label));
					}
					$item_options[] = array(
						'option_value' => $value,
						'option_name' => $value,
						'option_label' => $option_label,
						'option_price' => NULL,
						'sub_label'		=> $sub_label,
						'configuration_label'	=> $sub_label,
						'dynamic' => TRUE,
					);
				}
 			}	
			
 			
			$row['item_options'] = (count($item_options) > 0) ? $item_options : array(array());
			
			$data[$i] = $row;
			
			$count++;
		}
		
		$tagdata = $this->EE->TMPL->parse_variables($tagdata, $data);
		
		//removed unparsed tags
		if ($this->variable_prefix && preg_match_all('/{'.preg_quote($this->variable_prefix).'(.*?)}/', $tagdata, $matches))
		{
			foreach ($matches[0] as $match)
			{
				$tagdata = str_replace($match, '', $tagdata);
			}
		}
		$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
		
		return $tagdata;
	}
	
	public function replace_total_quantity($data, $params = array(), $tagdata = FALSE)
	{
		$total_quantity = 0;
		
		foreach ($data as $row)
		{
			if ( ! isset($row['quantity']))
			{
				continue;
			}
			
			if (isset($row['sub_items']))
			{
				foreach ($row['sub_items'] as $_row)
				{
					if ( ! isset($_row['quantity']))
					{
						continue;
					}
					
					$total_quantity += $row['quantity'] * $_row['quantity'];
				}
			}
			else
			{
				$total_quantity += $row['quantity'];
			}
		}
		
		return $total_quantity;
	}
	
	protected function compile_table_rows($data, $replace_tag = FALSE)
	{
		$this->table_rows = array();
		
		foreach ($data as $row_id => $row)
		{
			$this->table_rows[] = $this->compile_table_row($row, $row_id, $replace_tag);
			
			if (isset($this->sub_items[$row_id]))
			{
				$this->table_rows[] = array('data' => array(array('data' => $this->js_anchor(lang('show_package_details'), '$.cartthrobMatrix.togglePackage(this);'), 'class' => 'center')), 'class' => 'notSortable packageHeader');
				
				foreach ($this->sub_items[$row_id] as $i => $sub_item)
				{
					$this->table_rows[] = $this->compile_table_row($sub_item, $row_id.':'.$i, $replace_tag, FALSE, FALSE, 'package js_hide');
				}
			}
		}
	}
	
	protected function view_vars($replace_tag)
	{
		$vars = parent::view_vars($replace_tag);
		
		foreach ($vars['table_headers'] as $key => $header)
		{
			if ($key === $this->variable_prefix.'view_product_button')
			{
				$vars['table_headers'][$key] = '';
			}
		}
		
		return $vars;
	}
	
	protected function is_column_removable($header)
	{
		if ($header === $this->variable_prefix.'view_product_button')
		{
			return FALSE;
		}
		
		return ( ! isset($this->default_row[$header]));
	}
	
	protected function compile_headers($data)
	{
		$this->headers = array_keys($this->default_row);
		
		foreach ($data as $row_id => $row)
		{
			foreach ($row as $key => $value)
			{
				if ( ! in_array($key, $this->headers) && ! in_array($key, $this->hidden_columns))
				{
					$this->headers[] = $key;
				}
			}
			
			if (isset($this->sub_items[$row_id]))
			{
				foreach ($this->sub_items[$row_id] as $sub_item)
				{
					foreach (array_keys($sub_item) as $key)
					{
						if ( ! in_array($key, $this->headers) && ! in_array($key, $this->hidden_columns))
						{
							$this->headers[] = $key;
						}
					}
				}
			}
		}
		
		if (REQ === 'CP')
		{
			$this->headers[] = 'view_product_button';
		}
	}
	
	public function display_field($data, $replace_tag = FALSE)
	{
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		$this->EE->load->model('order_model');
		
		if ( ! $replace_tag)
		{
			$data = ($this->EE->input->get_post('entry_id'))
				? $this->EE->order_model->get_order_items($this->EE->input->get_post('entry_id', TRUE))
				: array();
		}
		
		$hide_fields = array(
			'weight',
			'shipping',
			'no_tax',
			'no_shipping',
		);
		
		$this->sub_items = array();
		
		foreach ($data as $row_id => $row)
		{
			if (isset($row['sub_items']))
			{
				$this->sub_items[$row_id] = $row['sub_items'];
				
				foreach ($this->sub_items[$row_id] as $sub_item)
				{
					foreach ($hide_fields as $key)
					{
						if ( ! empty($sub_item[$key]) && $sub_item[$key] != 0)
						{
							unset($hide_fields[array_search($key, $hide_fields)]);
						}
					}
				}
			}
			
			unset($data[$row_id]['row_order'], $data[$row_id]['order_id'], $data[$row_id]['extra'], $data[$row_id]['sub_items']);
			
			foreach ($hide_fields as $key)
			{
				if ( ! empty($row[$key]) && $row[$key] != 0)
				{
					unset($hide_fields[array_search($key, $hide_fields)]);
				}
			}
		}
		
		$this->hidden_columns = $hide_fields;
		$this->hidden_columns[] = 'row_id';
		
		$output = parent::display_field($data, $replace_tag);
		
		if ( ! $replace_tag && empty($this->EE->session->cache['cartthrob_order_items']['head']))
		{
			$this->EE->load->library('javascript');
			
			$this->EE->session->cache['cartthrob_order_items']['head'] = TRUE;
			
			$lang = array(
				'show_package_details' => lang('show_package_details'),
				'hide_package_details' => lang('hide_package_details'),
			);
			
			$this->EE->cp->add_to_head('<style type="text/css">.cartthrobOrderItems tbody tr.packageHeader td, .cartthrobOrderItems tbody tr.package td { background-color: #fafafa; }</style>');
			$this->EE->cp->add_to_foot('
			<script type="text/javascript">
			$.extend($.cartthrobMatrix.lang, '.json_encode($lang).');
			$.cartthrobMatrix.togglePackage = function(e){
				if ($(e).text() == $.cartthrobMatrix.lang.show_package_details) {
					$(e).html($.cartthrobMatrix.lang.hide_package_details);
				} else {
					$(e).html($.cartthrobMatrix.lang.show_package_details);
				}
				var next = $(e).parents("tr").next();
				while(next.hasClass("package")) {
					next = next.toggle().next();
				}
			};
			</script>
			');
			
			$this->EE->javascript->output('
			$("table.cartthrobOrderItems").bind("sortstart", function(e, ui) {
				var pkg = [];
				var row = $(ui.item);
				var next = row.next().next();
				if (next.hasClass("packageHeader")) {
					pkg.push(next);
					next = next.hide().next();
					while(next.hasClass("package")) {
						pkg.push(next);
						next = next.hide().next();
					}
				}
				$(e.target).sortable("option", "package", pkg);
			}).bind("sortstop", function(e, ui) {
				var current = $(ui.item);
				$.each($(e.target).sortable("option", "package"), function(i, row) {
					row.insertAfter(current);
					if (row.hasClass("packageHeader")) {
						row.show();
					}
					current = row;
				});
			});
			$("input.cartthrobOrderItemsEntryId").live("change", function(){
				$(this).parents("tr").find("a.view_product_button").attr("href", EE.BASE+"&C=content_publish&M=entry_form&entry_id="+$(this).val());
			});
			');
		}
		$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
		
		return $output;
	}

	
	public function save_settings($data)
	{
		// order items field can't be named items
		if ($this->EE->input->get_post('field_name') == 'items')
		{
			return show_error(lang('order_items_field_must_not_be_named_items'));
		}
		$data['field_fmt'] = 'none';

		return $data;
	}
	
	public function save($data)
	{
		$this->EE->session->cache['cartthrob_order_items'][$this->field_id] = NULL;
		
		if (is_array($data))
		{
			//if there's just one empty row
			if (count($data) === 1 && count(array_filter(current($data))) === 0)
			{
				$this->EE->session->cache['cartthrob_order_items'][$this->field_id] = array();
			
				return '';
			}
			
			$this->EE->session->cache['cartthrob_order_items'][$this->field_id] = $data;
			
			return 1;
		}
		
		return '';
	}
	
	public function post_save($data)
	{
		//if it's not set, it means save() was never called, which means it's most likely a channel_form not currently editing this field
		if (isset($this->EE->session->cache['cartthrob_order_items'][$this->field_id]))
		{
			$data = $this->EE->session->cache['cartthrob_order_items'][$this->field_id];

			unset($this->EE->session->cache['cartthrob_order_items'][$this->field_id]);
			
			$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
			
			$this->EE->load->model('order_model');
			
			$this->EE->order_model->update_order_items($this->settings['entry_id'], $data);
			$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
		}
	}
	
	public function delete($entry_ids)
	{
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		
		$this->EE->load->model('order_model');
		
		$this->EE->order_model->delete_order_items($entry_ids);
		
		$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
		
	}
	
	public function display_field_entry_id($name, $value, $row, $index, $blank = FALSE)
	{	
		return form_input(array(
			'name' => $name,
			'value' => $value,
			'class' => 'cartthrobOrderItemsEntryId',
		));
	}
	
	public function display_field_view_product_button($name, $value, $row, $index, $blank = FALSE)
	{
		$this->EE->load->helper('html');
		
		return anchor(BASE.AMP.'C=content_publish'.AMP.'M=entry_form'.AMP.'entry_id='.$row['entry_id'], lang('view'), 'target="_blank" class="view_product_button"');
	}
}

/* End of file ft.cartthrob_discount.php */
/* Location: ./system/expressionengine/third_party/cartthrob_discount/ft.cartthrob_discount.php */