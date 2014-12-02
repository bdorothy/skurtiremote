<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

 	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
		<caption><?=lang('payment_gateways_header')?></caption>
		<thead class="">
			<tr>
				<th colspan="2">
					<strong><?=lang('gateways_header')?></strong><br />
					<?=lang('gateways_description')?>
				</th>
 			</tr>
		</thead>
		<tbody>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label><?=lang('gateways_choose')?></label>
 				</td>
				<td style='width:50%;'>
				<select name='payment_gateway'  >
					<option value='' selected='selected'>--</option>
						<?php foreach ($payment_gateways as $plugin) : ?>
							<option value="<?=$plugin['classname']?>" <?php if ($settings['payment_gateway'] == $plugin['classname']) : ?>selected="selected"<?php endif; ?>>
								<?=lang($plugin['title'])?>
							</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label><?=lang('gateways_edit')?></label>
 				</td>
				<td style='width:50%;'>
					<select name='last_edited_gateway' class="plugins" id="select_payment_gateway">
					<option value='<?php echo $settings['payment_gateway']?>' selected='selected'>--</option>
						<?php foreach ($payment_gateways as $plugin) : ?>
							<option value="<?=$plugin['classname']?>" >
								<?=lang($plugin['title'])?>
							</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
		</tbody>
	</table>

<?=$this->load->view('plugin_settings', array('settings' => $settings, 'plugins' => $payment_gateways, 'plugin_type'=>'payment_gateway'))?>
