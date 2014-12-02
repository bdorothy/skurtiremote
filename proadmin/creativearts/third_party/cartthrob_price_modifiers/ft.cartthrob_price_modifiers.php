<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once PATH_THIRD.'cartthrob/config.php';
require_once PATH_THIRD.'cartthrob/fieldtypes/ft.cartthrob_matrix.php';

/**
 * @property CI_Controller $EE
 * @property Cartthrob_core_ee $cartthrob;
 * @property Cartthrob_cart $cart
 * @property Cartthrob_store $store
 */
class Cartthrob_price_modifiers_ft extends Cartthrob_matrix_ft
{
	public $info = array(
		'name' => 'CartThrob Price Modifiers',
		'version' => CARTTHROB_VERSION,
	);
	
	public $default_row = array(
		'option_value' => '',
		'option_name' => '',
		'price' => '',
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
	
	public function replace_tag($data, $params = array(), $tagdata = FALSE)
	{
		if (isset($params['orderby']) && $params['orderby'] === 'price')
		{
			$params['orderby'] = 'price_numeric';
		}
		
		return parent::replace_tag($data, $params, $tagdata);
	}
	
	public function display_field($data, $replace_tag = FALSE)
	{
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');

		$this->EE->lang->loadfile('cartthrob', 'cartthrob');
		
		$this->EE->load->model('cartthrob_settings_model');
		
		$this->EE->load->helper('html');
		
		if ( ! $presets = $this->EE->config->item('cartthrob:price_modifier_presets'))
		{
			$presets = array();
		}
		
		$options = array('' => $this->EE->lang->line('select_preset'));
		
		$json_presets = array();
		
		foreach ($presets as $key => $preset)
		{
			$json_presets[] = array(
				'name' => $key,
				'values' => $preset,
			);
			
			$options[] = $key;
		}
		
		$this->additional_controls = ul(
			array(
				form_dropdown('', $options),
				form_submit('', $this->EE->lang->line('load_preset'), 'onclick="$.cartthrobPriceModifiers.loadPreset($(this).parents(\'div.cartthrobMatrixControls\').prev(\'table.cartthrobMatrix\')); return false;"'),
				form_submit('', $this->EE->lang->line('delete_preset'), 'onclick="$.cartthrobPriceModifiers.deletePreset($(this).parents(\'div.cartthrobMatrixControls\').prev(\'table.cartthrobMatrix\')); return false;"'),
				form_submit('', $this->EE->lang->line('save_preset'), 'onclick="$.cartthrobPriceModifiers.savePreset($(this).parents(\'div.cartthrobMatrixControls\').prev(\'table.cartthrobMatrix\')); return false;"'),
			),
			array('class' => 'cartthrobMatrixPresets')
		);
		
		unset($this->default_row['inventory']);
		unset($this->default_row['weight']);
		
		$channel_id = $this->EE->input->get('channel_id'); 

		if ( ! $channel_id  && isset($this->EE->channel_form))
		{
 			$channel_id = $this->EE->channel_form->channel('channel_id');
		}
		
		$this->EE->load->helper('data_formatting');
		
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
			
			$this->EE->cp->add_to_foot('
			<script type="text/javascript">
			$.cartthrobPriceModifiers = {
				currentPreset: function(e) {
					return $(e).next("div.cartthrobMatrixControls").find("ul.cartthrobMatrixPresets select").val() || "";
				},
				presets: '.json_encode($json_presets).',
				savePreset: function(e) {
					var currentValue = (this.presets[this.currentPreset(e)] !== undefined) ? this.presets[this.currentPreset(e)].name : "";
					var name = prompt("'.$this->EE->lang->line('name_preset_prompt').'", currentValue);
					if (name)
					{
						this.presets.push({"name": name, "values": $.cartthrobMatrix.serialize(e)});
						this.updatePresets();
					}
				},
				updatePresets: function() {
					var select = "<select>";
					select += "<option value=\'\'>'.$this->EE->lang->line('select_preset').'</option>";
					for (i in this.presets) {
						select += "<option value=\'"+i+"\'>"+this.presets[i].name+"</option>";
					}
					select += "</select>";
					$("div.cartthrobMatrixControls ul.cartthrobMatrixPresets select").replaceWith(select);
					$.post(
						'.$url.',
						{
							"CSRF_TOKEN": EE.CSRF_TOKEN,
							"price_modifier_presets": this.presets
						},
						function(data){
							EE.CSRF_TOKEN = data.CSRF_TOKEN;
						},
						"json"
					);
				},
				loadPreset: function(e) {
					var which = this.currentPreset(e);
					if (this.presets[which] != undefined && confirm("'.$this->EE->lang->line('load_preset_confirm').'")) {
						$.cartthrobMatrix.unserialize(e, this.presets[which].values);
					}
				},
				deletePreset: function(e) {
					var which = this.currentPreset(e);
					if (which && this.presets[which] != undefined && confirm("'.$this->EE->lang->line('delete_preset_confirm').'")) {
						delete this.presets[which];
						this.updatePresets();
					}
				}
			};
			</script>
			');
			
			$this->EE->session->cache['cartthrob_price_modifiers']['head'] = TRUE;
		}
		$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
		
		return parent::display_field($data, $replace_tag);
	}
}

/* End of file ft.cartthrob_discount.php */
/* Location: ./system/expressionengine/third_party/cartthrob_discount/ft.cartthrob_discount.php */