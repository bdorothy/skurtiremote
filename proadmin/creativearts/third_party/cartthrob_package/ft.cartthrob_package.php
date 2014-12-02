<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once PATH_THIRD.'cartthrob/config.php';
require_once PATH_THIRD.'cartthrob/fieldtypes/ft.cartthrob_matrix.php';

/**
 * @property CI_Controller $EE
 */
class Cartthrob_package_ft extends Cartthrob_matrix_ft
{
	public $info = array(
		'name' => 'CartThrob Package',
		'version' => CARTTHROB_VERSION,
	);
	
	public $default_row = array(
		'entry_id' => 0,
		'title' => 0,
		'description' => '',
		'option_presets' => '',
		'allow_selection' => '',
	);
	
	public $buttons = array();
	
	public $show_default_row = FALSE;
	
	//public $hidden_columns = array();
	
	//public $additional_controls = '';
	
	//public $variable_prefix = '';
	
	//public $row_nomenclature = '';
	
	public function __construct()
	{
		parent::__construct();
	}
	function install()
	{
	    return array(
	        'field_prefix'  => '$',
	    );
	}
	public function pre_process($data)
	{
		//unserializes it
		$data = parent::pre_process($data);
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		
		$this->EE->load->library('data_filter');
		
		$this->EE->load->model('cartthrob_entries_model');
		
		if ( array_key_exists('price', $data))
		{
			$price =  element('price', $data); 
			unset($data['price']);
		}
		
		//get the entry_ids from the array
		$entry_ids = $this->EE->data_filter->key_values($data, 'entry_id');
		
		//preload all the entries pertaining to this package
		$this->EE->cartthrob_entries_model->load_entries_by_entry_id($entry_ids);
		$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
		
		return $data;
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
	
	public function display_settings($data)
	{
		$field_maxl = (empty($data['field_maxl'])) ? 12 : $data['field_maxl'];
		
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		
		$this->EE->load->library('cartthrob_loader');
		
		$field_prefix = (empty($data['field_prefix'])) ? $this->EE->cartthrob->store->config('number_format_defaults_prefix') : $data['field_prefix'];
		
		$this->EE->table->add_row(
			lang('field_max_length', 'field_maxl'),
			form_input(
				array('id' => 'cartthrob_package_field_maxl', 'name' => 'field_maxl_cpk', 'size' => 4, 'value' => $field_maxl)
				)
		);
		
		$this->EE->table->add_row(
			lang('number_format_defaults_prefix', 'field_prefix'),
			form_input(
				array('id' => 'cartthrob_package_field_prefix', 'name' => 'field_prefix_cpk', 'size' => 4, 'value' => $field_prefix)
				)
		);
 		$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
		
	}
	
	public function display_field($data, $replace_tag = FALSE)
	{
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		
		if ( ! isset($this->EE->session->cache['cartthrob_package']['head']))
		{
			$this->EE->session->cache['cartthrob_package']['head'] = TRUE;
			
			$this->EE->cp->add_to_head('
			<style type="text/css">
			.cartthrobPackageFilter {
				border: 1px solid #D0D7DF;
				border-top-width: 0;
				padding: 15px;
			}
			table.cartthrobPackage th:nth-child(2), table.cartthrobPackage th:nth-child(5), table.cartthrobPackage th:nth-child(6) {
				width: 1%;
			}
			table.cartthrobPackage td:nth-child(2) {
				text-align: center;
			}
			table.cartthrobPackage td:nth-child(2), table.cartthrobPackage td:nth-child(5), table.cartthrobPackage td:nth-child(6) {
				padding-right: 10px;
			}
			table.cartthrobPackageOptionPresets {
				table-layout: fixed;
				margin: 0 auto;
				border-collapse: collapse;
			}
			ul.cartthrobPackageFilteredEntries {
				background-color: white;
				list-style: none;
				margin: 10px 0 0;
				padding: 0;
				text-indent: 0;
				border: 1px solid #D0D7DF;
				height: 240px; /* 8 li */
				overflow: auto;
				overflow-x: none;
				overflow-y: scroll;
			}
			ul.cartthrobPackageFilteredEntries li {
				background-color: white;
				cursor: pointer;
				padding: 8px;
				height: 14px;
			}
			ul.cartthrobPackageFilteredEntries li:hover {
				background-color:#CCE6FF;
			}
			table.cartthrobMatrix table.cartthrobPackageOptionPresets td {
				border: 0 !important;
				padding: 0 !important;
				height: 28px;
				overflow: hidden;
				white-space: nowrap;
			}
			table.cartthrobMatrix table.cartthrobPackageOptionPresets td label {
				padding-right: 15px;
			}
			</style>
			');
			
			$this->EE->lang->loadfile('cartthrob');
			
			$this->EE->load->library('javascript');
			
			$this->EE->cp->add_js_script(array('ui' => array('autocomplete')));
			
			$this->EE->javascript->output('
			$(".cartthrobPackageFilter").parent().css("marginTop", 0);
			EE.cartthrobPackage = {
				currentFilter: {
					id: 0,
					xhr: null,
					entries: {},
				},
				getFilters: function(package, exclude_keywords){
					var filter = {};
					var selector = ":input";
					if (exclude_keywords === true) {
						selector += ":not(.keywords)";
					}
					$(package).next(".cartthrobMatrixControls").find(".cartthrobPackageFilter").children(selector).each(function(){
						filter[$(this).attr("class")] = $(this).val();
					});
					return filter;
				},
				showFilteredEntries: function(package) {
					if (EE.cartthrobPackage.currentFilter.xhr !== null) {
						console.log(EE.cartthrobPackage.currentFilter.xhr);
						EE.cartthrobPackage.currentFilter.xhr.abort();
					}
					EE.cartthrobPackage.currentFilter.id++;
					var filter = {
						CSRF_TOKEN: EE.CSRF_TOKEN,
						C: "addons_modules",
						M: "show_module_cp",
						module: "cartthrob",
						method: "package_filter",
						filter_id: EE.cartthrobPackage.currentFilter.id
					};
					$.extend(filter, EE.cartthrobPackage.getFilters(package));
					var list = $(package).next(".cartthrobMatrixControls").find(".cartthrobPackageFilteredEntries");
					var color = list.css("color");
					list.children("li").animate({color: "#999"}, 100);
					EE.cartthrobPackage.currentFilter.xhr = $.getJSON(EE.BASE, filter, function(data) {
						if (data.id != EE.cartthrobPackage.currentFilter.id) {
							return;
						}
						list.html("");
						$.each(data.entries, function(i, entry){
							EE.cartthrobPackage.currentFilter.entries[entry.entry_id] = entry;
							list.append($("<li />", {text: entry.title+" (id: "+entry.entry_id+")", rel: entry.entry_id, "class": "entry"}).css({color: "#999"}));
						});
						if (data.entries.length === 0) {
							list.append($("<li />", '.json_encode(array('text' => $this->EE->lang->line('no_products_in_search'))).'));
						}
						list.children("li").animate({color: color}, 100);
					});
				},
				loadEntry: function(entryID, package){
					var entry = EE.cartthrobPackage.currentFilter.entries[entryID];
					var row = $.cartthrobMatrix.addRow(package);
					row.find(".title").html(entry.title);
					row.find(".entry_id:not(:input)").html(entry.entry_id);
					row.find(".entry_id:input").val(entry.entry_id);
					var fieldName = row.find(".entry_id:input").attr("name").replace("entry_id", "NAME");
					var optionPresets = "<table border=\'0\' cellpadding=\'0\' cellspacing=\'0\' class=\'cartthrobPackageOptionPresets\'><tbody>";
					var allowSelection = optionPresets;
					$.each(entry.price_modifiers, function(priceModifier, data){
						var options = $.extend({}, data);
						var label = options.label;
						delete options.label;
						if ($.isEmptyObject(options)) {
							return;
						}
						allowSelection += "<tr><td><input type=\'checkbox\' value=\'1\' name=\'"+fieldName.replace("NAME", "allow_selection")+"["+priceModifier+"]\'></td></tr>";
						optionPresets += "<tr><td><label>"+label+"</label></td><td><select name=\'"+fieldName.replace("NAME", "option_presets")+"["+priceModifier+"]\'>";
						optionPresets += "<option>-----</option>";
						$.each(options, function(i, option){
							optionPresets += "<option value=\'"+option.option_value+"\'>"+option.option_name+"</option>";
						});
						optionPresets += "</select></td></tr>";
					});
					optionPresets += "</tbody></table>";
					allowSelection += "</tbody></table>";
					row.children("td:eq(4)").html(optionPresets);
					row.children("td:eq(5)").html(allowSelection);
				}
			};
			$(".cartthrobPackageFilter :input").bind("change", function(event){
				EE.cartthrobPackage.showFilteredEntries($(event.target).parents(".cartthrobMatrixControls").prev("table.cartthrobPackage"));
			}).bind("keypress", function(event){
				if (event.keyCode === 13) {
					return false;
				}
			});
			$(".cartthrobPackageFilter input.keywords").bind("keyup", function(event){
				EE.cartthrobPackage.showFilteredEntries($(event.target).parents(".cartthrobMatrixControls").prev("table.cartthrobPackage"));
			});
			$(".cartthrobPackageFilteredEntries li.entry").live("click", function(event){
				EE.cartthrobPackage.loadEntry($(event.target).attr("rel"), $(event.target).parents(".cartthrobMatrixControls").prev("table.cartthrobPackage"));
			});
			
			//call it on load
			EE.cartthrobPackage.showFilteredEntries($("table.cartthrobPackage"));
			');
		}
		
		$data = $this->pre_process($data);
 		$price = NULL;
		if ( array_key_exists('price', $data))
		{
			$price =  element('price', $data); 
			unset($data['price']);
		}
		$vars['categories'] = array('' => lang('filter_by_category'));
		
		$this->EE->load->model('cartthrob_settings_model');
		
		$channel_ids = $this->EE->config->item('cartthrob:product_channels');
		
		if ( ! $channel_ids)
		{
			$vars['channels'] = array('X' => lang('no_product_channels'));
		}
		else
		{
			$vars['channels'] = array();
			
			$vars['channels']['null'] = lang('filter_by_channel');
	
			/*
			if (count($channel_ids) > 1)
			{
				$vars['categories']['all'] = $vars['channels']['all'] = lang('all');
			}
			*/
			
			$vars['categories']['none'] = lang('none');
			
			$this->EE->load->model('channel_model');
			
			$query = $this->EE->channel_model->get_channels(NULL, array('channel_id', 'channel_title', 'cat_group'), array(array('channel_id' => $channel_ids)));
			
			$used_cat_groups = array();
	
			foreach ($query->result() as $row)
			{
				$vars['channels'][$row->channel_id] = $row->channel_title;
				
				if ($row->cat_group)
				{
					$this->EE->load->model('category_model');
					
					$cat_groups = explode('|', $row->cat_group);
					
					foreach ($cat_groups as $group_id)
					{
						if (in_array($group_id, $used_cat_groups))
						{
							continue;
						}
						
						$used_cat_groups[] = $group_id;
						
						$categories = $this->EE->category_model->get_channel_categories($group_id);
						
						if ($categories->num_rows() > 0)
						{
							$vars['categories']['NULL_1'] = '-------';
							
							foreach($categories->result() as $row)
							{
								$vars['categories'][$row->cat_id] = $row->cat_name;
							}
						}
					}
				}
			}
		}
		
		if (version_compare(APP_VER, '2.2', '<'))
		{
			$orig_view_path = $this->EE->load->_ci_view_path;
			
			$this->EE->load->_ci_view_path = PATH_THIRD.'cartthrob/views/';
			
			$this->additional_controls = $this->EE->load->view('cartthrob_package_filter', $vars, TRUE);
			
			$this->EE->load->_ci_view_path = $orig_view_path;
		}
		else
		{
			$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
			
			$this->additional_controls = $this->EE->load->view('cartthrob_package_filter', $vars, TRUE);
		}
		
		/// Display Price Field
		$channel_id = $this->EE->input->get('channel_id'); 
		
		if ($channel_id && $this->field_id == array_value($this->EE->config->item('cartthrob:product_channel_fields'), $channel_id, 'price'))
		{
			$prefix = $this->get_prefix();

			$span = '<span style="position:absolute;padding:5px 0 0 5px;">'.$prefix.'</span>';

				$this->EE->javascript->output('
					var span = $(\''.$span.'\').appendTo("body").css({top:-9999});
					var indent = span.width()+4;
					span.remove();

				$("#'.$this->field_id."_price".'").before(\''.$span.'\');
				$("#'.$this->field_id."_price".'").css({paddingLeft: indent});
				');

			if (empty($this->settings['field_maxl']))
			{
				$this->settings['field_maxl'] = 4; 
			}
			$this->additional_controls .= 
			 form_label('<br><strong class="title">'.$this->EE->lang->line('cartthrob_package_price').'</strong>', $this->field_name."_price").
			 form_input(array(
				'name' => 'field_id_'.$this->field_id.'[price]',
				'id' => $this->field_id."_price",
				//'class' => 'cartthrob_price_simple',
				'value' =>   $price,
				'maxlength' => $this->settings['field_maxl']
			));
		}
 		////
		$output = parent::display_field($data, $replace_tag);
		$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
		
		return $output;
	}
	public function replace_price($data, $params= array(), $tagdata = FALSE)
	{
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		$this->EE->load->library('number');
 		$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
		
		return $this->EE->number->format($this->cartthrob_price($data));
	}
 
	public function replace_plus_tax($data, $params = '', $tagdata = '')
	{
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		
		$this->EE->load->library('cartthrob_loader');
		
		$this->EE->load->library('number');

		if ($plugin = $this->EE->cartthrob->store->plugin($this->EE->cartthrob->store->config('tax_plugin')))
		{
			$data = $plugin->get_tax($this->cartthrob_price($data)) + $this->cartthrob_price($data);
		}
		$this->EE->number->set_prefix( $this->get_prefix() ); 
 		$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
		return $this->EE->number->format($data);
	}
	public function replace_no_tax($data, $params = '', $tagdata = '')
	{
		return $this->replace_tag($data, $params, $tagdata); 
	}
 
	public function replace_plus_tax_numeric($data, $params = '', $tagdata = '')
	{
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		
		$this->EE->load->library('cartthrob_loader');
		
		$this->EE->load->library('number');

		if ($plugin = $this->EE->cartthrob->store->plugin($this->EE->cartthrob->store->config('tax_plugin')))
		{
			$data = $plugin->get_tax($this->cartthrob_price($data)) + $this->cartthrob_price($data);
		}
 		$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
		return $data; 
	}
	public function replace_numeric($data, $params = '', $tagdata = '')
	{
		return $this->cartthrob_price($data);
	}
	public function cartthrob_price($data, $item = NULL)
	{
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		
		$this->EE->load->model(array('product_model', 'cartthrob_entries_model', 'cartthrob_field_model'));
		$this->EE->load->helper(array('array'));
		
		$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
		
		if ($item instanceof Cartthrob_item)
		{
			return $item->price(); 
		}
		if ( ! is_array($data))
		{
			$serialized = $data;
			
			if ( ! isset($this->EE->session->cache['cartthrob']['package']['cartthrob_price'][$serialized]))
			{
				$this->EE->session->cache['cartthrob']['package']['cartthrob_price'][$serialized] = _unserialize($data, TRUE);
			}
			
			$data = $this->EE->session->cache['cartthrob']['package']['cartthrob_price'][$serialized];
		}
		reset($data); 
		$price = 0; 
		
 		foreach ($data as $key => $value)
		{
			// skip the price field.
			if (!is_numeric($key))
			{
				if ($key == "price" && $value !== "" && $value !== NULL && $value !== FALSE)
				{
					var_dump($value);
					return $value; 
				}
				continue;
			}
			$entry_id = $value['entry_id']; 
			$price += trim($this->EE->product_model->get_base_price($entry_id)); 
			
 			if (isset($value['option_presets']))
			{
				foreach ($value['option_presets'] as $field_name => $option_value)
				{
 					 $item_option = $this->EE->product_model->get_price_modifier_value($entry_id, $field_name, $option_value); 
  					if (element('price', $item_option) !== FALSE)
					{
						$price += trim(element('price', $item_option, 0)); 
					}
				}
			}
		}
 		return $price;
	}
	public function replace_tag($data, $params = array(), $tagdata = FALSE)
	{
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		
		$this->EE->load->model(array('product_model', 'cartthrob_entries_model'));
		
		// don't want the STRING price being converted into a row. we only need the price for the :price tag
		if (array_key_exists("price", $data))
		{
			unset($data['price']);
		}
		foreach ($data as $row_id => $row)
		{
			if (isset($row['entry_id']) && $product = $this->EE->product_model->get_product($row['entry_id']))
			{
				if (!empty($row['option_presets']) && !isset($row['allow_selection']))
				{
					$row['allow_selection'] = array(); 
				}
 				$row['parent_id'] = $row['sub:parent_id']= $this->content_id();
				$row['row_id'] =$row['sub:row_id'] = $this->content_id().':'.$row_id.":";
				$row['identifier'] =$row['sub:identifier'] = ':'.$row_id;
				$row['child_id'] = $row['sub:child_id'] = $row_id;
				
				$data[$row_id] = array_merge($row, $this->EE->cartthrob_entries_model->entry_vars($product, $tagdata, 'sub:'));
			}
		}
		$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
		
		return parent::replace_tag($data, $params, $tagdata);
	}
	
	
	public function display_field_entry_id($name, $value, $row, $index, $blank = FALSE)
	{
		$output = '';
		
		//$output .= '<strong class="title">'.element('title', $product).'</strong>'.NBS.'(id: <span class="entry_id">'.$value.'</span>)';//.NBS.NBS.NBS.anchor('#', 'change &raquo;');
		$output .= '<span class="entry_id">'.$value.'</span>';
		
		$attributes = array(
			'type' => 'hidden',
			'name' => $name,
			'value' => $value,
			'class' => 'entry_id',
		);
		
		if ($blank)
		{
			$attributes['disabled'] = 'disabled';
		}
		
		$output .= '<input '._parse_attributes($attributes).'>';
		
		return $output;
	}
	
	public function display_field_title($name, $value, $row, $index, $blank = FALSE)
	{
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		
		$title = '';
		
		if ( ! empty($row['entry_id']))
		{
			$this->EE->load->model('product_model');
			
			$this->EE->load->helper(array('array', 'html'));
			
			$product = $this->EE->product_model->get_product($row['entry_id']);
			
			$title = element('title', $product);
			
			if ($product)
			{
				$title = anchor(BASE.AMP.'C=content_publish'.AMP.'M=entry_form'.AMP.'entry_id='.$row['entry_id'], $title, array('target' => '_blank'));
			}
		}
		$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
		
		return '<strong class="title">'.$title.'</strong>';
	}
	
	public function display_field_option_presets($name, $value, $row, $index, $blank = FALSE)
	{
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
	
		if (empty($row['entry_id']))
		{
			return NBS;
		}
		
		$ol = array();
		
		$this->EE->load->model('product_model');
		
		$this->EE->load->helper('array');
		
		$price_modifiers = $this->EE->product_model->get_all_price_modifiers($row['entry_id']);
		
		if ( ! $price_modifiers)
		{
			return NBS;
		}
		
		$this->EE->load->model('cartthrob_field_model');
		
		$this->EE->load->library('table');
		
		//i already know the table lib is loaded
		$table = new CI_Table();
		
		$table->set_template(array('table_open' => '<table border="0" cellpadding="0" cellspacing="0" class="cartthrobPackageOptionPresets">'));
		
		foreach ($price_modifiers as $field_name => $options)
		{
			if (count($options) === 0)
			{
				continue;
			}
			
			$select_options = array('' => '-----');
			
			foreach ($options as $option)
			{
				$select_options[$option['option_value']] = $option['option_name'];
			}
			
			$label = $this->EE->cartthrob_field_model->get_field_label($this->EE->cartthrob_field_model->get_field_id($field_name));
			
			$input_name = $name.'['.$field_name.']';
			
			$attributes = array('id' => $input_name);
			
			if ($blank)
			{
				$attributes['disabled'] = 'disabled';
			}
			
			$table->add_row(form_label($label, $input_name), form_dropdown($input_name, $select_options, element($field_name, $value), _parse_attributes($attributes)));
		}
		$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
		
		return ($table->rows) ? $table->generate() : NBS;
	}
	
	public function display_field_allow_selection($name, $value, $row, $index, $blank = FALSE)
	{
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		if (empty($row['entry_id']))
		{
			return NBS;
		}
		
		$ol = array();
		
		$this->EE->load->model('product_model');
		$this->EE->load->helper('array');
		$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
		
		$price_modifiers = $this->EE->product_model->get_all_price_modifiers($row['entry_id']);
		
		if ( ! $price_modifiers)
		{
			return NBS;
		}
		
		$table = new CI_Table();
		
		$table->set_template(array('table_open' => '<table border="0" cellpadding="0" cellspacing="0" class="cartthrobPackageOptionPresets">'));
		
		foreach ($price_modifiers as $field_name => $options)
		{
			if (count($options) === 0)
			{
				continue;
			}
			
			$extra = ($blank) ? 'disabled="disabled"' : '';
			
			$table->add_row(form_checkbox($name.'['.$field_name.']', '1', element($field_name, $value), $extra));
		}
		
		return ($table->rows) ? $table->generate() : NBS;
	}
}

/* End of file ft.cartthrob_discount.php */
/* Location: ./system/expressionengine/third_party/cartthrob_discount/ft.cartthrob_discount.php */