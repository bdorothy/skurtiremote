<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

 	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
		<caption><?=lang('email_customer_header')?></caption>
		<thead class="">
			<tr>
				<th colspan="2">
				<strong><?=lang('email_customer_header')?></strong><br />
				<?=lang('email_customer_description')?>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr class="even">
				<td>
					<label><?=lang('email_customer_form_variables')?></label>
				</td>
				<td style='width:50%;'>
					{transaction_id}<br />
					{shipping}<br />
					{tax}<br />
					{subtotal}<br />
					{total}<br />
					{customer_name}<br />
					{customer_email}<br />
					{customer_phone}<br />
					{coupon_codes}<br />
					{last_four_digits}<br />
					{full_billing_address}<br />
					{full_shipping_address}<br />
					{billing_first_name}<br />
					{billing_last_name}<br />
					{billing_address}<br />
					{billing_address2}<br />
					{billing_city}<br />
					{billing_state}<br />
					{billing_zip}<br />
					{shipping_first_name}<br />
					{shipping_last_name}<br />
					{shipping_address}<br />
					{shipping_address2}<br />
					{shipping_city}<br />
					{shipping_state}<br />
					{shipping_zip}<br />
					{shipping_option}	
				</td>
			</tr>
			<tr class="odd">
				<td>
					<label><?=lang('send_customer_email')?></label>
				</td>
				<td style='width:50%;'>
					<label class="radio"><input class='radio' type='radio' name='send_confirmation_email' value='1' <?php if ($settings['send_confirmation_email']) : ?>checked='checked'<?php endif; ?> /> <?=lang('yes')?></label></label>
					<label class="radio"><input class='radio' type='radio' name='send_confirmation_email' value='0' <?php if ( ! $settings['send_confirmation_email']) : ?>checked='checked'<?php endif; ?> /> <?=lang('no')?></label></label>
				</td>
			</tr>
			<tr class="even">
				<td>
					<label><?=lang('email_customer_notification_from')?></label>
				</td>
				<td style='width:50%;'>
					<?=form_input('email_order_confirmation_from', $settings['email_order_confirmation_from'])?>
				</td>
			</tr>
			<tr class="odd">
				<td>
					<label><?=lang('email_customer_notification_from_name')?></label>
				</td>
				<td style='width:50%;'>
					<?=form_input('email_order_confirmation_from_name', $settings['email_order_confirmation_from_name'])?>
				</td>
			</tr>
			<tr class="even">
				<td>
					<label><?=lang('email_customer_notification_subject')?></label>
				</td>
				<td style='width:50%;'>
					<?=form_input('email_order_confirmation_subject', $settings['email_order_confirmation_subject'])?>
				</td>
			</tr>
			<tr class="odd">
				<td>
					<label><?=lang('email_customer_notification_type')?></label>
				</td>
				<td style='width:50%;'>
					<label class="radio"><input class='radio' type='radio' name='email_order_confirmation_plaintext' value='0' <?php if ( ! $settings['email_order_confirmation_plaintext']) : ?>checked='checked'<?php endif; ?> /> <?=lang('email_customer_notification_html')?></label>
					<label class="radio"><input class='radio' type='radio' name='email_order_confirmation_plaintext' value='1' <?php if ($settings['email_order_confirmation_plaintext']) : ?>checked='checked'<?php endif; ?> /> <?=lang('email_customer_notification_plaintext')?></label>
				</td>
			</tr>
			<tr class="odd">
				<td>
					<label><?=lang('email_customer_notification')?> <br /><?=lang('email_customer_body_note')?><br /><br /> ex: {exp:channel:entries entry_id="ORDER_ID"}... </label>
				</td>
				<td style='width:50%;'>
					<?=form_textarea('email_order_confirmation',$settings['email_order_confirmation'])?>
				</td>
			</tr>
		</tbody>
	</table>
