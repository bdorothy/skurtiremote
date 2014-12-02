<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
 
<?php echo $form_edit; ?>
	
 	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
		<tbody>
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					 <?=$view['title']?>
 				</td>
				<td style='width:50%;'>
 					<input   type='checkbox' name='delete_order'  value='yes' checked="checked" />  <?=lang('delete_when_checked') ?>
					<input type="hidden" value="<?=$view['entry_id']?>" name="id" /> 
					<input type="hidden" value="view" name="return" /> 
				</td>
 			</tr>
 		</tbody>
	</table>
	<p><input type="submit" name="submit" value="<?=lang('submit')?>" class="submit" /></p>
	
	</form>
 
