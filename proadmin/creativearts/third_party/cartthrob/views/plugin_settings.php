<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
//@NOTE: if there are too many plugins, it's possible that an out of memory error will occur. Remove unneeded plugins if a white page, or an unusual redirect occurs on a plugin settings page. 
?>

<?php foreach ($plugins as $plugin) : ?>
	<div style="display:none;" class="<?=$plugin_type?>_settings" id="<?=$plugin['classname']?>">

		<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
			<thead class="">
				<tr>
					<th colspan="2">
						<strong><?=lang($plugin['title'])?> <?=lang('settings')?></strong><br />
					</th>
				</tr>
			</thead>
			<tbody>
				<?php if (!empty($plugin['note'])) : ?>
				<tr class="<?=alternator('odd', 'even')?>">
					<td colspan="2">
						<div class="subtext note"><?=lang('gateway_settings_note_title')?></div>
						<?=lang($plugin['note'])?>
					</td>
				</tr>
				<?php endif; ?>
				<?php if (!empty($plugin['overview'])) : ?>
				<tr class="<?=alternator('odd', 'even')?>">
					<td colspan="2">
 						<div class="ct_overview">
							<?=lang($plugin['overview'])?>
						</div>
					</td>
				</tr>
				<?php endif; ?>
				<?php if (!empty($plugin['affiliate'])) : ?>
				<tr class="<?=alternator('odd', 'even')?>">
					<td>
						<div class="subtext"><?=lang('gateway_settings_affiliate_title')?></div>
					</td>
					<td style='width:50%;'>
						<?=lang($plugin['affiliate'])?>
					</td>
				</tr>
				<?php endif; ?>
			</tbody>
		</table>
 
		<?php if (is_array($plugin['settings'])) : ?>
			<?php foreach ($plugin['settings'] as $setting) : ?>
				<?php if ($setting['type'] == 'matrix') : ?>
					<?php
					    //retrieve the current set value of the field
					    $current_values = (isset($settings[$plugin['classname'].'_settings'][$setting['short_name']])) ?
					 		$settings[$plugin['classname'].'_settings'][$setting['short_name']] : FALSE;
					    
					    //set the value to the default value if there is no set value and the default value is defined
					    $current_values = ($current_values === FALSE && isset($setting['default'])) ? 
							$setting['default'] : $current_values;
					?>
					<div class="matrix">
						<table cellpadding="0" cellspacing="0" border="0" class="mainTable padTable">
							<thead>
							    <tr>
									<th></th>
									<?php foreach ($setting['settings'] as $count => $matrix_setting) : ?>
									<?php
										$style=""; 
									    $setting['settings'][$count]['style'] = $style;
									?>
									<th>
										<strong><?=lang($matrix_setting['name'])?></strong><?=(isset($matrix_setting['note'])) ? '<br />'.lang($matrix_setting['note']) : ''?>
									</th>
									<?php endforeach; ?>
									<th style="width:20px;"></th>
							    </tr>
							</thead>
							<tbody>
								<?php
									if ($current_values === FALSE || ! count($current_values))
									{
										$current_values = array(array());
										foreach ($setting['settings'] as $matrix_setting)
										{
											$current_values[0][$matrix_setting['short_name']] = isset($matrix_setting['default']) ? $matrix_setting['default'] : '';
										}
									}
								?>
								<?php foreach ($current_values as $count => $current_value) : ?>
									<tr class="<?=$plugin['classname'].'_'.$setting['short_name']?>_setting" 
										rel = "<?=$plugin['classname'].'_settings['.$setting['short_name'].']'?>" 		
										id="<?=$plugin['classname'].'_'.$setting['short_name']?>_setting_<?=$count?>">
										<td><img border="0" src='<?=URL_THIRD_THEMES?>cartthrob/images/ct_drag_handle.gif' width="10" height="17" /></td>
										<?php foreach ($setting['settings'] as $matrix_setting) : ?>
											<td  style="<?=$matrix_setting['style']?>" rel="<?=$matrix_setting['short_name']?>"><?=$cartthrob_mcp->plugin_setting($matrix_setting['type'], $plugin['classname'].'_settings['.$setting['short_name'].']['.$count.']['.$matrix_setting['short_name'].']', @$current_value[$matrix_setting['short_name']], @$matrix_setting['options'], @$matrix_setting['attributes'])?></td>
										<?php endforeach; ?>
										<td>
											<a href="#" class="remove_matrix_row">
												<img border="0" src='<?=$this->config->item('theme_folder_url')?>cp_themes/default/images/content_custom_tab_delete.png' />
											</a>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
					
					<fieldset class="plugin_add_new_setting">
						<a href="#" class="ct_add_matrix_row" id="add_new_<?=$plugin['classname'].'_'.$setting['short_name']?>">
							<?=lang('add_another_row')?>
						</a>
					</fieldset>
	
					<table style="display: none;" class="<?=$plugin['classname']?>">
						<tr id="<?=$plugin['classname'].'_'.$setting['short_name']?>_blank" class="<?=$setting['short_name']?>">
							<td  ><img border="0" src='<?=URL_THIRD_THEMES?>cartthrob/images/ct_drag_handle.gif' width="10" height="17" /></td>
							
							<?php foreach ($setting['settings'] as $matrix_setting) : ?>
								<td  class="<?=$matrix_setting['short_name']?>" style="<?=$matrix_setting['style']?>"><?=$cartthrob_mcp->plugin_setting($matrix_setting['type'], '', (isset($matrix_setting['default'])) ? $matrix_setting['default'] : '', @$matrix_setting['options'], @$matrix_setting['attributes'])?></td>
							<?php endforeach; ?>
							<td>
								<a href="#" class="remove_matrix_row"><img border="0" src='<?=$this->config->item('theme_folder_url')?>cp_themes/default/images/content_custom_tab_delete.png' /></a>
							</td>
						</tr>
					</table>
					<?php elseif ($setting['type'] == 'header') : ?>
						<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
							<thead class="">
								<tr>
									<th colspan="2">
										<strong><?=$setting['name']?></strong><br />
									</th>
								</tr>
							</thead>
						</table>
					<?php elseif ($setting['type'] == 'add_to_head') : ?>
							<?=$cartthrob_mcp->plugin_setting('add_to_head', '', (isset($setting['default'])) ? $setting['default'] : '')?>
					<?php elseif ($setting['type'] == 'add_to_foot') : ?>
							<?=$cartthrob_mcp->plugin_setting('add_to_foot', '', (isset($setting['default'])) ? $setting['default'] : '')?>
					<?php else : ?>
						<?php
							//retrieve the current set value of the field
							$current_value = (isset($settings[$plugin['classname'].'_settings'][$setting['short_name']])) ? $settings[$plugin['classname'].'_settings'][$setting['short_name']] : FALSE;
							//set the value to the default value if there is no set value and the default value is defined
							$current_value = ($current_value === FALSE && isset($setting['default'])) ? $setting['default'] : $current_value;
						?>
						<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
						<tbody>
							<tr class="even">
								<td>
									<label><?=lang($setting['name'])?></label><br><span class="subtext"><?=(isset($setting['note'])) ? lang($setting['note']) : ''?></span>
 								</td>
								<td style='width:50%;'>
									<?=$cartthrob_mcp->plugin_setting($setting['type'], $plugin['classname'].'_settings['.$setting['short_name'].']', $current_value, @$setting['options'], @$setting['attributes'])?>
								</td>
							</tr>
						</tbody>
						</table>

						
				<?php endif; ?>
	
			<?php endforeach; ?>
		<?php endif; ?>
 		<?php if (!empty($plugin['required_fields'])) : ?>
 			<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
				<thead class="">
					<tr>
						<th >
							<strong><?=lang('gateways_required_fields')?></strong><br />
							<?=lang('gateways_required_fields_description')?>
							
						</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($plugin['required_fields'] as $value) : ?>
 					<tr class="<?=alternator('odd', 'even')?>">
						<td>
							<?=$value?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
		<?php if (!empty($plugin['html'])) : ?>
 			<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
				<thead class="">
					<tr>
						<th >
							<?=lang('gateways_sample_html')?><br /><br />

						</th>
					</tr>
				</thead>
				<tbody>
 					<tr class="<?=alternator('odd', 'even')?>">
						<td>
							<textarea rows="50" style="font-size:10px;"><?=htmlentities($plugin['html'])?></textarea>
							
						</td>
					</tr>
				</tbody>
			</table>
		<?php endif; ?>
		
		<?php if ($plugin_type === 'payment_gateway' && count($plugins) > 1) : ?>
		<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
			<thead class="">
				<tr>
					<th colspan="2">
						<strong><?=lang($plugin['title'])?> <?=lang('settings')?></strong><br />
					</th>
				</tr>
			</thead>
			<tbody>
				<tr class="<?=alternator('odd', 'even')?>">
					<td>
						<div class="subtext"><?=lang('plugin_select')?></div>
					</td>
					<td style='width:50%;'>
						<?=Cartthrob_core::get_class($plugin['classname']);?>
					</td>
				</tr>
				<tr class="<?=alternator('odd', 'even')?>">
					<td>
						<div class="subtext"><?=lang('gateways_form_input')?></div>
					</td>
					<td style='width:50%;'>
						<?=($encoded = get_instance()->encrypt->encode(Cartthrob_core::get_class($plugin['classname'])))?>
					</td>
				</tr>
				<tr class="<?=alternator('odd', 'even')?>">
					<td>
						<div class="subtext"><?=lang('gateways_form_input_urlencoded')?></div>
					</td>
					<td style='width:50%;'>
						<?=urlencode($encoded)?>
					</td>
				</tr>
			</tbody>
		</table>
		<?php endif; ?>
		
		
 		<?php if (!empty($plugin['additional_template_fields'])) : ?>
 			<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
				<thead class="">
					<tr>
						<th colspan="2">
						<strong><?=lang('plugins_fields')?></strong><br />
						<p><?=lang('plugins_in_addition_to')?><a href='http://livecart.com/docs/tags_detail/checkout_form/'><?=lang('plugins_field_notes')?></p><br />
						</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($plugin['additional_template_fields'] as $template_field) : ?>
 
 					<tr class="<?=alternator('odd', 'even')?>">
						<td>
							<strong <?php if ($template_field['required']) : ?> class='red'<?php endif; ?>><?=$template_field['name']?></strong><br /> 
							<?php if (isset($template_field['description'])) : ?>
								<?=$template_field['description']?> 
							<?php endif; ?>
						</td>
						<td>
							Field Name: <?=$template_field['short_name']?><br />
								<?php if (isset($template_field['option_values'])) : ?>
								Expected values: <?=$template_field['option_values']?> 
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
<?php endforeach; ?>