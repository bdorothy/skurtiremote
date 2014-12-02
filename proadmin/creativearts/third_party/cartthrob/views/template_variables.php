<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<?=str_repeat(BR, 2);?>
<?=form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob'.AMP.'method=quick_save'.AMP.'return=installation')?>

	<div>
 
 	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
		<caption><?=lang('template_variable_settings')?></caption>
		<th colspan="2">
			<?=lang('template_variable_description')?>
		</th>
		<tbody>
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
				<label><?=lang('template_var_store_name')?></label>
 				</td>
				<td style='width:50%;'>
 					<input  dir='ltr' type='text' name='store_name'  value='<?=$settings['store_name']?>' size='90'   />
				</td>
 			</tr>

			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
				<label><?=lang('template_var_store_address1')?></label>
 				</td>
				<td style='width:50%;'>
 					<input  dir='ltr' type='text' name='store_address1'  value='<?=$settings['store_address1']?>' size='90'  />
				</td>
 			</tr>

			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
				<label><?=lang('template_var_store_address2')?></label>
 				</td>
				<td style='width:50%;'>
 					<input  dir='ltr' type='text' name='store_address2'  value='<?=$settings['store_address2']?>' size='90'  />
				</td>
 			</tr>

			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
				<label><?=lang('template_var_store_city')?></label>
 				</td>
				<td style='width:50%;'>
 					<input  dir='ltr' type='text' name='store_city'  value='<?=$settings['store_city']?>' size='90'  />
				</td>
 			</tr>

			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
				<label><?=lang('template_var_store_state')?></label>
 				</td>
				<td style='width:50%;'>
 					<input  dir='ltr' type='text' name='store_state'  value='<?=$settings['store_state']?>' size='90'  />
				</td>
 			</tr>

			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
				<label><?=lang('template_var_store_zip')?></label>
 				</td>
				<td style='width:50%;'>
 					<input  dir='ltr' type='text' name='store_zip'  value='<?=$settings['store_zip']?>' size='90' maxlength='5' />
				</td>
 			</tr>

			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
				<label><?=lang('template_var_store_country')?></label>
 				</td>
				<td style='width:50%;'>
 					<input  dir='ltr' type='text' name='store_country'  value='<?=$settings['store_country']?>' size='90'  />
				</td>
 			</tr>

			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
				<label><?=lang('template_var_store_phone')?></label>
 				</td>
				<td style='width:50%;'>
					<?=form_textarea('store_phone', $settings['store_phone'])?>
				</td>
 			</tr>

			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
				<label><?=lang('template_var_store_description')?></label>
 				</td>
				<td style='width:50%;'>
 					<input  dir='ltr' type='text' name='store_description'  value='<?=$settings['store_description']?>' size='90'   />
				</td>
 			</tr>
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
				<label><?=lang('template_var_store_shipping_estimate')?></label>
 				</td>
				<td style='width:50%;'>
 					<input  dir='ltr' type='text' name='store_shipping_estimate'  value='<?=$settings['store_shipping_estimate']?>' size='90'  />
				</td>
 			</tr>

			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
				<label><?=lang('template_var_store_about_us')?></label>
 				</td>
				<td style='width:50%;'>
					<?=form_textarea('store_about_us', $settings['store_about_us'])?>
				</td>
 			</tr>

			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
				<label><?=lang('template_var_store_checkout_page')?></label><br>
					<small><?=lang('template_var_store_checkout_page_note')?></small>
 				</td>
				<td style='width:50%;'>
					<?=form_dropdown('store_checkout_page', array('checkout' => lang('checkout'), 'shipping' => lang('shipping')), $settings['store_checkout_page'])?>
 				</td>
 			</tr>

			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
				<label><?=lang('template_var_store_google_code')?></label>
 				</td>
				<td style='width:50%;'>
					<input  dir='ltr' type='text' name='store_google_code'  value='<?=$settings['store_google_code']?>' size='90'  />
				</td>
 			</tr>
		</tbody>	
	</table>
 
</div>
