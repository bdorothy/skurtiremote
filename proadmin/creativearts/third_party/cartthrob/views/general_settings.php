<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

 	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
		<caption><?=lang('general_settings_header')?></caption>
		<thead class="">
			<tr>
				<th colspan="2">
					<strong><?=lang('global_settings_header')?></strong><br />
					<?=lang('global_settings_description')?>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<label><?=lang('logged_in')?></label>
					<div class="subtext"><?=lang('global_settings_login_description')?></div>
				</td>
				<td style='width:50%;'>
					<input class='radio' type='radio' name='logged_in' value='1' <?php if ($settings['logged_in']) : ?>checked='checked'<?php endif; ?> /> 
					<?=lang('yes')?>
					<input class='radio' type='radio' name='logged_in' value='0' <?php if ( ! $settings['logged_in']) : ?>checked='checked'<?php endif; ?> /> 
					<?=lang('no')?>
				</td>
			</tr>
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
 					<label><?=lang('global_settings_default_member_id')?></label>
					<div class="subtext"><?=lang('global_settings_default_member_id_description')?></div>
				</td>
				<td style='width:50%;'>
 					<input  dir='ltr' type='text' name='default_member_id' id='default_member_id' value='<?=$settings['default_member_id']?>' size='90' maxlength='100' />
				</td>
			</tr>
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<label><?=lang('global_settings_session_expire')?></label>
					<div class="subtext"><?=lang('global_settings_session_description')?></div> 
				</td>
				<td style='width:50%;'>
					<input  dir='ltr' type='text' name='session_expire' id='session_expire' value='<?=$settings['session_expire']?>' size='90' maxlength='100' />
				</td>
			</tr>
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<label><?=lang('show_debug')?></label>
  				</td>
				<td style='width:50%;'>
					<input class='radio' type='radio' name='show_debug' value='1' <?php if ($settings['show_debug'] ==1) : ?>checked='checked'<?php endif; ?> /> 
						<?=lang('yes')?>
					<input class='radio' type='radio' name='show_debug' value='0' <?php if ( ! $settings['show_debug']) : ?>checked='checked'<?php endif; ?> /> 
						<?=lang('no')?>
					<input class='radio' type='radio' name='show_debug' value='super_admin' <?php if ( $settings['show_debug'] =="super_admin") : ?>checked='checked'<?php endif; ?> /> 
						<?=lang('super_admins_only')?>
				</td>
			</tr>
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<label><?=lang('global_settings_clear_cart')?></label>
					<div class="subtext"><?=lang('global_settings_clear_cart_description')?></div> 
 				</td>
				<td style='width:50%;'>
					
					<input class='radio' type='radio' name='clear_cart_on_logout' value='1' <?php if ($settings['clear_cart_on_logout']) : ?>checked='checked'<?php endif; ?> /> 
					<?=lang('yes')?>
					<input class='radio' type='radio' name='clear_cart_on_logout' value='0' <?php if ( ! $settings['clear_cart_on_logout']) : ?>checked='checked'<?php endif; ?> />
					<?=lang('no')?>
					
				</td>
			</tr>
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<label><?=lang('global_settings_clear_session')?></label>
					<div class="subtext"><?=lang('global_settings_clear_session_description')?></div> 
 				</td>
				<td style='width:50%;'>
					
					<input class='radio' type='radio' name='clear_session_on_logout' value='1' <?php if ($settings['clear_session_on_logout']) : ?>checked='checked'<?php endif; ?> /> 
					<?=lang('yes')?>
					<input class='radio' type='radio' name='clear_session_on_logout' value='0' <?php if ( ! $settings['clear_session_on_logout']) : ?>checked='checked'<?php endif; ?> />
					<?=lang('no')?>
					
				</td>
			</tr>
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<label><?=lang('allow_empty_cart_checkout')?></label>
					<div class="subtext"><?=lang('allow_empty_cart_checkout_description')?></div> 
 				</td>
				<td style='width:50%;'>
					<input class='radio' type='radio' name='allow_empty_cart_checkout' value='1' <?php if ($settings['allow_empty_cart_checkout']) : ?>checked='checked'<?php endif; ?> /> 
					<?=lang('yes')?>
					<input class='radio' type='radio' name='allow_empty_cart_checkout' value='0' <?php if ( ! $settings['allow_empty_cart_checkout']) : ?>checked='checked'<?php endif; ?> />
					<?=lang('no')?>
					
				</td>
			</tr>
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<label><?=lang('global_settings_quantity_limit')?></label>
					<div class="subtext"><?=lang('global_settings_quantity_description')?></div> 
 				</td>
				<td style='width:50%;'>
					<input  dir='ltr' type='text' name='global_item_limit' id='global_item_limit' value='<?=$settings['global_item_limit']?>' size='90' maxlength='100' />
					
				</td>
			</tr>
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<label><?=lang('global_settings_logging_enabled')?></label>
					<div class="subtext"><?=lang('global_settings_logging_description')?></div> 
 				</td>
				<td style='width:50%;'>
					<input class='radio' type='radio' name='enable_logging' value='1' <?php if ($settings['enable_logging']) : ?>checked='checked'<?php endif; ?> /> 
					<?=lang('yes')?>
					<input class='radio' type='radio' name='enable_logging' value='0' <?php if ( ! $settings['enable_logging']) : ?>checked='checked'<?php endif; ?> />
					<?=lang('no')?>
				</td>
			</tr>
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<label><?=lang('global_settings_cp_menu')?></label>
					<div class="subtext"><?=lang('global_settings_cp_menu_description')?></div> 
 				</td>
				<td style='width:50%;'>
					<input class='radio' type='radio' name='cp_menu' value='1' <?php if ($settings['cp_menu']) : ?>checked='checked'<?php endif; ?> /> 
					<?=lang('yes')?>
					<input class='radio' type='radio' name='cp_menu' value='0' <?php if ( ! $settings['cp_menu']) : ?>checked='checked'<?php endif; ?> />
					<?=lang('no')?>
				</td>
			</tr>
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<label><?=lang('global_settings_cp_menu_label')?></label>
					<div class="subtext"><?=lang('global_settings_cp_menu_label_description')?></div> 
 				</td>
				<td style='width:50%;'>
					<input  dir='ltr' type='text' name='cp_menu_label' id='cp_menu_label' value='<?=$settings['cp_menu_label']?>' size='90' maxlength='100' />
					
				</td>
			</tr>
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<label><?=lang('global_settings_session_use_fingerprint')?></label>
					<div class="subtext"><?=lang('global_settings_session_use_fingerprint_description')?></div> 
 				</td>
				<td style='width:50%;'>
					<input class='radio' type='radio' name='session_use_fingerprint' value='1' <?php if ($settings['session_use_fingerprint']) : ?>checked='checked'<?php endif; ?> /> 
					<?=lang('yes')?>
					<input class='radio' type='radio' name='session_use_fingerprint' value='0' <?php if ( ! $settings['session_use_fingerprint']) : ?>checked='checked'<?php endif; ?> />
					<?=lang('no')?>
				</td>
			</tr>
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<label><?=lang('global_settings_session_fingerprint_method')?></label>
					<div class="subtext"><?=lang('global_settings_session_fingerprint_method_description')?><br><i><?=lang('global_settings_session_fingerprint_method_warning')?></i></div>
 				</td>
				<td style='width:50%;'>
					<?=form_dropdown('session_fingerprint_method', array(lang('global_settings_session_fingerprint_method_0'), lang('global_settings_session_fingerprint_method_1'), lang('global_settings_session_fingerprint_method_2'), lang('global_settings_session_fingerprint_method_3'), lang('global_settings_session_fingerprint_method_4'), lang('global_settings_session_fingerprint_method_5')), $settings['session_fingerprint_method'])?>
				</td>
			</tr>
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<label><?=lang('global_settings_garbage_collection_cron')?></label>
					<?php
					$url = $this->paths->build_action_url('Cartthrob_mcp', 'garbage_collection');
					?>
					<div class="subtext"><?=sprintf(lang('global_settings_garbage_collection_cron_description'), $url, $url)?></div> 
 				</td>
				<td style='width:50%;'>
					<input class='radio' type='radio' name='garbage_collection_cron' value='1' <?php if ($settings['garbage_collection_cron']) : ?>checked='checked'<?php endif; ?> /> 
					<?=lang('yes')?>
					<input class='radio' type='radio' name='garbage_collection_cron' value='0' <?php if ( ! $settings['garbage_collection_cron']) : ?>checked='checked'<?php endif; ?> />
					<?=lang('no')?>
				</td>
			</tr>
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<label><?=lang('global_settings_checkout_form_captcha')?></label>
					<div class="subtext"><?=lang('global_settings_checkout_form_captcha_description')?></div> 
 				</td>
				<td style='width:50%;'>
					<input class='radio' type='radio' name='checkout_form_captcha' value='1' <?php if ($settings['checkout_form_captcha']) : ?>checked='checked'<?php endif; ?> /> 
					<?=lang('yes')?>
					<input class='radio' type='radio' name='checkout_form_captcha' value='0' <?php if ( ! $settings['checkout_form_captcha']) : ?>checked='checked'<?php endif; ?> />
					<?=lang('no')?>
					
				</td>
			</tr>
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<label><?=lang('global_settings_allow_fractional_quantities')?></label>
 				</td>
				<td style='width:50%;'>
					<input class='radio' type='radio' name='allow_fractional_quantities' value='1' <?php if ($settings['allow_fractional_quantities']) : ?>checked='checked'<?php endif; ?> /> 
					<?=lang('yes')?>
					<input class='radio' type='radio' name='allow_fractional_quantities' value='0' <?php if ( ! $settings['allow_fractional_quantities']) : ?>checked='checked'<?php endif; ?> />
					<?=lang('no')?>
				</td>
			</tr>
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<label><?=lang('global_settings_admin_checkout_groups')?></label>
					<div class="subtext"><?=lang('global_settings_admin_checkout_groups_description')?></div>
 				</td>
				<td style='width:50%;'>
					<?=form_hidden('admin_checkout_groups[]', 1)?>
					<?=form_multiselect('admin_checkout_groups[]', $member_groups, $settings['admin_checkout_groups'])?>
				</td>
			</tr>
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<label><?=lang('msm_show_all')?></label>
					<div class="subtext"><?=lang('msm_show_all_description')?></div>
 				</td>
				<td style='width:50%;'>
					<input class='radio' type='radio' name='msm_show_all' value='1' <?php if ($settings['msm_show_all']) : ?>checked='checked'<?php endif; ?> /> 
					<?=lang('yes')?>
					<input class='radio' type='radio' name='msm_show_all' value='0' <?php if ( ! $settings['msm_show_all']) : ?>checked='checked'<?php endif; ?> />
					<?=lang('no')?>
				</td>
			</tr>
		</tbody>
	</table>
