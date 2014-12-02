<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

 	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
		<caption><?=lang('payment_security_header')?></caption>
		<thead class="">
			<tr>
				<th colspan="2">
					<strong><?=lang('security_settings_header')?></strong>
				</th>
 			</tr>
		</thead>
		<tbody>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label><?=lang('security_settings_allow_gateway_selection')?></label>
					<div class="subtext"><?=lang('security_settings_allow_gateway_selection_description')?></div>
				</td>
				<td style='width:50%;'>
					<input class='radio' type='radio' name='allow_gateway_selection' value='1' <?php if ($settings['allow_gateway_selection']) : ?>checked='checked'<?php endif; ?> /> 
					<?=lang('yes')?>
					<input class='radio' type='radio' name='allow_gateway_selection' value='0' <?php if ( ! $settings['allow_gateway_selection']) : ?>checked='checked'<?php endif; ?> /> 
					<?=lang('no')?>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label><?=lang('security_settings_selectable_gateways')?></label>
				</td>
				<td style='width:50%;'>
						<?php foreach ($payment_gateways as $gateway) : ?>
							<?php 
								echo $cartthrob_mcp->plugin_setting("checkbox", 
									'available_gateways['.$gateway['classname'].']',
									 @$settings['available_gateways'][$gateway['classname']],
									array("label" => $gateway['title'], 'name' => $gateway['classname']),
									array("id" => $gateway['classname'])
									); 
								?>
								<br /> 
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label><?=lang('security_settings_cc_modulus_checking')?></label>
					<div class="subtext"><?=lang('security_settings_cc_modulus_description')?></div>
				</td>
				<td style='width:50%;'>
					<input class='radio' type='radio' name='modulus_10_checking' value='1' <?php if ($settings['modulus_10_checking']) : ?>checked='checked'<?php endif; ?> /> 
					<?=lang('yes')?>
					<input class='radio' type='radio' name='modulus_10_checking' value='0' <?php if ( ! $settings['modulus_10_checking']) : ?>checked='checked'<?php endif; ?> /> 
					<?=lang('no')?>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label><?=lang('gateways_format')?></label>
					<div class="subtext"><?=lang('gateways_format_description')?></div>
					
 				</td>
				<td style='width:50%;'>
					<?=form_dropdown('gateways_format', 
						array(	'bootstrap'		=> lang('gateways_format_bootstrap'),
								'default'	=> lang('gateways_format_default'), 
								), 
								(isset($settings['gateways_format'])?$settings['gateways_format']:'bootstrap'
								)
						)
					?>
				</td>
			</tr>
		</tbody>
	</table>
