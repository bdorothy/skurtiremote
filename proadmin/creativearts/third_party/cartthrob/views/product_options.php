<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

 	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
		<caption><?=lang('product_options_header')?></caption>
		<thead class="">
			<tr>
				<th colspan="2">
					<strong><?=lang('product_options_header')?></strong><br />
					<?=lang('product_description')?>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<label><?=lang('product_allow_duplicate_items')?></label>
					<div class="subtext"><?=lang('product_allow_duplicate_instructions')?></div>
 				</td>
				<td style='width:50%;'>
					<input class='radio' type='radio' name='allow_products_more_than_once' value='1' <?php if ($settings['allow_products_more_than_once']) : ?>checked='checked'<?php endif; ?> /> 
						<?=lang('yes')?>
					<input class='radio' type='radio' name='allow_products_more_than_once' value='0' <?php if ( ! $settings['allow_products_more_than_once']) : ?>checked='checked'<?php endif; ?> /> 
						<?=lang('no')?>
				</td>
			</tr>
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<label><?=lang('product_split_items_by_quantity')?></label>
					<div class="subtext"><?=lang('product_split_items_by_quantity_instructions')?></div>
 				</td>
				<td style='width:50%;'>
					<input class='radio' type='radio' name='product_split_items_by_quantity' value='1' <?php if ($settings['product_split_items_by_quantity']) : ?>checked='checked'<?php endif; ?> /> 
						<?=lang('yes')?>
 					<input class='radio' type='radio' name='product_split_items_by_quantity' value='0' <?php if ( ! $settings['product_split_items_by_quantity']) : ?>checked='checked'<?php endif; ?> /> 
						<?=lang('no')?>
				</td>
			</tr>
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<label><?=lang('email_low_stock_send_warning')?></label>
				</td>
				<td style='width:50%;'>
					<input class='radio' type='radio' name='send_inventory_email' value='1' <?php if ($settings['send_inventory_email']) : ?>checked='checked'<?php endif; ?> /> <?=lang('yes')?> 
					<input class='radio' type='radio' name='send_inventory_email' value='0' <?php if ( ! $settings['send_inventory_email']) : ?>checked='checked'<?php endif; ?> /> <?=lang('no')?></label>
				</td>
			</tr>
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<label><?=lang('low_stock_value')?></label>
				</td>
				<td style='width:50%;'>
					<?=form_input('low_stock_level', $settings['low_stock_level'])?>
				</td>
			</tr>
		</tbody>
	</table>
