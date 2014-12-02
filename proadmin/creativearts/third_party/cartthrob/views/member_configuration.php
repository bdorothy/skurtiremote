<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

 	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
		<caption><?=lang('member_configuration_header')?></caption>
		<thead class="">
			<tr>
				<th colspan="2">
					<strong><?=lang('members_form_header')?></strong><br />
					<?=lang('members_description')?>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr  class="<?=alternator('odd', 'even')?>">
				<td>
					<label><?=lang('members_save_data')?>?</label>
					<div class="subtext"><?=lang('members_saving_instructions')?></div>
 				</td>
				<td style='width:50%;'>
 						<input class='radio' type='radio' name='save_member_data' value='1' <?php if ($settings['save_member_data']) : ?>checked='checked'<?php endif; ?> />
						<?=lang('yes')?>
 						<input class='radio' type='radio' name='save_member_data' value='0' <?php if ( ! $settings['save_member_data']) : ?>checked='checked'<?php endif; ?> /> 
						<?=lang('no')?>
				</td>
			</tr>
			<?php if ($profile_edit_active): ?>
			<tr  class="<?=alternator('odd', 'even')?>">
				<td>
					<label><?=lang('members_use_profile_edit')?>?</label>
					<div class="subtext"><?=lang('profile_edit_saving_instructions')?></div>
 				</td>
				<td style='width:50%;'>
 						<input class='radio' type='radio' name='use_profile_edit' value='1' <?php if ($settings['use_profile_edit']) : ?>checked='checked'<?php endif; ?> />
						<?=lang('yes')?>
 						<input class='radio' type='radio' name='use_profile_edit' value='0' <?php if ( ! $settings['use_profile_edit']) : ?>checked='checked'<?php endif; ?> /> 
						<?=lang('no')?>
				</td>
			</tr>
			<?php endif; ?>
		</tbody>
	</table>

 	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
		<thead class="">
			<tr>
				<th>
					<strong></strong><br />
					<?=lang('member_data_template_fields')?>	
				</th>
				<th>
					<strong></strong><br />
					<?=lang('member_data_custom_fields')?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($customer_data_fields as $field) : ?>
			<tr class="<?=alternator('odd', 'even')?>">
				<td>
					<label><?=humanize($field)?> (<?=$field?>)</label>
 				</td>
				<td style='width:50%;'>
					<?php if ($field === 'email_address') : ?>
						<?=form_dropdown('member_email_address_field', array(lang('members_built_in_fields') => array('email' => lang('email')), lang('members_custom_fields') => $member_fields), (isset($settings['member_email_address_field'])?$settings['member_email_address_field']:''))?>
					<?php else : ?>
						<?=form_dropdown('member_'.$field.'_field', $member_fields, (isset($settings['member_'.$field.'_field'])?$settings['member_'.$field.'_field']:''))?>
					<?php endif; ?>
				</td>
			</tr>
			<?php endforeach; ?>
 		</tbody>
	</table>
	
	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
		<caption><?=lang('member_login_options')?></caption>
		<thead class="">
			<tr>
				<th colspan="2">
					<strong><?=lang('member_creation_options')?></strong><br />
					<?=lang('member_creation_options_description')?>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr class="even">
				<td>
					<label><?=lang('member_login_options_header')?></label>
					<div class="subtext"><?=lang('member_login_options_description')?></div>
 				</td>
				<td style='width:50%;'>
					<?=form_dropdown('checkout_registration_options', 
						array('auto-login'	=> lang('member_auto_login'),'use_ee_settings'	=> lang('member_use_ee_settings'), ), 
						(isset($settings['checkout_registration_options'])?$settings['checkout_registration_options']:'')
						)?>
				</td>
			</tr>
		</tbody>
	</table>
	