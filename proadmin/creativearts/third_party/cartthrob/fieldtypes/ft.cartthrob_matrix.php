<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once PATH_THIRD.'cartthrob/config.php';

/**
 * @property CI_Controller $EE
 * @property Cartthrob_core_ee $cartthrob;
 * @property Cartthrob_cart $cart
 * @property Cartthrob_store $store
 */
class Cartthrob_matrix_ft extends EE_Fieldtype
{
	public $has_array_data = TRUE;
	
	public $default_row = array();
	
	public $show_default_row = TRUE;
	
	public $buttons = array(
		'add_row' => '$.cartthrobMatrix.addRow($(this).parents(\'div.cartthrobMatrixControls\').prev(\'table.cartthrobMatrix\'));',
		'add_column' => '$.cartthrobMatrix.addColumn($(this).parents(\'div.cartthrobMatrixControls\').prev(\'table.cartthrobMatrix\'));',
	);
	
	public $hidden_columns = array();
	
	public $additional_controls = '';
	
	public $variable_prefix = '';
	
	public $row_nomenclature = 'row';
	
	public $data;
	
	public $headers = array();
	
	public $table_rows = array();
	
	public $table_headers = array();

    public $prefix_only = FALSE;
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function pre_process($data)
	{
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		$this->EE->load->helper('data_formatting');
		$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
		
		return _unserialize($data, TRUE);
	}
	
	protected function compile_headers($data)
	{
		$this->headers = array_keys($this->default_row);
		
		foreach ($data as $row)
		{
			foreach ($row as $key => $value)
			{
				if ( ! in_array($key, $this->headers) && ! in_array($key, $this->hidden_columns))
				{
					$this->headers[] = $key;
				}
			}
		}
	}
	
	protected function compile_table_row($row, $row_id, $replace_tag = FALSE, $sortable = TRUE, $removable = TRUE, $class = '')
	{
		$new_row = array('data' => array());
		
		if ( ! $replace_tag)
		{
			if ( ! $sortable)
			{
				$new_row['data'][] = '&nbsp;';
			}
			else
			{
				$new_row['data'][] = array(
					'data' => '&nbsp;',
					'class' => 'drag_handle',
				);
			}
		}
		
		foreach ($this->headers as $index => $header)
		{
			$index += 1;
			
			$new_row['data'][$index] = (isset($row[$header])) ? $row[$header] : '';
			
			if ( ! $replace_tag)
			{
				$method = 'display_field_'.$header;
				
				if (method_exists($this, $method))
				{
					$new_row['data'][$index] = $this->$method($this->field_name.'['.$row_id.']['.$header.']', $new_row['data'][$index], $row, $index);
				}
				else
				{
					if (is_array($new_row['data'][$index]))
					{
						$new_row['data'][$index] = implode('|', $new_row['data'][$index]);
					}
					
					if ( ! preg_match('/[\r\n]/', $new_row['data'][$index]))
					{
						$new_row['data'][$index] = form_input($this->field_name.'['.$row_id.']['.$header.']', $new_row['data'][$index]);
					}
					else
					{
						$new_row['data'][$index] = form_textarea(array('name' => $this->field_name.'['.$row_id.']['.$header.']', 'value' => $new_row['data'][$index], 'rows' => 3));
					}
				}
			}
		}
		
		if ( ! $replace_tag)
		{
			if ( ! $removable)
			{
				$last_col = array('data' => '&nbsp;');
			}
			else
			{
				$last_col = array(
					'data' => $this->js_anchor(
						img(array(
							'border' => '0',
							'src' => $this->EE->config->item('theme_folder_url').'cp_themes/default/images/content_custom_tab_delete.png'
						)),
						'$.cartthrobMatrix.removeRow(this);'
					),
					'class' => 'remove',
				);
			}
			
			foreach ($this->hidden_columns as $col)
			{
				$last_col['data'] .= form_hidden($this->field_name.'['.$row_id.']['.$col.']', (isset($row[$col])) ? $row[$col] : '');
			}
			
			$new_row['data'][] = $last_col;
		}
		
		if ( ! $sortable)
		{
			$new_row['class'] = ($class) ? $class.' notSortable' :'notSortable';
		}
		
		return $new_row;
	}
	
	protected function compile_table_rows($data, $replace_tag = FALSE)
	{
		$this->table_rows = array();
		
		foreach ($data as $row_id => $row)
		{
			$this->table_rows[$row_id] = $this->compile_table_row($row, $row_id, $replace_tag);
		}
	}
	
	protected function compile_table_headers($data, $replace_tag = FALSE)
	{
		$this->table_headers = array();
		
		foreach ($this->headers as $header)
		{
			if ($header)
			{
				//get the lang key, ie cartthrob_matrix_your_header, carthrob_package_some_header, etc.
				$key = strtolower(str_replace('_ft', '', get_class($this))).'_'.$header;
				$table_header = lang($key);
				
				//there is NO a lang key for this header and fieldtype, look for cartthrob_matrix
				if ($table_header === $key)
				{
					$table_header = lang('cartthrob_matrix_'.$header);
					
					//there is NO a lang key for this header, show the template tag by default
					if ($table_header === 'cartthrob_matrix_'.$header)
					{
						$table_header = $header;
						
						if ( ! array_key_exists($header, $this->default_row))
						{
							$header = $this->variable_prefix.$header;
						}
					}
				}
				
				if ( ! $replace_tag)
				{
					$table_header = '<span>'.$table_header.'</span>';
				}
				
				//add remove column button
				if ( ! $replace_tag && $this->is_column_removable($header))
				{			
					$table_header .= $this->js_anchor(img(array('border' => '0', 'src' => $this->EE->config->item('theme_folder_url').'cp_themes/default/images/content_custom_tab_delete.png')), '$.cartthrobMatrix.removeColumn(this);', array('class' => 'remove_column'));
				}
				
				$this->table_headers[$header] = $table_header;
			}
			else
			{
				$this->table_headers[] = '';
			}
		}
		
		if ( ! $replace_tag)
		{
			array_unshift($this->table_headers, '');
		}
		
		$this->table_headers[] = '';
	}
	
	protected function is_column_removable($header)
	{
		return ( ! isset($this->default_row[$header]));
	}
	
	protected function compile_table($data, $replace_tag = FALSE)
	{
		$this->compile_headers($data);
		
		$this->compile_table_rows($data, $replace_tag);
		
		$this->compile_table_headers($data, $replace_tag);
	}
	
	public function display_field($data, $replace_tag = FALSE)
	{
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		$this->EE->load->helper(array('url', 'html'));
		$this->EE->load->helper('data_formatting');
		$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
		
		if ( ! is_array($data))
		{
			$data = _unserialize($data, TRUE);
		}
		
		if ( ! $data && $this->show_default_row)
		{
			$data = array($this->default_row);
		}
		
		$output = '';//form_hidden($this->field_name, '');
		
		reset($data);
		
		$this->compile_table($data, $replace_tag);
		
		if ( ! $replace_tag)
		{
			if (empty($this->EE->session->cache['cartthrob_matrix']['head']))
			{
				$this->EE->load->library('javascript');
				
				$this->EE->cp->add_to_head('<link rel="stylesheet" href="'.URL_THIRD_THEMES.'cartthrob/css/cartthrob_matrix.css" type="text/css" media="screen" />');
				
				$this->EE->cp->add_js_script(array('ui' => array('sortable')));
				
				if (REQ != 'CP')
				{
					$this->EE->cp->add_to_head('<link rel="stylesheet" href="'.URL_THIRD_THEMES.'cartthrob/css/cartthrob_matrix_table.css" type="text/css" media="screen" />');
				}
				
				$lang = array(
					'remove_row_confirm' => lang('remove_row_confirm'),
					'remove_column_confirm' => lang('remove_column_confirm'),
					'name_column_prompt' => lang('name_column_prompt'),
				);
				
				$this->EE->cp->add_to_foot('
				<script type="text/javascript">
				$.cartthrobMatrix = {
					lang: '.json_encode($lang).',
					blankRows: {},
					hiddenRows: {},
					variablePrefix: {},
					handleButton: \'&nbsp;\',
					removeRowButton: \''.$this->js_anchor(img(array('border' => '0', 'src' => $this->EE->config->item('theme_folder_url').'cp_themes/default/images/content_custom_tab_delete.png')), '$.cartthrobMatrix.removeRow(this);').'\',
					removeColumnButton: \''.$this->js_anchor(img(array('border' => '0', 'src' => $this->EE->config->item('theme_folder_url').'cp_themes/default/images/content_custom_tab_delete.png')), '$.cartthrobMatrix.removeColumn(this);', array('class' => 'remove_column')).'\',
					removeRow: function(e) {
						var table = $(e).parents("table");
						if (confirm($.cartthrobMatrix.lang.remove_row_confirm))
						{
							var row = $(e).parents("tr");
							var next = row.next();
							var pkg = [];
							if (next.hasClass("packageHeader")) {
								pkg.push(next);
								next = next.next();
								while(next.hasClass("package")) {
									pkg.push(next);
									next = next.next();
								}
							}
							for (i in pkg){
								pkg[i].remove();
							}
							if (table.find("tbody tr").length === 1) {
								$.cartthrobMatrix.addRow(table);
							}
							row.remove();
						}
						$.cartthrobMatrix.resetRows(table);
					},
					clearRows: function(e) {
						$(e).children("tbody").children("tr").remove();
					},
					addRow: function(e) {
						var row = "<tr><td class=\'drag_handle\'>"+$.cartthrobMatrix.handleButton+"</td>";
						var fieldName = $(e).attr("id");
						$.each($.cartthrobMatrix.blankRows[fieldName], function(i,value){
							row += "<td>"+value+"</td>";
						});
						row += "<td class=\'remove\'>"+$.cartthrobMatrix.removeRowButton;
						$.each($.cartthrobMatrix.hiddenRows[fieldName], function(i,value){
							row += value;
						});
						row += "</td></tr>";
						row = $(e).children("tbody").append(row).children("tr:last");
						row.find(":input").attr("disabled", false);
						row.find(":input").eq(0).focus();
						$.cartthrobMatrix.resetRows($(e));
						return row;
					},
					removeColumn: function(e) {
						if (confirm($.cartthrobMatrix.lang.remove_column_confirm))
						{
							var index = $(e).parent().index();
							var fieldName = $(e).parents("table").attr("id");
							$(e).parents("table").children("tbody").children("tr").each(function(){
								$(this).children("td").eq(index).remove();
							});
							$.cartthrobMatrix.blankRows[fieldName].splice(index, 1);
							$(e).parent().remove();
						}
					},
					resetRows: function(e) {
						var index = -1;
						$(e).children("tbody").children("tr").each(function(){
							if ($(this).is(":not(.notSortable)")) {
								var mod = (index % 2) ? "odd" : "even";
								$(this).removeClass("even").removeClass("odd");
								$(this).addClass(mod);
								index++;
							}
							$(this).find(":input").each(function(){
								$(this).attr("name", $(this).attr("name").replace(/^(.*?)\[.*?(:.*)?\](.*?)$/, "$1["+index+"$2]$3"));
							});
						});
					},
					addColumn: function(e, name) {
						var fieldName = $(e).attr("id");
						if ( ! name) {
							name = $.trim(prompt($.cartthrobMatrix.lang.name_column_prompt)).toLowerCase().replace(/\s/g, "_").replace(/[^a-z0-9_]/g, "");
						}
						if ( ! name) {
							return;
						}
						var tag = name;
						if (this.variablePrefix[fieldName] !== undefined) {
							tag = this.variablePrefix[fieldName]+tag;
						}
						tag = "{"+tag+"}";
						$(e).children("thead").find("th:last").before("<th data-tag=\""+tag+"\"><span>"+name+"</span>"+$.cartthrobMatrix.removeColumnButton+"</th>");
						$(e).children("tbody").children("tr").each(function(){
							var td = $(this).children("td");
							if (td.length == 1) {
								td.attr("colspan", td.attr("colspan") + 1);
							} else {
								td.last().before("<td><input type=\'text\' name=\'"+fieldName+"[INDEX]["+name+"]\'></td>");
							}
						});
						$.cartthrobMatrix.blankRows[fieldName].push("<input type=\'text\' name=\'"+fieldName+"[INDEX]["+name+"]\'>");
						$(e).children("tbody").children("tr:first :input:last").focus();
						$.cartthrobMatrix.resetRows($(e));
					},
					serialize: function(e) {
						var data = [];
						$(e).find(":input").each(function(){
							var match = $(this).attr("name").match(/.*\[(\d+)\]\[(.*)\]/);
							if (match) {
								if (data[match[1]] == undefined) {
									data[match[1]] = {};
								}
								data[match[1]][match[2]] = $(this).val();
							}
						});
						return data;
					},
					unserialize: function(e, data) {
						this.clearRows(e);
						for (i in data) {
							this.addRow(e);
							for (j in data[i]) {
								if ( ! this.hasColumn(e, j)) {
									this.addColumn(e, j);
								}
								$(e).find(":input").each(function(){
									if ($(this).attr("name").match(new RegExp(".*\\\["+i+"\\\]\\\["+j+"\\\]"))) {
										$(this).val(data[i][j]);
										return false;
									}
								});
							}
						}
						$(e).find("tbody tr:first :input:first").focus();
					},
					hasColumn: function(e, column) {
						var hasColumn = false;
						$(e).children("thead").find("th").each(function(){
							if ($(this).text() == column || $(this).data("tag") == "{"+column+"}") {
								hasColumn = true;
								return false;
							}
						});
						return hasColumn;
					}
				}
				</script>
				');
				
				$this->EE->javascript->output('
				$("table.cartthrobMatrix").sortable({
					handle: ".drag_handle",
					items: "tbody tr:not(.notSortable)",
					stop: function(e, ui) {
						$.cartthrobMatrix.resetRows($(ui.item).parents("table"));
					}
				});
				$("table.cartthrobMatrix thead tr th").live({
					mouseover: function(){
						var span = $(this).find("span:first");
						if ($(this).data("name") === undefined) {
							$(this).data("name", span.html());
						}
						span.html($(this).data("tag"));
					},
					mouseout: function(){
						$(this).find("span:first").html($(this).data("name"));
					}
				});
				');
				
				$this->EE->session->cache['cartthrob_matrix']['head'] = TRUE;
			}
			
			foreach ($this->headers as $index => $header)
			{
				$method = 'display_field_'.$header;
				
				if (method_exists($this, $method))
				{
					$blank_row[] = $this->$method($this->field_name.'[INDEX]['.$header.']', FALSE, $this->default_row, $index, TRUE);
				}
				else
				{
					$blank_row[] = form_input(array('name' => $this->field_name.'[INDEX]['.$header.']', 'disabled' => 'disabled'));
				}
			}
			
			$hidden = array();
			
			foreach ($this->hidden_columns as $col)
			{
				$hidden[] = '<input type="hidden" name="'.$this->field_name.'[INDEX]['.$col.']" disabled="disabled" />';
			}
			
			$this->EE->javascript->output("$.cartthrobMatrix.blankRows['{$this->field_name}'] = ".json_encode($blank_row).';');
			$this->EE->javascript->output("$.cartthrobMatrix.hiddenRows['{$this->field_name}'] = ".json_encode($hidden).';');
			$this->EE->javascript->output("$.cartthrobMatrix.variablePrefix['{$this->field_name}'] = '".$this->variable_prefix."';");
		}
		
		$this->EE->load->helper('inflector');
		
		$vars = $this->view_vars($replace_tag);

		if (version_compare(APP_VER, '2.2', '<'))
		{
			$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
			
			$orig_view_path = $this->EE->load->_ci_view_path;
			
			$this->EE->load->_ci_view_path = PATH_THIRD.'cartthrob/views/';
			
			$output .= $this->EE->load->view('cartthrob_matrix', $vars, TRUE);
			
			$this->EE->load->_ci_view_path = $orig_view_path;
			$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
		}
		else
		{
			$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
			
			$output .= $this->EE->load->view('cartthrob_matrix', $vars, TRUE);
			$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
		}
		
		if ( ! $replace_tag)
		{
			$output .= '<div class="cartthrobMatrixControls">';
			
			if ($this->buttons)
			{
				$buttons = array();
				
				foreach ($this->buttons as $name => $onclick)
				{
					$buttons[] = $this->js_anchor($this->EE->lang->line($name), $onclick);
				}
				
				$output .= ul($buttons, array('class' => 'cartthrobMatrixButtons'));
			}
			
			$output .= $this->additional_controls;
			
			$output .= '</div>';
		}
		
		return $output;
	}
	
	public function save($data)
	{
		if (is_array($data))
		{
			//if there's just one empty row
			if (count($data) === 1 && count(array_filter(current($data))) === 0)
			{
				return '';
			}
			
			return base64_encode(serialize($data));
		}
		
		return '';
	}
	
	public function save_settings($data)
	{
		$data['field_fmt'] = 'none';
		
		return $data;
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
		if (count($data) === 0 && preg_match('/'.LD.'if '.$this->variable_prefix.'no_results'.RD.'(.*?)'.LD.'\/if'.RD.'/s', $tagdata, $match))
		{
			$this->EE->TMPL->tagdata = str_replace($match[0], '', $this->EE->TMPL->tagdata);
			
			$this->EE->TMPL->no_results = $match[1];
		}
		
		if ( ! $data)
		{
			return $this->EE->TMPL->no_results();
		}
		
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		
		$this->EE->load->helper('data_formatting');
		
		$total_results = count($data);
		
		$this->EE->load->library('data_filter');
		$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
		
		$this->EE->data_filter->sort($data, (isset($params['orderby'])) ? $params['orderby'] : FALSE, (isset($params['sort'])) ? $params['sort'] : FALSE);
		
		$count = 1;
		
		$variables = array();
		
		foreach ($data as $i => $row)
		{
			if (method_exists($this, 'replace_tag_row'))
			{
				$row = $this->replace_tag_row($row);
			}
			
			foreach ($row as $key => $value)
			{
				if (is_array($value) && $key !== 'sub_items')
				{
					$row[$key] = implode('|', $value);
				}
			}
			
			if ( ! isset($row['row_id']))
			{
				$row['row_id'] = $i;
			}
			
			$row['count'] = $count;
			
			$row['total_results'] = $total_results;
			
			$row['first_'.$this->row_nomenclature] = (int) ($count === 1);
			
			$row['last_'.$this->row_nomenclature] = (int) ($count === $total_results);
			
			$row = $this->prefix_only ? array_key_prefix($row, $this->variable_prefix) : array_merge($row, array_key_prefix($row, $this->variable_prefix));
			
			$row[$this->row_nomenclature.'_count'] = $row[$this->variable_prefix.'count'];
			
			$row['total_'.$this->row_nomenclature.'s'] = $row[$this->variable_prefix.'total_results'];
		
			if (preg_match_all('/'.LD.'('.preg_quote($this->variable_prefix).'|row_)?'.'switch=([\042\047])(.+)\\2'.RD.'/', $tagdata, $matches))
			{
				foreach ($matches[0] as $i => $v)
				{
					$values = explode('|', $matches[3][$i]);
					
					$row[substr($matches[0][$i], 1, -1)] = $values[($count - 1) % count($values)];
				}
			}
			
			$variables[] = $row;
			
			$count++;
		}
		
		return $this->EE->TMPL->parse_variables($tagdata, $variables);
	}
	
	public function replace_table($data, $params = array(), $tagdata = FALSE)
	{
		return $this->display_field($data, TRUE);
	}
	
	public function replace_total_results($data, $params = array(), $tagdata = FALSE)
	{
		return count($data);
	}
	
	//deprecated, alias for total_results
	public function replace_option_count($data, $params = array(), $tagdata = FALSE)
	{
		return $this->replace_total_results($data, $params, $tagdata);
	}
	
	public function replace_label($data, $params = array(), $tagdata = FALSE)
	{
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
	
		$this->EE->load->model('cartthrob_field_model');
		$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
		
		return $this->EE->cartthrob_field_model->get_field_label($this->field_id);
	}
	
	protected function view_vars($replace_tag)
	{
		return array(
			'field_name' => $this->field_name,
			'class' => (get_class($this) === 'Cartthrob_matrix_ft') ? '' : camelize(str_replace('_ft', '', get_class($this))),
			'table_headers' => $this->table_headers,
			'table_rows' => $this->table_rows,
			'replace_tag' => $replace_tag,
		);
	}
	
	protected function js_anchor($title, $onclick = '', $attributes = '')
	{
		if ($onclick)
		{
			$attributes['onclick'] = $onclick;
		}
		
		if ($attributes)
		{
			$attributes = _parse_attributes($attributes);
		}
	    
		return '<a href="javascript:void(0);"'.$attributes.'>'.$title.'</a>';
	}

	/**
	 * low_search_index
	 * Build search index for Low Search
	 *
	 * @param mixed $data the field's data
	 *
	 * @return string
	 */
	public function third_party_search_index($field_data)
	{
		$search_index = '';
		
		if (!empty($this->settings['entry_id']))
		{
			$this->row['entry_id'] = $this->settings['entry_id'];
		}
		
		//converts serialized -> array
		$data = $this->pre_process($field_data);

		//creates string with each row and each row's nodes separated by carriage return
		foreach ($data as $row)
		{
			$search_index .= chr(32).implode(chr(32), $row);
		}

		return $search_index;
	}
}

/* End of file ft.cartthrob_discount.php */
/* Location: ./system/expressionengine/third_party/cartthrob_discount/ft.cartthrob_discount.php */