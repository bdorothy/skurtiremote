<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
 
$groups = array('admin', 'customer'); 

foreach ($groups as $group) : 
	$count = 0; 
	if ($count >1)
	{
		$caption = ${$status.'_notification_options_lang'};
	}
	else
	{
		$caption = lang('order_status_notifications_header');
	}
	
	$count ++; 
	foreach ( $orders_status as $item) :
		
		// all settings will be instantiated in case they're not already set
		$status = $group."_".$item; 

		$settings_array = array(
			$status.'_send_email',
			$status.'_email_to',
			$status.'_email_from',
			$status.'_email_from_name',
			$status.'_email_subject',
			$status.'_plain_text',
			$status.'_email_message',
			$status.'_notification_header',
			$status.'_notification_options',
			$status.'_notification_description',
			$status.'_email_message_note',
			
		); 

		foreach ($settings_array as $st)
		{
			if (!isset($settings[$st]))
			{
				$settings[$st] = NULL; 
			}
			// if no language is set, then use the setting itself to generate the language
			if (lang($st) == $st)
			{
				${$st."_lang"} = ucwords(str_replace('_', ' ', $st));
  
			}
			else
			{
				${$st."_lang"} = lang($st); 
			}
		}

?>
	
		 	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
				<caption><?=$caption?></caption>
				<thead class="">
					<tr>
						<th colspan="2">
						<strong><?=${$status.'_notification_header_lang'}?></strong><br />
						<?=${$status.'_notification_description_lang'}?>
						</th>
					</tr>
				</thead>
				<tbody>
					<tr class="even">
						<td>
							<label><?=lang('email_form_variables')?></label>
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
							<label><?=${$status.'_send_email_lang'}?></label>
						</td>
						<td style='width:50%;'>
							<label class="radio"><input class='radio' type='radio' name='<?=$status?>_send_email' value='1' <?php if ($settings[$status.'_send_email']) : ?>checked='checked'<?php endif; ?> /> <?=lang('yes')?></label>
							<label class="radio"><input class='radio' type='radio' name='<?=$status?>_send_email' value='0' <?php if ( ! $settings[$status.'_send_email']) : ?>checked='checked'<?php endif; ?> /> <?=lang('no')?></label>
						</td>
					</tr>
					<tr class="even">
						<td>
							<label><?=${$status.'_email_to_lang'}?></label>
						</td>
						<td style='width:50%;'>
							<?=form_input($status.'_email_to', $settings[$status.'_email_to'])?>
						</td>
					</tr>
					<tr class="odd">
						<td>
							<label><?=${$status.'_email_from_lang'}?> (<?=lang('variables')?>: {customer_email})</label>
						</td>
						<td style='width:50%;'>
							<?=form_input($status.'_email_from', $settings[$status.'_email_from'])?>
						</td>
					</tr>
					<tr class="even">
						<td>
							<label><?=${$status.'_email_from_name_lang'}?> (<?=lang('variables')?>: {customer_name})</label>
						</td>
						<td style='width:50%;'>
							<?=form_input($status.'_email_from_name', $settings[$status.'_email_from_name'])?>
						</td>
					</tr>
					<tr class="odd">
						<td>
							<label><?=${$status.'_email_subject_lang'}?></label>
						</td>
						<td style='width:50%;'>
							<?=form_input($status.'_email_subject', $settings[$status.'_email_subject'])?>
						</td>
					</tr>
					<tr class="even">
						<td>
							<label><?=lang('email_type')?></label>
						</td>
						<td style='width:50%;'>
							<label class="radio"><input class='radio' type='radio' name='<?=$status?>_plain_text' value='0' <?php if ( ! $settings[$status.'_plain_text']) : ?>checked='checked'<?php endif; ?> /> <?=lang('send_html_email')?></label>
							<label class="radio"><input class='radio' type='radio' name='<?=$status?>_plain_text' value='1' <?php if ($settings[$status.'_plain_text']) : ?>checked='checked'<?php endif; ?> /> <?=lang('send_text_email')?></label>
						</td>
					</tr>
					<tr class="odd">
						<td>
						<label<?=${$status.'_email_message_lang'}?> <br /><?=${$status.'_email_message_note_lang'}?><br /><br /> ex: {exp:channel:entries entry_id="ORDER_ID"}... </label>
						</td>
						<td style='width:50%;'>
							<?=form_textarea($status.'_email_message', $settings[$status.'_email_message'])?>
						</td>
					</tr>
				</tbody>
			</table>
<?php 

	endforeach; 
endforeach; 


 