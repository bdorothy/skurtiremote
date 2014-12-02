<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
		<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
			<thead class="">
				<tr>
					<th colspan="2">
			<?=!empty($structure['caption'])?'<caption>'.lang($structure['caption']).'</caption>':'' ?>
						<strong><?=lang($structure['title'])?></strong><br />
						<?=!empty($structure['description'])? lang($structure['description']) : ''?>
					</th>
				</tr>
			</thead>
		<tbody></tbody>
	</table>

		<?php if (is_array($structure['settings'])) : ?>
			<?php foreach ($structure['settings'] as $setting) : ?>
				<?php if ($setting['type'] == 'matrix') : ?>
					<?php
					    //retrieve the current set value of the field
					    $current_values = (isset($settings[ $setting['short_name']]) ) ? $settings[ $setting['short_name']] : FALSE;
					    
					    //set the value to the default value if there is no set value and the default value is defined
					    $current_values = ($current_values === FALSE && isset($setting['default'])) ? 
							$setting['default'] : $current_values;
					?>
			
				<?php if (isset($structure['stacked']) && $structure['stacked'] == TRUE) : ?>
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
						 	<table class="mainTable padTable notification_table"  border="0" cellspacing="0" cellpadding="0">
								<thead><tr><th colspan="2"></th></tr></thead>
								<tbody>
									<?php foreach ($setting['settings'] as $matrix_setting) : ?>
										<tr>
											<td><strong><?=lang($matrix_setting['name'])?></strong><?=(isset($matrix_setting['note'])) ? '<br />'.lang($matrix_setting['note']) : ''?></td>
											<td  rel="<?=$matrix_setting['short_name']?>" class="<?=$matrix_setting['short_name'].'_setting_option'?>">
											<?=$cartthrob_mcp->plugin_setting($matrix_setting['type'], $setting['short_name'].'['.$count.']['.$matrix_setting['short_name'].']', @$current_value[$matrix_setting['short_name']], @$matrix_setting['options'], @$matrix_setting['attributes'])?></td>
										</tr>
									<?php endforeach; ?>
									<tr class="<?php echo alternator('even', 'odd');?>">
										<td colspan="2">
											<a href="#" class="remove_product_table" style="display:block;float:left;margin: 0 0 0 8px;position:data-hashative;top: 9px;">
												<img border="0" alt="<?=lang('delete_this_row')?>" src='<?=$this->config->item('theme_folder_url')?>cp_themes/default/images/content_custom_tab_delete.png' />

											</a>
										</td>
							 		</tr>
								</tbody>
							</table>
 						<?php endforeach; ?>

					<fieldset id="add_notification">
						<a href="javascript:void(0)" class="ct_add_field_bttn">
							<?=lang('add_another_row')?>
						</a>
					</fieldset>
					
				 	<table class="mainTable padTable" id="notification_blank" style="display:none" border="0" cellspacing="0" cellpadding="0">
						<thead><tr><th colspan="2"></th></tr></thead>
						<tbody>
							<?php foreach ($setting['settings'] as $matrix_setting) : ?>
								<tr>
									<td><strong><?=lang($matrix_setting['name'])?></strong><?=(isset($matrix_setting['note'])) ? '<br />'.lang($matrix_setting['note']) : ''?><td>
								<?=$cartthrob_mcp->plugin_setting($matrix_setting['type'], '', (isset($matrix_setting['default'])) ? $matrix_setting['default'] : '', @$matrix_setting['options'], @$matrix_setting['attributes'])?></td>
								</tr>
							<?php endforeach; ?>
							<tr class="<?php echo alternator('even', 'odd');?>">
								<td colspan="2">
									<a href="#" class="remove_product_table" style="display:block;float:left;margin: 0 0 0 8px;position:data-hashative;top: 9px;">
										<img border="0" alt="<?=lang('delete_this_row')?>" src='<?=$this->config->item('theme_folder_url')?>cp_themes/default/images/content_custom_tab_delete.png' />

									</a>
								</td>
					 		</tr>
						</tbody>
					</table>
					
				<?php else : ?>
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
									<tr class="<?=$setting['short_name']?>_setting" 
										rel = "<?=$setting['short_name']?>" 		
										id="<?=$setting['short_name']?>_setting_<?=$count?>">
										<td><img border="0" src='<?=URL_THIRD_THEMES?>cartthrob/images/ct_drag_handle.gif' width="10" height="17" /></td>
										<?php foreach ($setting['settings'] as $matrix_setting) : ?>
											<td  style="<?=$matrix_setting['style']?>" rel="<?=$matrix_setting['short_name']?>" class="<?=$matrix_setting['short_name'].'_setting_option'?>"><?=$cartthrob_mcp->plugin_setting($matrix_setting['type'], $setting['short_name'].'['.$count.']['.$matrix_setting['short_name'].']', @$current_value[$matrix_setting['short_name']], @$matrix_setting['options'], @$matrix_setting['attributes'])?></td>
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
					
					<fieldset class="plugin_add_new_setting" >
						<a href="#" class="ct_add_matrix_row" rel="settings_template" id="add_new_<?=$setting['short_name']?>">
							<?=lang('add_another_row')?>
						</a>
					</fieldset>
	
					<table style="display: none;" class="<?=$structure['class']?>">
						<tr id="<?=$setting['short_name']?>_blank" class="<?=$setting['short_name']?>">
							<td  ><img border="0" src='<?=URL_THIRD_THEMES?>cartthrob/images/ct_drag_handle.gif' width="10" height="17" /></td>
							
							<?php foreach ($setting['settings'] as $matrix_setting) : ?>
								<td style="<?=$matrix_setting['style']?>"  rel="<?=$matrix_setting['short_name']?>"  class="<?=$matrix_setting['short_name'].'_setting_option'?>"><?=$cartthrob_mcp->plugin_setting($matrix_setting['type'], '', (isset($matrix_setting['default'])) ? $matrix_setting['default'] : '', @$matrix_setting['options'], @$matrix_setting['attributes'])?></td>
							<?php endforeach; ?>
							<td>
								<a href="#" class="remove_matrix_row"><img border="0" src='<?=$this->config->item('theme_folder_url')?>cp_themes/default/images/content_custom_tab_delete.png' /></a>
							</td>
						</tr>
					</table>
				<?php endif; ?>

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
							$current_value = (isset($settings[ $setting['short_name']])) ? $settings[ $setting['short_name']] : FALSE;
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
									<?=$cartthrob_mcp->plugin_setting($setting['type'], $setting['short_name'], $current_value, @$setting['options'], @$setting['attributes'])?>
								</td>
							</tr>
						</tbody>
						</table>
				<?php endif; ?>
			<?php endforeach; ?>
		<?php endif; ?>
 
