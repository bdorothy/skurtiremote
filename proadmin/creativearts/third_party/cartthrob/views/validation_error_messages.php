<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
	<!-- start instruction box -->
	<div class="ct_instruction_box">
		<h2><?=lang('validation_header')?></h2>
		<p><?=lang('validation_description')?></p>
	</div>

	<!-- end instruction box -->
	<div id="ct_form_options">
		<div class="ct_form_header">
			<h2><?=lang('validation_form_header')?></h2>
		</div>

		<fieldset >
			<label><?=lang('validation_missing_fields')?></label>
			<input dir='ltr' type='text' name='customer_info_validation_msg' id='customer_info_validation_msg' value='<?=$settings['customer_info_validation_msg']?>' size='90' maxlength='100' />
		</fieldset>

		<div class="ct_form_header">
			<h2><?=lang('validation_customer_name')?></h2>
		</div>
		<div class="legend">
			<div class="ct_instruction_left"> 
				<strong><?=lang('validation_form_field_name')?></strong><br />
				<?=lang('validation_form_field_description')?>
			</div>
			<div class="ct_instruction_right"> 
				<strong><?=lang('validation_error_display_text')?></strong><br />
				<?=lang('validation_error_display_description')?>
			</div>
			<div class="clear"></div>
		</div>

		<fieldset >
			<label><?=lang('validation_first_name')?></label>
			<input name="customer_field_labels[first_name]" type="text" value="<?=@$settings['customer_field_labels']['first_name']?>" />
		</fieldset>
	
		<fieldset >
			<label><?=lang('validation_last_name')?></label>
			<input name="customer_field_labels[last_name]" type="text" value="<?=@$settings['customer_field_labels']['last_name']?>" />
		</fieldset>
	
		<div class="ct_form_header">
			<h2><?=lang('validation_customer_billing_address')?></h2>
		</div>
	
		<fieldset >
			<label><?=lang('validation_address')?></label>
			<input name="customer_field_labels[address]" type="text" value="<?=@$settings['customer_field_labels']['address']?>" />
		</fieldset>

		<fieldset>
			<label><?=lang('validation_address2')?></label>
			<input name="customer_field_labels[address2]" type="text" value="<?=@$settings['customer_field_labels']['address2']?>" />
		</fieldset>

		<fieldset >
			<label><?=lang('validation_city')?></label>
			<input name="customer_field_labels[city]" type="text" value="<?=@$settings['customer_field_labels']['city']?>" />
		</fieldset>

		<fieldset >
			<label><?=lang('validation_state')?></label>
			<input name="customer_field_labels[state]" type="text" value="<?=@$settings['customer_field_labels']['state']?>" />
		</fieldset>

		<fieldset >
			<label><?=lang('validation_zip')?></label>
			<input name="customer_field_labels[zip]" type="text" value="<?=@$settings['customer_field_labels']['zip']?>" />
		</fieldset>

		<fieldset>
			<label><?=lang('validation_country')?></label>
			<input name="customer_field_labels[country]" type="text" value="<?=@$settings['customer_field_labels']['country']?>" />
		</fieldset>

		<div class="ct_form_header">
			<h2><?=lang('validation_customer_contact_info')?></h2>
		</div>

		<fieldset >
			<label><?=lang('validation_phone')?></label>
			<input name="customer_field_labels[phone]" type="text" value="<?=@$settings['customer_field_labels']['phone']?>" />
		</fieldset>

		<fieldset>
			<label><?=lang('validation_email_address')?></label>
			<input name="customer_field_labels[email_address]" type="text" value="<?=@$settings['customer_field_labels']['email_address']?>" />
		</fieldset>

		<fieldset>
			<label><?=lang('validation_company')?></label>
			<input name="customer_field_labels[company]" type="text" value="<?=@$settings['customer_field_labels']['company']?>" />
		</fieldset>

		<div class="ct_form_header">
			<h2><?=lang('validation_customer_shipping_address')?></h2>
		</div>

		<fieldset>
			<label><?=lang('validation_shipping_first_name')?></label>
			<input name="customer_field_labels[shipping_first_name]" type="text" value="<?=@$settings['customer_field_labels']['shipping_first_name']?>" />
		</fieldset>

		<fieldset>
			<label><?=lang('validation_shipping_last_name')?></label>
			<input name="customer_field_labels[shipping_last_name]" type="text" value="<?=@$settings['customer_field_labels']['shipping_last_name']?>" />
		</fieldset>

		<fieldset>
			<label><?=lang('validation_shipping_address')?></label>
			<input name="customer_field_labels[shipping_address]" type="text" value="<?=@$settings['customer_field_labels']['shipping_address']?>" />
		</fieldset>

		<fieldset>
			<label><?=lang('validation_shipping_address2')?></label>
			<input name="customer_field_labels[shipping_address2]" type="text" value="<?=@$settings['customer_field_labels']['shipping_address2']?>" />
		</fieldset>

		<fieldset>
			<label><?=lang('validation_shipping_city')?></label>
			<input name="customer_field_labels[shipping_city]" type="text" value="<?=@$settings['customer_field_labels']['shipping_city']?>" />
		</fieldset>

		<fieldset>
			<label><?=lang('validation_shipping_state')?></label>
			<input name="customer_field_labels[shipping_state]" type="text" value="<?=@$settings['customer_field_labels']['shipping_state']?>" />
		</fieldset>

		<fieldset>
			<label><?=lang('validation_shipping_zip')?></label>
			<input name="customer_field_labels[shipping_zip]" type="text" value="<?=@$settings['customer_field_labels']['shipping_zip']?>" />
		</fieldset>

		<fieldset>
			<label><?=lang('validation_shipping_option')?></label>
			<input name="customer_field_labels[shipping_option]" type="text" value="<?=@$settings['customer_field_labels']['shipping_option']?>" />
		</fieldset>

		<div class="ct_form_header">
			<h2><?=lang('validation_credit_card_information')?></h2>
		</div>

		<fieldset >
			<label><?=lang('validation_credit_card_number')?></label>
			<input name="customer_field_labels[credit_card_number]" type="text" value="<?=@$settings['customer_field_labels']['credit_card_number']?>" />
		</fieldset>

		<fieldset >
			<label><?=lang('validation_expiration_month')?></label>
			<input name="customer_field_labels[expiration_month]" type="text" value="<?=@$settings['customer_field_labels']['expiration_month']?>" />
		</fieldset>

		<fieldset >
			<label><?=lang('validation_expiration_year')?></label>
			<input name="customer_field_labels[expiration_year]" type="text" value="<?=@$settings['customer_field_labels']['expiration_year']?>" />
		</fieldset>

		<fieldset>
			<label><?=lang('validation_card_code')?></label>
			<input name="customer_field_labels[card_code]" type="text" value="<?=@$settings['customer_field_labels']['card_code']?>" />
		</fieldset>
	</div>