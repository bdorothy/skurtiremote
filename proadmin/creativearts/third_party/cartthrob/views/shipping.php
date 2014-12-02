<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

 	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
		<caption><?=lang('shipping_header')?></caption>
		<thead class="">
			<tr>
				<th colspan="2">
					<strong><?=lang('shipping_header')?></strong><br />
					<?=lang('shipping_description')?>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr class="even">
				<td>
				    <label><?=lang('shipping_choose_a_plugin')?></label>
 				</td>
				<td style='width:50%;'>
				    <select name='shipping_plugin' class='plugins' id="select_shipping_plugin">
						<option value=''><?=lang('shipping_defined_per_product')?></option>
						<?php foreach ($shipping_plugins as $plugin) : ?>
							<option value="<?=$plugin['classname']?>" <?php if ($settings['shipping_plugin'] == $plugin['classname']) : ?>selected="selected"<?php endif; ?>>
								<?=lang($plugin['title'])?>
							</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
		</tbody>
	</table>

<?=$this->load->view('plugin_settings', array('settings'=>$settings, 'plugins'=>$shipping_plugins, 'plugin_type'=>'shipping_plugin'))?>
