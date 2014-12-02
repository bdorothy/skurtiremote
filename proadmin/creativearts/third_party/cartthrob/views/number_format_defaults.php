<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

 	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
		<caption><?=lang('number_format_defaults_header')?></caption>
		<thead class="">
			<tr>
				<th colspan="2">
					<strong><?=lang('number_format_defaults_heading')?></strong>
					<br />
					<?=lang('number_format_defaults_description')?>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<label><?=lang('number_format_defaults_decimals')?></label>
 				</td>
				<td style='width:50%;'>
					<input  dir='ltr' type='text' name='number_format_defaults_decimals' id='number_format_defaults_decimals' value='<?=$settings['number_format_defaults_decimals']?>' size='90' maxlength='100' />
				</td>
			</tr>
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<label><?=lang('number_format_defaults_dec_point')?></label>
 				</td>
				<td style='width:50%;'>
					<input  dir='ltr' type='text' name='number_format_defaults_dec_point' id='number_format_defaults_dec_point' value='<?=$settings['number_format_defaults_dec_point']?>' size='90' maxlength='100' />
				</td>
			</tr>			
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<label><?=lang('number_format_defaults_thousands_sep')?></label>
 				</td>
				<td style='width:50%;'>
					<input  dir='ltr' type='text' name='number_format_defaults_thousands_sep' id='number_format_defaults_thousands_sep' value='<?=$settings['number_format_defaults_thousands_sep']?>' size='90' maxlength='100' />
				</td>
			</tr>			
 			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<label><?=lang('number_format_defaults_prefix')?></label>
 				</td>
				<td style='width:50%;'>
					<?=form_input(array('name' => 'number_format_defaults_prefix', 'id' => 'number_format_defaults_prefix', 'value' => $settings['number_format_defaults_prefix'], 'size' => '90', 'maxlength' => '100', 'dir' => 'ltr'))?>
				</td>
			</tr>
 			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<label><?=lang('number_format_defaults_prefix_position')?></label>
 				</td>
				<td style='width:50%;'>
					<input class='radio' type='radio' name='number_format_defaults_prefix_position' value='BEFORE' <?php if (! $settings['number_format_defaults_prefix_position'] || $settings['number_format_defaults_prefix_position'] =="BEFORE") : ?>checked='checked'<?php endif; ?> /> 
					<?=lang('before')?>
				<input class='radio' type='radio' name='number_format_defaults_prefix_position' value='AFTER' <?php if ($settings['number_format_defaults_prefix_position'] == "AFTER") : ?>checked='checked'<?php endif; ?> /> 
					<?=lang('after')?>
			</tr>
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<label><?=lang('number_format_defaults_currency_code')?></label>
 				</td>
				<td style='width:50%;'>
					<input  dir='ltr' type='text' name='number_format_defaults_currency_code' id='number_format_defaults_currency_code' value='<?=$settings['number_format_defaults_currency_code']?>' size='90' maxlength='100' />
				</td>
			</tr>
 			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<label><?=lang('round_to')?></label>
					<div class="subtext"><?=lang('rounding_description')?></div>
 				</td>
				<td style='width:50%;'>
					<input class='radio' type='radio' name='rounding_default' value='standard' <?php if (! $settings['rounding_default'] || $settings['rounding_default'] =="standard") : ?>checked='checked'<?php endif; ?> /> 
					<?=lang('rounding_standard')?>

					<input class='radio' type='radio' name='rounding_default' value='round_up' <?php if (! $settings['rounding_default'] || $settings['rounding_default'] =="round_up") : ?>checked='checked'<?php endif; ?> /> 
					<?=lang('round_up')?>

					<input class='radio' type='radio' name='rounding_default' value='round_down' <?php if (! $settings['rounding_default'] || $settings['rounding_default'] =="round_down") : ?>checked='checked'<?php endif; ?> /> 
					<?=lang('round_down')?>

					<input class='radio' type='radio' name='rounding_default' value='round_up_extra_precision' <?php if (! $settings['rounding_default'] || $settings['rounding_default'] =="round_up_extra_precision") : ?>checked='checked'<?php endif; ?> /> 
					<?=lang('round_up_extra_precision')?>
					
					<input class='radio' type='radio' name='rounding_default' value='swedish' <?php if ($settings['rounding_default'] == "swedish") : ?>checked='checked'<?php endif; ?> /> 
					<?=lang('rounding_swedish')?>

					<input class='radio' type='radio' name='rounding_default' value='new_zealand' <?php if ($settings['rounding_default'] == "new_zealand") : ?>checked='checked'<?php endif; ?> /> 
					<?=lang('rounding_new_zealand')?>
				</td>
			</tr>
		</tbody>
	</table>
