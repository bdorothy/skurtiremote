<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

	<?php echo $form_open;  ?> 
 	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
		<thead class="">
			<tr>
				<th colspan="2">
					<strong><?=lang('tax_settings')?></strong><br />
					<?=lang('tax_global_options')?>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr  class="<?=alternator('odd', 'even')?>">
				<td>
					<label><?=lang('tax_use_shipping_address')?></label>
					
				</td>
				<td style='width:50%;'>
					<input class='radio' type='radio' name='tax_use_shipping_address' value='1' <?php if ($settings['tax_use_shipping_address']) : ?>checked='checked'<?php endif; ?> /> 
					<?=lang('yes')?>
					<input class='radio' type='radio' name='tax_use_shipping_address' value='0' <?php if ( ! $settings['tax_use_shipping_address']) : ?>checked='checked'<?php endif; ?> /> 
					<?=lang('no')?>
				</td>
			</tr>
			<tr  class="<?=alternator('odd', 'even')?>">
				<td>
					<label><?=lang('tax_rounding_options')?></label>
					<div class="subtext"><?=lang('tax_rounding_options_note')?></div>
				</td>
				<td style='width:50%;'>
					<input class='radio' type='radio' name='round_tax_only_on_subtotal' value='1' <?php if ($settings['round_tax_only_on_subtotal']) : ?>checked='checked'<?php endif; ?> /> 
					<?=lang('yes')?>
					<input class='radio' type='radio' name='round_tax_only_on_subtotal' value='0' <?php if ( ! $settings['round_tax_only_on_subtotal']) : ?>checked='checked'<?php endif; ?> /> 
					<?=lang('no')?>
				</td>
			</tr>
			<tr  class="<?=alternator('odd', 'even')?>">
				<td>
				    <label><?=lang('tax_choose_a_plugin')?></label>
 				</td>
				<td style='width:50%;'>
				    <select name='tax_plugin' class='plugins' id="select_tax_plugin">
						<?php foreach ($tax_plugins as $plugin) : ?>
							<option value="<?=$plugin['classname']?>" <?php if ($settings['tax_plugin'] == $plugin['classname']) : ?>selected="selected"<?php endif; ?>>
								<?=lang($plugin['title'])?>
							</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
		</tbody>
	</table>
	<?=$this->load->view('plugin_settings', array('settings'=>$settings, 'plugins'=>$tax_plugins, 'plugin_type'=>'tax_plugin'))?>
	<p><input type="submit" name="submit" value="Submit" class="submit" /></p>
	<?=form_close()?>
 
			<?php foreach ($taxes as $key => $value):  
				$count = (!isset($count) ? 1 : $count+=1);
				$class = (($count % 2 != 0) ? "even" : "odd"); 
			 ?>
									
			<?php if ($count == 1): ?>
				<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
					<thead class="">
						<tr>
							<th>
								<strong><?=lang('tax_name')?></strong> 
			 				</th>
							<th>
								<strong><?=lang('tax_percent')?></strong> 
			 				</th>
							<th>
								<strong><?=lang('tax_country')?></strong> 
			 				</th>
							<th>
								<strong><?=lang('tax_state')?></strong> 
			 				</th>
							<th>
								<strong><?=lang('tax_zip')?></strong> 
			 				</th>
							<th colspan="2">
			 					&nbsp;
							</th>
						</tr>
					</thead>
					<tbody>
			
			<?php endif; ?>
			<tr class="<?php echo $class ?>">
				<td>
					<?php echo $value['tax_name']; ?>
				</td>
				<td>
					%<?php echo number_format( (double) $value['percent'], 2, ".", ""); ?>
				</td>
				<td>
					<?php echo $value['country']; ?>
				</td>
				<td>
					<?php echo $value['state']; ?>
				</td>
				<td>
					<?php echo $value['zip']; ?>
				</td>
				<td>
					<a href="<?php echo $edit_href.$value['id']; ?>"><?=lang('edit')?> &raquo;</a>
				</td>
				<td>
					 <a href="<?php echo $delete_href. $value['id'];  ?>"> <?=lang('delete')?> &raquo;</a>
				</td>
			</tr>
				<?php if (count($taxes) == $count):  ?>
						</tbody>
					</table>

					<?php echo $pagination; ?>
				<?php endif; ?>
			<?php endforeach; ?>
			
			<fieldset class="plugin_add_new_setting">
				<a href="<?php echo $add_href;  ?>" class="ct_add_tax_row"><?=lang('tax_add_another_setting')?></a>
			</fieldset>
