<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
 <?php echo $form_edit; ?>
	
	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
		<tbody>
 			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<?=lang('description') ?> 
 				</td>
				<td style='width:50%;'>
 					<input  dir='ltr' type='text' name='description'  value='' size='90' maxlength='100' />
				</td>
 			</tr>

 			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<?=lang('level') ?> 
 				</td>
				<td style='width:50%;'>
 					<input  dir='ltr' type='text' name='level'  value='' size='90' maxlength='100' />
				</td>
 			</tr>
 
 		</tbody>
	</table>

	<input type="hidden" value="add" name="action" /> 
	
	<p><input type="submit" name="submit" value="<?=lang('submit')?>" class="submit" /></p>
	
</form>
 