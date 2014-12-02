<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
 
<?php foreach ($tax as $key => $value): ?> 
<?php echo $form_edit; ?>
	
 	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
		<thead class="">
			<tr>
				<th colspan="2">
					<strong><?=lang('delete_tax')?>  </strong><br />
					<?=lang('delete_tax_description')?>
					<input  type='hidden' name='id'  value='<?=$value['id']?>' size='90' />
 					
				</th>
			</tr>
		</thead>
		<tbody>
			<tr class="odd">
				<td>
					<?=lang('tax_name') ?>: <?=$value['tax_name']?>
 				</td>
				<td style='width:50%;'>
 					<input   type='checkbox' name='delete_tax'  value='yes' checked="checked" />  <?=lang('delete_if_checked') ?>
				</td>
 			</tr>
 		</tbody>
	</table>
	<p><input type="submit" name="submit" value="<?=lang('submit')?>" class="submit" /></p>
	
	</form>
<?php endforeach; ?>
