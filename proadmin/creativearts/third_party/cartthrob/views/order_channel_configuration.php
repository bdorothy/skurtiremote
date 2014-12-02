<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

 	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
		<caption><?=lang('order_channel_configuration_header')?></caption>
		<thead class="">
			<tr>
				<th colspan="2">
					<strong><?=lang('orders_header')?></strong><br />
					<?=lang('orders_options_description')?>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label><?=lang('section')?></label>
 				</td>
				<td style='width:50%;'>
					<select name='orders_channel' class="channels" id="select_orders">
						<option value=''></option>
						<?php foreach ($channels as $channel) : ?>
						<option value="<?=$channel['channel_id']?>" <?php if ($settings['orders_channel'] == $channel['channel_id']) : ?>selected="selected"<?php endif; ?>>
							<?=$channel['channel_title']?>
						</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
 			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label><?=lang('save_orders')?>?</label>
					<div class="subtext"><?=lang('orders_saving_instructions')?></div>
 				</td>
				<td style='width:50%;'>
 						<input class='radio' type='radio' name='save_orders' value='1' <?php if ($settings['save_orders']) : ?>checked='checked'<?php endif; ?> />
						<?=lang('yes')?>
 						<input class='radio' type='radio' name='save_orders' value='0' <?php if ( ! $settings['save_orders']) : ?>checked='checked'<?php endif; ?> /> 
						<?=lang('no')?>
				</td>
			</tr>
 			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label><?=lang('order_numbers')?></label>
					<div class="subtext"><?=lang('order_numbers_instructions')?></div>
 				</td>
				<td style='width:50%;'>
 						<input class='radio' type='radio' name='orders_sequential_order_numbers' value='0' <?php if ( ! $settings['orders_sequential_order_numbers']) : ?>checked='checked'<?php endif; ?> />
						<?=lang('order_numbers_entry_id')?>
 						<input class='radio' type='radio' name='orders_sequential_order_numbers' value='1' <?php if ($settings['orders_sequential_order_numbers']) : ?>checked='checked'<?php endif; ?> /> 
						<?=lang('order_numbers_sequential')?>
 				</td>
			</tr>
 			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_title_prefix')?></label>
				</td>
				<td style='width:50%;'>
					<input type="text" name="orders_title_prefix" value="<?=$settings['orders_title_prefix']?>" />   
				</td>
			</tr>
 			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label><?=lang('orders_title_suffix')?></label>
				</td>
				<td style='width:50%;'>
					<input type="text" name="orders_title_suffix" value="<?=$settings['orders_title_suffix']?>" />
				</td>
			</tr>
 			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_url_title_prefix')?></label>
				</td>
				<td style='width:50%;'>
					<input type="text" name="orders_url_title_prefix" value="<?=$settings['orders_url_title_prefix']?>" />           
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_url_title_suffix')?></label>
				</td>
				<td style='width:50%;'>
					<input type="text" name="orders_url_title_suffix" value="<?=$settings['orders_url_title_suffix']?>" />           
				</td>
			</tr>	
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label><?=lang('orders_convert_country_code')?>?</label>
					<div class="subtext"><?=lang('orders_convert_country_code_instructions')?></div>
					
				</td>
				<td style='width:50%;'>
 						<input class='radio' type='radio' name='orders_convert_country_code' value='1' <?php if ($settings['orders_convert_country_code']) : ?>checked='checked'<?php endif; ?> />
						<?=lang('yes')?>
  						<input class='radio' type='radio' name='orders_convert_country_code' value='0' <?php if ( ! $settings['orders_convert_country_code']) : ?>checked='checked'<?php endif; ?> /> 
						<?=lang('no')?>
 				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label><?=lang('global_settings_last_order_number')?></label>
					<div class="subtext"><?=lang('global_settings_last_order_number_description')?></div> 
 				</td>
				<td style='width:50%;'>
					<input  dir='ltr' type='text' name='last_order_number' id='last_order_number' value='<?=$settings['last_order_number']?>' size='90' maxlength='100' />
					
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label><?=lang('update_inventory_when_editing_order')?></label>
					<div class="subtext"><?=lang('update_inventory_when_editing_order_description')?></div> 
 				</td>
				<td style='width:50%;'>
 						<input class='radio' type='radio' name='update_inventory_when_editing_order' value='1' <?php if ($settings['update_inventory_when_editing_order']) : ?>checked='checked'<?php endif; ?> />
						<?=lang('yes')?>
  						<input class='radio' type='radio' name='update_inventory_when_editing_order' value='0' <?php if ( ! $settings['update_inventory_when_editing_order']) : ?>checked='checked'<?php endif; ?> /> 
						<?=lang('no')?>
 				</td>
			</tr>
		</tbody>
	</table>
	
 	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
		<caption><?=lang('orders_status_field')?></caption>
		<thead class="visualEscapism">
			<tr>
				<th><?=lang('preference')?></th><th><?=lang('setting')?></th>
			</tr>
		</thead>
		<tbody>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label><?=lang('orders_default_status')?></label>
					<div class="subtext"><?=lang('orders_set_status')?> </div>
 				</td>
				<td style='width:50%;'>
 					<select name='orders_default_status' class='select status_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($statuses[$settings['orders_channel']])) : ?>
							<?php foreach ($statuses[$settings['orders_channel']] as $status) : ?>
								<option value="<?=$status['status']?>" <?php if ($settings['orders_default_status'] == $status['status']) : ?>selected="selected"<?php endif; ?>>
									<?=$status['status']?>
								</option>
							<?php endforeach; ?>  
						<?php endif; ?>
					</select>
				</td>
			</tr>
 			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label><?=lang('orders_processing_status')?></label>
					<div class="subtext"><?=lang('orders_set_processing_status')?> </div>
 				</td>
				<td style='width:50%;'>
					<select name='orders_processing_status' class='select status_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($statuses[$settings['orders_channel']])) : ?>
							<?php foreach ($statuses[$settings['orders_channel']] as $status) : ?>
								<option value="<?=$status['status']?>" <?php if ($settings['orders_processing_status'] == $status['status']) : ?>selected="selected"<?php endif; ?>>
									<?=$status['status']?>
								</option>
							<?php endforeach; ?>  
						<?php endif; ?>
					</select>
				</td>
			</tr>
 			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label><?=lang('orders_failed_status')?></label>
  				</td>
				<td style='width:50%;'>
					<select name='orders_failed_status' class='select status_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($statuses[$settings['orders_channel']])) : ?>
							<?php foreach ($statuses[$settings['orders_channel']] as $status) : ?>
								<option value="<?=$status['status']?>" <?php if ($settings['orders_failed_status'] == $status['status']) : ?>selected="selected"<?php endif; ?>>
									<?=$status['status']?>
								</option>
							<?php endforeach; ?>  
						<?php endif; ?>
					</select>
 				</td>
			</tr>
 			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label><?=lang('orders_declined_status')?></label>
 				</td>
				<td style='width:50%;'>
					<select name='orders_declined_status' class='select status_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($statuses[$settings['orders_channel']])) : ?>
							<?php foreach ($statuses[$settings['orders_channel']] as $status) : ?>
								<option value="<?=$status['status']?>" <?php if ($settings['orders_declined_status'] == $status['status']) : ?>selected="selected"<?php endif; ?>>
									<?=$status['status']?>
								</option>
							<?php endforeach; ?>  
						<?php endif; ?>
					</select>
				</td>
			</tr>  
 			
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label><?=lang('status_pending')?></label>
 				</td>
				<td style='width:50%;'>
					<select name='orders_status_pending' class='select status_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($statuses[$settings['orders_channel']])) : ?>
							<?php foreach ($statuses[$settings['orders_channel']] as $status) : ?>
								<option value="<?=$status['status']?>" <?php if ($settings['orders_status_pending'] == $status['status']) : ?>selected="selected"<?php endif; ?>>
									<?=$status['status']?>
								</option>
							<?php endforeach; ?>  
						<?php endif; ?>
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label><?=lang('status_expired')?></label>
 				</td>
				<td style='width:50%;'>
					<select name='orders_status_expired' class='select status_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($statuses[$settings['orders_channel']])) : ?>
							<?php foreach ($statuses[$settings['orders_channel']] as $status) : ?>
								<option value="<?=$status['status']?>" <?php if ($settings['orders_status_expired'] == $status['status']) : ?>selected="selected"<?php endif; ?>>
									<?=$status['status']?>
								</option>
							<?php endforeach; ?>  
						<?php endif; ?>
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label><?=lang('status_canceled')?></label>
 				</td>
				<td style='width:50%;'>
					<select name='orders_status_canceled' class='select status_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($statuses[$settings['orders_channel']])) : ?>
							<?php foreach ($statuses[$settings['orders_channel']] as $status) : ?>
								<option value="<?=$status['status']?>" <?php if ($settings['orders_status_canceled'] == $status['status']) : ?>selected="selected"<?php endif; ?>>
									<?=$status['status']?>
								</option>
							<?php endforeach; ?>  
						<?php endif; ?>
					</select>
				</td>
			</tr>
 	   		<tr class="<?=alternator('even', 'odd')?>">
					<td>
						<label><?=lang('status_voided')?></label>
	 				</td>
					<td style='width:50%;'>
						<select name='orders_status_voided' class='select status_orders' >
							<option value='' class="blank" ></option>
							<?php if ($settings['orders_channel'] && isset($statuses[$settings['orders_channel']])) : ?>
								<?php foreach ($statuses[$settings['orders_channel']] as $status) : ?>
									<option value="<?=$status['status']?>" <?php if ($settings['orders_status_voided'] == $status['status']) : ?>selected="selected"<?php endif; ?>>
										<?=$status['status']?>
									</option>
								<?php endforeach; ?>  
							<?php endif; ?>
						</select>
					</td>
				</tr>
				<tr class="<?=alternator('even', 'odd')?>">
					<td>
						<label><?=lang('status_refunded')?></label>
	 				</td>
					<td style='width:50%;'>
						<select name='orders_status_refunded' class='select status_orders' >
							<option value='' class="blank" ></option>
							<?php if ($settings['orders_channel'] && isset($statuses[$settings['orders_channel']])) : ?>
								<?php foreach ($statuses[$settings['orders_channel']] as $status) : ?>
									<option value="<?=$status['status']?>" <?php if ($settings['orders_status_refunded'] == $status['status']) : ?>selected="selected"<?php endif; ?>>
										<?=$status['status']?>
									</option>
								<?php endforeach; ?>  
							<?php endif; ?>
						</select>
					</td>
				</tr>			
			  	<tr class="<?=alternator('even', 'odd')?>">
					<td>
						<label><?=lang('status_reversed')?></label>
	 				</td>
					<td style='width:50%;'>
						<select name='orders_status_reversed' class='select status_orders' >
							<option value='' class="blank" ></option>
							<?php if ($settings['orders_channel'] && isset($statuses[$settings['orders_channel']])) : ?>
								<?php foreach ($statuses[$settings['orders_channel']] as $status) : ?>
									<option value="<?=$status['status']?>" <?php if ($settings['orders_status_reversed'] == $status['status']) : ?>selected="selected"<?php endif; ?>>
										<?=$status['status']?>
									</option>
								<?php endforeach; ?>  
							<?php endif; ?>
						</select>
					</td>
				</tr>
				<tr class="<?=alternator('even', 'odd')?>">
					<td>
						<label><?=lang('status_offsite')?></label>
	 				</td>
					<td style='width:50%;'>
						<select name='orders_status_offsite' class='select status_orders' >
							<option value='' class="blank" ></option>
							<?php if ($settings['orders_channel'] && isset($statuses[$settings['orders_channel']])) : ?>
								<?php foreach ($statuses[$settings['orders_channel']] as $status) : ?>
									<option value="<?=$status['status']?>" <?php if ($settings['orders_status_offsite'] == $status['status']) : ?>selected="selected"<?php endif; ?>>
										<?=$status['status']?>
									</option>
								<?php endforeach; ?>  
							<?php endif; ?>
						</select>
					</td>
				</tr>    
		</tbody>
	</table>
	
 	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
		<caption><?=lang('order_data_fields')?></caption>
		<thead class="">
			<tr>
				<th>
					<strong><?=lang('order_data_type')?></strong><br />
					<?=lang('order_data_type_instructions')?>	
				</th>
				<th>
					<strong><?=lang('orders_channel')?></strong><br />
					<?=lang('order_fields_in_channel')?>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label><?=lang('orders_items_field')?></label>
					<div class="subtext"><?=lang('orders_items_field_instructions')?></div>
 				</td>
				<td style='width:50%;'>
					<select name='orders_items_field' class='select field_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_items_field'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option>
							<?php endforeach; ?>   
						<?php endif; ?>         
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_subtotal_field')?> </label>
 				</td>
				<td style='width:50%;'>
					<select name='orders_subtotal_field' class='select field_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_subtotal_field'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
								<?=$field['field_label']?></option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_subtotal_plus_tax_field')?> </label>
 				</td>
				<td style='width:50%;'>
					<select name='orders_subtotal_plus_tax_field' class='select field_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_subtotal_plus_tax_field'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
								<?=$field['field_label']?></option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_tax_field')?> </label>
					<div class='subtext'><?=lang('orders_tax_instructions')?></div>
 				</td>
				<td style='width:50%;'>
					<select name='orders_tax_field' class='select field_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_tax_field'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_shipping_field')?> </label>
					<div class="subtext"><?=lang('orders_shipping_field_instructions')?></div>
 				</td>
				<td style='width:50%;'>
					<select name='orders_shipping_field' class='select field_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_shipping_field'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_shipping_plus_tax_field')?> </label>
 				</td>
				<td style='width:50%;'>
					<select name='orders_shipping_plus_tax_field' class='select field_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_shipping_plus_tax_field'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_discount_field')?> </label>
					<div class="subtext"><?=lang('orders_discount_field_instructions')?></div>
 				</td>
				<td style='width:50%;'>
					<select name='orders_discount_field' class='select field_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_discount_field'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_total_field')?> </label>
 				</td>
				<td style='width:50%;'>
					<select name='orders_total_field' class='select field_orders' >
					<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_total_field'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_transaction_id')?> </label>
					<div class="subtext"><?=lang('orders_transaction_id_instructions')?></div>
 				</td>
				<td style='width:50%;'>
					<select name='orders_transaction_id' class='select field_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_transaction_id'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_last_four_digits')?> </label>
					<div class="subtext"><?=lang('orders_last_four_digits_instructions')?></div>
 				</td>
				<td style='width:50%;'>
					<select name='orders_last_four_digits' class='select field_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_last_four_digits'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
 					<label> <?=lang('orders_coupon_codes')?> </label>
					<div class="subtext"><?=lang('orders_coupon_codes_instructions')?></div>
 				</td>
				<td style='width:50%;'>
 					<select name='orders_coupon_codes' class='select field_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_coupon_codes'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option>
							<?php endforeach; ?>
						<?php endif; ?>    
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_shipping_method')?> </label>
					<div class="subtext"><?=lang('orders_shipping_method_instructions')?></div>
				</td>
				<td style='width:50%;'>
					<select name='orders_shipping_option' class='select field_orders' >
			   			<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_shipping_option'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_payment_gateway')?> </label>
				</td>
				<td style='width:50%;'>
					<select name='orders_payment_gateway' class='select field_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_payment_gateway'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option>
							<?php endforeach; ?>
						<?php endif; ?> 
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_error_message_field')?> </label>
					<div class="subtext"><?=lang('orders_error_message_field_instructions')?></div>
				</td>
				<td style='width:50%;'>
					<select name='orders_error_message_field' class='select field_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_error_message_field'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option>
							<?php endforeach; ?>
						<?php endif; ?> 
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_language_field')?> </label>
					<div class="subtext"><?=lang('orders_language_field_instructions')?></div>
				</td>
				<td style='width:50%;'>
					<select name='orders_language_field' class='select field_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_language_field'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option>
							<?php endforeach; ?>
						<?php endif; ?> 
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_customer_name')?> </label>
					<div class="subtext"><?=lang('orders_customer_name_instructions')?></div>
 				</td>
				<td style='width:50%;'>
					<select name='orders_customer_name' class='select field_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_customer_name'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option>
							<?php endforeach; ?>  
						<?php endif; ?>              
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_customer_email')?> </label>
					<div class="subtext"><?=lang('orders_customer_email_instructions')?></div>
 				</td>
				<td style='width:50%;'>
					<select name='orders_customer_email' class='select field_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_customer_email'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option>
							<?php endforeach; ?>
						<?php endif; ?>            
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_customer_ip_address')?> </label>
 				</td>
				<td style='width:50%;'>
					<select name='orders_customer_ip_address' class='select field_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_customer_ip_address'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option>
							<?php endforeach; ?>
						<?php endif; ?> 
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_customer_phone')?> </label>
 				</td>
				<td style='width:50%;'>
					<select name='orders_customer_phone' class='select field_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_customer_phone'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option>
							<?php endforeach; ?>
						<?php endif; ?> 
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_full_billing_address')?> </label>
					<div class="subtext"><?=lang('orders_full_billing_address_instructions')?></div>
 				</td>
				<td style='width:50%;'>
					<select name='orders_full_billing_address' class='select field_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_full_billing_address'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_billing_first_name')?> </label>
 				</td>
				<td style='width:50%;'>
					<select name='orders_billing_first_name' class='select field_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_billing_first_name'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option>
							<?php endforeach; ?>
						<?php endif; ?> 
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_billing_last_name')?> </label>
 				</td>
				<td style='width:50%;'>
					<select name='orders_billing_last_name' class='select field_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_billing_last_name'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option>
							<?php endforeach; ?>
						<?php endif; ?> 
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_billing_company')?> </label>
 				</td>
				<td style='width:50%;'>
					<select name='orders_billing_company' class='select field_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_billing_company'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option>
							<?php endforeach; ?>
						<?php endif; ?> 
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_billing_address')?> </label>
 				</td>
				<td style='width:50%;'>
					<select name='orders_billing_address' class='select field_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_billing_address'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_billing_address2')?> </label>
 				</td>
				<td style='width:50%;'>
					<select name='orders_billing_address2' class='select field_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_billing_address2'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_billing_city')?> </label>
 				</td>
				<td style='width:50%;'>
					<select name='orders_billing_city' class='select field_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_billing_city'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option>
							<?php endforeach; ?> 
						<?php endif; ?> 
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_billing_state')?> </label>
 				</td>
				<td style='width:50%;'>
					<select name='orders_billing_state' class='select field_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_billing_state'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option>
							<?php endforeach; ?>  
						<?php endif; ?>
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_billing_zip')?> </label>
 				</td>
				<td style='width:50%;'>
					<select name='orders_billing_zip' class='select field_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_billing_zip'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option>
							<?php endforeach; ?>
						<?php endif; ?>   
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_billing_country')?> </label>
 				</td>
				<td style='width:50%;'>
					<select name='orders_billing_country' class='select field_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_billing_country'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option>
							<?php endforeach; ?>
						<?php endif; ?>   
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_billing_country_code')?> </label>
 				</td>
				<td style='width:50%;'>
					<select name='orders_country_code' class='select field_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_country_code'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option>
							<?php endforeach; ?>
						<?php endif; ?> 
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_full_shipping_address')?> </label>
 				</td>
				<td style='width:50%;'>
					<select name='orders_full_shipping_address' class='select field_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_full_shipping_address'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option>
							<?php endforeach; ?>  
						<?php endif; ?>
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_shipping_first_name')?> </label>
 				</td>
				<td style='width:50%;'>
					<select name='orders_shipping_first_name' class='select field_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_shipping_first_name'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option>
							<?php endforeach; ?> 
						<?php endif; ?> 
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_shipping_last_name')?> </label>
 				</td>
				<td style='width:50%;'>
					<select name='orders_shipping_last_name' class='select field_orders' >
			   			<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_shipping_last_name'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option>
							<?php endforeach; ?> 
						<?php endif; ?>
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_shipping_company')?> </label>
 				</td>
				<td style='width:50%;'>
					<select name='orders_shipping_company' class='select field_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_shipping_company'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option>
							<?php endforeach; ?>
						<?php endif; ?> 
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_shipping_address')?> </label>
 				</td>
				<td style='width:50%;'>
					<select name='orders_shipping_address' class='select field_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_shipping_address'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_shipping_address2')?> </label>
 				</td>
				<td style='width:50%;'>
					<select name='orders_shipping_address2' class='select field_orders' >
			   			<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_shipping_address2'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option>   
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_shipping_city')?> </label>
 				</td>
				<td style='width:50%;'>
					<select name='orders_shipping_city' class='select field_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_shipping_city'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option>
							<?php endforeach; ?>
						<?php endif; ?>  
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_shipping_state')?> </label>
 				</td>
				<td style='width:50%;'>
					<select name='orders_shipping_state' class='select field_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_shipping_state'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option> 
							<?php endforeach; ?>
						<?php endif; ?> 
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_shipping_zip')?> </label>
 				</td>
				<td style='width:50%;'>
					<select name='orders_shipping_zip' class='select field_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_shipping_zip'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option>   
							<?php endforeach; ?> 
						<?php endif; ?>
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_shipping_country')?> </label>
 				</td>
				<td style='width:50%;'>
					<select name='orders_shipping_country' class='select field_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_shipping_country'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option>   
							<?php endforeach; ?> 
						<?php endif; ?>
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_shipping_country_code')?> </label>
 				</td>
				<td style='width:50%;'>
					<select name='orders_shipping_country_code' class='select field_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_shipping_country_code'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option>
							<?php endforeach; ?>
						<?php endif; ?> 
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_site_id')?> </label>
 				</td>
				<td style='width:50%;'>
					<select name='orders_site_id' class='select field_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_site_id'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option>
							<?php endforeach; ?>
						<?php endif; ?> 
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_subscription_id')?> </label>
 				</td>
				<td style='width:50%;'>
					<select name='orders_subscription_id' class='select field_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_subscription_id'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option>
							<?php endforeach; ?>
						<?php endif; ?> 
					</select>
				</td>
			</tr>
			<tr class="<?=alternator('even', 'odd')?>">
				<td>
					<label> <?=lang('orders_vault_id')?> </label>
 				</td>
				<td style='width:50%;'>
					<select name='orders_vault_id' class='select field_orders' >
						<option value='' class="blank" ></option>
						<?php if ($settings['orders_channel'] && isset($fields[$settings['orders_channel']])) : ?>
							<?php foreach ($fields[$settings['orders_channel']] as $field) : ?>
								<option value="<?=$field['field_id']?>" <?php if ($settings['orders_vault_id'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>>
									<?=$field['field_label']?>
								</option>
							<?php endforeach; ?>
						<?php endif; ?> 
					</select>
				</td>
			</tr>
 		</tbody>
	</table>
