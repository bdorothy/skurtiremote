<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
	
<?php foreach ($tax as $key => $value): ?> 
	
<?php echo $form_edit; ?>
 	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
		<thead class="">
			<tr>
				<th colspan="2">
					<strong><?=lang('edit_tax')?></strong><br />
 				</th>
			</tr>
		</thead>
		<tbody>
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
				<label><?=lang('tax_name')?></label>
 				</td>
				<td style='width:50%;'>
					<input  type='hidden' name='id'  value='<?=$value['id']?>' size='90' />
				
 					<input  dir='ltr' type='text' name='tax_name'  value='<?=$value['tax_name']?>' size='90' />
				</td>
 			</tr>
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
				<label><?=lang('tax_percent')?></label>
 				</td>
				<td style='width:50%;'>
 					<input  dir='ltr' type='text' name='percent'  value='<?=$value['percent']?>' size='90' maxlength='5' />
				</td>
 			</tr>
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<label><?=lang('tax_country')?></label>
 				</td>
				<td style='width:50%;'>
					<select name="country" class="countries_blank" value="<?=$value['country']?>"></select>
				</td>
			</tr>
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<label><?=lang('tax_state')?></label>
 				</td>
				<td style='width:50%;'>
					<select name="state" class="states_blank" value="<?=$value['state']?>"></select>
				</td>
			</tr>
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<label><?=lang('tax_zip')?></label>
 				</td>
				<td style='width:50%;'>
					<input  dir='ltr' type='text' name='zip'  value='<?=$value['zip']?>' size='90'  />
				</td>
			</tr>
 			<?php /*
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<label><?=lang('tax_special')?></label>
 				</td>
				<td style='width:50%;'>
					<input  dir='ltr' type='text' name='special'  value='<?=$value['special']?>' size='90' />
				</td>
			</tr>
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<label><?=lang('tax_choose_a_plugin')?></label>
 				</td>
				<td style='width:50%;'>
					<select name='plugin' class='plugins' id="select_tax_plugin">
						<?php
 						 foreach ($tax_plugins as $plugin) : ?>
							<option value="<?=$plugin['classname']?>" <?php if ($value['plugin'] == $plugin['classname']) : ?>selected="selected"<?php endif; ?>>
								<?=$plugin['title']?>
							</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			*/ ?> 
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<label><?=lang('tax_shipping')?></label>
 				</td>
				<td style='width:50%;'>
					<?php
						$checked_yes = (($value['shipping_is_taxable'] == '1') ? TRUE : FALSE);
						$checked_no = (($value['shipping_is_taxable'] == '0') ? TRUE : FALSE);
					?>
					<label style="display:block;font-weight:normal;"><?=form_radio('shipping_is_taxable', '1', $checked_yes)?>&nbsp;<?=lang('yes')?>&nbsp;<?=form_radio('shipping_is_taxable', '0', $checked_no)?>&nbsp;<?=lang('no')?></label>
				</td>
			</tr>
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<?=lang('tax_delete') ?> 
 				</td>
				<td style='width:50%;'>
 					<input   type='checkbox' name='delete_tax'  value='yes' /> <?=lang('delete_if_checked') ?>
				</td>
 			</tr>
		</tbody>
	</table>
	<p><input type="submit" name="submit" value="<?=lang('submit')?>" class="submit" /></p>
	</form>
	<?php endforeach; ?>
	