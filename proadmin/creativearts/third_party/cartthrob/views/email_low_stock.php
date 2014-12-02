<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
 
 	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
		<caption><?=lang('email_low_stock_header')?></caption>
		<thead class="">
			<tr>
				<th colspan="2">
				<strong><?=lang('email_low_stock_header')?></strong><br />
				<?=lang('email_low_stock_description')?>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr class="odd">
				<td>
					<label><?=lang('low_stock_value')?></label>
				</td>
				<td style='width:50%;'>
					<?=form_input('low_stock_level', $settings['low_stock_level'])?>
				</td>
			</tr>
			<tr class="even">
				<td>
					<label><?=lang('email_form_variables')?></label>
				</td>
				<td style='width:50%;'>
					ENTRY_ID,
					STOCK_LEVEL
				</td>
			</tr>
			<tr class="odd">
				<td>
					<label><?=lang('send_email')?></label>
				</td>
				<td style='width:50%;'>
					<label class="radio"><input class='radio' type='radio' name='send_inventory_email' value='1' <?php if ($settings['send_inventory_email']) : ?>checked='checked'<?php endif; ?> /> <?=lang('yes')?></label>
					<label class="radio"><input class='radio' type='radio' name='send_inventory_email' value='0' <?php if ( ! $settings['send_inventory_email']) : ?>checked='checked'<?php endif; ?> /> <?=lang('no')?></label>
				</td>
			</tr>
			<tr class="even">
				<td>
					<label><?=lang('admin_email')?></label>
				</td>
				<td style='width:50%;'>
					<?=form_input('low_stock_email', $settings['low_stock_email'])?>
				</td>
			</tr>
			<tr class="odd">
				<td>
					<label><?=lang('email_admin_notification_from')?></label>
				</td>
				<td style='width:50%;'>
					<?=form_input('email_inventory_notification_from', $settings['email_inventory_notification_from'])?>
				</td>
			</tr>
			<tr class="even">
				<td>
					<label><?=lang('email_admin_notification_from_name')?></label>
				</td>
				<td style='width:50%;'>
					<?=form_input('email_inventory_notification_from_name', $settings['email_inventory_notification_from_name'])?>
				</td>
			</tr>
			<tr class="odd">
				<td>
					<label><?=lang('email_admin_notification_subject')?></label>
				</td>
				<td style='width:50%;'>
					<?=form_input('email_inventory_notification_subject', $settings['email_inventory_notification_subject'])?>
				</td>
			</tr>
			<tr class="even">
				<td>
					<label><?=lang('email_low_stock_notification_type')?></label>
				</td>
				<td style='width:50%;'>
					<label class="radio"><input class='radio' type='radio' name='email_low_stock_notification_plaintext' value='0' <?php if ( ! $settings['email_low_stock_notification_plaintext']) : ?>checked='checked'<?php endif; ?> /> <?=lang('email_admin_notification_html')?></label>
					<label class="radio"><input class='radio' type='radio' name='email_low_stock_notification_plaintext' value='1' <?php if ($settings['email_low_stock_notification_plaintext']) : ?>checked='checked'<?php endif; ?> /> <?=lang('email_admin_notification_plaintext')?></label>
				</td>
			</tr>
			<tr class="odd">
				<td>
				<label><?=lang('email_admin_notification')?> <br /><?=lang('email_low_stock_body_note')?><br /><br /> ex: {exp:channel:entries entry_id="ENTRY_ID"}... </label>
				</td>
				<td style='width:50%;'>
					<?=form_textarea('email_inventory_notification', $settings['email_inventory_notification'])?>
				</td>
			</tr>
		</tbody>
	</table>
